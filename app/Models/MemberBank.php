<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberBank extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'member_id', 'bank_id', 'bank_account', 'bank_number', 'bank_info', 'bank_info_en', 'sort', 'group'
    ];

    public function scopeOfMember($query, $member)
    {
        return $query->where('member_id', $member);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }
}
