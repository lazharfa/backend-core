<?php

namespace App\Utils;


use Exception;
use Illuminate\Support\Facades\Log;

class Curl
{

    public static function get($url, $header = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HTTP200ALIASES, (array)400);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 70);
        curl_setopt($ch, CURLOPT_TIMEOUT, 70);
        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        return [
            $result,
            $err
        ];
    }

    public static function post(string $url, array $dataString, $header = array())
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FAILONERROR, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_HTTP200ALIASES, (array)400);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 70);
            curl_setopt($ch, CURLOPT_TIMEOUT, 70);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            Log::debug("response code : " . $httpCode);
            Log::debug("response result : " . $result);

            if ($httpCode >= 401) {
                return [
                    null,
                    "token invalid"
                ];
            }

            $err = curl_error($ch) . curl_errno($ch);

            curl_close($ch);

            return [
                $result,
                $err
            ];

        } catch (Exception $exception) {
            return [
                null,
                $exception->getMessage()
            ];
        }
    }

    public function put($url, $dataString, $header = array())
    {
        $header = array_merge(array(
            'Content-Type: application/json'
        ), $header);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        $result = curl_exec($ch);
        Log::debug("response result : " . $result);
        return $result;
    }

    public function delete($url, $dataString, $header = array())
    {

        $header = array_merge(array(
            'Content-Type: application/json'
        ), $header);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        $result = curl_exec($ch);
        Log::debug("response result : " . $result);
        return $result;
    }

}
