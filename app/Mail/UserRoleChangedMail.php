<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserRoleChangedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $user;
    public $currencyRole;
    public $newRole;

    public function __construct(User $user, $currencyRole, $newRole)
    {
        $this->user = $user;
        $this->currencyRole = $currencyRole;
        $this->newRole = $newRole;
    }

    // Hàm build trong mail
    public function build()
    {
        return $this->view('emails.user-role-changed')
            ->with([
                'user' => $this->user,
                'currencyRole' => $this->currencyRole,
                'newRole' => $this->newRole,
            ]);
    }
}
