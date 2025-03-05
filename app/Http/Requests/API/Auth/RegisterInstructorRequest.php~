<?php

namespace App\Http\Requests\API\Auth;

use App\Http\Requests\API\Bases\BaseFormRequest;
use App\Models\QaSystem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RegisterInstructorRequest extends BaseFormRequest
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
    public function rules()
    {
        $qaSystemCount = QaSystem::query()->count();

        $rules = [];

        if (!Auth::check()) {
            $rules['name'] = 'nullable|string|max:255';
            $rules['email'] = 'nullable|email|unique:users,email';
            $rules['password'] = 'nullable|string|min:6';
            $rules['confirm_password'] = 'nullable|same:password';
        }

        $rules += [
            'phone' => 'nullable|unique:profiles,phone|regex:/^0[0-9]{9}$/',
            'address' => 'nullable|string|max:255|min:10',
            'institution_name' => 'nullable|string|max:255',
            'degree' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'major' => 'nullable|string|max:255',
            'certificates' => 'nullable|array',
            'certificates.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:2048',
            'qa_systems' => 'nullable|array|size:' . $qaSystemCount,
            'qa_systems.*' => 'required',
        ];

        return $rules;
    }


    public function messages()
    {
        $messages = [
            'phone.required' => 'Vui lòng nhập số điện thoại của bạn',
            'phone.unique' => 'Số điện thoại đã tồn tại',
            'phone.regex' => 'Số điện thoại không đúng định dạng',
            'address.required' => 'Vui lòng nhập địa chỉ của bạn',
            'institution_name.max' => 'Tên trường học được nhập tối đa 255 ký tự',
            'degree.file' => 'Bằng cấp phải là một tệp hợp lệ.',
            'degree.mimes' => 'Bằng cấp chỉ chấp nhận các định dạng: jpg, jpeg, png, pdf.',
            'degree.max' => 'Kích thước tệp bằng cấp không được vượt quá 2MB.',
            'major.max' => 'Chuyên ngành được nhập tối đa 255 ký tự',
            'certificates.required' => 'Vui lòng tải lên ít nhất một chứng chỉ',
            'certificates.*.file' => 'Chứng chỉ phải là một tệp hợp lệ.',
            'certificates.*.mimes' => 'Chứng chỉ chấp nhận các định dạng: jpg, jpeg, png, pdf.',
            'certificates.*.max' => 'Kích thước tệp chứng chỉ không được vượt quá 2MB.',
            'qa_systems.required' => 'Hãy trả lời câu hỏi được đưa ra từ hệ thống của chúng tôi.',
            'qa_systems.size' => 'Bạn phải trả lời tất cả :size câu hỏi từ hệ thống.',
            'qa_systems.*.required' => 'Tất cả câu hỏi của hệ thống đều bắt buộc phải trả lời.'
        ];

        if (!Auth::check()) {
            $messages['name.required'] = 'Vui lòng nhập tên của bạn';
            $messages['name.string'] = 'Tên nhập sai định dạng';
            $messages['name.max'] = 'Ten được nhập tối đa 255 ký tự';
            $messages['email.required'] = 'Vui lòng nhập email';
            $messages['email.email'] = 'Email nhập sai định dạng';
            $messages['email.unique'] = 'Email đã tồn tại';
            $messages['password.required'] = 'Vui lòng nhập mật khẩu';
            $messages['password.string'] = 'Mật khẩu nhập sai định dạng';
            $messages['password.min'] = 'Mật khẩu phải có ít nhất 6 ký tự';
            $messages['confirm_password.required'] = 'Vui lòng xác nhận mật khẩu';
            $messages['confirm_password.same'] = 'Mật khẩu xác nhận không khớp';
        }

        return $messages;
    }
}
