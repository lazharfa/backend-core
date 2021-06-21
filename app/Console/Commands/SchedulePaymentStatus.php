<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Utils\Telegram;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SchedulePaymentStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:check-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule to check status payment';

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

        $chatId = env('CHAT_ID');

        $listBank = [
            'Mandiri' => 1,
            'BCA' => 2,
            'BNI' => 3,
            'BSM' => 4,
            'BRI' => 5,
        ];

        foreach ($listBank as $bankName => $bankId) {

            $payment = Payment::where('bank_id', $bankId)->latest()->first();

            if ($payment) {

                if ($payment->created_at < now()->subHour(1)) {

                    $text = 'Halo Gaes, Bank ' . $bankName . ' member ' .  env('APP_MEMBER') . ' Sedang ganguan. Notif terakhir pada ' . Carbon::parse($payment->created_at)->addHour(7);

                    Telegram::send($text, $chatId);
                    Telegram::send($text, '519051964'); // Azwar Nur Patriosa
                    Telegram::send($text, '287486373'); // Tanti Isyka Rafatullah

                }

            }

        }


    }
}
