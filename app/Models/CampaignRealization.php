<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignRealization extends Model
{
    protected $fillable = [
        'member_id', 'realization_number', 'realization_status', 'campaign_id', 'supervisor_id', 'director_id', 'creator_id', 'total', 'total_approve'
    ];

    public function scopeOfMember($query, $member)
    {
        return $query->where('member_id', $member);
    }

    public static function realizationNumber()
    {
        $code = 'RLZ/';
        $dateNow = date("ymd");
        $campaignRealization = CampaignRealization::where('realization_number', 'like', $code . $dateNow . '%')->orderBy('realization_number', 'desc')->first();

        $number = 0;
        if ($campaignRealization) {
            $number = substr($campaignRealization->realization_number, -3);
        }

        $number = $number + 1;
        $number = strval(str_pad($number, 3, '0', STR_PAD_LEFT));
        return $code . $dateNow . $number;
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function director()
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function details()
    {
        return $this->hasMany(CampaignRealizationDetail::class, 'realization_id');
    }

    public function progresses()
    {
        return $this->hasMany(RealizationProgress::class, 'realization_id');
    }
}
