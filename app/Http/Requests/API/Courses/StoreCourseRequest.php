<?php

namespace App\Http\Requests\API\Courses;

use App\Http\Requests\API\Bases\BaseFormRequest;

class StoreCourseRequest extends BaseFormRequest
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
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'isPracticalCourse' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Tên khóa học không được để trống',
            'name.string' => 'Tên khóa học phải là chuỗi',
            'name.max' => 'Tên khóa học không được vượt quá 255 ký tự',
            'category_id.required' => 'Danh mục không được để trống',
            'category_id.integer' => 'Danh mục phải là số nguyên',
            'category_id.exists' => 'Không tìm thấy danh mục',
            'isPracticalCourse.boolean' => 'Loại khoá học phải là true hoặc false',
        ];
    }
}
