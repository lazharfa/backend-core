<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappHistories extends Model
{
    protected $fillable = [
        'member_id', 'whatsapp_number'
    ];
}
