<?php

namespace App\Console\Commands;

use App\Jobs\SendDonationReceived;
use App\Models\Donation;
use Illuminate\Console\Command;

class TellDonors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tell:donors';

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

        $donations = Donation::query();

        if (env('SEND_SMS', false)) {
            $donations = $donations->orWhere(function ($query) {
                $query->whereNotNull('donor_phone')->whereNull('message_sent_at')->whereNotNull('total_donation')->whereNull('staff_id');
            });
        }

        if (env('SEND_EMAIL', false)) {
            $donations = $donations->orWhere(function ($query) {
                $query->whereNotNull('donor_email')->whereNull('email_sent_at')->whereNotNull('total_donation')->whereNull('staff_id');
            });
        }

        $donations = $donations->get();

        foreach ($donations as $donation) {

            SendDonationReceived::dispatchNow($donation);

        }
    }

}
