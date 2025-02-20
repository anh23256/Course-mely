<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\User\ChangePasswordRequest;
use App\Http\Requests\API\User\UpdateUserProfileRequest;
use App\Models\Career;
use App\Models\Profile;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use LoggableTrait, ApiResponseTrait, UploadToCloudinaryTrait;

    const FOLDER_USER = 'users';
    const FOLDER_CERTIFICATE = 'certificates';

    public function showProfile()
    {
        try {
            $user = Auth::user();

            return $this->respondOk('Thông tin người dùng ' . $user->name, [
                'user' => $user->load('profile.careers'),
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại.');
        }
    }

    public function updateProfile(UpdateUserProfileRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();

            if ($request->hasFile('avatar')) {
                if ($user->avatar) {
                    $this->deleteImage($user->avatar, self::FOLDER_USER);
                }

                $avatarUrl = $this->uploadImage($request->file('avatar'), self::FOLDER_USER);
                $user->avatar = $avatarUrl;
            }

            $user->name = $request->name ?? $user->name;
            $user->save();

            $profile = Profile::query()->where('user_id', $user->id)->first();

            if ($profile) {
                if ($request->hasFile('certificates')) {
                    $certificates = json_decode($profile->certificates, true);

                    if (!empty($certificates)) {
                        $this->deleteMultiple($certificates, self::FOLDER_CERTIFICATE);
                    }

                    $uploadedCertificates = $this->uploadCertificates($request->file('certificates'));
                }

                $profile->update([
                    'about_me' => $request->about_me ?? $profile->about_me,
                    'phone' => $request->phone ?? $profile->phone,
                    'address' => $request->address ?? $profile->address,
                    'experience' => $request->experience ?? $profile->experience,
                    'certificates' => !empty($uploadedCertificates)
                        ? json_encode($uploadedCertificates)
                        : $profile->certificates,
                    'bio' => $request->bio ? $this->prepareBioData($request->bio, $profile) : $profile->bio,
                ]);
            }

            if ($request->has('careers')) {
                foreach ($request->careers as $careerData) {
                    Career::updateOrCreate(
                        [
                            'profile_id' => $profile->id,
                            'institution_name' => $careerData['institution_name'],
                        ],
                        [
                            'degree' => $careerData['degree'],
                            'major' => $careerData['major'],
                            'start_date' => $careerData['start_date'],
                            'end_date' => $careerData['end_date'],
                        ]
                    );
                }
            }

            DB::commit();

            return $this->respondOk('Cập nhật thông tin thành công', [
                'user' => $user->load('profile.careers'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại.');
        }
    }

    private function uploadCertificates($certificates)
    {
        if ($certificates) {
            return $this->uploadImageMultiple($certificates, self::FOLDER_CERTIFICATE);
        }
        return [];
    }

    private function prepareBioData($bioData, $profile)
    {
        if ($bioData) {
            $bio = [];
            $profile = !empty($profile->bio) ? json_decode($profile->bio,true) : '';

            if (isset($bioData['facebook'])) {
                $bio['facebook'] = $bioData['facebook'];
            }else{
                if($profile && !empty($profile['facebook'])) $bio['facebook'] = $profile['facebook'];
            }

            if (isset($bioData['instagram'])) {
                $bio['instagram'] = $bioData['instagram'];
            }else{
                if($profile && !empty($profile['instagram'])) $bio['instagram'] = $profile['instagram'];
            }

            if (isset($bioData['github'])) {
                $bio['github'] = $bioData['github'];
            }else{
                if($profile && !empty($profile['github'])) $bio['github'] = $profile['github'];
            }

            if (isset($bioData['linkedin'])) {
                $bio['linkedin'] = $bioData['linkedin'];
            }else{
                if($profile && !empty($profile['linkedin'])) $bio['linkedin'] = $profile['linkedin'];
            }

            if (isset($bioData['twitter'])) {
                $bio['twitter'] = $bioData['twitter'];
            }else{
                if($profile && !empty($profile['twitter'])) $bio['twitter'] = $profile['twitter'];
            }

            if (isset($bioData['youtube'])) {
                $bio['youtube'] = $bioData['youtube'];
            }else{
                if($profile && !empty($profile['youtube'])) $bio['youtube'] = $profile['youtube'];
            }

            if (isset($bioData['website'])) {
                $bio['website'] = $bioData['website'];
            }else{
                if($profile && !empty($profile['website'])) $bio['website'] = $profile['website'];
            }

            return json_encode($bio);
        }

        return null;
    }


    public function changePassword(ChangePasswordRequest $request)
    {
        try {

            $user = Auth::user();


            if (!Hash::check($request->old_password, $user->password)) {
                return $this->respondError('Mật khẩu hiện tại không đúng');
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            $user->tokens->each(function ($token) {
                $token->delete();
            });

            return $this->respondOk('Mật khẩu của ' . $user->name . ' đã được thay đổi thành công. Vui lòng đăng nhập lại!');
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại.');
        }
    }

    public function getMyCourseBought(Request $request)
    {
        try {
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại.');
        }
    }
}
