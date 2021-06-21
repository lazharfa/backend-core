<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignSuggest extends Model
{
    protected $fillable = [
        'member_id', 'campaign_type', 'suggest'
    ];

    protected $casts = [
        'suggest' => 'json'
    ];

    public function scopeOfMember($query, $member)
    {
        return $query->where('member_id', $member);
    }

}
