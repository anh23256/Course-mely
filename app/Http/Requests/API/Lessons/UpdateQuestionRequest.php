<?php

namespace App\Http\Requests\API\Lessons;

use App\Http\Requests\API\Bases\BaseFormRequest;

class UpdateQuestionRequest extends BaseFormRequest
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
        $rules = [
            'question' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'answer_type' => 'sometimes|in:single_choice,multiple_choice',
            'options' => 'sometimes|array|min:2|max:5',
            'options.*.answer' => 'required_with:options|string',
            'options.*.is_correct' => 'required_with:options|boolean',
        ];

        if ($this->hasFile('image')) {
            $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048';
        }

        return $rules;
    }

    protected function prepareForValidation()
    {
        if ($this->has('options')) {
            $this->merge([
                'options' => collect($this->input('options', []))->map(function ($option) {
                    return [
                        'answer' => $option['answer'] ?? '',
                        'is_correct' => filter_var($option['is_correct'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                    ];
                })->toArray(),
            ]);
        }
    }

    public function messages()
    {
        return [
            'question.string' => 'Câu hỏi phải là chuỗi.',
            'question.max' => 'Câu hỏi không được quá 255 ký tự.',
            'description.string' => 'Mô tả phải là chuỗi.',
            'answer_type.in' => 'Loại câu trả lời không hợp lệ.',
            'options.array' => 'Danh sách câu trả lời phải là mảng.',
            'options.min' => 'Danh sách câu trả lời phải có ít nhất 2 phần tử.',
            'options.max' => 'Danh sách câu trả lời không được quá 5 phần tử.',
            'options.*.answer.required_with' => 'Câu trả lời là bắt buộc khi có danh sách câu trả lời.',
            'options.*.answer.string' => 'Câu trả lời phải là chuỗi.',
            'options.*.is_correct.required_with' => 'Trạng thái đúng/sai là bắt buộc khi có danh sách câu trả lời.',
            'options.*.is_correct.boolean' => 'Trạng thái đúng/sai phải là boolean.',
            'image.image' => 'File phải là hình ảnh.',
            'image.mimes' => 'Hình ảnh phải có định dạng: jpeg, png, jpg, gif, svg hoặc webp.',
            'image.max' => 'Kích thước hình ảnh không được vượt quá 2MB.',
        ];
    }
}
