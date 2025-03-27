<?php

namespace App\Http\Requests\Admin\Gifts;

use Illuminate\Foundation\Http\FormRequest;

class StoreGiftRequest extends FormRequest
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
                'name' => 'required|string|max:255',
                'stock' => 'required|integer|min:0',
                'probability' => 'required|numeric|min:0|max:100',
                'description' => 'nullable|string',
                'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'Tên không được để trống',
            'name.string'   => 'Tên phải là một chuỗi',
            'name.max'      => 'Tên không được quá 255 kí tự',
            'stock.required' => 'Số lượng không được để trống',
            'stock.integer'   => 'Số lượng phải là một số nguyên',
            'stock.min'      => 'Số lượng không được nhỏ hơn 0',
            'probability.required' => 'Tỉ lệ không được để trống',
            'probability.numeric'   => 'Tỉ lệ phải là một số',
            'probability.min'      => 'Tỉ lệ không được nhỏ hơn 0',
            'probability.max'      => 'Tỉ lệ tối đa là 100',
        ];
    }
}
