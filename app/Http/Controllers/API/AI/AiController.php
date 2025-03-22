<?php

namespace App\Http\Controllers\API\AI;

use App\Http\Controllers\Controller;
use App\Services\AiCloudflareService;
use App\Services\AiGeminiService;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;

class AiController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    protected $cloudflare;

    public function __construct(AiCloudflareService $cloudflare)
    {
        $this->cloudflare = $cloudflare;
    }

//    public function generateText(Request $request)
//    {
//        try {
//            $request->validate([
//                'title' => 'required|string',
//            ]);
//
//            $response = $this->aiService->generateText($request->title);
//
//            return $this->respondOk('Lấy dữ liệu thành công', $response);
//        } catch (\Exception $e) {
//            $this->logError($e, $request->all());
//
//            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
//        }
//    }

    public function generateTextCloudflare(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string',
            ]);

            $response = $this->cloudflare->generateText($request);

            return $this->respondOk('Lấy dữ liệu thành công', $response);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }
}
