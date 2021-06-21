<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Volunteer extends Model
{
    protected $fillable = [
        'member_id', 'name', 'volunteer_status', 'place_birth', 'birthday', 'address', 'email', 'phone', 'religion', 'insurance', 'volunteerism', 'skills', 'work', 'reason_join', 'photo'
    ];

    public function scopeOfMember($query, $member)
    {
        return $query->where('member_id', $member);
    }

}
