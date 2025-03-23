<?php

namespace App\Http\Requests\API\Lessons;

use App\Http\Requests\API\Bases\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderQuestionRequest extends BaseFormRequest
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
            '*.id' => 'required|integer|exists:questions,id',
            '*.order' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            '*.id.required' => 'ID câu hỏi là bắt buộc.',
            '*.id.integer' => 'ID câu hỏi phải là số nguyên.',
            '*.id.exists' => 'Câu hỏi không tồn tại.',
            '*.order.required' => 'Thứ tự câu hỏi là bắt buộc.',
            '*.order.integer' => 'Thứ tự câu hỏi phải là số nguyên.',
            '*.order.min' => 'Thứ tự câu hỏi không được nhỏ hơn 0.',
        ];
    }
}
