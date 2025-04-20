<?php

namespace App\Http\Requests\Admin\Chats;

use Illuminate\Foundation\Http\FormRequest;

class StoreSendMessageRequest extends FormRequest
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
            'conversation_id' => 'required|exists:conversations,id',
            'content' => 'nullable|string|max:255',
            'type' => 'nullable|string',
            'parent_id' => 'nullable|exists:messages,id',
            'meta_data' => 'nullable|json',
            'input_file' => 'nullable|array',
            'input_file.*' => 'file|max:25600',
        ];
    }
    public function messages()
    {
        return [
            'conversation_id.required' => 'ID cuộc trò chuyện là bắt buộc.',
            'conversation_id.exists' => 'Cuộc trò chuyện không tồn tại trong hệ thống.',

            'content.required' => 'Nội dung tin nhắn không được để trống.',
            'content.string' => 'Nội dung tin nhắn phải là chuỗi ký tự.',
            'content.max' => 'Nội dung tin nhắn không được vượt quá 255 ký tự.',

            'type.string' => 'Trường kiểu tin nhắn phải là chuỗi.',

            'parent_id.exists' => 'Tin nhắn gốc không tồn tại trong hệ thống.',

            'meta_data.json' => 'Dữ liệu meta phải ở định dạng JSON hợp lệ.',

            'input_file.array' => 'Trường tệp đính kèm phải là một mảng.',

            'input_file.*.file' => 'Mỗi tệp đính kèm phải là một tệp hợp lệ.',
            'input_file.*.max' => 'Mỗi tệp đính kèm không được vượt quá 25MB.'
        ];
    }
}
