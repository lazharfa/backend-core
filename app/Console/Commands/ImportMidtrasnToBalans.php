<?php

namespace App\Console\Commands;

use App\Imports\BalansDisbursement;
use App\Imports\ChatsImport;
use App\Imports\ImportMidtransPencairan;
use App\Jobs\PushDonationToBalans;
use App\Models\Donation;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ImportMidtrasnToBalans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'balans:import-penerimaan';

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
        foreach (File::allFiles(public_path('balans/')) as $file) {
            $items = Excel::toCollection(new ImportMidtransPencairan, $file);


            Log::debug($file->getFilename());


            $paymentAt = str_replace('.xlsx', '', $file->getFilename());

            $paymentAt = Carbon::createFromFormat('m-d-Y H:i:s', "$paymentAt-2021 13:00:00");

            Log::debug($paymentAt);

            $donationNumbers = $items->pluck('order');

            $donations = Donation::query()->whereIn('donation_number', $donationNumbers)->get();

            foreach ($donations as $donation) {

                $filtered = $items->filter(function ($value) use ($donation) {
                    return $value->order == $donation->donation_number;
                });

                $donation->total_donation = $filtered->merchant_has;

                PushDonationToBalans::dispatchNow($donation, $paymentAt);
            }
        }
    }
}
