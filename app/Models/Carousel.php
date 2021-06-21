<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carousel extends Model
{
    protected $fillable = [
        'member_id', 'file_name', 'creator_id', 'updater_id'
    ];

    public function scopeOfMember($query, $member)
    {
        return $query->where('member_id', $member);
    }
}
