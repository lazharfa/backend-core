<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'member_id', 'page_title', 'page_title_en', 'page_content', 'page_content_en', 'page_slug', 'page_slug_en', 'creator_id', 'updater_id'
    ];

    public function scopeOfMember($query, $member)
    {
        return $query->where('member_id', $member);
    }

}
