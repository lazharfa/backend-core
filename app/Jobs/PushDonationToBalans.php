<?php

namespace App\Jobs;

use App\Models\Donation;
use App\Models\Payment;
use App\Utils\Curl;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PushDonationToBalans implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $donation;
    protected $paymentAt;

    /**
     * Create a new job instance.
     *
     * @param Donation $donation
     * @param null $paymentAt
     */
    public function __construct(Donation $donation, $paymentAt = null)
    {
        $this->donation = $donation;

        if ($paymentAt) $this->paymentAt = $paymentAt;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            $host = env('BALANS_HOST', 'http://api.balans.id/api/auth/');

            $client = new Client([
                'base_uri' => $host,
                'timeout' => 0,
                'allow_redirects' => false,
                'http_errors' => false,
            ]);

            if (!Cache::get('token-balans')) {

                $dataLogin = array('email' => env('BALANS_EMAIL'), 'password' => env('BALANS_PASS'));

                $resultLogin = $client->post('login', ['json' => $dataLogin]);

                Log::debug($resultLogin->getBody());

                $resLogin = json_decode($resultLogin->getBody(), true);

                Log::error($resLogin['data']['token']);

                Cache::forever('token-balans', $resLogin['data']['token']);
            }

            $header = array(
                "Authorization" => "Bearer " . Cache::get('token-balans'),
                "Content-Type" => "application/json",
                "Accept" => "application/json"
            );

            $donation = $this->donation;

            $coaPenerimaan = '402.01.000.000';

            if (strpos($donation->note, 'iklan')) {
                $channelName = 'Iklan';
            } elseif ($donation->channel_source == 'adaide') {
                $channelName = 'Iklan';
            } elseif (strpos($donation->note, 'email')) {
                $channelName = 'Email';
            } elseif ($donation->staff_id) {
                $channelName = 'CR';
            } elseif ($donation->channel_medium) {
                $channelName = $donation->channel_medium;
            } elseif (!$donation->channel_source) {
                $channelName = 'Web';
            } else {
                $channelName = 'Web';
            }

            if ($donation->campaign) {
                $coaPenerimaan = $donation->campaign->category->coa_receivable;
                $description = "Penerimaan Proram {$donation->campaign->campaign_title} / {$donation->donor_name}";
            } else {
                $description = "Penerimaan Proram {$donation->type_donation} / {$donation->donor_name}";
            }

            $payments = $donation->payment()->whereNull('push_to_balans_at')->get();

            foreach ($payments as $payment) {

                $transactionDate = $payment->payment_at->addHours(7)->format('Y-m-d H:i:s');

                if ($this->paymentAt) {
                    $transactionDate = $this->paymentAt;
                }

                $transaction = [
                    [
                        "program_code" => $donation->campaign ? $donation->campaign->code : "SU",
                        "program_price_per_qty" => $this->paymentAt ? $donation->total_donation : $payment->total_payment,
                        "program_total_qty" => 1,
                        "coa_penerimaan" => $coaPenerimaan,
                        "program_trs_desc" => $description,
                        "coa_bank" => $payment->bank->coa_code
                    ]
                ];

                $dataTransaction = array(
                    'id_org' => env('BALANS_ORG'),
                    'donor_identity' => $donation->donor->donor_identity,
                    'donor_name' => $donation->donor_name,
                    'code_transaction' => "{$donation->donation_number}-{$payment->id}",
                    'transaction_via' => '',
                    'type_transaction' => '2',
                    'transaction_date' => $transactionDate,
                    'transactions' => json_encode($transaction),
                    'channel_name' => $channelName
                );

                Log::debug("Transaction Code : {$donation->id}-{$payment->id} ");
                Log::debug($dataTransaction);

                $resultPost = $client->post('transaction/create-by-coa', ['json' => $dataTransaction, 'headers' => $header]);

                Log::debug("Status Code " . $resultPost->getStatusCode());
                Log::debug("Status Body " . $resultPost->getBody());

                if ($resultPost->getStatusCode() >= 400) {

                    if ($resultPost->getStatusCode() == 401) {
                        Cache::forget('token-balans');
                    }

                    continue;
                }

                $donation->payment()->update(['push_to_balans_at' => now()]);

            }

        } catch (Exception $exception) {

            Log::error("Error Push Donation to Balans");
            Log::error($exception);
        }
    }
}
