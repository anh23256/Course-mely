<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'membership_plan_id',
        'start_date',
        'end_date',
        'status',
        'activity_logs'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'activity_logs' => 'array'
    ];

    public function addLog($action, $details = null, $data = [])
    {
        $logs = $this->activity_logs ?? [];

        $logs[] = [
            'action' => $action,
            'details' => $details,
            'data' => $data,
            'timestamp' => now()->toDateTimeString(),
        ];

        $this->activity_logs = $logs;
        $this->save();

        return $this;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function membershipPlan()
    {
        return $this->belongsTo(MembershipPlan::class);
    }
}
