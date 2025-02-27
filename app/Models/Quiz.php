<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title'
    ];

    public function lessons()
    {
        return $this->morphOne(Lesson::class, 'lessonable');
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
