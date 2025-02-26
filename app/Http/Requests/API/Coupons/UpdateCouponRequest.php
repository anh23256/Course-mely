<?php

namespace App\Http\Requests\API\Coupons;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCouponRequest extends FormRequest
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
            'code'             => 'required|string|unique:coupons,code,' . $this->coupon->id,
            'name'             => 'required|string|max:255',
            'discount_type'    => 'required|in:percentage,fixed',
            'discount_value'   => 'required|numeric|min:0',
            'start_date'       => 'required|date',
            'expire_date'      => 'required|date|after:start_date',
            'description'      => 'required|string',
            
        ];
    }

    public function messages()
    {
        return [
            'code.required' => 'Mã giảm giá không được để trống.',
            'code.unique' => 'Mã giảm giá đã tồn tại.',
            'discount_type.in' => 'Loại giảm giá chỉ có thể là percentage hoặc fixed.',
            'discount_value.min' => 'Giá trị giảm không thể nhỏ hơn 0.',
            'expire_date.after' => 'Ngày hết hạn phải sau ngày bắt đầu.',
        ];
    }
}
