<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappWorker extends Model
{
    public $incrementing = false;

    public $primaryKey = 'worker_name';

    protected $fillable = [
        'worker_name',
        'worker_status',
        'last_check',
        'next_check'
    ];

    protected $dates = [
        'last_check',
        'next_check'
    ];

}
