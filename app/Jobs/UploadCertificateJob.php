<?php

namespace App\Jobs;

use App\Models\Approvable;
use App\Models\Course;
use App\Models\User;
use App\Notifications\CourseApprovedNotification;
use App\Notifications\CourseRejectedNotification;
use App\Notifications\CourseSubmittedNotification;
use App\Services\CourseValidatorService;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadCertificateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const FOLDER = 'certificates';
    private $filePath;
    private $user;
    private $course;
    private $template;

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath, User $user, Course $course, $template = null)
    {
        $this->filePath = $filePath;
        $this->user = $user;
        $this->course = $course;
        $this->template = $template;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if (!$this->filePath || !$this->user || !$this->course) {
                Log::error('Không có đủ thông tin');
                return;
            }

            $user = $this->user;
            $course = $this->course;
            $template = $this->template ?? null;

            $fullPath = storage_path("app/public/{$this->filePath}");

            if (Storage::disk('public')->exists($this->filePath)) {
                $uploadResult = Cloudinary::uploadFile($fullPath, [
                    'folder' => 'certificates',
                    'public_id' => pathinfo($this->filePath, PATHINFO_FILENAME),
                ]);

                Storage::disk('public')->delete($this->filePath);
            }

            $pdfUrl = $uploadResult->getSecurePath();

            if (!$pdfUrl) {
                Log::error('Không có url trả về');
                return;
            }

            if


        } catch (\Exception $e) {
            Log::error("Lỗi tự động duyệt khóa học: " . $e->getMessage());

            return;
        }
    }
}
