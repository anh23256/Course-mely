<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class Course extends Model
{
    use HasFactory, SoftDeletes, HasRoles;

    const LEVEL_BEGINNER = 'beginner';
    const LEVEL_INTERMEDIATE =
        'intermediate';
    const LEVEL_ADVANCED = 'advanced';

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'category_id',
        'code',
        'name',
        'slug',
        'thumbnail',
        'intro',
        'price',
        'price_sale',
        'is_free',
        'description',
        'duration',
        'level',
        'total_student',
        'requirements',
        'benefits',
        'qa',
        'status',
        'visibility',
        'modification_request',
        'accepted',
        'views'
    ];

    public $attributes = [
        'status' => self::STATUS_DRAFT,
        'total_student' => 0,
        'requirements' => null,
        'benefits' => null,
        'qa' => null,
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class)->orderBy('order');
    }

    public function lessons()
    {
        return $this->hasManyThrough(Lesson::class, Chapter::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function approvables()
    {
        return $this->morphOne(Approvable::class, 'approvable');
    }

    public function coupons()
    {
        return $this->belongsToMany(Coupon::class);
    }

    public function scopeSearch($query, $searchQuery)
    {
        return $query->when($searchQuery, function ($query) use ($searchQuery) {
            $query->where('name', 'like', '%' . $searchQuery . '%');
        });
    }

    public function wishLists()
    {
        return $this->hasMany(WishList::class, 'course_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

}
