<?php

namespace App\Http\Requests\API\Auth;

use App\Http\Requests\API\Bases\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends BaseFormRequest
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
            'token' => ['required'],
            'password' => ['required', 'string', 'max:255', 'confirmed', 'min:8']
        ];
    }

    public function messages()
    {
        return [
            'token.required' => 'Token là bắt buộc',

            'password.required' => 'Mật khẩu không được để trống',
            'password.string' => 'Mật khẩu phải là chuỗi',
            'password.max' => 'Mật khẩu không dài quá 255 kí tự',
            'password.min' => 'Mật khẩu phải có ít nhất 8 kí tự',
            'password.confirmed' => 'Mật khẩu xác nhận không đúng'
        ];
    }
}
