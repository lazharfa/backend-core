<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QurbanLocation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'location_name',
        'location_slug',
        'location_status',
        'location_cover',
        'location_quota',
        'location_description'
    ];

    protected $casts = [
        'location_quota' => 'integer'
    ];

    public function parent()
    {
        return $this->hasOne(QurbanLocation::class, 'id', 'parent_id');
    }

    public function child()
    {
        return $this->hasMany(QurbanLocation::class, 'parent_id');
    }
}
