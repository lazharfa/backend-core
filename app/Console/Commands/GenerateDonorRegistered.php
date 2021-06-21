<?php

namespace App\Console\Commands;

use App\Models\Donation;
use App\Models\User;
use Illuminate\Console\Command;

class GenerateDonorRegistered extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:donor-registered';

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
        $donors = User::query()->whereNull('registered_at')->get();

        print_r("successfully get {$donors->count()} donors.");
        print_r("\r\n");

        foreach ($donors as $donor) {
            print_r("find donor id {$donor->id} donors.");
            print_r("\r\n");
            $donation = Donation::query()->whereNotNull('total_donation')
                ->where('donor_id', $donor->id)->orderBy('date_donation')->first();

            if ($donation) {

                print_r("found first donation {$donation->donation_number} at {$donation->date_donation->addHours(7)}.");
                print_r("\r\n");
                $donor->update(['registered_at' => $donation->date_donation->addHours(7)->format('Y-m-d')]);
            }
        }

        print_r("finish all");
        print_r("\r\n");

    }
}
