<?php

namespace App\Http\Requests\API\User;

use App\Http\Requests\API\Bases\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ChangePasswordRequest extends BaseFormRequest
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
            'old_password' => ['required', 'string', 'max:255'],
            'new_password'   => ['required', 'string', 'min:8', 'max:255', 'regex:/^(?=.*[A-Z])/', Rule::notIn($this->input('old_password'))],
            'confirm_new_password' => ['required', 'same:new_password'],
        ];
    }

    public function messages()
    {
        return [
            // Mật khẩu
            'old_password.required'  => 'Mật khẩu là bắt buộc.',
            'old_password.string'    => 'Định dạng mật khẩu không hợp lệ.',
            'old_password.max'       => 'Mật khẩu không được vượt quá 255 ký tự.',

            // Mật khẩu
            'new_password.required'  => 'Mật khẩu là bắt buộc.',
            'new_password.string'    => 'Định dạng mật khẩu không hợp lệ.',
            'new_password.min'       => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'new_password.max'       => 'Mật khẩu không được vượt quá 255 ký tự.',
            'new_password.regex'     => 'Mật khẩu phải chứa ít nhất một chữ cái viết hoa.',
            'new_password.not_in'    => 'Mật khẩu mới không được trùng với mật khẩu hiện tại',

            // Repassword
            'confirm_new_password.required' => 'Vui lòng xác nhận mật khẩu.',
            'confirm_new_password.same' => 'Mật khẩu và xác nhận mật khẩu không khớp.',
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = Auth::user();
            if (!Hash::check($this->input('old_password'), $user->password)) {
                $validator->errors()->add('old_password', 'Mật khẩu hiện tại không chính xác.');
            }
        });
    }
}
