<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    protected $fillable = [
        'member_id', 'email', 'campaign', 'name'
    ];

    public function scopeOfMember($query, $member)
    {
        return $query->where('member_id', $member);
    }

    public $casts = [
        'campaign' => 'boolean'
    ];

}
