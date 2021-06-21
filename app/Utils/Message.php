<?php

namespace App\Utils;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class Message
{
    const zenzivaV1 = [
        'insanbumimandiri.org' => true,
        'rumahasuh.org' => true,
        'pesantrenquran.org' => true
    ];

    public static function send($text, $phone)
    {

        try {
            $client = new Client();

            if (isset(self::zenzivaV1[env('APP_MEMBER')])) {

                Log::debug("Send message with zenziva v1");

                $queryString = implode('&', [
                    'userkey=' . env('ZENZIVA_USER'),
                    'passkey=' . env('ZENZIVA_PASSWORD'),
                    'nohp=' . $phone,
                    'res=json',
                    'pesan=' . urlencode($text)
                ]);

                $response = $client->get("https://alpha.zenziva.net/apps/smsapi.php?$queryString");

                $body = $response->getBody();

                Log::debug($body);

                $response = json_decode($response->getBody(), true);

                Log::debug($response);

            } else {

                Log::debug("Send message with zenziva v2");

                $payloads = array(
                    'userkey' => env('ZENZIVA_USER'),
                    'passkey' => env('ZENZIVA_PASSWORD'),
                    'to' => $phone,
                    'message' => $text
                );

                $client->post("https://console.zenziva.net/masking/api/sendsms", ['json' => $payloads]);

            }

        } catch (Exception | GuzzleException $e) {

            Log::error($e->getMessage());

        }
    }

    public static function sendWhatsappMessage($phone, $broadcast, $message, $donation_number=null)
    {

        try {
            $whatsapp_url = env('WHATSAPP_URL');
            $whatsapp_worker = env('WHATSAPP_WORKER');
            $member = env('APP_MEMBER');

            if ($whatsapp_url && $whatsapp_worker) {
                $client = new Client();

                $body = [
                    "priority" => 0,
                    "worker" => $whatsapp_worker,
                    "broadcast" => $broadcast,
                    "message" => $message,
                    "phone" => $phone,
                    "callback"  => "https://core.{$member}/api/donation/notification?donation_number={$donation_number}&type={$broadcast}",
                ];

                if ($donation_number) {
                    $body['donation_number'] = $donation_number;
                }

                $client->request('POST', "{$whatsapp_url}/api/chat/notification", [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $body
                ]);
            }

        } catch (Exception | GuzzleException $e) {
            Log::error($e->getMessage());
            throw $e;
        }
    }

}
