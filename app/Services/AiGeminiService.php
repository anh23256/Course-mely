<?php

namespace App\Services;

use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Gemini;

class AiGeminiService
{

    use LoggableTrait, ApiResponseTrait;

    protected $gemini;

    public function __construct()
    {
        $this->gemini = Gemini::client(env('GEMINI_API_KEY'));
    }

    public function generateText($title)
    {
        try {
            $result = $this->gemini->geminiPro()->generateContent($title);

            return $result->text();
        } catch (\Exception $e) {
            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lagi sau');
        }
    }

    public function __destruct()
    {
        $this->gemini = null;
    }
}
