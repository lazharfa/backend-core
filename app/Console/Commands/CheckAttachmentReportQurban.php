<?php

namespace App\Console\Commands;

use App\Models\QurbanOrder;
use Illuminate\Console\Command;

class CheckAttachmentReportQurban extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     *
     */
    protected $signature = 'report-attachment:check';

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
        $files = collect(scandir(storage_path('app/public/qurban-report/source')));

        $grouped = $files->filter(function ($item) {
            return count(explode("-", $item)) == 3;
        })->groupBy(function ($fileName) {

            $splitted = explode("-", $fileName);

            if (count($splitted) == 3) {
                return $splitted[1];
            }

            return null;

        });

        foreach ($grouped as $key => $item) {

            $qurbanOrder = QurbanOrder::find($key);

            if ($qurbanOrder) {
                $qurbanOrder->update([
                    'qurban_attachments' => $item
                ]);
            }

        }

    }
}
