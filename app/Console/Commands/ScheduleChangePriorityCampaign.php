<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use Illuminate\Console\Command;

class ScheduleChangePriorityCampaign extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaign:change-priority';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change priority when expired';

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

        $dateNow = now()->addHour(7)->toDateString();

        Campaign::where('priority', '!=', 3)->where('expired_at', '<=', $dateNow)->update([
            'priority' => 3
        ]);

    }
}
