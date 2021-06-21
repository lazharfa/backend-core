<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappAttachment extends Model
{
    protected $fillable = [
        'whatsapp_message_id',
        'file_name',
    ];
}
