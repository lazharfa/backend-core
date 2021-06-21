<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValidationEmail extends Model
{
    protected $fillable = [
        'name',
        'email',
        'start_at',
        'done_at',
        'status'
    ];

    protected $dates = [
        'start_at',
        'done_at'
    ];
}
