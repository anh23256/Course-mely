<?php

namespace App\Http\Controllers\API\Student;

use App\Http\Controllers\Controller;
use App\Jobs\UploadCertificateJob;
use App\Models\Course;
use App\Models\CourseUser;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function generateCertificate(string $slug)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->respondUnauthorized('Bạn không có quyền truy cập, vui lòng đăng nhập và thử lại.');
            }

            $course = Course::query()->where('slug', $slug)->first();

            if (!$course) {
                return $this->respondNotFound('Không tìm thấy khóa học.');
            }

            $courseUser = CourseUser::query()->where(['user_id' => $user->id, 'course_id' => $course->id])->first();

            if (!$courseUser) {
                return $this->respondForbidden('Người dùng chưa mua khóa học này.');
            }

            if ($courseUser->progress_percent !== 100 || $courseUser->completed_at == null) {
                return $this->respondError('Tiến độ chưa đạt 100%.');
            }

            $images = [
                'logo' => public_path('assets/images/logo-container.png'),
                'daudo' => public_path('assets/images/daudocoursemely.jpeg'),
            ];

            $base64Images = [];

            foreach ($images as $key => $path) {
                if (file_exists($path)) {
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_get_contents($path);
                    $base64Images[$key] = 'data:image/' . $type . ';base64,' . base64_encode($data);
                }
            }

            $pdf = Pdf::loadView('certificates.certificate', [
                'course' => $course,
                'user' => $user,
                'image' => $base64Images
            ])->setPaper('A4', 'landscape')->setOptions(['dpi' => 96]);

            $fileName = "certificate_{$user->id}_{$course->id}.pdf";
            $path = "certificates/{$fileName}";

            Storage::disk('public')->put($path, $pdf->output());

            $pdfUrl = Storage::disk('public')->url($path);

            

            $start = microtime(true);

            UploadCertificateJob::dispatch($path, $user, $course)->onQueue('certificate');

            $end = microtime(true);

            return $this->respondOk('Tạo chứng chỉ thành công', ['time' => $end - $start, 'pdf_url' => $pdfUrl]);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, chưa thể tạo chứng chỉ');
        }
    }
}
