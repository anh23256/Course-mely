<?php

namespace App\Http\Requests\API\User;

use App\Http\Requests\API\Bases\BaseFormRequest;
use App\Models\Profile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCareerRequest extends BaseFormRequest
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
                Rule::unique('careers', 'institution_name')->ignore($this->route('careerID'))
            ],
            'careers.*.degree' => 'required|string|max:255',
            'careers.*.major' => 'required|string|max:255',
            'careers.*.start_date' => 'required|date',
            'careers.*.end_date' => 'required|date|after_or_equal:careers.*.start_date',
            'careers.*.description' => 'nullable|string',
        ];
    }
}
