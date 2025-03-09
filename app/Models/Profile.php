<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'about_me',
        'phone',
        'address',
        'experience',
        'bio',
        'certificates',
        'qa_systems',
        'banking_info'
    ];

    protected $casts = [
        'bio' => 'array',
        'banking_info' => 'array'
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function careers()
    {
        return $this->hasMany(Career::class);
    }
}
