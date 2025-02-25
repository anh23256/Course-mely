<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'url',
        'asset_id',
        'mux_playback_id',
        'duration',
    ];

    public function lessons()
    {
        return $this->morphOne(Lesson::class, 'lessonable');
    }
}
