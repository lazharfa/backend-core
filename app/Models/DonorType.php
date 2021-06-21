<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonorType extends Model
{
    protected $fillable = [
    	'donor_id',
		'expired_at',
		'type'
    ];
}
