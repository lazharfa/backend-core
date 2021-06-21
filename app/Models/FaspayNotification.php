<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaspayNotification extends Model
{
    protected $fillable = [
    	'code',
		'status',
		'responses',
		'donation_id'
    ];
}
