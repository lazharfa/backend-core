<?php

namespace App\Console\Commands;

use App\Utils\Telegram;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BalanceZenziva extends Command
{
    const zenzivaV1 = [
        'insanbumimandiri.org' => true,
        'rumahasuh.org' => true,
        'pesantrenquran.org' => true
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'balance-zenziva';

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
        try {
            $client = new Client();

            if (isset(self::zenzivaV1[env('APP_MEMBER')])) {

                Log::debug("Check balance with zenziva v1");

                $queryString = implode('&', [
                    'userkey=' . env('ZENZIVA_USER'),
                    'passkey=' . env('ZENZIVA_PASSWORD')
                ]);

                $response = $client->get("https://masking.zenziva.net/api/balance?$queryString");

                $response = json_decode($response->getBody(), true);

                $appMember = env('APP_MEMBER');

                $textNotification = "Balance Zenziva {$appMember} sisa : {$response["credit"]}";

                $credit = (double) str_replace(',','', $response['credit']);

                if ($credit < 2000) {

                    Telegram::send($textNotification, '-520057347');

                }

            } else {

                Log::debug("Check balance with zenziva v2");

                $queryString = implode('&', array(
                    'userkey=' . env('ZENZIVA_USER'),
                    'passkey=' . env('ZENZIVA_PASSWORD')
                ));

                $response = $client->get("https://console.zenziva.net/api/balance?$queryString");

                $response = json_decode($response->getBody(), true);

                $appMember = env('APP_MEMBER');

                $textNotification = "Balance Zenziva {$appMember} sisa : {$response["balance"]}";

                $balance = (double) str_replace(',', '', $response['balance']);

                if ($balance < 250000) {

                    Telegram::send($textNotification, '-520057347');

                }

            }

        } catch (Exception | GuzzleException $e) {

            Log::error($e->getMessage());

        }
    }
}
