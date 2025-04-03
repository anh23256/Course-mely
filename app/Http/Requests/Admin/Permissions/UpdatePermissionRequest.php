<?php

namespace App\Http\Requests\Admin\Permissions;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
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
       // Lấy ID của quyền từ route hoặc request
       $permissionId = $this->route('permission') ?? $this->input('id');

       return [
           'name' => [
               'required',
               Rule::unique('permissions', 'name')->ignore($permissionId),
           ],
           'description' => 'required|string',
       ];
    }
    public function messages()
    {
        return [
            'name.required' => 'Tên quyền không được để trống',
            'name.unique' => 'Tên quyền quyền đã tồn tại',
            'description.required' => 'Nhập mô tả của quyền này',
        ];
    }
}
