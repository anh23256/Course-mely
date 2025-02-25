<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'discount_max_value',
        'start_date',
        'expire_date',
        'status',
        'used_count',
    ];

    
}
