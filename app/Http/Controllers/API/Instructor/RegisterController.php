<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\RegisterInstructorRequest;
use App\Jobs\AutoApproveInstructorJob;
use App\Models\Approvable;
use App\Models\Career;
use App\Models\Profile;
use App\Models\User;
use App\Notifications\RegisterInstructorNotification;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    use LoggableTrait, UploadToCloudinaryTrait, ApiResponseTrait;

    const FOLDER_CERTIFICATES = 'certificates';

    const URL_IMAGE_DEFAULT = "https://res.cloudinary.com/dvrexlsgx/image/upload/v1732148083/Avatar-trang-den_apceuv_pgbce6.png";

    public function register(RegisterInstructorRequest $request)
    {
        if (!Auth::check()) {
            return $this->respondUnauthorized('Bạn cần đăng nhập để đăng ký làm giảng viên');
        }

        try {
            DB::beginTransaction();
            /** @var User $user */
            $user = Auth::user();

            $uploadedCertificates = $this->uploadCertificates($request->file('certificates'));

            $qaSystemsData = $this->prepareQaSystemsData($request->qa_systems);

            // $profile = $this->createProfile($user->id, $request->only(['phone', 'address', 'experience']), $uploadedCertificates, $qaSystemsData);

            $approvable = Approvable::where('approvable_id', $user->id)
                ->where('approvable_type', User::class)
                ->first();

            if (!$approvable) {
                $approvable = new Approvable();
                $approvable->approvable_id = $user->id;
                $approvable->approvable_type = User::class;
                $approvable->status = 'pending';
                $approvable->request_date = now();
                $approvable->save();
            } else {
                return $this->respondOk('Yêu cầu kiểm duyệt đã được gửi');
            }

            Log::info('dssssss' . $this->checkInstructorApproval($user)['progress']);

            AutoApproveInstructorJob::dispatch($user, $this->checkInstructorApproval($user));

            $managers = User::query()->role([
                'admin',
            ])->get();

            foreach ($managers as $manager) {
                $manager->notify(new RegisterInstructorNotification($user->load('approvables')));
            }

            DB::commit();

            return $this->respondCreated('Gửi yêu cầu đăng ký thành công', $user->load('profile'));
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    private function createProfile(int $userId, array $data, array $certificates, array $qaSystemsData)
    {
        return Profile::query()->create(array_merge($data, [
            'user_id' => $userId,
            'certificates' => json_encode($certificates),
            'qa_systems' => json_encode($qaSystemsData),
        ]));
    }

    private function uploadCertificates($certificates)
    {
        if ($certificates) {
            return $this->uploadImageMultiple($certificates, self::FOLDER_CERTIFICATES);
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

    public function validateInstructor()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->respondNotFound('Vui lòng đăng nhập và thử lại');
            }

            $checkApproval = $this->checkInstructorApproval($user);

            return $this->respondOk('Kiểm tra thông tin giảng viên', $checkApproval);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
    private function checkInstructorApproval(User $instructor)
    {
        $profile = $instructor->profile;
        $errors = [];
        $pass = [];

        if (!$profile || empty($profile->about_me) || strlen($profile->about_me) < 50) {
            $errors[] = "Giảng viên phải có phần giới thiệu với tối thiểu 50 ký tự.";
        } else {
            $pass[] = "Giảng viên có phần giới thiệu hợp lệ.";
        }

        if (!$profile || empty($profile->phone)) {
            $errors[] = "Giảng viên phải có số điện thoại.";
        } else {
            $pass[] = "Giảng viên có số điện thoại hợp lệ.";
        }

        if (!$profile || empty($profile->address)) {
            $errors[] = "Giảng viên phải có địa chỉ.";
        } else {
            $pass[] = "Giảng viên có địa chỉ.";
        }

        if (!$profile || empty($profile->experience)) {
            $errors[] = "Giảng viên phải có thông tin kinh nghiệm.";
        } else {
            $pass[] = "Giảng viên có kinh nghiệm.";
        }

        if (!$profile || empty(json_decode($profile->bio, true))) {
            $errors[] = "Giảng viên phải có phần mô tả cá nhân.";
        } else {
            $pass[] = "Giảng viên có mô tả cá nhân.";
        }

        if (!$profile || empty($profile->certificates) || count(json_decode($profile->certificates, true)) < 1) {
            $errors[] = "Giảng viên phải có ít nhất một chứng chỉ.";
        } else {
            $pass[] = "Giảng viên có chứng chỉ.";
        }

        if (!$profile || empty($profile->qa_systems) || count(json_decode($profile->qa_systems, true)) < 3) {
            $errors[] = "Giảng viên phải trả lời câu hỏi hệ thống.";
        } else {
            $pass[] = "Giảng viên đã trả lời câu hỏi hệ thống.";
        }

        $progress = $this->calculateProgress($errors, $pass);

        return [
            'status' => empty($errors),
            'errors' => $errors,
            'pass' => $pass,
            'progress' => $progress * 100
        ];
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
