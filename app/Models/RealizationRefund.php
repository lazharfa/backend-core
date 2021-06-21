<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RealizationRefund extends Model
{
    protected $fillable = [
        'campaign_id', 'campaign_realization_id', 'total_refund', 'receiver_id', 'received_at', 'creator_id'
    ];

    public function scopeOfMember($query, $member)
    {
        return $query->where('member_id', $member);
    }

}
