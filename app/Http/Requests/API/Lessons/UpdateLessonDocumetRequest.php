<?php

namespace App\Http\Requests\API\Lessons;

use App\Http\Requests\API\Bases\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonDocumetRequest extends BaseFormRequest
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
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'document_file' => 'nullable|file|mimes:pdf,doc,docx,xls,ppt,png,jpg,jpeg',
            'document_url' => 'nullable|url',
            'is_free_preview' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'title.string' => 'Tiêu đề phải là chuỗi ký tự.',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự.',
            'content.string' => 'Nội dung phải là chuỗi ký tự.',
            'document_file.mimes' => 'Tài liệu phải có định dạng pdf, doc, docx, xls, ppt, png, jpg, jpeg.',
            'document_url.url' => 'URL tài liệu không hợp lệ.',
            'is_free_preview.boolean' => 'Giá trị xem trước miễn phí phải là true hoặc false.',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->document_file === 'null' || $this->document_file === '') {
            $this->request->remove('document_file');
        }
    }
}
