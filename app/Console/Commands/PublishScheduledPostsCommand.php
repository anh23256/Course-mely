<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class PublishScheduledPostsCommand extends Command
{
    protected $signature = 'posts:publish-scheduled';

    protected $description = 'Kiểm tra và cập nhật các bài viết được đặt ở chế độ hẹn giờ đăng';

    public function handle()
    {
        $now = Carbon::now();

        $posts = Post::where('status', 'scheduled')
            ->where('published_at', '<=', $now)
            ->get();

        $count = $posts->count();

        if ($count === 0) {
            $this->info('Không tìm thấy bài viết nào ở trạng thái chờ đăng');
            return 0;
        }

        $this->info("Xử lý {$count} đăng tải bài viết");

        foreach ($posts as $post) {
            try {
                $post->status = 'published';
                $post->published_at = $now;
                $post->save();

                $this->info("Đăng tải bài viết: {$post->id} - {$post->title}");
            } catch (\Exception $e) {
                $this->error("Có lỗi khi đăng bài viết: {$post->id}");
                Log::error("Không thể chuyển đổi trạng thái bài viết: {$post->id}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info('Hoàn thành kiểm tra');
        return 0;
    }
}
