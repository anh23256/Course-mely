<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Search\SearchRequest;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function search(SearchRequest $request)
    {
        try {
            $query = $request->input('q');

            $results = [];

            $courses = DB::table('courses')
                ->select('id', 'name', 'slug', 'thumbnail')
                ->where('status', 'approved')
                ->where('visibility', 'public')
                ->where(function ($q) use ($query) {
                    $q->whereRaw("MATCH(name, description) AGAINST(? IN NATURAL LANGUAGE MODE)", [$query])
                        ->orWhere('name', 'LIKE', "%{$query}%")
                        ->orWhere('description', 'LIKE', "%{$query}%");
                })
                ->orderBy('total_student', 'desc')
                ->limit(5)
                ->get();

            if ($courses->isNotEmpty()) {
                $results['courses'] = $courses;
            }

            $posts = DB::table('posts')
                ->select('id', 'title', 'slug', 'thumbnail')
                ->where('status', 'published')
                ->where(function ($q) use ($query) {
                    $q->whereRaw("MATCH(title, content, description) AGAINST(? IN NATURAL LANGUAGE MODE)", [$query])
                        ->orWhere('title', 'LIKE', "%{$query}%")
                        ->orWhere('content', 'LIKE', "%{$query}%")
                        ->orWhere('description', 'LIKE', "%{$query}%");
                })
                ->limit(5)
                ->get();

            if ($posts->isNotEmpty()) {
                $results['posts'] = $posts;
            }

            $instructors = User::query()->select('id', 'name', 'email', 'avatar')
                ->where(function ($q) use ($query) {
                    $q->whereRaw("MATCH(name, email) AGAINST(? IN NATURAL LANGUAGE MODE)", [$query])
                        ->orWhere('name', 'LIKE', "%{$query}%")
                        ->orWhere('email', 'LIKE', "%{$query}%");
                })
                ->where('status', 'active')
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'instructor');
                })
                ->limit(3)
                ->get();

            if ($instructors->isNotEmpty()) {
                $results['instructors'] = $instructors;
            }

            if (empty($results)) {
                return $this->respondNotFound('Không có dữ liệu');
            }

            return $this->respondOk('Kết quả tìm kiếm', $results);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Internal Server Error');
        }
    }
}
