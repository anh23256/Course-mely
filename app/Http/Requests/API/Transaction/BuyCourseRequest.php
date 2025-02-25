<?php

namespace App\Http\Requests\API\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class BuyCourseRequest extends FormRequest
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
            'slug' => ['required', 'exists:courses,slug'],
            'amount' => ['required', 'numeric'],
            'coupon_code' => ['nullable', 'string']
        ];
    }

    public function messages(): array
    {
        return [
            'slug.required' => 'Vui lòng nhập khóa học.',
            'slug.exists' => 'Khóa học không tồn tại.',

            'amount.required' => 'Vui lòng nhập số tiền.',
            'amount.numeric' => 'Định dạng số tiền không đúng. Vui lòng nhập số.',

            'coupon_code.string' => 'Mã giảm giá phải là chuỗi ký tự hợp lệ.',
        ];
    }
}
