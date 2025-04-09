<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Services\OpenAiService;
use Illuminate\Http\Request;

class OpenAiController extends Controller
{

    protected $openAiService;

    public function __construct(OpenAiService $openAiService)
    {
        $this->openAiService = $openAiService;
    }

    public function createConvention(Request $request)
    {
        
    }
}
