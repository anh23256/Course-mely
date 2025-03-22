<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spin extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'spin_count', 'received_at', 'expires_at'];
}
