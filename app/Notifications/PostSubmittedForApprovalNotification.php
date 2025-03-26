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

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Lưu thông báo vào database
     */
    public function toDatabase($notifiable)
    {
        Log::info("Saving notification to the database for user ID: " . $notifiable->id . " | Post ID: " . $this->post->id);

        return [
            'type' => 'post_submitted',
            'post_id' => $this->post->id,
            'post_title' => $this->post->title,
            'post_slug' => $this->post->slug ?? '',
            'post_thumbnail' => $this->post->thumbnail ?? '',
            'message' => "Bài viết '{$this->post->title}' đã được gửi yêu cầu kiểm duyệt.",
        ];
    }

    /**
     * Gửi thông báo qua broadcast
     */
    public function toBroadcast($notifiable)
    {
        $this->notifiableId = $notifiable->id;
        return new BroadcastMessage([
            'post_id' => $this->post->id,
            'post_title' => $this->post->title,
            'post_slug' => $this->post->slug ?? '',
            'post_thumbnail' => $this->post->thumbnail ?? '',
            'message' => "Bài viết '{$this->post->title}' đã được gửi để kiểm duyệt!",
        ]);
    }

    /**
     * Định nghĩa kênh broadcast
     */
    public function broadcastOn()
    {
        $channel = new PrivateChannel('notification.' . $this->notifiableId); // Gửi tới admin
        Log::info('Broadcasting on channel: ' . $channel->name);
        return $channel;
    }
}