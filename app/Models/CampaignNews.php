<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CampaignNews extends Model
{
    use SoftDeletes;

    public $casts = [
        'news_media' => 'json'
    ];

    protected $fillable = [
        'member_id',
        'campaign_id',
        'news_title',
        'news_title_en',
        'news_content',
        'news_content_en',
        'news_date',
        'news_slug',
        'news_slug_en',
        'category_id',
        'news_image',
        'news_media',
        'sent_at',
        'creator_id',
        'updater_id',
        'deleted_at'
    ];

    protected $appends = ['category_campaign'];

    public function scopeOfMember($query, $member)
    {
        return $query->where('member_id', $member);
    }


    public function getCategoryCampaignAttribute()
    {
        return Category::where('id', function ($query) {
            $query->from('campaigns')->select('category_id')
                ->where('id', $this->campaign_id);
        })->first();
    }

    public function campaign()
    {
        return $this->hasOne(Campaign::class, 'id',
            'campaign_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updater_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

}
