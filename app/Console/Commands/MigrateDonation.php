<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateDonation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:donation';

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
     * @return mixed
     */
    public function handle()
    {

        DB::beginTransaction();

        $donations = DB::connection('mysql')->table('transaksi')
            ->select('page_content.title as campaign_title',
                'transaksi.donasi as donation',
                'transaksi.unique_id as unique_value',
                'transaksi.bank_id',
                'transaksi.name as donor_name',
                'transaksi.email as donor_email',
                'transaksi.telepon as donor_phone',
                'transaksi.status as status_donation',
                'transaksi.created_at as created_at',
                'user.email as email_staff',
                'transaksi.anonim as anonymous',
                'tipe_donasi as type_donation',
                'transaksi.description as note'
            )
            ->leftJoin('page_content', 'transaksi.page_id', '=', 'page_content.page_id')
            ->leftJoin('user', 'transaksi.created_by', '=', 'user.user_id')
            ->get();

        foreach ($donations as $donation) {

            $campaignId = null;
            $totalDonation = null;
            $expiredAt = date('Y-m-d H:i:s', strtotime($donation->created_at . ' +1 day'));;
            $bankId = null;
            $staffId = null;
            $verifiedAt = null;

            if ($donation->status_donation == 1) {

                $totalDonation = $donation->donation + $donation->unique_value;
                $verifiedAt = $donation->created_at;

            }

            if ($donation->campaign_title) {

                $campaignId = DB::connection('pgsql')
                    ->table('campaigns')
                    ->select('id')
                    ->where('campaign_title', $donation->campaign_title)
                    ->get()->toArray();


                if (count($campaignId) > 0) {

                    $campaignId = $campaignId[0]->id;

                } else {

                    $campaignId = null;

                }

            }

            if ($donation->bank_id) {

                switch ($donation->bank_id) {

                    case 6 :
                        $bankId = 5;
                        break;
                    case 7:
                        $bankId = 6;
                        break;
                    case 0:
                        $bankId = null;
                        break;
                    default:
                        $bankId = $donation->bank_id;

                }

            }

            if ($donation->email_staff) {

                $staffId = DB::connection('pgsql')
                    ->table('users')
                    ->select('id')
                    ->where('email', $donation->email_staff)
                    ->get()->toArray();

                if (count($staffId) > 0) {

                    $staffId = $staffId[0]->id;

                } else {

                    $staffId = null;

                }


            }

            $dataDonation = [
                'member_id' => 'insanbumimandiri.org',
                'campaign_id' => $campaignId,
                'date_donation' => $donation->created_at,
                'expired_at' => $expiredAt,
                'donation' => $donation->donation,
                'unique_value' => $donation->unique_value,
                'total_donation' => $totalDonation,
                'bank_id' => $bankId,
                'donor_name' => $donation->donor_name,
                'donor_phone' => $donation->donor_phone,
                'donor_email' => $donation->donor_email,
                'anonymous' => $donation->anonymous,
                'staff_id' => $staffId,
                'verified_at' => $verifiedAt,
                'note' => $donation->note,
                'created_at' => $donation->created_at,
                'updated_at' => $donation->created_at,
            ];


            DB::connection('pgsql')
                ->table('donations')
                ->insert($dataDonation);

        }

        DB::commit();

        return $donations->count();

    }
}
