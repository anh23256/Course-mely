<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemFund extends Model
{
    use HasFactory;

    protected $fillable = [
        'balance',
        'pending_balance'
    ];
}
