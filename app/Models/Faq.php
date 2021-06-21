<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = ['faq_topic_id', 'question', 'answer', 'sort'];
}
