<?php

namespace App\Http\Requests\API\Courses;

use App\Http\Requests\API\Bases\BaseFormRequest;
use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RestoreCourseMultipleRequest extends BaseFormRequest
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
            'ids' => 'required|array',
            'ids.*' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $course = Course::query()->withTrashed()->find($value);

                    if (!$course) {
                        $fail('Khóa học không tồn tại.');
                        return;
                    }

                    if (!$course->trashed()) {
                        $fail('Khóa học chưa bị xóa.');
                        return;
                    }

                    if ($course->user_id !== Auth::id()) {
                        $fail('Bạn không phải là người tạo khóa học này.');
                    }
                }
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'Vui lòng chọn ít nhất một khóa học để khôi phục.',
            'ids.array' => 'Dữ liệu không hợp lệ.',
            'ids.*.required' => 'ID khóa học không hợp lệ.',
            'ids.*.integer' => 'ID khóa học phải là số nguyên.'
        ];
    }
}
