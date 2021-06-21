<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'member_id',
        'category_type',
        'is_menu',
        'category_name',
        'category_name_en',
        'category_slug',
        'category_slug_en',
        'category_info',
        'category_info_en',
        'text_color',
        'badge_color'
    ];

    protected $casts = [
        'is_menu' => 'boolean'
    ];

    public function scopeOfMember($query, $member)
    {
        return $query->where('member_id', $member);
    }

    public function campaign()
    {
        return $this->hasMany(Campaign::class, 'category_id');
    }
}
