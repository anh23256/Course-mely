<?php

namespace App\Http\Requests\API\Lessons;

use App\Http\Requests\API\Bases\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonVideoRequest extends BaseFormRequest
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
            'title' => 'sometimes|string|max:255',
            'video_file' => 'nullable|file|mimes:mp4,mov,avi|max:102400',
            'is_free_preview' => 'nullable|in:0,1',
            'content' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'title.string' => 'Tiêu đề phải là chuỗi',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự',
            'video_file.file' => 'File video phải là file',
            'video_file.mimes' => 'File video phải có định dạng mp4, mov, avi',
            'video_file.max' => 'File video không được vượt quá 100MB',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->video_file === 'null' || $this->video_file === '') {
            $this->request->remove('video_file');
        }
    }

}
