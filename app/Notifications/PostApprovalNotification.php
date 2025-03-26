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

class PostApprovalNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    protected $post;
    protected $status;
    protected $note;

    /**
     * Create a new notification instance.
     */
    public function __construct(Post $post, string $status, string $note)
    {
        $this->post = $post;
        $this->status = $status;
        $this->note = $note;
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
        $statusText = $this->status === 'approved' ? 'được duyệt' : 'bị từ chối';
        Log::info("Saving notification to the database for user ID: " . $notifiable->id . " | Post ID: " . $this->post->id);

        return [
            'type' => 'post_approval',
            'post_id' => $this->post->id,
            'post_title' => $this->post->title,
            'post_slug' => $this->post->slug ?? '', // Nếu bạn có slug cho bài viết
            'post_thumbnail' => $this->post->thumbnail ?? '', // Nếu bài viết có thumbnail
            'status' => $this->status,
            'message' => "Bài viết '{$this->post->title}' đã $statusText. Ghi chú: {$this->note}",
        ];
    }

    /**
     * Gửi thông báo qua broadcast
     */
    public function toBroadcast($notifiable)
    {
        $statusText = $this->status === 'approved' ? 'được duyệt' : 'bị từ chối';
        return new BroadcastMessage([
            'post_id' => $this->post->id,
            'post_title' => $this->post->title,
            'post_slug' => $this->post->slug ?? '',
            'post_thumbnail' => $this->post->thumbnail ?? '',
            'status' => $this->status,
            'message' => "Bài viết '{$this->post->title}' đã $statusText!",
        ]);
    }

    /**
     * Định nghĩa kênh broadcast
     */
    public function broadcastOn()
    {
        $channel = new PrivateChannel('notification.' . $this->post->user_id);
        Log::info('Broadcasting on channel: ' . $channel->name);
        return $channel;
    }
}