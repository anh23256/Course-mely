<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class PostSubmittedForApprovalNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    protected $post;
    protected $notifiableId;
    /**
     * Create a new notification instance.
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        Log::info("Saving notification to the database for user ID: " . $notifiable->id . " | Post ID: " . $this->post->id);

        return $this->notificationData();
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->notificationData());
    }

    private function getUrl()
    {
        $approvableId = $this->post->approvables ? $this->post->approvables->id : null;
        return $approvableId ? route('admin.approvals.posts.show', $approvableId) : '#';
    }

    private function notificationData(): array
    {
        return [
            'type' => 'post_submitted',
            'post_id' => $this->post->id,
            'post_title' => $this->post->title,
            'post_slug' => $this->post->slug ?? '',
            'post_thumbnail' => $this->post->thumbnail ?? '',
            'message' => "Bài viết '{$this->post->title}' đã gửi yêu cầu kiểm duyệt.",
            'url' => $this->getUrl(),
        ];
    }
}
