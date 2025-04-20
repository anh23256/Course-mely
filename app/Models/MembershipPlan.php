<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'instructor_id',
        'code',
        'name',
        'description',
        'price',
        'duration_months',
        'benefits',
        'status',
    ];

    protected $casts = [
        'benefits' => 'array',
    ];

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function membershipCourseAccess()
    {
        return $this->belongsToMany(Course::class, 'membership_course_access');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function membershipSubscription()
    {
        return $this->hasOne(MembershipSubscription::class, 'membership_plan_id');
    }
}
