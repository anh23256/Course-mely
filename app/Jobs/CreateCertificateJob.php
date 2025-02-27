<?php

namespace App\Jobs;

use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\User;
use App\Traits\LoggableTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateCertificateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LoggableTrait;
    protected $userId;
    protected $courseId;
    const FOLDER = "certificates";

    protected $tries = 2;
    protected $backoff = 10;

    public function __construct($userId, $courseId)
    {
        $this->userId = $userId;
        $this->courseId = $courseId;
    }

    public function handle()
    {
        try {
            if (!$this->userId || !$this->courseId) return;

            $user = User::find($this->userId);

            $course = Course::query()->where(['id' => $this->courseId, 'status' => 'approved'])->first();

            if (!$course || !$user) return;

            $courseUser = CourseUser::query()->where(['user_id' => $user->id, 'course_id' => $course->id])->first();

            if (!$courseUser) return;

            if ($courseUser->progress_percent !== 100 || $courseUser->completed_at == null) return;

            $certificate = Certificate::query()->where(['user_id' => $user->id, 'course_id' => $course->id])->first();

            if (!$certificate) {
                $certificate_code = 'CERT-' . $user->id . $course->id . random_int(111111, 999999);
                $certificateTemplate = CertificateTemplate::query()->where('status', 1)->first();
                $ngayHoanThanh = Carbon::now(env('APP_TIMEZONE'))->locale('vi')->translatedFormat('d F, Y');

                if (!$certificateTemplate) return;

                $images = [
                    'logo_course_mely' => public_path('assets/images/logo-container.png'),
                    'dau_do' => public_path('assets/images/daudocoursemely.png'),
                    'background_template' => $certificateTemplate->background,
                ];

                $variables = [
                    'user_name' => $user->name,
                    'course_name' => $course->name,
                    'instructor_name' => $course->user->name,
                    'dau_do' => $images['dau_do'] ?? '#',
                    'logo_course_mely' =>  $images['logo_course_mely'] ?? '#',
                    'chung_nhan_so' => $certificate_code,
                    'ngay_hoan_thanh' => $ngayHoanThanh,
                    'background_template' => $images['background_template'] ?? '#',
                ];

                $templateVariables = json_decode($certificateTemplate->variables);
                $content = $certificateTemplate->content;

                foreach ($templateVariables as $key => $placeholder) {
                    if (array_key_exists($key, $variables)) {
                        $content = str_replace($key, $variables[$key], $content);
                    }
                }

                $start = microtime(true);

                $pdf = Pdf::loadHTML($content)->setPaper('A4', 'landscape');
                $filename = "certificate_{$user->id}_{$course->id}";
                $pdfContent = $pdf->output();

                $uploadResult = Cloudinary::upload('data:application/pdf;base64,' . base64_encode($pdfContent), [
                    'folder' => 'certificates',
                    'resource_type' => 'auto',
                    'public_id' => $filename,
                ]);

                $pdfUrl = $uploadResult->getSecurePath();

                if (!$pdfUrl) return;

                $certificate = Certificate::create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'certificate_template_id' => $certificateTemplate->id,
                    'certificate_code' => $certificate_code,
                    'issued_at' => now(env('APP_TIMEZONE')),
                    'file_path' => $pdfUrl,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Tạo chứng chỉ thất bại",['error' => $e->getMessage()]);
        }
    }
}
