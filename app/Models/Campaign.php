<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;

class Campaign extends Model
{
    protected $fillable = [
        'member_id',
        'creator_id',
        'category_id',
        'campaign_status',
        'campaign_image',
        'campaign_media',
        'campaign_location',
        'volunteer',
        'campaign_title',
        'campaign_title_en',
        'campaign_slug',
        'campaign_slug_en',
        'target_donation',
        'total_fund',
        'expired_at',
        'invitation_message',
        'invitation_message_en',
        'descriptions',
        'descriptions_en',
        'priority',
        'code',
        'donation_percentage',
        'push_to_balans_at',
    ];

    protected $casts = [
        'target_donation' => 'double',
        'total_fund' => 'double',
        'campaign_media' => 'json'
    ];

    public function scopeOfMember($query, $member)
    {
        return $query->where('member_id', $member);
    }

    public function scopeExpired($query, $is_expired)
    {
        if ($is_expired == '1') {
            return $query->where('expired_at', '<', date('Y-m-d'));
        }

        return $query;
    }

    public function scopeFilterCategory($query, $category_slug)
    {
        try {
            if ($category_slug) {
                $category = Category::whereCategorySlug($category_slug)->firstOrFail();

                return $query->whereCategoryId($category->id);
            }
        } catch (\Exception $e) { }

        return $query;
    }

    public function scopeSortParam($query, $sort_type)
    {
        $query = $query->orderBy('priority');

        switch ($sort_type) {
            case 'urgent':
                return $query->orderBy('donation_percentage', 'desc')->orderBy('expired_at')->latest();
                break;

            case 'expire_soon':
                return $query->orderBy('expired_at')->latest();
                break;

            case 'expire_long':
                return $query->orderBy('expired_at', 'desc')->latest();
                break;

            default:
                return $query->latest();
                break;
        }
    }

    public function scopeRangePercentage($query, $range_percentage)
    {
        if ($range_percentage) {
            $percentages = explode(',', $range_percentage);

            if (count($percentages) == 2) {
                return $query->whereIn('donation_percentage', $percentages);
            }
        }

        return $query;
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function donations_verified()
    {
        return $this->donations()->whereNotNull('total_donation');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function realization()
    {
        return $this->hasMany(CampaignRealization::class, 'campaign_id');
    }

    public function realizationRefund()
    {
        return $this->hasMany(RealizationRefund::class, 'campaign_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
