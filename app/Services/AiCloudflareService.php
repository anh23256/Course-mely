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

        try {
            $response = $this->sendRequestToCloudflare($apiUrl, $messages);

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

    protected function buildMessages($title)
    {
        return [
            [
                "role" => "system",
                "content" => "Bạn là một trợ lý AI hữu ích và bạn sẽ trả lời bằng tiếng Việt."
            ],
            [
                "role" => "user",
                "content" => 'Hãy viết một đoạn văn ngắn về ' . $title
            ]
        ];
    }

    protected function sendRequestToCloudflare($apiUrl, $messages)
    {
        return $this->cloudflare->post($apiUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . config('cloudflare.api_key'),
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'messages' => $messages,
                'max_tokens' => 200
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
