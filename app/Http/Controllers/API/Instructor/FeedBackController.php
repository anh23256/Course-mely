<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Rating;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedBackController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function getFeedbacks(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền truy cập.');
            }

            $courses = Course::query()
                ->where('user_id', $user->id)
                ->pluck('id');

            if ($courses->isEmpty()) {
                return $this->respondNotFound('Không tìm thấy khoá học.');
            }

            $query = Rating::query()
                ->whereIn('course_id', $courses)
                ->with([
                    'course:id,name,slug',
                    'user:id,name,email,avatar'
                ]);


            if ($request->has('fromDate')) {
                $query->whereDate('created_at', '>=', $request->input('fromDate'));
            }

            if ($request->has('toDate')) {
                $query->whereDate('created_at', '<=', $request->input('toDate'));
            }

            $feedbacks = $query
                ->orderBy('created_at', 'desc')
                ->get();

            if ($feedbacks->isEmpty()) {
                return $this->respondForbidden('Không có đánh giá');
            }

            return $this->respondOk('Danh sách đánh giá', $feedbacks);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }
}
