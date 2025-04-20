<?php

namespace App\Http\Requests\API\Reactions;

use Illuminate\Foundation\Http\FormRequest;

class StoreReactionRequest extends FormRequest
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
            'type' => 'required|string',
            'comment_id'=>'required|exists:comments,id',
        ];
    }
    public function messages()
    {
        return [
            'type.required' => 'type là bắt buộc',
            'type.string' => 'type phải phải là chuỗi kí tự',
            'comment_id.required' =>'comment_id là bắt buộc',
            'comment_id.exists' =>'Bình luận không tồn tại',
        ];
    }
}
