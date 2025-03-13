<?php

namespace App\Http\Requests\API\User;

use App\Http\Requests\API\Bases\BaseFormRequest;
use App\Models\Profile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'phone' => 'sometimes|numeric|min:10|max:11',
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
            'careers.*.institution_name' => [
                'required',
                'string',
                'max:255',
            ],
            'careers.*.degree' => 'required|string|max:255',
            'careers.*.major' => 'required|string|max:255',
            'careers.*.start_date' => 'required|date',
            'careers.*.end_date' => 'nullable|date|after_or_equal:careers.*.start_date',
            'careers.*.description' => 'nullable|string',
            'careers.*.id' => 'nullable',
        ];
    }
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $careers = request()->input('careers', []);
            $userID = Auth::id();
            $profile = Profile::query()->where('user_id', $userID)->first();

            $institution_names = [];

            foreach ($careers as $index => $career) {
                $institutionName = $career['institution_name'];
                $careerId = $career['id'] ?? null;

                if (!in_array($institutionName, $institution_names)) {
                    $institution_names[] = $institutionName;
                } else {
                    $validator->errors()->add("careers.$index.institution_name", 'Đã tồn tại tên cơ sở này.');
                }

                if ($profile) {
                    $profileId = $profile->id;

                    $query = DB::table('careers')
                        ->where('profile_id', $profileId)
                        ->where('institution_name', $institutionName);

                    if (!empty($careerId)) {
                        $query->where('id', '<>', $careerId);
                    }

                    if ($query->exists()) {
                        $validator->errors()->add("careers.$index.institution_name", 'Đã tồn tại tên cơ sở này.');
                    }
                }
            }
        });
    }
}
