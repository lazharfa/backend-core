<?php


namespace App\Utils;


class Telegram
{

    public static function send($text, $chatId)
    {
        $botToken = 'bot' . env('BOT_TOKEN');

        $uri = "https://api.telegram.org/" . $botToken . "/sendMessage?chat_id=". $chatId . "&text=" . urlencode($text);

        return Curl::get($uri);

    }

    public static function loginSend($text, $chatId)
    {
        $botToken = 'bot' . env('BOT_TOKEN');

        $uri = "https://api.telegram.org/" . $botToken . "/sendMessage?chat_id=-321278752&text=" . urlencode($text);

        return Curl::get($uri);

    }

}
