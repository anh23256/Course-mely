<?php

namespace App\Http\Requests\API\Coupons;

use App\Http\Requests\API\Bases\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends BaseFormRequest
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
            'code' => 'sometimes|string|max:255|unique:coupons,code,' . $this->route('couponId') . ',id',
            'name' => 'sometimes|string|max:255',
            'discount_type' => 'sometimes|in:percentage,fixed',
            'discount_value' => [
                'sometimes',
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
                },
            ],
            'discount_max_value' => 'sometimes|numeric|min:1|nullable|required_if:discount_type,percentage',
            'start_date' => 'sometimes|date',
            'expire_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'max_usage' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:0,1',
            'user_ids' => 'array|nullable',
            'user_ids.*' => 'exists:users,id',
        ];
    }

    public function messages()
    {
        return [
            'code.sometimes' => 'Mã giảm giá không bắt buộc nhưng cần phải là chuỗi nếu được cung cấp.',
            'code.string' => 'Mã giảm giá phải là chuỗi ký tự.',
            'code.unique' => 'Mã giảm giá này đã tồn tại, vui lòng chọn mã khác.',
            'code.max' => 'Mã giảm giá không được vượt quá 255 ký tự.',

            'name.sometimes' => 'Tên mã giảm giá không bắt buộc nhưng cần phải là chuỗi nếu được cung cấp.',
            'name.string' => 'Tên mã giảm giá phải là chuỗi ký tự.',
            'name.max' => 'Tên mã giảm giá không được vượt quá 255 ký tự.',

            'discount_type.sometimes' => 'Loại giảm giá không bắt buộc nhưng phải hợp lệ nếu được cung cấp.',
            'discount_type.in' => 'Loại giảm giá không hợp lệ.',

            'discount_value.sometimes' => 'Giá trị giảm giá không bắt buộc nhưng cần phải là số nếu được cung cấp.',
            'discount_value.numeric' => 'Giá trị giảm giá phải là số hợp lệ.',
            'discount_value.min' => 'Giá trị giảm giá phải lớn hơn hoặc bằng 0.',

            'discount_value.fixed_range' => 'Giá trị giảm giá phải nằm trong khoảng từ 10.000 đến 1.000.000 VND nếu giảm giá theo tiền.',
            'discount_value.percentage_range' => 'Giá trị giảm giá phải nằm trong khoảng từ 10% đến 100% nếu giảm giá theo phần trăm.',

            'discount_max_value.sometimes' => 'Giá trị giảm tối đa không bắt buộc nhưng phải hợp lệ nếu được cung cấp.',
            'discount_max_value.numeric' => 'Giá trị giảm tối đa phải là số.',
            'discount_max_value.min' => 'Giá trị giảm tối đa phải lớn hơn hoặc bằng 1.',
            'discount_max_value.required_if' => 'Giá trị giảm tối đa là bắt buộc nếu loại giảm giá là phần trăm.',

            'start_date.sometimes' => 'Ngày bắt đầu không bắt buộc nhưng phải là ngày hợp lệ nếu được cung cấp.',
            'start_date.date' => 'Ngày bắt đầu phải là ngày hợp lệ.',

            'expire_date.date' => 'Ngày hết hạn phải là ngày hợp lệ.',
            'expire_date.after_or_equal' => 'Ngày hết hạn phải sau hoặc bằng ngày bắt đầu.',

            'description.string' => 'Mô tả phải là chuỗi ký tự.',

            'status.sometimes' => 'Trạng thái không bắt buộc nhưng cần hợp lệ nếu được cung cấp.',
            'status.in' => 'Trạng thái không hợp lệ (chỉ bao gồm "0" hoặc "1").',

            'user_ids.array' => 'Danh sách người dùng cần xóa phải là một mảng.',
            'user_ids.*.exists' => 'Một hoặc nhiều người dùng không tồn tại.',
        ];
    }
}
