<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{

    public $incrementing = false;

    protected $fillable = [
        'id', 'company_name', 'domain_name', 'member_status', 'meta_data'
    ];

    protected $casts = [
        'meta_data' => 'json'
    ];
}
