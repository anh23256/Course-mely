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
            'mux_asset_id' => 'sometimes|string',
            'mux_playback_id' => 'sometimes|string',
            'is_free_preview' => 'nullable|in:0,1',
            'content' => 'nullable|string',
            'duration' => 'nullable|numeric',
        ];
    }

    public function messages()
    {
        return [
            'title.string' => 'Tiêu đề phải là chuỗi',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự',
            'mux_asset_id.string' => 'Asset ID phải là chuỗi',
            'mux_playback_id.string' => 'Playback ID phải là chuỗi',
            'duration.numeric' => 'Thời lượng phải là số',
        ];
    }
}
