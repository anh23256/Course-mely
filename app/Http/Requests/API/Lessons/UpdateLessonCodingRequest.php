<?php

namespace App\Http\Requests\API\Lessons;

use App\Http\Requests\API\Bases\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonCodingRequest extends BaseFormRequest
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
            'title' => 'required|string|max:255',
            'instruct' => 'nullable|string',
            'content' => 'nullable|string',
            'language' => 'nullable|string',
            'hints' => 'nullable|array',
            'result_code' => 'nullable|string',
            'solution_code' => 'nullable|string',
            'sample_code' => 'nullable|string',
            'test_case' => 'nullable|array',
            'test_case.*.input' => 'required|string',
            'test_case.*.output' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Tiêu đề không được để trống',
            'title.string' => 'Tiêu đề phải là chuỗi',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự',
            'instruct.string' => 'Nội dung phải là chuỗi',
            'instruct.max' => 'Tiêu đề không được vượt quá 255 ký tự',
            'content.string' => 'Nội dung phải là chuỗi',
            'language.string' => 'Ngôn ngữ lập trình phải là chuỗi',
            'hints.array' => 'Gợi ý phải là mảng',
            'result_code.string' => 'Mã kết quả phải là chuỗi',
            'solution_code.string' => 'Mã lý thuyết phải là chuỗi',
            'sample_code.string' => 'Code mẫu phải là chuỗi',
            'test_case.array' => 'Test case phải là mảng',
            'test_case.*.input.required' => 'Input của test case là bắt buộc',
            'test_case.*.input.string' => 'Input của test case phải là chuỗi',
            'test_case.*.output.required' => 'Output của test case là bắt buộc',
            'test_case.*.output.string' => 'Output của test case phải là chuỗi',
        ];
    }
}
