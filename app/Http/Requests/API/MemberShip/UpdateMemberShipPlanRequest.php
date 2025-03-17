<?php

namespace App\Http\Requests\API\MemberShip;

use App\Http\Requests\API\Bases\BaseFormRequest;
use App\Models\MembershipPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberShipPlanRequest extends BaseFormRequest
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
        $membershipPlan = MembershipPlan::query()->where('code', $this->route('code'))->first();

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('membership_plans', 'name')->ignore(optional($membershipPlan)->id)
            ],
            'price' => 'sometimes|required|numeric|min:0',
            'duration_months' => 'sometimes|required|integer|between:1,12',
            'description' => 'sometimes|nullable|string',
            'benefits' => 'sometimes|required|array',
            'benefits.*' => 'required|string',
            'course_ids' => 'sometimes|array',
            'course_ids.*' => 'exists:courses,id'
        ];
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
            'duration_months.between' => 'Thời hạn gói thành viên phải từ 1 đến 12 tháng',

            'benefits.required' => 'Quyền lợi thành viên không được để trống',
            'benefits.array' => 'Quyền lợi thành viên phải là danh sách',
            'benefits.*.required' => 'Quyền lợi thành viên không được để trống',
            'benefits.*.string' => 'Quyền lợi thành viên phải là chuỗi ký tự',

            'course_ids.array' => 'Danh sách khóa học phải là một mảng.',
            'course_ids.*.exists' => 'Khóa học không tồn tại.',
        ];
    }
}
