<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BenefitRecipient extends Model
{
    protected $fillable = [
        'member_id', 'campaign_id', 'realization_progress_id', 'name', 'address', 'birthday', 'gender'
    ];
}
