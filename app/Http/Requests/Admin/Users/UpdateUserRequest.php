<?php

namespace App\Http\Requests\Admin\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateUserRequest extends FormRequest
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
        $roles = Role::query()->get()->pluck('name')->toArray();

        $roles = array_values($roles);

        return [
            'name'       => ['required', 'string', 'min:2', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'avatar'     => ['nullable', 'image', 'max:2000'],
            'status'     => ['required', 'in:active,inactive,blocked'],
            'role' => [
                'required',
                'in:' . implode(',', $roles),
            ],
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

            //Trạng thái
            'status.required' => 'Trạng thái là bắt buộc.',
            'status.in' => 'Trạng thái phải là một trong các giá trị: active, inactive, hoặc blocked.',

            // Vai trò
            'role.required' => 'Vai trò là bắt buộc.',
            'role.in'       => 'Vai trò không hợp lệ.',
        ];
    }
}
