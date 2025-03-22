<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\MemberShip\StoreMemberShipPlanRequest;
use App\Http\Requests\API\MemberShip\UpdateMemberShipPlanRequest;
use App\Models\Approvable;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\MembershipPlan;
use App\Models\Rating;
use App\Models\User;
use App\Notifications\MemberShipPlanRequestNotification;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MemberShipPlanController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function getMemberShipPlans()
    {
        try {
            $instructor = Auth::user();

            if (!$instructor || !$instructor->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền thực hiện chức năng này');
            }

            $memberShipPlans = MembershipPlan::query()
                ->where('instructor_id', $instructor->id)->get();

            if (!$memberShipPlans) {
                return $this->respondNotFound('Không có dữ liệu');
            }

            $memberShipPlans->makeHidden([
                'updated_at'
            ]);

            return $this->respondOk('Danh sách gói thành viên của: ' . $instructor->name, $memberShipPlans);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function getMemberShipPlan(string $code)
    {
        try {
            $instructor = Auth::user();

            if (!$instructor || !$instructor->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền thực hiện chức năng này');
            }

            $memberShipPlan = MembershipPlan::query()
                ->with(['membershipCourseAccess' => function ($query) {
                    $query->select('id', 'name', 'slug', 'thumbnail')
                        ->where('status', 'approved');
                }])
                ->where('instructor_id', $instructor->id)
                ->where('code', $code)->first();

            if (!$memberShipPlan) {
                return $this->respondNotFound('Không có dữ liệu');
            }


            return $this->respondOk('Thông tin gói: ' . $memberShipPlan->code, $memberShipPlan);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function storeMemberShipPlan(StoreMemberShipPlanRequest $request)
    {
        try {
            DB::beginTransaction();

            $instructor = Auth::user();

            if (!$instructor || !$instructor->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền thực hiện chức năng này');
            }

            $data = $request->validated();

            $eligibilityCheck = $this->checkMembershipEligibility($instructor);

            if (!$eligibilityCheck['eligible']) {
                return $this->respondError($eligibilityCheck['message']);
            }

            if ($this->checkDuplicateMembershipPlan($data['name'], $data['duration_months'], $instructor->id)) {
                return $this->respondError('Bạn đã có gói thành viên với tên và thời hạn tương tự');
            }

            $uuid = Str::uuid();
            $data['code'] = substr($uuid, 0, 10);
            $data['instructor_id'] = $instructor->id;

            $membershipPlan = MembershipPlan::query()->create($data);

            if (!empty($data['course_ids']) && is_array($data['course_ids'])) {
                $approvedCourses = Course::query()
                    ->whereIn('id', $data['course_ids'])
                    ->where('user_id', $instructor->id)
                    ->where('status', 'approved')
                    ->pluck('id')
                    ->toArray();

                $membershipPlan->membershipCourseAccess()->sync($approvedCourses);
            }

            DB::commit();

            return $this->respondCreated('Tạo gói thành viên thành công', $membershipPlan);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function updateMemberShipPlan(UpdateMemberShipPlanRequest $request, string $code)
    {
        try {
            DB::beginTransaction();

            $instructor = Auth::user();

            if (!$instructor || !$instructor->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền thực hiện chức năng này');
            }

            $memberShipPlan = MembershipPlan::query()
                ->where('instructor_id', $instructor->id)
                ->where('code', $code)
                ->first();

            if (!$memberShipPlan) {
                return $this->respondNotFound('Không tìm thấy gói thành viên');
            }

            $data = $request->validated();

            $nameToCheck = $data['name'] ?? $memberShipPlan->name;
            $durationToCheck = $data['duration_months'] ?? $memberShipPlan->duration_months;

            if ($this->checkDuplicateMembershipPlan(
                $nameToCheck,
                $durationToCheck,
                $instructor->id,
                $memberShipPlan->id
            )) {
                return $this->respondError('Bạn đã có gói thành viên khác với tên và thời hạn tương tự');
            }

            if (isset($data['course_ids']) && is_array($data['course_ids'])) {
                $approvedCourses = Course::query()
                    ->whereIn('id', $data['course_ids'])
                    ->where('user_id', $instructor->id)
                    ->where('status', 'approved')
                    ->pluck('id')
                    ->toArray();

                $memberShipPlan->membershipCourseAccess()->sync($approvedCourses);

                unset($data['course_ids']);
            }

            $memberShipPlan->update($data);

            DB::commit();

            return $this->respondOk('Cập nhật gói thành viên thành công', $memberShipPlan);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function toggleStatus(string $code, string $action)
    {
        try {
            $instructor = Auth::user();

            if (!$instructor || !$instructor->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền thực hiện chức năng này');
            }

            $memberShipPlan = MembershipPlan::query()
                ->where('code', $code)
                ->where('instructor_id', $instructor->id)
                ->first();

            if (!$memberShipPlan) {
                return $this->respondNotFound('Không tìm thấy gói thành viên');
            }

            if ($memberShipPlan->status === 'draft') {
                return $this->respondError('Gói thành viên đang ở trạng thái nháp, không thể thay đổi trạng thái');
            } elseif ($memberShipPlan->status === 'pending') {
                return $this->respondError('Gói thành viên đang chờ duyệt, không thể thay đổi trạng thái');
            }

            $status = $action === 'enable' ? 'active' : 'inactive';

            $memberShipPlan->status = $status;
            $memberShipPlan->save();

            $message = $action === 'enable' ? 'Kích hoạt thành công' : 'Vô hiệu hóa thành công';
            return $this->respondOk($message);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function sendRequestMembershipPlan(Request $request, string $code)
    {
        try {
            DB::beginTransaction();

            $instructor = Auth::user();

            if (!$instructor || !$instructor->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền thực hiện chức năng này');
            }

            $memberShipPlan = MembershipPlan::query()
                ->with('membershipCourseAccess', function ($query) {
                    return $query->select('membership_plan_id', 'course_id');
                })
                ->where('code', $code)
                ->where('instructor_id', $instructor->id)
                ->first();

            if (!$memberShipPlan) {
                return $this->respondNotFound('Không tìm thấy gói thành viên');
            }

            $existingRequest = Approvable::query()->where([
                'approvable_type' => MembershipPlan::class,
                'approvable_id' => $memberShipPlan->id,
            ])->whereIn('status', ['pending', 'approved'])->first();

            if ($existingRequest) {
                if ($existingRequest->status === 'pending') {
                    return $this->respondError('Gói này đã có yêu cầu kiểm duyệt đang chờ xử lý');
                } else {
                    return $this->respondError('Gói này đã được phê duyệt trước đó');
                }
            }

            $courseCount = $memberShipPlan->membershipCourseAccess->count();
            $benefits = $this->decodeJson($memberShipPlan->benefits);

            if (empty($memberShipPlan->name) || strlen($memberShipPlan->name) < 5) {
                return $this->respondError("Gói thành viên phải có tên với tối thiểu 5 ký tự.");
            }

            if (empty($memberShipPlan->description) || strlen($memberShipPlan->description) < 100) {
                return $this->respondError("Mô tả gói thành viên phải có tối thiểu 100 ký tự.");
            }

            if (count($benefits) < 4 || count($benefits) > 10) {
                return $this->respondError('Lợi ích gói thành viên phải có từ 4 đến 10 mục.');
            }

            if ($courseCount < 5) {
                return $this->respondError('Gói phải có tối thiểu 5 khoá học để có thể gửi yêu cầu');
            }

            $memberShipPlan->status = 'active';
            $memberShipPlan->save();

            $approvalRequest = Approvable::query()->create([
                'approvable_type' => MembershipPlan::class,
                'approvable_id' => $memberShipPlan->id,
                'status' => 'approved',
                'request_date' => now(),
            ]);

            $managers = User::query()
                ->whereHas('roles', function ($query) {
                    $query->whereIn('name', ['admin', 'employee']);
                })
                ->get();

            foreach ($managers as $manager) {
                $manager->notify(new MemberShipPlanRequestNotification(
                    $memberShipPlan,
                    $instructor,
                    $approvalRequest
                ));
            }

            $instructor->notify(new MemberShipPlanRequestNotification(
                $memberShipPlan,
                $instructor,
                $approvalRequest
            ));

            DB::commit();

            return $this->respondCreated('Gửi yêu cầu kiểm duyệt thành công');
        } catch (\Exception $e) {
            $this->logError($e);

            DB::rollBack();

            return $this->respondServerError();
        }
    }

    protected function checkDuplicateMembershipPlan($name, $duration_months, $instructorId, $excludePlanId = null)
    {
        $query = MembershipPlan::query()
            ->where('instructor_id', $instructorId)
            ->where('name', $name)
            ->where('duration_months', $duration_months);

        if ($excludePlanId) {
            $query->where('id', '!=', $excludePlanId);
        }

        return $query->exists();
    }

    protected function checkMembershipEligibility($instructor)
    {
        $existingMembership = MembershipPlan::query()
            ->where('instructor_id', $instructor->id)
            ->count();

        if ($existingMembership >= 3) {
            return [
                'eligible' => false,
                'message' => 'Bạn chỉ có thể tạo tối đa 3 gói membership'
            ];
        }

        $approvalCourse = Course::query()
            ->where('user_id', $instructor->id)
            ->where('status', 'approved')
            ->count();

        if ($approvalCourse < 5) {
            return [
                'eligible' => false,
                'message' => 'Bạn cần có ít nhất 5 khoá học để tạo gói membership'
            ];
        }

        $studentCount = CourseUser::query()
            ->whereIn('course_id', function ($query) use ($instructor) {
                $query->select('id')->from('courses')->where('user_id', $instructor->id);
            })
            ->distinct('user_id')
            ->count();

        if ($studentCount < 50) {
            return [
                'eligible' => false,
                'message' => 'Bạn cần có ít nhất 50 học viên đăng ký khoá học'
            ];
        }

        $avgRatings = Rating::query()
            ->whereIn('course_id', function ($query) use ($instructor) {
                $query->select('id')->from('courses')->where('user_id', $instructor->id);
            })->avg('rate');

        if ($avgRatings < 3.0) {
            return [
                'eligible' => false,
                'message' => 'Đánh giá trung bình của bạn cần đạt ít nhất 3.0/5.0'
            ];
        }

        return [
            'eligible' => true,
            'message' => 'Bạn đủ điều kiện để tạo membership'
        ];
    }
}
