<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'progress_percent',
        'enrolled_at',
        'completed_at',
        'source',
        'access_status'
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $attributes = [
        'progress_percent' => 0,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function rating()
    {
        return $this->hasOne(Rating::class, 'user_id', 'user_id');
    }
}
