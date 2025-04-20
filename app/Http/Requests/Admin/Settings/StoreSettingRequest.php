<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Foundation\Http\FormRequest;

class StoreSettingRequest extends FormRequest
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
    public function rules()
    {
        return [
            'key'   => ['required', 'string', 'max:255', 'unique:settings,key'],
            'label' => ['required','string','max:255'],
            'type'  => ['required','in:text,textarea,image'],
        ];
        
        if ($setting && $setting->type === 'image') {
            $rules['value'] = ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'];
        } else {
            $rules['value'] = ['nullable', 'string'];
        }

        return $rules;
    }

    /**
     * Get the custom error messages for the validator.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'label.required' => 'Trường nhãn (label) là bắt buộc.',
            'label.string'   => 'Label phải là chuỗi ký tự.',
            'label.max'      => 'Label không được vượt quá 255 ký tự.',

            'key.required'   => 'Trường key là bắt buộc.',
            'key.string'     => 'Trường key phải là chuỗi ký tự.',
            'key.max'        => 'Trường key không được vượt quá 255 ký tự.',
            'key.unique'     => 'Trường key đã tồn tại trong hệ thống.',

            'type.required'  => 'Vui lòng chọn loại setting.',
            'type.in'        => 'Loại setting không hợp lệ. Vui lòng chọn một trong các loại: text, textarea, image.',

            'value.string'   => 'Value phải là chuỗi ký tự.',
            'value.image'    => 'Tệp tải lên phải là ảnh.',
            'value.mimes'    => 'Ảnh phải có định dạng: jpeg, png, jpg, gif, svg, webp.',
            'value.max'      => 'Ảnh không được vượt quá 2MB.',
        ];
    }
}
