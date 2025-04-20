<?php

namespace App\Http\Requests\Admin\Chats;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupChatRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'members' => ['required', 'array', 'min:2'],
            'members.*' => ['exists:users,id'],
        ];
    }
    public function messages()
    {
        return [
            'name.string' =>'Tên phải là chuỗi',
            'name.max'=> 'Tên không được quá 255 kí tự',
            'members.required' => 'Nhóm phải có ít nhất 2 thành viên khác.',
            'members.array' => 'Danh sách thành viên không hợp lệ.',
            'members.min' => 'Nhóm phải có ít nhất 3 người (bao gồm bạn).',
            'members.*.exists' => 'Thành viên không hợp lệ.',
        ];
    }
}
