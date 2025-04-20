<?php

namespace App\Http\Requests\API\Wallet;

use App\Http\Requests\API\Bases\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreWithDrawalRequest extends BaseFormRequest
{
    protected $wallet;
    protected $minWithdrawal = 200000; 
    protected $maxWithdrawal = 10000000; 
    protected $minBalance = 100000; 

    public function authorize(): bool
    {
        $user = Auth::user();
        $this->wallet = $user ? $user->wallet : null;

        return $this->wallet !== null;
    }

    public function rules(): array
    {
        $availableForWithdrawal = $this->wallet ? $this->wallet->balance - $this->minBalance : 0;

        $maxWithdrawal = min($availableForWithdrawal, $this->maxWithdrawal);

        return [
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
            'acq_id' => 'required',
            'amount' => [
                'required',
                'numeric',
                'min:' . $this->minWithdrawal,
                'max:' . $this->maxWithdrawal,
                function ($attribute, $value, $fail) use ($availableForWithdrawal, $maxWithdrawal) {
                    if ($value > $availableForWithdrawal) {
                        $fail("Số dư trong tài khoản phải duy trì tối thiểu " . number_format($this->minBalance) . " VNĐ. Bạn đang rút " . number_format($availableForWithdrawal) . " VNĐ");
                    }
                }
            ],
            'add_info' => 'required|string',
            'bank_name' => 'required|string',
        ];
    }

    protected function removeVietnameseDiacritics($str)
    {
        $vietnamese = [
            'á',
            'à',
            'ả',
            'ã',
            'ạ',
            'ă',
            'ắ',
            'ằ',
            'ẳ',
            'ẵ',
            'ặ',
            'â',
            'ấ',
            'ầ',
            'ẩ',
            'ẫ',
            'ậ',
            'é',
            'è',
            'ẻ',
            'ẽ',
            'ẹ',
            'ê',
            'ế',
            'ề',
            'ể',
            'ễ',
            'ệ',
            'í',
            'ì',
            'ỉ',
            'ĩ',
            'ị',
            'ó',
            'ò',
            'ỏ',
            'õ',
            'ọ',
            'ô',
            'ố',
            'ồ',
            'ổ',
            'ỗ',
            'ộ',
            'ơ',
            'ớ',
            'ờ',
            'ở',
            'ỡ',
            'ợ',
            'ú',
            'ù',
            'ủ',
            'ũ',
            'ụ',
            'ư',
            'ứ',
            'ừ',
            'ử',
            'ữ',
            'ự',
            'ý',
            'ỳ',
            'ỷ',
            'ỹ',
            'ỵ',
            'đ'
        ];

        $english = [
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'i',
            'i',
            'i',
            'i',
            'i',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'y',
            'y',
            'y',
            'y',
            'y',
            'd'
        ];

        return str_replace($vietnamese, $english, $str);
    }

    public function messages(): array
    {
        return [
            'account_no.required' => 'Số tài khoản không được để trống',
            'account_no.numeric' => 'Số tài khoản phải là số',

            'account_name.required' => 'Tên tài khoản không được để trống',
            'account_name.string' => 'Tên tài khoản phải là chuỗi ký tự',

            'acq_id.required' => 'Mã ngân hàng không được để trống',

            'amount.required' => 'Số tiền rút không được để trống',
            'amount.numeric' => 'Số tiền phải là số',
            'amount.min' => 'Số tiền rút tối thiểu là ' . number_format($this->minWithdrawal) . ' VNĐ',
            'amount.max' => 'Số tiền rút tối đa là ' . number_format($this->maxWithdrawal) . ' VNĐ',

            'add_info.required' => 'Thông tin bổ sung không được để trống',
            'add_info.string' => 'Thông tin bổ sung phải là chuỗi ký tự',

            'bank_name.required' => 'Tên ngân hàng không được để trống',
            'bank_name.string' => 'Tên ngân hàng phải là chuỗi ký tự',
        ];
    }

    protected function failedAuthorization()
    {
        throw new \Illuminate\Auth\Access\AuthorizationException(
            'Bạn không có ví để thực hiện giao dịch rút tiền.'
        );
    }
}
