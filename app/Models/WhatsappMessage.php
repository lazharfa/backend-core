<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappMessage extends Model
{

    protected $fillable = [
        'message'
    ];

    public function attachments()
    {
        return $this->hasMany(WhatsappAttachment::class);
    }

}
