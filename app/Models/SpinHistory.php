<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpinHistory extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'reward_type', 'reward_id', 'reward_name', 'spun_at'];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'reward_id');
    }

    public function gift()
    {
        return $this->belongsTo(Gift::class, 'reward_id');
    }
}
