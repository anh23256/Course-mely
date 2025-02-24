<?php

namespace App\Http\Controllers\API\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseUser;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class CertificateController extends Controller
{
    use LoggableTrait, ApiResponseTrait;
    const UPLOAD_CERTIFICATE = 'ceritificates';

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

            $pdf = Pdf::loadView('certificates.certificate', [
                'course' => $course,
                'user' => $user
            ])->setPaper('A4', 'landscape');

            $path = resource_path('pdfs/certificate_' . $course->code . '_' . $user->code . '.pdf');

            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0777, true);
            }

            $pdf->save($path);

            return $this->respondOk('Tạo chứng nhận thành công', $path);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, chưa thể tạo chứng chỉ');
        }
    }
}
