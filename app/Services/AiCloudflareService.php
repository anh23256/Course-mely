<?php

namespace App\Services;

use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class AiCloudflareService
{
    use LoggableTrait, ApiResponseTrait;

    protected $cloudflare;

    public function __construct()
    {
        $this->cloudflare = new Client();
    }

    public function generateText(Request $request)
    {
        $apiUrl = $this->getApiUrl();
        $messages = $this->buildMessages($request->input('title'));
        $maxTokens = $this->calculateMaxTokens($request->input('title'));

        try {
            $response = $this->sendRequestToCloudflare($apiUrl, $messages, $maxTokens);

            return $this->handleApiResponse($response);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());
            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    protected function getApiUrl()
    {
        return config('cloudflare.url') . config('cloudflare.account_id') . '/ai/run/@cf/meta/llama-3-8b-instruct';
    }

    protected function buildMessages($prompt)
    {
        return [
            [
                "role" => "system",
                "content" => "Bạn là một trợ lý AI hữu ích và bạn sẽ trả lời bằng tiếng Việt. Hãy viết nội dung chi tiết, chuyên nghiệp và có cấu trúc rõ ràng."
            ],
            [
                "role" => "user",
                "content" => $prompt
            ]
        ];
    }

    protected function calculateMaxTokens($prompt)
    {
        $promptLength = strlen($prompt);

        if (
            strpos(strtolower($prompt), 'chi tiết') !== false ||
            strpos(strtolower($prompt), 'bài viết') !== false ||
            strpos(strtolower($prompt), '800') !== false ||
            strpos(strtolower($prompt), '1000') !== false
        ) {
            return 1500;
        }

        return 300;
    }

    protected function sendRequestToCloudflare($apiUrl, $messages, $maxTokens)
    {
        return $this->cloudflare->post($apiUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . config('cloudflare.api_key'),
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'messages' => $messages,
                'max_tokens' => $maxTokens
            ]
        ]);
    }

    protected function handleApiResponse($response)
    {
        $result = json_decode($response->getBody(), true);

        if (isset($result['success']) && $result['success'] === true) {
            return $result['result']['response'] ?? 'Không có dữ liệu';
        }

        return $this->respondError($result['message'] ?? 'Có lỗi xảy ra, vui lòng thử lại sau');
    }

    public function __destruct()
    {
        $this->cloudflare = null;
    }
}
