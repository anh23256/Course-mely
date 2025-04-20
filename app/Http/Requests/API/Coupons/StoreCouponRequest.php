<?php

namespace App\Http\Requests\API\Coupons;

use App\Http\Requests\API\Bases\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreCouponRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => 'required|string|unique:coupons,code|max:255',
            'name' => 'required|string|max:255',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ($this->input('discount_type') === 'fixed') {
                        if ($value < 10000 || $value > 1000000) {
                            $fail('Giá trị giảm giá theo tiền phải nằm trong khoảng từ 10.000 đến 1.000.000 VND.');
                        }
                    } elseif ($this->input('discount_type') === 'percentage') {
                        if ($value < 10 || $value > 100) {
                            $fail('Giá trị giảm giá theo phần trăm phải nằm trong khoảng từ 10% đến 100%.');
                        }
                    }
                }
            ],
            'discount_max_value' => 'required_if:discount_type,percentage|numeric|min:1',
            'start_date' => 'nullable|date',
            'expire_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'specific_course' => 'nullable|in:1,0',
            'max_usage' => 'nullable|numeric|min:0',
            'user_ids' => 'array|nullable',
            'user_ids.*' => 'exists:users,id',
            'course_ids' => 'array|nullable',
            'course_ids.*' => 'exists:courses,id'
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Mã giảm giá là bắt buộc.',
            'code.string' => 'Mã giảm giá phải là chuỗi ký tự.',
            'code.unique' => 'Mã giảm giá đã tồn tại, vui lòng chọn mã khác.',
            'code.max' => 'Mã giảm giá không được dài quá 255 ký tự.',

            'name.required' => 'Tên mã giảm giá là bắt buộc.',
            'name.string' => 'Tên mã giảm giá phải là chuỗi ký tự.',
            'name.max' => 'Tên mã giảm giá không được dài quá 255 ký tự.',

            'discount_type.required' => 'Loại giảm giá là bắt buộc.',
            'discount_type.in' => 'Loại giảm giá không hợp lệ.',

            'discount_value.required' => 'Giá trị giảm giá là bắt buộc.',
            'discount_value.numeric' => 'Giá trị giảm giá phải là số.',
            'discount_value.min' => 'Giá trị giảm giá phải lớn hơn hoặc bằng 0.',

            'discount_value.fixed_range' => 'Giá trị giảm giá phải nằm trong khoảng từ 10.000 đến 1.000.000 VND nếu giảm giá theo tiền.',
            'discount_value.percentage_range' => 'Giá trị giảm giá phải nằm trong khoảng từ 10% đến 100% nếu giảm giá theo phần trăm.',

            'discount_max_value.required_if' => 'Giá trị giảm tối đa là bắt buộc khi loại giảm giá là phần trăm.',
            'discount_max_value.numeric' => 'Giá trị giảm tối đa phải là số.',
            'discount_max_value.min' => 'Giá trị giảm tối đa phải lớn hơn hoặc bằng 1.',

            'start_date.required' => 'Ngày bắt đầu là bắt buộc.',
            'start_date.date' => 'Ngày bắt đầu phải là ngày hợp lệ.',

            'expire_date.date' => 'Ngày hết hạn phải là ngày hợp lệ.',
            'expire_date.after_or_equal' => 'Ngày hết hạn phải sau hoặc bằng ngày bắt đầu.',

            'description.string' => 'Mô tả phải là chuỗi ký tự.',

            'user_ids.array' => 'Danh sách người dùng phải là một mảng.',
            'user_ids.*.exists' => 'Một hoặc nhiều người dùng không tồn tại.',

            'course_ids.array' => 'Danh sách khóa học phải là một mảng.',
            'course_ids.*.exists' => 'Khóa học không tồn tại.',
        ];
    }
}
