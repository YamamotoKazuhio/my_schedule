<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = ['title', 'date', 'description', 'category', 'is_completed', 'user_id'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
