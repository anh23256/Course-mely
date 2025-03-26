<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Search\SearchRequest;
use App\Models\Course;
use App\Models\Post;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use function PHPUnit\Framework\isEmpty;

class SearchController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function search(SearchRequest $request)
    {
        try {
            $query = $request->input('q');

            $results = [];

            $courses = Course::search($query)
                ->where('status', 'approved')
                ->where('visibility', 'public')
                ->orderBy('total_student', 'desc')
                ->take(5)
                ->get();
            if ($courses->isNotEmpty()) {
                $results['courses'] = $courses;
            }

            $posts =  Post::search($query)
                ->where('status', 'published')
                ->take(5)->get();
            if ($posts->isNotEmpty()) {
                $results['posts'] = $posts;
            }

            $instructors = User::search($query)
                ->get()
                ->filter(function ($user) {
                    return $user->hasRole('instructor') && $user->status === 'active';
                })
                ->take(5);
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
