<?php

namespace App\Http\Requests\API\Coupons;

use Illuminate\Foundation\Http\FormRequest;

class StoreCouponRequest extends FormRequest
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
            'code'                 => 'required|string|unique:coupons,code|max:255',
            'name'                 => 'required|string|max:255',
            'discount_type'        => 'required|in:percentage,fixed',
            'discount_value'       => 'required|numeric|min:0',
            'start_date'           => 'required|date',
            'expire_date'          => 'required|date|after:start_date',
            'description'          => 'required|string',
            
        ];
    }

    public function messages(): array
    {
        return [
            'code.required'           => 'Mã giảm giá không được để trống.',
            'code.string'             => 'Mã giảm giá phải là chuỗi ký tự.',
            'code.unique'             => 'Mã giảm giá này đã tồn tại, vui lòng chọn mã khác.',
            'code.max'                => 'Mã giảm giá không quá 255 kí tự',

            'name.required'           => 'Tên giảm giá không được để trống.',
            'name.string'             => 'Tên mã giảm giá phải là chuỗi ký tự.',
            'name.max'                => 'Tên giảm giá không quá 255 kí tự',

            'discount_type.required'  => 'Loại giảm giá không được để trống.',
            'discount_type.in'        => 'Loại giảm giá phải là "percentage" hoặc "fixed".',

            'discount_value.required' => 'Giá trị giảm giá không được để trống.',
            'discount_value.numeric'  => 'Giá trị giảm giá phải là số.',
            'discount_value.min'      => 'Giá trị giảm giá phải lớn hơn hoặc bằng 0.',

            'start_date.required'     => 'Ngày bắt đầu không được để trống.',
            'start_date.date'         => 'Ngày bắt đầu phải là định dạng ngày hợp lệ.',

            'expire_date.required'    => 'Ngày hết hạn không được để trống.',
            'expire_date.date'        => 'Ngày hết hạn phải là định dạng ngày hợp lệ.',
            'expire_date.after'       => 'Ngày hết hạn phải lớn hơn ngày bắt đầu.',

            'description.required'     => 'Mô tả không được để trống.',
            'description.string'      => 'Mô tả phải là chuỗi ký tự.',
        ];
    }
}
