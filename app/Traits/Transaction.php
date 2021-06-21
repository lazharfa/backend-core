<?php

namespace App\Traits;

use App\Veritrans\Midtrans;
use Exception;
use GuzzleHttp\Client;
use App\Models\FaspayNotification;

trait Transaction
{
    protected function getSnapToken($donation, $bank_number)
    {
        try {
            Midtrans::$serverKey = env('SERVER_KEY_VERITRANS');

            //set Veritrans::$isProduction  value to true for production mode
            Midtrans::$isProduction = env('PRODUCTION_VERITRANS', false);

            $donor = $donation->donor()->select('full_name', 'phone_number')->first();
            $campaignTitle = $donation->campaign ? $donation->campaign->campaign_title : 'Donasi Umum';

            $transaction_details = [
                'order_id' => $donation->donation_number,
                'gross_amount' => $donation->donation
            ];

            $customer_details = [
                'first_name'    => $donor->full_name,
                'email'         => $donation->donor_email,
                'phone'         => $donation->donor_phone
            ];

            $item_details = [
                [
                    'id' => 1,
                    'quantity' => 1,
                    'name' => substr($campaignTitle, 0, 47) . "...",
                    'price' => $donation->donation
                ]
            ];

            $transaction_data = [
                'customer_details' => $customer_details,
                'transaction_details' => $transaction_details,
                'item_details' => $item_details
            ];

            if ($bank_number) {
                $transaction_data['enabled_payments'] = [$bank_number];
            }

            $midtrans = new Midtrans;

            return [$midtrans->getSnapToken($transaction_data), null];
        } catch (Exception $exception) {
            return [null, $exception->getMessage()];
        }
    }

    protected function faspayPayment($donation, $payment_channel, $donor, $campaign)
    {
        $donation_number = $donation->donation_number;
        
        $signature = md5(env('FASPAY_USER_ID') . env('FASPAY_PASSWORD') . $donation_number);

        $json = [
            'request' => 'Donasi',
            'merchant_id' => env('FASPAY_MERCHANT_ID'),
            'merchant' => env('FASPAY_MERCHANT_NAME'),
            'bill_no' => $donation_number,
            'bill_date' => date('Y-m-d H:i:s', strtotime($donation->created_at . ' + 7 hours')),
            'bill_expired' => date('Y-m-d H:i:s', strtotime($donation->created_at . ' + 29 days')),
            'bill_desc' => 'Donasi',
            'bill_currency' => 'IDR',
            'bill_total' => $donation->donation . '00',
            'payment_channel' => $payment_channel,
            'pay_type' => 1,
            'cust_no' => $donation->donor_id,
            'cust_name' => $donor->full_name,
            'msisdn' => $donor->phone_number,
            'email' => $donor->email,
            'terminal' => 10,
            'item' => [
                [
                    "product" => $campaign ? $campaign->campaign_title : $donation->type_donation,
                    "qty" => "1",
                    "amount" => $donation->donation . '00',
                    "payment_plan" => "01",
                    "merchant_id" => "99999",
                    "tenor" => "00"
                ]
            ],
            'signature' => hash('sha1', $signature)
        ];

        try {
            FaspayNotification::create([
                'code'          => $donation_number,
                'status'        => 'payload',
                'responses'     => json_encode($json)
            ]);
            
            $client = new Client();

            $res = $client->request('POST', env('FASPAY_URL'), [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $json
            ]);

            $response = json_decode($res->getBody());
            return $response->redirect_url;
        } catch (Exception $e) {
            // echo $e->getMessage();
        }
    }
}
