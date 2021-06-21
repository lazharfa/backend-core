<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QurbanPrice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'qurban_type_id',
        'qurban_location_id',
        'price'
    ];

    public function type()
    {
        return $this->hasOne(QurbanType::class, 'id', 'qurban_type_id');
    }

    public function location()
    {
        return $this->hasOne(QurbanLocation::class, 'id', 'qurban_location_id');
    }
}
