<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignRealizationDetail extends Model
{
    protected $fillable = [
        'realization_id', 'estimated_name', 'price', 'quantity', 'subtotal', 'date_required', 'creator_id', 'approved_id', 'approved_at'
    ];
}
