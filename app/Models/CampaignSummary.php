<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignSummary extends Model
{
    protected $fillable = [
        'summary_name', 'summary_sum'
    ];
}
