<?php

namespace App\Http\Requests\API\Courses;

use App\Http\Requests\API\Bases\BaseFormRequest;
use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DeleteCourseMultipleRequest extends BaseFormRequest
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
                'exists:courses,id',
                function ($attribute, $value, $fail) {
                    $course = Course::query()->find($value);

                    if (!$course) {
                        $fail('Khóa học không tồn tại.');
                        return;
                    }

                    if ($course->user_id !== Auth::id()) {
                        $fail('Bạn không phải là người tạo khóa học này.');
                    }

                    if ($course->chapters()->count() > 0) {
                        $fail('Khóa học đang chứa chương học, không thể xóa.');
                    }
                }
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'Vui lòng chọn ít nhất một khóa học để xóa.',
            'ids.array' => 'Dữ liệu không hợp lệ.',
            'ids.*.required' => 'ID khóa học không hợp lệ.',
            'ids.*.integer' => 'ID khóa học phải là số nguyên.',
            'ids.*.exists' => 'Khóa học không tồn tại.'
        ];
    }
}
