<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use jeremykenedy\LaravelRoles\Traits\HasRoleAndPermission;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoleAndPermission;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'member_id',
        'uid',
        'full_name',
        'nick_name',
        'marital_status',
        'birthday',
        'religion',
        'gender',
        'email',
        'email_verified_at',
        'email_status',
        'email_update',
        'password',
        'phone_number',
        'phone_status',
        'phone_update',
        'profile_picture',
        'education',
        'work',
        'range_income',
        'address',
        'biography',
        'country_code',
        'user_status',
        'company',
        'registered_at',
        'donor_identity',
        'member_bank_id'
    ];

    protected $dates = [
        'email_verified_at',
        'registered_at',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function scopeOfMember($query, $member)
    {
        return $query->where('member_id', $member);
    }

    public function donations()
    {
        return $this->hasMany(Donation::class, 'donor_id');
    }

    public function donation()
    {
        return $this->hasOne(Donation::class, 'donor_id')->whereNotNull('donor_email');
    }

    public function donorType()
    {
        return $this->hasOne(DonorType::class, 'donor_id', 'id')->where('expired_at', '>=' , date('Y-m-d'));
    }

}
