<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Search\SearchRequest;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FilterController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function filter(SearchRequest $request)
    {
        try {
          
            return $this->respondOk('Kết quả tìm kiếm');
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Internal Server Error');
        }
    }
}
