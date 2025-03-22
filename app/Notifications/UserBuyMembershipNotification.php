<?php

namespace App\Notifications;

use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserBuyMembershipNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    private $user;

    private $membership;

    public function __construct($user, $membership)
    {
        $this->user = $user;
        $this->membership = $membership;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    private function getUrl()
    {
        $transactionCode = $this->membership->invoices->first()->transaction->transaction_code ?
            $this->membership->invoices->first()->transaction->transaction_code :
            null;
        return $transactionCode ? route('admin.transactions.show', $transactionCode) : '#';
    }

    private function notificationData(): array
    {
        return [
            'type' => 'user_buy_course',
            'message' => $this->user->name . ' đã đăng ký gói membership ' . $this->membership->name,
            'user_avatar' => $this->user->avatar,
            'url' => $this->getUrl()
        ];
    }

    public function toDatabase($notifiable)
    {
        return new DatabaseMessage($this->notificationData());
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->notificationData());
    }
}
