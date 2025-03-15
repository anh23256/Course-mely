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
            'phone' => 'sometimes|numeric|digits_between:10,13',
            'address' => 'sometimes|string|max:255',
            'experience' => 'sometimes|string',
            'certificates' => 'nullable|array',
            'certificates.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:2048',
            'bio' => 'nullable|array',
            'bio.github' => 'nullable|url',
            'bio.website' => 'nullable|url',
            'bio.youtube' => 'nullable|url',
            'bio.facebook' => 'nullable|url',
            'bio.twitter' => 'nullable|url',
            'bio.linkedin' => 'nullable|url',
            'bio.instagram' => 'nullable|url',
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
    public function messages(): array
    {
        return [
            'bio.facebook' => 'Facebook phải là url.',
            'bio.instagram' => 'Instagram phải là url.',
            'bio.github' => 'Github phải là url.',
            'bio.linkedin' => 'Linkedin phải là url.',
            'bio.twitter' => 'Twitter phải là url.',
            'bio.youtube' => 'Youtube phải là url.',
            'bio.website' => 'Website phải là url.',
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

    public function messages(): array
    {
        return [
            'name.sometimes' => 'Vui lòng kiểm tra lại tên.',
            'name.string' => 'Tên phải là một chuỗi ký tự.',
            'name.max' => 'Tên không được vượt quá 255 ký tự.',

            'avatar.image' => 'Avatar phải là một hình ảnh.',
            'avatar.mimes' => 'Avatar phải có định dạng: jpg, jpeg, webp, png.',
            'avatar.max' => 'Avatar không được vượt quá 2MB.',

            'phone.sometimes' => 'Vui lòng kiểm tra lại số điện thoại.',
            'phone.numeric' => 'Số điện thoại chỉ được chứa các chữ số.',
            'phone.digits_between' => 'Số điện thoại phải có từ 10 đến 13 chữ số.',

            'address.sometimes' => 'Vui lòng kiểm tra lại địa chỉ.',
            'address.string' => 'Địa chỉ phải là một chuỗi ký tự.',
            'address.max' => 'Địa chỉ không được vượt quá 255 ký tự.',

            'experience.sometimes' => 'Vui lòng kiểm tra lại kinh nghiệm.',
            'experience.string' => 'Kinh nghiệm phải là một chuỗi ký tự.',

            'certificates.array' => 'Chứng chỉ phải được gửi dưới dạng danh sách.',
            'certificates.*.file' => 'Mỗi chứng chỉ phải là một tập tin.',
            'certificates.*.mimes' => 'Chứng chỉ phải có định dạng: jpg, jpeg, png, webp, pdf.',
            'certificates.*.max' => 'Chứng chỉ không được vượt quá 2MB.',

            'bio.array' => 'Thông tin cá nhân phải được gửi dưới dạng danh sách.',
            'bio.facebook.url' => 'Địa chỉ Facebook phải là một URL hợp lệ.',
            'bio.instagram.url' => 'Địa chỉ Instagram phải là một URL hợp lệ.',
            'bio.github.url' => 'Địa chỉ GitHub phải là một URL hợp lệ.',
            'bio.linkedin.url' => 'Địa chỉ LinkedIn phải là một URL hợp lệ.',
            'bio.twitter.url' => 'Địa chỉ Twitter phải là một URL hợp lệ.',
            'bio.youtube.url' => 'Địa chỉ YouTube phải là một URL hợp lệ.',
            'bio.website.url' => 'Địa chỉ website phải là một URL hợp lệ.',

            'about_me.string' => 'Giới thiệu bản thân phải là một chuỗi ký tự.',

            'email.prohibited' => 'Không được phép thay đổi email qua form này.',
            'qa_systems.prohibited' => 'Không được phép thay đổi hệ thống QA qua form này.',

            'careers.array' => 'Sự nghiệp phải được gửi dưới dạng danh sách.',
            'careers.*.institution_name.required' => 'Tên cơ sở/tổ chức là bắt buộc.',
            'careers.*.institution_name.string' => 'Tên cơ sở/tổ chức phải là một chuỗi ký tự.',
            'careers.*.institution_name.max' => 'Tên cơ sở/tổ chức không được vượt quá 255 ký tự.',
            'careers.*.degree.required' => 'Bằng cấp là bắt buộc.',
            'careers.*.degree.string' => 'Bằng cấp phải là một chuỗi ký tự.',
            'careers.*.degree.max' => 'Bằng cấp không được vượt quá 255 ký tự.',
            'careers.*.major.required' => 'Chuyên ngành là bắt buộc.',
            'careers.*.major.string' => 'Chuyên ngành phải là một chuỗi ký tự.',
            'careers.*.major.max' => 'Chuyên ngành không được vượt quá 255 ký tự.',
            'careers.*.start_date.required' => 'Ngày bắt đầu là bắt buộc.',
            'careers.*.start_date.date' => 'Ngày bắt đầu phải là một ngày hợp lệ.',
            'careers.*.end_date.date' => 'Ngày kết thúc phải là một ngày hợp lệ.',
            'careers.*.end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
            'careers.*.description.string' => 'Mô tả phải là một chuỗi ký tự.'
        ];
    }
}
