<?php

namespace App\Console\Commands;

use App\Jobs\PushDonationToBalans;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncDonationToBalans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'balans:sync-donation';

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
        Log::debug("get all donations");
        $payments = Payment::query()->whereNotNull('donation_id')->whereNull('push_to_balans_at')
            ->whereIn('bank_id', explode(',', env('BALANS_PUSH_BANK', '1,2,3,4,5')))->get();

        Log::debug("start push donation to balans");
        foreach ($payments as $payment) {
            PushDonationToBalans::dispatchNow($payment->donation);
        }
    }
}
