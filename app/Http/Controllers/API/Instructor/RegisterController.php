<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\RegisterInstructorRequest;
use App\Jobs\AutoApproveInstructorJob;
use App\Jobs\ProcessInstructorRegistrationJob;
use App\Models\Approvable;
use App\Models\Career;
use App\Models\Profile;
use App\Models\User;
use App\Notifications\RegisterInstructorNotification;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
use App\Traits\UploadToLocalTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    use LoggableTrait, ApiResponseTrait, UploadToLocalTrait;

    const FOLDER_CERTIFICATES = 'certificates';

    public function register(RegisterInstructorRequest $request)
    {
        if (!Auth::check()) {
            return $this->respondUnauthorized('Bạn cần đăng nhập để đăng ký làm giảng viên');
        }

        try {
            $user = Auth::user();

            if (Approvable::where('approvable_id', $user->id)
                ->where('approvable_type', User::class)
                ->exists()) {
                return $this->respondOk('Yêu cầu kiểm duyệt đã được gửi');
            }

            $uploadedCertificates = $this->uploadCertificates($request->file('certificates'));

            $qaSystemsData = $this->prepareQaSystemsData($request->qa_systems);

            $requestData = [
                'qa_systems' => $qaSystemsData,
                'certificates' => $uploadedCertificates,
            ];

            ProcessInstructorRegistrationJob::dispatch($user, $requestData);

            return $this->respondCreated('Gửi yêu cầu đăng ký thành công', $user);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function checkHandleRole(Request $request)
    {
        try {
            $user = Auth::user();

            $role = $user->roles->first();

            return $this->respondOk('Vai trò của người dùng', $role);

        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }


    private function uploadCertificates($certificates)
    {
        if ($certificates) {
            return $this->uploadMultiple($certificates, self::FOLDER_CERTIFICATES);
        }
        return [];
    }

    private function prepareQaSystemsData($qaSystems)
    {
        return collect($qaSystems)->map(function ($qaSystem) {
            return [
                'question' => $qaSystem['question'],
                'selected_options' => $qaSystem['selected_options'],
                'options' => $qaSystem['options'],
            ];
        })->toArray();
    }

    private function calculateProgress(array $errors, array $pass)
    {
        try {
            $countErrors = count($errors);
            $countPass = count($pass);

            return empty($errors) ? 100 : $countPass / ($countErrors + $countPass);
        } catch (\Exception $e) {
            $this->logError($e);
        }
    }
}
