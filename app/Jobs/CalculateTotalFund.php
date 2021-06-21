<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class CalculateTotalFund implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $campaignId = null;

    /**
     * Create a new job instance.
     *
     * @param int $campaignId
     * @return void
     */
    public function __construct(int $campaignId)
    {
        $this->campaignId = $campaignId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $campaign = Campaign::find($this->campaignId);
        $total_fund = Donation::where('campaign_id', $this->campaignId)->whereNotNull('total_donation')->select(DB::raw('sum(total_donation)'))->first()->sum;

        $donation_percentage = (($total_fund > 0) && ($campaign->target_donation > 0)) ? round(($total_fund / $campaign->target_donation * 100)) : 0;

        $campaign->update(compact('total_fund', 'donation_percentage'));
    }
}
