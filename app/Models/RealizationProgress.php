<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RealizationProgress extends Model
{
    protected $fillable = [
        'campaign_id', 'realization_id', 'benefit_recipient_id', 'beneficiaries', 'female_beneficiaries', 'male_beneficiaries', 'average_age', 'distance_from_city', 'time_from_city', 'total_population', 'majority_work', 'majority_religion', 'description_progress', 'progress_date', 'updater_id', 'creator_id'
    ];

    public function scopeOfMember($query, $member)
    {
        return $query->where('member_id', $member);
    }

}
