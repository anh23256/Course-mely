<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\WishList\StoreWishListRequest;
use App\Models\Course;
use App\Models\Rating;
use App\Models\Video;
use App\Models\WishList;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WishListController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $user = Auth::user();

            $courses = Course::query()
                ->with([
                    'category',
                    'wishLists',
                    'user',
                    'chapters' => function ($query) {
                        $query->with(['lessons' => function ($lessonQuery) {
                            $lessonQuery->with('lessonable');
                        }])->withCount('lessons');
                    },
                ])
                ->withCount([
                    'chapters',
                    'lessons'
                ])
                ->whereHas('wishLists', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->get();

            $courseRatings = Rating::whereIn('course_id', $courses->pluck('id'))
                ->groupBy('course_id')
                ->select(
                    'course_id',
                    DB::raw('COUNT(*) as ratings_count'),
                    DB::raw('ROUND(AVG(rate), 1) as average_rating')
                )
                ->get()
                ->keyBy('course_id');

            $result = $courses->map(function ($course) use ($courseRatings) {
                $videoLessons = $course->chapters->flatMap(function ($chapter) {
                    return $chapter->lessons->where('lessonable_type', Video::class);
                });

                $totalVideoDuration = $videoLessons->sum(function ($lesson) {
                    return $lesson->lessonable->duration ?? 0;
                });

                $ratingInfo = $courseRatings->get($course->id);

                return [
                    'id' => $course->id,
                    'name' => $course->name,
                    'slug' => $course->slug,
                    'thumbnail' => $course->thumbnail,
                    'level' => $course->level,
                    'status' => $course->status,
                    'chapters_count' => $course->chapters_count,
                    'lessons_count' => $course->lessons_count,
                    'is_practical_course' => $course->is_practical_course,
                    'ratings' => [
                        'count' => $ratingInfo ? $ratingInfo->ratings_count : 0,
                        'average' => $ratingInfo ? $ratingInfo->average_rating : 0
                    ],
                    'total_video_duration' => $totalVideoDuration,
                    'category' => [
                        'id' => $course->category->id ?? null,
                        'name' => $course->category->name ?? null,
                        'slug' => $course->category->slug ?? null
                    ],
                    'user' => [
                        'id' => $course->user->id ?? null,
                        'name' => $course->user->name ?? null,
                        'avatar' => $course->user->avatar ?? null,
                        'code' => $course->user->code ?? null
                    ]
                ];
            });

            return $this->respondOk('Danh sách khoá học yêu thích của người dùng: ' . $user->name, $result);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWishListRequest $request)
    {
        try {
            $user = Auth::user();

            $existingWishList = WishList::where('user_id', $user->id)
                ->where('course_id', $request->course_id)
                ->exists();

            if ($existingWishList) {
                return $this->respondError('Khóa học đã tồn tại trong danh sách yêu thích');
            }

            $wishList = WishList::query()->firstOrCreate([
                'user_id' => $user->id,
                'course_id' => $request->course_id,
            ]);

            return $this->respondCreated('Đã thêm khóa học vào danh sách yêu thích', $wishList);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user = Auth::user();

            $wishList = WishList::query()
                ->where('user_id', $user->id)
                ->where('course_id', $id)
                ->first();

            if (!$wishList) {
                return $this->respondNotFound('Không tìm thấy khóa học trong danh sách yêu thích');
            }

            $wishList->delete();

            return $this->respondOk('Đã xóa khóa học khỏi danh sách yêu thích');
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
