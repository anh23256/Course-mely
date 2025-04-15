<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstructorCommission extends Model
{
    use HasFactory;
    protected $fillable = [
        'instructor_id',
        'rate',
        'rate_logs',
    ];

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }
}
