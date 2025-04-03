<?php

namespace App\Notifications;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $status;
    protected $note;
    protected $membershipPlan;

    public function __construct(string $status, string $note, $membershipPlan)
    {
        $this->status = $status;
        $this->note = $note;
        $this->membershipPlan = $membershipPlan;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        $subject = $this->status === 'approved'
            ? 'Gói thành viên của bạn đã được phê duyệt'
            : 'Gói thành viên của bạn đã bị từ chối';

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.membership-approval-response', [
                'status' => $this->status,
                'note' => $this->note,
                'membershipPlan' => $this->membershipPlan,
            ]);
    }

    public function toDatabase($notifiable)
    {
        $message = $this->status === 'approved'
            ? 'Gói thành viên "' . $this->membershipPlan->name . '" đã được phê duyệt.'
            : 'Gói thành viên "' . $this->membershipPlan->name . '" đã bị từ chối.';

        return [
            'status' => $this->status,
            'note' => $this->note,
            'message' => $message,
        ];
    }

    public function toBroadcast($notifiable)
    {
        $message = $this->status === 'approved'
            ? 'Gói thành viên "' . $this->membershipPlan->name . '" đã được phê duyệt.'
            : 'Gói thành viên "' . $this->membershipPlan->name . '" đã bị từ chối.';

        return new BroadcastMessage([
            'status' => $this->status,
            'note' => $this->note,
            'message' => $message,
        ]);
    }

    public function broadcastOn()
    {
        return new PrivateChannel('notification.' . $this->membershipPlan->instructor_id);
    }
}
