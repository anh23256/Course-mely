<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Categories\StoreCategoryRequest;
use App\Http\Requests\Admin\Categories\UpdateCategoryRequest;
use App\Models\Category;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function index()
    {
        try {
            $categories = Category::query()
                ->select('id', 'name', 'slug')
                ->where([
                    'parent_id' => null,
                    'status' => 1
                ])
                ->get();

            return $this->respondOk('Danh sách danh mục', $categories);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
