<?php
/**
 * Created by PhpStorm.
 * User: coder
 * Date: 03/05/19
 * Time: 14:03
 */

namespace App\Transformers;


use Carbon\Carbon;

class Date
{

    public static function ChangeTimezone($timezone, $date)
    {

        $date = Carbon::parse($date);

        if ($timezone > 0) {

            $date = $date->addHours($timezone);

        } elseif ($timezone < 0) {

            $date = $date->subHours($timezone);

        }

        return $date->toDateTimeString();

    }
}
