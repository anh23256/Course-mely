<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'coin',
        'amount',
        'bank_name',
        'account_name',
        'account_number',
        'account_holder',
        'note',
        'qr_code',
        'status',
        'request_date',
        'completed_date',
        'admin_comment',
        'instructor_confirmation',
        'instructor_confirmation_date',
        'instructor_confirmation_note',
        'is_received',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

}
