<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaqTopic extends Model
{
    protected $fillable = ['slug', 'name', 'sort', 'description'];
}
