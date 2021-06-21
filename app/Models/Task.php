<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Task extends Model
{

    protected $fillable = [
        'member_id', 'task_number', 'task_category', 'task_type', 'file_url', 'task_deadline', 'description', 'creator_id', 'updater_id'
    ];

    public function scopeOfMember($query, $member)
    {
        return $query->where('member_id', $member);
    }

    public static function taskNumber()
    {
        $code = 'TK/';
        $dateNow = date("ymd");
        $task = Task::where('task_number', 'like', $code . $dateNow . '%')->orderBy('task_number', 'desc')->first();

        $number = 0;
        if ($task) {
            $number = substr($task->task_number, -3);
        }

        $number = $number + 1;
        $number = strval(str_pad($number, 3, '0', STR_PAD_LEFT));
        return $code . $dateNow . $number;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->task_number = self::taskNumber();
            $model->creator_id = Auth::id();
        });

        static::updating(function ($model) {
            $model->updater_id = Auth::id();
        });

    }

}
