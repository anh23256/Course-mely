<?php

namespace App\Http\Requests\API\User;

use App\Http\Requests\API\Bases\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBankingInfoRequest extends BaseFormRequest
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
            'id' => 'required|string',
            'name' => 'required|string',
            'bin' => 'required|numeric',
            'short_name' => 'nullable|string',
            'logo' => 'nullable|string',
            'account_no' => 'required|numeric',
            'account_name' => [
                'required',
                'string',
                'min:5',
                'max:50',
                function ($attribute, $value, $fail) {
                    $value = str_replace(' ', '', $value);

                    $normalizedValue = $this->removeVietnameseDiacritics(strtoupper($value));

                    if (!preg_match('/^[A-Z]+$/', $normalizedValue)) {
                        $fail('Tên tài khoản chỉ được chứa chữ cái in hoa (không dấu), không chứa khoảng trắng hay ký tự đặc biệt.');
                    }
                }
            ],
            'is_default' => 'nullable|boolean',
        ];
    }

    protected function removeVietnameseDiacritics($str)
    {
        $vietnamese = [
            'á', 'à', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ',
            'é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ',
            'í', 'ì', 'ỉ', 'ĩ', 'ị',
            'ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ',
            'ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự',
            'ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ',
            'đ'
        ];

        $english = [
            'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
            'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e',
            'i', 'i', 'i', 'i', 'i',
            'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
            'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
            'y', 'y', 'y', 'y', 'y',
            'd'
        ];

        return str_replace($vietnamese, $english, $str);
    }

    public function messages(): array
    {
        return [
            'id.required' => 'ID ngân hàng không được để trống',

            'name.required' => 'Tên ngân hàng không được để trống',
            'name.string' => 'Tên ngân hàng phải là chuỗi ký tự',

            'bin.required' => 'Mã ngân hàng không được để trống',

            'account_no.required' => 'Số tài khoản không được để trống',
            'account_no.numeric' => 'Số tài khoản phải là số',

            'account_name.required' => 'Tên tài khoản không được để trống',
            'account_name.string' => 'Tên tài khoản phải là chuỗi ký tự',

            'is_default.boolean' => 'Trạng thái mặc định phải là giá trị boolean',
        ];
    }
}
