<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Career extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'institution_name',
        'degree',
        'major',
        'start_date',
        'end_date',
        'description'
    ];
}
