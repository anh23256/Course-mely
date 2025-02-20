<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Jobs\AutoApproveCourseJob;
use App\Mail\CourseSubmitMail;
use App\Models\Approvable;
use App\Models\Course;
use App\Models\User;
use App\Notifications\CourseSubmittedNotification;
use App\Services\CourseValidatorService;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendRequestController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function submitCourse(Request $request, string $slug)
    {
        try {
            $course = Course::query()
                ->where('slug', $slug)
                ->first();

            if (!$course) {
                return $this->respondNotFound('Khóa học không tồn tại');
            }

            if ($course->user_id !== auth()->id()) {
                return $this->respondNotFound('Bạn không có quyền truy cập');
            }

//            $errors = CourseValidatorService::validateCourse($course);
//
//            if (!empty($errors)) {
//                return $this->respondValidationFailed('Khoá học chưa đạt yêu cầu kiểm duyệt', $errors);
//            }

            $status = $course->status;

            $approvable = Approvable::query()->firstOrNew([
                'approvable_id' => $course->id,
                'approvable_type' => Course::class
            ]);
//            dd($approvable);

            DB::beginTransaction();

            switch ($status) {
                case 'draft':
                    $approvable->status = 'pending';
                    $approvable->request_date = now();
                    $approvable->save();
                    $course->update(['status' => 'pending']);

                    $course->update([
                        'status' => 'pending',
                    ]);

                    $managers = User::query()->role([
                        'admin',
                    ])->get();

                    foreach ($managers as $manager) {
                        $manager->notify(new CourseSubmittedNotification($course));
                    }

                    DB::commit();

                    AutoApproveCourseJob::dispatch($course)->delay(now()->addSeconds(20));

                    return $this->respondCreated('Gửi yêu cầu thành công');
                case 'pending':
                    $approvable->delete();
                    $course->update(['status' => 'draft']);

                    $managers = User::query()->role([
                        'admin',
                    ])->get();

                    foreach ($managers as $manager) {
                        $manager->notifications()
                            ->whereJsonContains('data->course_id', $course->id)
                            ->whereJsonContains('data->type', 'register_course')
                            ->delete();
                    }

                    foreach ($managers as $manager) {
                        $manager->notify(new CourseSubmittedNotification($course));
                    }

                    DB::commit();

                    AutoApproveCourseJob::dispatch($course)->delay(now()->addSeconds(20));

                    return $this->respondOk('Hủy yêu cầu kiểm duyệt thành công');
                case 'approved':
                    DB::rollBack();
                    return $this->respondError('Khóa học đã được duyệt, không thể gửi yêu cầu');

                case 'rejected':
                    $approvable->status = 'pending';
                    $approvable->request_date = now();
                    $approvable->save();
                    $course->update(['status' => 'pending']);

                    $managers = User::query()->role([
                        'admin',
                    ])->get();

                    foreach ($managers as $manager) {
                        $manager->notifications()
                            ->whereJsonContains('data->course_id', $course->id)
                            ->whereJsonContains('data->type', 'register_course')
                            ->delete();
                    }

                    foreach ($managers as $manager) {
                        $manager->notify(new CourseSubmittedNotification($course));
                    }

                    DB::commit();

                    AutoApproveCourseJob::dispatch($course)->delay(now()->addSeconds(20));
                    return $this->respondCreated('Gửi lại yêu cầu kiểm duyệt thành công');
                default:
                    DB::rollBack();
                    return $this->respondBadRequest('Trạng thái khóa học không hợp lệ');
            }
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }
}
