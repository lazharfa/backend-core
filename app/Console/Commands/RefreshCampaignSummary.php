<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Models\CampaignSummary;
use App\Models\Donation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshCampaignSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaign:refresh-summary';

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

        $campaigns = Campaign::where('campaign_status', 'Publish')->get()->count() + env('TOTAL_CAMPAIGN', 0);
        $donors = Donation::select('donor_id')->whereNotNull('total_donation')->distinct()->get()->count() + env('TOTAL_DONOR', 0);
        $benefitRecipients = env('BENEFIT_RECIPIENT', 0);

        CampaignSummary::updateOrCreate(
            ['summary_name' => 'campaigns'],
            ['summary_sum' => $campaigns]
        );

        CampaignSummary::updateOrCreate(
            ['summary_name' => 'donors'],
            ['summary_sum' => $donors]
        );

        CampaignSummary::updateOrCreate(
            ['summary_name' => 'benefit_recipients'],
            ['summary_sum' => $benefitRecipients]
        );

    }
}
