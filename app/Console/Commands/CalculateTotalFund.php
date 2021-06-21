<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Models\Donation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CalculateTotalFund extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate-total-fund';

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
        $donations = Donation::whereNotNull('total_donation')->whereNotNull('campaign_id')
            ->select('campaign_id', DB::raw('sum(total_donation) as total_fund'))
            ->groupBy('campaign_id')->get();

        foreach ($donations as $donation) {
            $campaign = Campaign::find($donation->campaign_id);

            $total_fund = $donation->total_fund;
            $donation_percentage = (($total_fund > 0) && ($campaign->target_donation > 0)) ? round(($total_fund / $campaign->target_donation * 100)) : 0;

            $campaign->update(compact('total_fund', 'donation_percentage'));

        }
    }
}
