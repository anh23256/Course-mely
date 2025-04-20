<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpinSetting extends Model
{
    use HasFactory;
    protected $fillable = [
        'status',
        'has_enough_spin_types',
        'has_enough_gifts',
        'is_probability_valid',
        'total_probability',
    ];

    protected $casts = [
        'has_enough_spin_types' => 'boolean',
        'has_enough_gifts' => 'boolean',
        'is_probability_valid' => 'boolean',
    ];
}