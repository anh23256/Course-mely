<?php

namespace App\Http\Controllers\API\Student;

use App\Http\Controllers\Controller;
use App\Jobs\UploadCertificateJob;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Course;
use App\Models\CourseUser;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToLocalTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    use LoggableTrait, ApiResponseTrait, UploadToLocalTrait;

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

            $certificate = Certificate::query()->where(['user_id' => $user->id, 'course_id' => $course->id])->first();

            if (!$certificate) {
                $certificate_code = 'CERT-' . $user->id . $course->id . random_int(111111, 999999);
                $certificateTemplate = CertificateTemplate::query()->first();
                $ngayHoanThanh = Carbon::now(env('APP_TIMEZONE'))->locale('vi')->translatedFormat('d F, Y');

                if (!$certificateTemplate) {
                    return $this->respondError('Không có mẫu chứng chỉ');
                }

                $variables = [
                    'user->name' => $user->name,
                    'course->name' => $course->name,
                    'course->user->name' => $course->user->name,
                    'completion_date' => now()->format('d-m-Y'),
                    'daudoCourseMely' => $base64Images['daudo'],
                    'logoCourseMely' => $base64Images['logo'],
                    'chungnhanso' => $certificate_code,
                    'ngayHoanThanhKhoaHoc' => $ngayHoanThanh
                ];

                $content = $certificateTemplate->content;

                foreach ($variables as $key => $value) {
                    $content = str_replace("{{" . $key . "}}", $value, $content);
                }

                if ($certificateTemplate->background_image) {
                    $content = "
                        <style>
                            body {
                                background-image: url('{$certificateTemplate->background_image}');
                                background-size: cover;
                                background-repeat: no-repeat;
                                background-position: center;
                                height: 100%;
                            }
                        </style>
                        {$content}";
                }

                $pdf = Pdf::loadHTML($content)->setPaper('A4', 'landscape')->setOptions(['dpi' => 96]);

                $filename = "certificate_{$user->id}_{$course->id}.pdf";
                $pdfContent = $pdf->output();

                Storage::disk('public')->put("certificates/{$filename}",  base64_encode($pdfContent));
                $pdfUrl = Storage::url("certificates/{$filename}");

                if (!$pdf) {
                    return $this->respondError('Không thể tạo đường dẫn chứng chỉ');
                }

                $certificate = Certificate::create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'certificate_template_id' => $certificateTemplate->id,
                    'certificate_code' => $certificate_code,
                    'issued_at' => now(env('APP_TIMEZONE')),
                    'file_path' => $pdfUrl,
                ]);

                $now = Carbon::now();
                $midnight = $now->copy()->endOfDay();
                $delay = $midnight->diffInSeconds($now);

                UploadCertificateJob::dispatch('certificates/' . $filename, base64_encode($pdfContent), $user->id, $course->id);
                    // ->delay(now()->addSeconds($delay));
            }

            return $this->respondOk('Tạo đường dẫn chứng chỉ thành công', ['pdf_url' => $certificate->file_path]);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, chưa thể tạo chứng chỉ');
        }
    }
}
