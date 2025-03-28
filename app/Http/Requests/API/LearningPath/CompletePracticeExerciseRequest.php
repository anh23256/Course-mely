<?php

namespace App\Http\Requests\API\LearningPath;

use App\Http\Requests\API\Bases\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class CompletePracticeExerciseRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|integer|exists:questions,id',
            'answers.*.answer_id' => 'required',
            'answers.*.answer_id.*' => 'integer|exists:answers,id',
        ];
    }

    public function messages()
    {
        return [
            'answers.required' => 'Danh sách câu trả lời là bắt buộc.',
            'answers.array' => 'Danh sách câu trả lời phải là một mảng.',
            'answers.*.question_id.required' => 'ID câu hỏi là bắt buộc.',
            'answers.*.question_id.integer' => 'ID câu hỏi phải là số nguyên.',
            'answers.*.question_id.exists' => 'Câu hỏi không tồn tại.',
            'answers.*.answer_id.required' => 'ID câu trả lời là bắt buộc.',
            'answers.*.answer_id.*.integer' => 'ID câu trả lời phải là số nguyên.',
            'answers.*.answer_id.*.exists' => 'Câu trả lời không tồn tại.',
        ];
    }
}
