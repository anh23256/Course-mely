<?php

namespace App\Http\Requests\Admin\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class UpdateProfileRequest extends FormRequest
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
            //
            'name'             => ['required', 'string', 'min:2', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'avatar'           => ['nullable', 'image', 'max:2000'],
            'current_password' => [
                'nullable',
                'string',
                'min:8',
                function ($attribute, $value, $fail) {
                    if (request()->filled('password') && !request()->filled('current_password')) {
                        $fail('Bạn phải nhập mật khẩu hiện tại để thay đổi mật khẩu.');
                    }
                    if (request()->filled('current_password') && !Hash::check($value, auth()->user()->password)) {
                        $fail('Mật khẩu hiện tại không đúng.');
                    }
                }
            ],
            'password'         => ['nullable', 'string', 'min:8', 'confirmed'   ],
        ];
    }

    public function messages()
    {
        return [
            // Tên
            'name.required' => 'Tên là bắt buộc.',
            'name.string'   => 'Định dạng tên không hợp lệ.',
            'name.regex'    => 'Định dạng tên không hợp lệ.',
            'name.min'      => 'Tên phải có ít nhất 2 ký tự',
            'name.max'      => 'Tên không được vượt quá 255 ký tự.',

            // Avatar
            'avatar.image'  => 'Hình ảnh đại diện phải là một tệp hình ảnh.',
            'avatar.max'    => 'Hình ảnh đại diện không được vượt quá 2MB.',

            //Password
            'current_password.min' => 'Mật khẩu hiện tại phải có ít nhất 8 ký tự.',
            'current_password.string' => 'Mật khẩu hiện tại không hợp lệ.',

            'password.required'  => 'Vui lòng nhập mật khẩu mới.',
            'password.min'       => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Mật khẩu mới không khớp, vui lòng nhập lại',

        ];
    }
}
