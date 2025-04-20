<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Ratings\StoreRatingRequest;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\Rating;
use App\Notifications\RatingNotification;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function store(StoreRatingRequest $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $userId = Auth::id();

            $data = $request->except('user_id');

            $course = Course::query()->with('instructor')->where('slug', $data['course_slug'])->first();

            if (!$course) {
                return $this->respondError('Không tìm thấy khóa học.');
            }

            $completed = CourseUser::where([
                'user_id' => $userId,
                'course_id' => $course->id
            ])->value('progress_percent') === 100;

            if (!$completed) {
                return $this->respondError('Bạn phải hoa thành khoá học mới được đánh giá');
            }

            $alreadyRated = Rating::query()->where([
                'user_id' => $userId,
                'course_id' => $course->id
            ])->exists();

            if ($alreadyRated) {
                return $this->respondError('Bạn đã đánh giá khoá học này trước đó, không thể đánh giá lại.');
            }

            Rating::query()->updateOrCreate(
                ['user_id' => $userId, 'course_id' => $course->id],
                ['content' => $data['content'], 'rate' => $data['rate']]
            );

            $user->notify(new RatingNotification($course, $course->instructor));

            return $this->respondCreated('Gửi đánh giá thành công');
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lagi.');
        }
    }

    public function index($courseId)
    {
        try {
            $ratings = Rating::where('course_id', $courseId)
                ->with('user:id,name')
                ->latest()
                ->paginate(10);
            if ($ratings->isEmpty()) {
                return $this->respondNotFound('Không tìm thấy đánh giá');
            }
            return response()->json([
                'status' => true,
                'ratings' => $ratings
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function checkCourseState(Request $request, string $courseSlug)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->respondForbidden('Bạn không có quyền truy cập.');
            }

            $course = Course::query()
                ->where('slug', $courseSlug)
                ->first();

            if (!$course) {
                return $this->respondNotFound('Không tìm thấy khoá học.');
            }

            $alreadyRated = Rating::where([
                'user_id' => $user->id,
                'course_id' => $course->id,
            ])->exists();

            return $this->respondOk('Thao tác thành công', $alreadyRated);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function getLastRatings()
    {
        try {

            $ratings = Rating::with('user:id,name,avatar')
                ->whereHas('user', fn($q) => $q->where('status', 'active'))
                ->select('id', 'content', 'user_id', 'created_at')
                ->orderByDesc('created_at') // Lấy đánh giá mới nhất trước
                ->get()
                ->unique('user_id') // Giữ lại mỗi user 1 đánh giá mới nhất
                ->take(6) // Chỉ lấy tối đa 6 user khác nhau
                ->values();


            if (!$ratings) {
                return $this->respondNotFound('Không có đánh giá nào');
            }

            return $this->respondOk('Danh sách đánh giá', $ratings);
        } catch (\Exception $e) {

            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function getCourseRatings($slug)
    {
        try {

            $course = Course::where('slug', $slug)->firstOrFail();

            $ratings = Rating::where('course_id', $course->id)
                ->whereHas('user', fn($q) => $q->where('status', 'active'))
                ->with('user:id,name,avatar,code')
                ->latest()
                ->limit(5)
                ->get(['id','rate', 'content', 'user_id','created_at']);

            if (!$ratings) {
                return $this->respondNotFound('Không có đánh giá nào');
            }

            $totalRatings = $ratings->count();
            $averageRating = $totalRatings > 0 ? round($ratings->avg('rate'), 1) : 0;

            return $this->respondOk('Danh sách đánh giá', [
                'ratings' => $ratings,
                'total_ratings' => $totalRatings,
                'average_rating' => $averageRating
            ]);
        } catch (\Exception $e) {

            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
