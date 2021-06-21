<?php

namespace App\Console\Commands;

use App\Models\QurbanOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CheckReportQurbanVideo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report-video:check';

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
        QurbanOrder::whereNotNull('video_available_at')->whereNull('send_report_at')->update([
            'video_available_at' => null
        ]);

        $files = File::allFiles(storage_path("app/public/qurban-report/videos/"));

        foreach ($files as $file) {

            $fileName = $file->getFilename();
            $fileName = explode(".", $fileName);

            $orderId = explode("-", $fileName[0])[1];

            $qurbanOrder = QurbanOrder::find($orderId);

            if ($qurbanOrder) {
                $qurbanOrder->update([
                    'video_available_at' => now()
                ]);
            }

        }

    }
}
