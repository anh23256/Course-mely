<?php

namespace App\Http\Requests\API\Note;

use App\Http\Requests\API\Bases\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateNoteRequest extends BaseFormRequest
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
            'lesson_id' => 'required|integer',
            'content' => 'required|max:2000',
        ];
    }

    public function messages()
    {
        return [
            'lesson_id.required' => 'lesson_id là bắt buộc',
            'lesson_id.integer' => 'lesson_id phải là số nguyên',
            'content.required' => 'content là bắt buộc',
            'content.max' => 'content không được vượt quá 2000 kí tự'
        ];
    }
}
