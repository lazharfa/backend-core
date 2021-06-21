<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    protected $fillable = [
    	'user_id',
		'email',
		'request',
		'response',
		'activity',
		'notification_sent'
    ];
}
