<?php

namespace App\Console\Commands;

use App\Models\QurbanOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CheckReportQurbanImage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

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
        $files = File::allFiles(storage_path("app/public/qurban-report/images/"));

        foreach ($files as $file) {

            $fileName = str_replace(".jpg", "", $file->getFilename());
            $fileName = explode("-", $fileName);

            $orderId = $fileName[1];

            if (isset($fileName[2])) {
                $orderId = $fileName[2];
            }

            $qurbanOrder = QurbanOrder::find($orderId);

            if ($qurbanOrder) {
                $qurbanOrder->update([
                    'image_available_at' => now()
                ]);
            }

        }
    }
}
