<?php

namespace App\Http\Requests\API\MemberShip;

use App\Http\Requests\API\Bases\BaseFormRequest;
use App\Models\MembershipPlan;
use Illuminate\Foundation\Http\FormRequest;

class StoreMemberShipPlanRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:membership_plans,name',
            'price' => 'required|numeric|min:0',
            'duration_months' => 'required|integer|in:3,6,12',
            'description' => 'nullable|string',
            'benefits' => 'required|array',
            'benefits.*' => 'required|string',
            'course_ids' => 'required|array',
            'course_ids.*' => 'exists:courses,id'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $durationMonths = $this->input('duration_months');
            $courseIds = $this->input('course_ids');
            $instructorId = auth()->id();

            if (!$courseIds) {
                return;
            }

            $existingPlan = MembershipPlan::query()->where('duration_months', $durationMonths)
                ->where('instructor_id', $instructorId)
                ->first();
            if ($existingPlan) {
                $validator->errors()->add(
                    'duration_months',
                    'Đã tồn tại một gói thành viên với thời hạn (' . $durationMonths . ' tháng). Vui lòng chọn thời hạn khác.'
                );
                return;
            }

            if ($durationMonths == 3) {
                if (count($courseIds) < 5) {
                    $validator->errors()->add('course_ids', 'Gói thành viên 3 tháng phải có tối thiểu 5 khóa học.');
                }
            } elseif ($durationMonths == 6) {
                if (count($courseIds) < 5) {
                    $validator->errors()->add('course_ids', 'Gói thành viên 6 tháng phải có tối thiểu 10 khóa học.');
                }
            } elseif ($durationMonths == 12) {
                if (count($courseIds) < 5) {
                    $validator->errors()->add('course_ids', 'Gói thành viên 12 tháng phải có tối thiểu 10 khóa học.');
                }
            }

            $this->validateCourseUniqueness($validator, $courseIds, $instructorId, $durationMonths);
        });
    }

    protected function validateCourseUniqueness($validator, $courseIds, $instructorId, $currentDuration)
    {
        if ($currentDuration == 12) {
            return;
        }

        $otherMembershipPlans = MembershipPlan::query()->where('instructor_id', $instructorId)
            ->where('duration_months', '!=', $currentDuration)
            ->get();

        foreach ($otherMembershipPlans as $plan) {
            $planCourseIds = $plan->membershipCourseAccess()->pluck('course_id')->toArray();

            if (empty($planCourseIds)) {
                continue;
            }

            $overlap = array_intersect($courseIds, $planCourseIds);
            $overlapCount = count($overlap);

            $overlapPercentage = ($overlapCount / count($courseIds)) * 100;
            $reverseOverlapPercentage = ($overlapCount / count($planCourseIds)) * 100;

            $maxOverlapPercentage = max($overlapPercentage, $reverseOverlapPercentage);

            if ($maxOverlapPercentage >= 80) {
                $validator->errors()->add(
                    'course_ids',
                    'Gói thành viên ' . $currentDuration . ' tháng không được trùng trên 80% nội dung với gói "' .
                        $plan->name . '" (' . $plan->duration_months . ' tháng).'
                );
                break;
            }
        }
    }

    public function messages()
    {
        return [
            'name.required' => 'Tên gói thành viên không được để trống',
            'name.string' => 'Tên gói thành viên phải là chuỗi ký tự',
            'name.max' => 'Tên gói thành viên không được vượt quá 255 ký tự',
            'name.unique' => 'Tên gói thành viên đã tồn tại. Vui lòng chọn tên khác.',

            'price.required' => 'Giá gói thành viên không được để trống',
            'price.numeric' => 'Giá gói thành viên phải là số',
            'price.min' => 'Giá gói thành viên không được âm',

            'description.string' => 'Mô tả phải là chuỗi ký tự',

            'duration_months.required' => 'Thời hạn gói thành viên không được để trống',
            'duration_months.integer' => 'Thời hạn gói thành viên phải là số nguyên',
            'duration_months.in' => 'Thời hạn gói thành viên chỉ được chọn 3, 6 hoặc 12 tháng',

            'benefits.required' => 'Quyền lợi thành viên không được để trống',
            'benefits.array' => 'Quyền lợi thành viên phải là danh sách',
            'benefits.*.required' => 'Quyền lợi thành viên không được để trống',
            'benefits.*.string' => 'Quyền lợi thành viên phải là chuỗi ký tự',

            'course_ids.required' => 'Danh sách khóa học không được để trống',
            'course_ids.array' => 'Danh sách khóa học phải là một mảng.',
            'course_ids.*.exists' => 'Khóa học không tồn tại.',
        ];
    }
}
