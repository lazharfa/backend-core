<?php

namespace App\Console\Commands;

use App\Models\WhatsappJob;
use App\Models\WhatsappWorker;
use App\Utils\Telegram;
use Exception;
use Illuminate\Console\Command;

class WhatsappWorkerChecker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checker:whatsapp-worker';

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
     * @throws Exception
     */
    public function handle()
    {

        $whatsappWorkers = WhatsappWorker::all();

        foreach ($whatsappWorkers as $whatsappWorker) {

            if ($whatsappWorker->next_check->diffInHours($whatsappWorker->last_check) >= 3) {

                $whatsappWorker->delete();

            } elseif ($whatsappWorker->next_check < now()) {

                $whatsappWorker->update([
                    'worker_status' => 'Down',
                    'next_check' => now()->addHours(1)
                ]);

                $text = 'Worker ' . $whatsappWorker->worker_name . ' is down.';

                Telegram::send($text, env("CHAT_ID")); // Singham Lagatari
                Telegram::send($text, '519051964'); // Azwar Nur Patriosa
                Telegram::send($text, '287486373'); // Tanti Isyka Rafatullah

            }

            WhatsappJob::where('job_end_at', '>=', now()->subHour(10))->where('job_status', 'Failed')->update([
                'job_start_at' => null,
                'job_end_at' => null,
                'job_status' => 'On Queue'
            ]);

        }
    }
}
