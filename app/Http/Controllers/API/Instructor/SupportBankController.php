<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\SupportBank\StoreGenerateQrRequest;
use App\Models\SupportedBank;
use App\Models\WithdrawalRequest;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SupportBankController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function index()
    {
        try {
            $banks = SupportedBank::query()->get();

            if ($banks->isEmpty()) {
                return $this->respondNotFound('Không tìm thấy ngân hàng nào');
            }

            return $this->respondOk('Danh sách ngân hàng: ', $banks);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError(
                'Có lỗi xảy ra, vui lòng thử lại sau'
            );
        }
    }

    public function generateQR(Request $request)
    {
        try {
            $data = $request->validate([
                'account_no' => 'nullable|numeric',
                'account_name' => 'nullable|string',
                'acq_id' => 'nullable',
                'amount' => 'nullable|numeric',
                'add_info' => 'nullable|string',
                'bank_name' => 'required|string',
            ]);

            $response = \Illuminate\Support\Facades\Http::post('https://api.vietqr.io/v2/generate', [
                'accountNo' => $data['account_no'],
                'accountName' => $data['account_name'],
                'acqId' => $data['acq_id'],
                'amount' => $data['amount'],
                'addInfo' => $data['add_info'] ?? '',
                'template' => 'pwMusbq',
            ]);

            if ($response->failed()) {
                return $this->respondError('Có lỗi xảy ra, vui lòng thử lại sau');
            }

            $responseBody = $response->json();

            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $responseBody['data']['qrDataURL']));
            $filePath = 'qr/' . uniqid() . '.png';
            Storage::disk('public')->put($filePath, $imageData);

            return $this->respondCreated('Gửi yêu cầu thành công');
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError(
                'Có lỗi xảy ra, vui lòng thử lại sau'
            );
        }
    }
}
