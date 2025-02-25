<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'instructor_id',
        'title',
        'description',
        'stream_key',
        'mux_playback_id',
        'status',
        'start_time',
        'end_time',
    ];

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function conversation()
    {
        return $this->morphOne(Conversation::class, 'conversationable');
    }

    public function participants()
    {
        return $this->hasMany(LiveSessionParticipant::class);
    }

}
