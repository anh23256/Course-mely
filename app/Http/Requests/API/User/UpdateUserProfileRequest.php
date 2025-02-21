<?php

namespace App\Http\Requests\API\User;

use App\Http\Requests\API\Bases\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserProfileRequest extends BaseFormRequest
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
            'name' => 'sometimes|string|max:255',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,webp,png|max:2048',
            'phone' => 'sometimes|string|max:15',
            'address' => 'sometimes|string|max:255',
            'experience' => 'sometimes|string',
            'certificates' => 'nullable|array',
            'certificates.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:2048',
            'bio' => 'nullable|array',
            'bio.facebook' => 'nullable|url',
            'bio.instagram' => 'nullable|url',
            'bio.github' => 'nullable|url',
            'bio.linkedin' => 'nullable|url',
            'bio.twitter' => 'nullable|url',
            'bio.youtube' => 'nullable|url',
            'bio.website' => 'nullable|url',
            'about_me' => 'nullable|string',
            'email' => 'prohibited',
            'qa_systems' => 'prohibited',
            'careers' => 'nullable|array',
            'careers.*.institution_name' => 'required|string|max:255',
            'careers.*.degree' => 'required|string|max:255',
            'careers.*.major' => 'required|string|max:255',
            'careers.*.start_date' => 'required|date',
            'careers.*.end_date' => 'nullable|date|after_or_equal:careers.*.start_date',
            'careers.*.description' => 'nullable|string',
        ];
    }
}
