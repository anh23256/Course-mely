<?php

namespace App\Http\Requests\API\Ratings;

use App\Http\Requests\API\Bases\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreRatingRequest extends BaseFormRequest
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
            'course_slug' => 'required|string',
            'content' => 'nullable|string|max:2000',
            'rate' => 'required|int|min:0|max:5',
        ];
    }
    public function messages()
    {
        return [
            'content.max'=>'Nội dung không được quá 2000 kí tự',
            'content.string'=>'Nội dung phải là chuỗi kí tự',
            'rate.required'=>'Đánh giá là bắt buộc',
            'rate.integer' =>'Đánh giá phải là số nguyên',
        ];
    }
}
