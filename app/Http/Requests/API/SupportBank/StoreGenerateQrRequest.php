<?php

namespace App\Http\Requests\API\SupportBank;

use App\Http\Requests\API\Bases\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreGenerateQrRequest extends BaseFormRequest
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
            'accountNo' => 'required|numeric',
            'accountName' => 'required|string',
            'acqId' => 'nullable|numeric',
            'amount' => 'required|numeric',
            'addInfo' => 'nullable|string',
            'format' => 'nullable|string',
            'template' => 'nullable|string',
        ];
    }
}
