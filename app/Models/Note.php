<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lesson_id',
        'time',
        'content'
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
