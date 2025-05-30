<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'file_path',
        'file_type',
    ];

    public function lessons()
    {
        return $this->morphMany(Lesson::class, 'lessonable');
    }   

    
}
