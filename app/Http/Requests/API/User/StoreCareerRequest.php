<?php

namespace App\Http\Requests\API\User;

use App\Http\Requests\API\Bases\BaseFormRequest;
use App\Models\Profile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCareerRequest extends BaseFormRequest
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
            'careers' => 'nullable|array',
            'careers.*.institution_name' => [
                'required',
                'string',
                'max:255',
            ],
            'careers.*.degree' => 'required|string|max:255',
            'careers.*.major' => 'required|string|max:255',
            'careers.*.start_date' => 'required|date',
            'careers.*.end_date' => 'required|date|after_or_equal:careers.*.start_date',
            'careers.*.description' => 'nullable|string',
        ];
    }
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $careers = request()->input('careers', []);

            if (!Auth::id()) {
                $validator->errors()->add('auth', 'Bạn cần đăng nhập để thực hiện thao tác này.');
                return;
            }

            $profile = Profile::query()->firstOrCreate([
                'user_id' => Auth::id()
            ]);

            $institution_names = [];

            foreach ($careers as $index => $career) {
                $institutionName = $career['institution_name'];

                $profileId = $profile->id;

                $query = DB::table('careers')
                    ->where('profile_id', $profileId)
                    ->where('institution_name', $institutionName);

                if ($query->exists()) {
                    $validator->errors()->add("careers.$index.institution_name", 'Đã tồn tại tên cơ sở này.');
                }

                if (!in_array($institutionName, $institution_names)) {
                    $institution_names[] = $institutionName;
                } else {
                    $validator->errors()->add("careers.$index.institution_name", 'Không được thêm các cơ sở giống nhau.');
                }
            }
        });
    }
}
