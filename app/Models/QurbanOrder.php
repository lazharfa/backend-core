<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QurbanOrder extends Model
{
    protected $fillable = [
        'parent_id',
        'donor_id',
        'donation_id',
        'qurban_type_id',
        'qurban_location_id',
        'qurban_name',
        'qurban_status',
        'qurban_price',
        'qurban_attachments',
        'send_report_at',
        'check_at',
        'check_id',
        'video_available_at',
        'image_available_at'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'qurban_attachments' => 'json'
    ];

    protected $appends = [
        'qurban_location',
        'qurban_type',
        'qurban_number'
    ];

    public function getQurbanLocationAttribute()
    {

        $qurbanLocation = QurbanLocation::with('parent')->find($this->attributes['qurban_location_id']);

        if ($qurbanLocation->parent){
            return $qurbanLocation->location_name . ', ' . $qurbanLocation->parent->location_name;
        }

        return $qurbanLocation->location_name;

    }

    public function getQurbanTypeAttribute()
    {
        if (!isset($this->attributes['qurban_type_id'])) {
            return null;
        }
        return QurbanType::find($this->attributes['qurban_type_id'])->type_name;
    }

    public function getQurbanNumberAttribute()
    {
        return Donation::find($this->attributes['donation_id'])->donation_number;
    }

    public function donation()
    {
        return $this->hasOne(Donation::class, 'id', 'donation_id');
    }

    public function location()
    {
        return $this->hasOne(QurbanLocation::class, 'id', 'qurban_location_id');
    }

    public function type()
    {
        return $this->hasOne(QurbanType::class, 'qurban_type_id', 'id');
    }

    public function price()
    {
        return $this->hasOne(QurbanPrice::class, 'id', 'qurban_price');
    }
}
