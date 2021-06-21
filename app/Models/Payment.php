<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'member_id',
        'donation_id',
        'total_payment',
        'bank_id',
        'payment_at',
        'claim_at',
        'description',
        'created_id',
        'push_to_balans_at'
    ];

    protected $casts = [
        'total_payment' => 'double',
        'donation_id' => 'integer'
    ];

    protected $dates = [
        'payment_at', 'claim_at'
    ];

    public function scopeOfMember($query, $member)
    {
        return $query->where('member_id', $member);
    }

    public function bank()
    {
        return $this->belongsTo(MemberBank::class, 'bank_id', 'id');
    }

    public function donation()
    {
        return $this->belongsTo(Donation::class, 'donation_id');
    }
}
