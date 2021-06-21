<?php

namespace App\Console\Commands;

use App\Jobs\PushCampaignToBalans;
use App\Models\Campaign;
use App\Utils\Curl;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncCampaignToBalans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'balans:sync-campaign';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start';


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
        $campaigns = Campaign::query()->whereNull('push_to_balans_at')
            ->whereNotNull('code')->get();

        foreach ($campaigns as $campaign) {
            PushCampaignToBalans::dispatchNow($campaign);
        }

    }
}
