<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappJob extends Model
{
    protected $fillable = [
        'priority',
        'priority',
        'donation_id',
        'job_type',
        'job_start_at',
        'job_end_at',
        'job_status',
        'whatsapp_message_id',
        'whatsapp_name',
        'whatsapp_number',
        'worker',
        'worker_mode',
        'qurban_order_id'
    ];

    public function message()
    {
        return $this->belongsTo(WhatsappMessage::class, 'whatsapp_message_id');
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

}
