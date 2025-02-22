<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coding extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'language',
        'hints',
        'sample_code',
        'result_code',
        'solution_code',
        'instruct'
    ];

    public function lessons()
    {
        return $this->morphOne(Lesson::class, 'lessonable');
    }

    protected $casts = [
        'hints' => 'array',
    ];
}
