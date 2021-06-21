<?php

namespace App\Console\Commands;

use App\Models\Donation;
use App\Models\DonorType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class GenerateDonorType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:donor-type';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        # Tipe donatur :
        # 1. platinum (1 tahun >=20 juta),
        # 2. loyal (>3x donasi setahun),
        # 3. Retensi —> Donasi <=3x selama 1 tahun
        #    Retensi -> Tidak Donasi selama sehatun
        #    Retensi -> DE yang tidak berdonasi tahun tsb < 3 tahun (maks 3 tahun tidak berdonasi)
        # 4. pasif -> donatur namun tidak pernah berdonasi lagi selama 3 tahun
        # 5. prospek/pasif -> cuma ICO tapi ga purchase,

        $year = now()->subYear()->year;

        $donors = User::query()->whereYear('registered_at', '<=', $year)
            ->leftJoin('donor_types', function ($join) use ($year) {

                $year += 1;
                $join->on('users.id', '=', 'donor_types.donor_id')
                    ->where('donor_types.expired_at', '=', "$year-12-31") ;
            })
            ->whereNull('donor_types.id')
            ->orderBy('registered_at')->orderBy('users.id')
            ->select('users.*')
            ->get();

        $totalDonors = $donors->count();

        foreach ($donors as $key => $donor) {

            print_r("Execute $key/$totalDonors.");
            print_r("\r\n");

            $expiredAt = Carbon::parse("$year-12-31")->lastOfYear();
            $startOfYear = $expiredAt->copy()->startOfYear();
            $type = null;

            /** donation in $year **/
            $donations = Donation::query()->where('date_donation', '>=', $startOfYear->format('Y-m-d'))
                ->where('date_donation', '<=', $expiredAt->format('Y-m-d'))
                ->where('donor_id', $donor->id)->get();

            # Total donation >= 20.000.000
            if ($donations->sum('total_donation') >= 20000000) {
                $type = 'Platinum';
            } elseif (
                $donations->filter(function ($donation) {
                    return $donation->total_donation != null;
                })->count() > 3
            ) {
                $type = 'Loyal';
            } elseif (
                //checkout without transfer
                $donations->filter(function ($donation) {
                    return $donation->total_donation != null;
                })->count() == $donations->count()
            ) {
                $type = 'Prospect';
            } else {

                if (
                    $donations->filter(function ($donation) {
                        return $donation->total_donation != null;
                    })->count() <= 3
                ) {
                    $type = 'Retention';
                } else {
                    /** donation before $year **/
                    $lastDonations = Donation::query()->latest()->first();

                    if (!$lastDonations->filter(function ($donation) {
                        return $donation->total_donation;
                    })) {
                        $type = 'Passive';
                    } else {

                        if ($lastDonations->date_donation->diffInYears(now()) > 3) {
                            $type = 'Passive';
                        } else {
                            $type = 'Retention';
                        }

                    }
                }

            }

            DonorType::firstOrCreate(
                [
                    'donor_id' => $donor->id,
                    'expired_at' => $expiredAt->addYear()
                ],
                [
                    'type' => $type,
                ]);

        }

        print_r("All is done.");
    }
}
