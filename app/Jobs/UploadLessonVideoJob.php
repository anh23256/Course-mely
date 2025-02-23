<?php

namespace App\Jobs;

use App\Models\Lesson;
use App\Models\Video;
use App\Services\VideoUploadService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UploadLessonVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const FOLDER = 'videos/lessons';

    protected $filePath;
    protected $lessonData;
    protected $chapter;

    /**
     * Create a new job instance.
     */
    public function __construct($filePath, $lessonData, $chapter)
    {
        $this->filePath = $filePath;
        $this->lessonData = $lessonData;
        $this->chapter = $chapter;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $file = storage_path('app/' . $this->filePath);

            if (!file_exists($file)) {
                throw new \Exception('File video không tồn tại');
            }

            $videoService = new VideoUploadService();
            $dataFile = $videoService->uploadVideo($file, self::FOLDER, true);
            $muxVideoUrl = $videoService->uploadVideoToMux($dataFile['secure_url']);

            if (!$muxVideoUrl) {
                throw new \Exception('Có lỗi xảy ra khi upload video lên Mux');
            }

            $video = Video::create([
                'title' => $this->lessonData['title'],
                'url' => $dataFile['secure_url'],
                'asset_id' => $muxVideoUrl['asset_id'],
                'mux_playback_id' => $muxVideoUrl['playback_id'],
                'duration' => $dataFile['duration'],
            ]);

            Lesson::create([
                'chapter_id' => $this->chapter->id,
                'title' => $this->lessonData['title'],
                'slug' => $this->lessonData['slug'],
                'type' => 'video',
                'lessonable_type' => Video::class,
                'lessonable_id' => $video->id,
                'order' => $this->chapter->lessons->max('order') + 1,
                'content' => $this->lessonData['content'] ?? null,
                'is_free_preview' => $this->lessonData['is_free_preview'] ?? false,
            ]);

            unlink($file);
        } catch (\Exception $e) {
            Log::error('UploadLessonVideoJob failed: ' . $e->getMessage());
        }
    }
}
