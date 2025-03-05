<?php

namespace App\Http\Controllers\API\Common;

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use App\Models\Video;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Redis\Connections\PredisConnection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class CourseController
{
    use LoggableTrait, ApiResponseTrait;

    public function getDiscountedCourses()
    {
        try {
            $courses = Course::query()
                ->with([
                    'category:id,name',
                    'user:id,name,avatar',
                    'chapters' => function ($query) {
                        $query->withCount('lessons');
                    },
                ])
                ->withCount([
                    'chapters',
                    'lessons'
                ])
                ->where('price_sale', '>', 0)
                ->where('visibility', '=', 'public')
                ->where('status', '=', 'approved')
                ->orderBy('total_student', 'desc')
                ->limit(10)
                ->get();

            if ($courses->isEmpty()) {
                return $this->respondNotFound('Không có dữ liệu');
            }

            return $this->respondOk('Danh sách khoá học đang giảm giá', $courses);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Internal Server Error');
        }
    }

    public function getFreeCourses()
    {
        try {
            $courses = Course::query()
                ->with([
                    'category:id,name',
                    'user:id,name,avatar',
                    'chapters' => function ($query) {
                        $query->withCount('lessons');
                    },
                ])
                ->withCount([
                    'chapters',
                    'lessons'
                ])
                ->where('is_free', '=', 1)
                ->where('visibility', '=', 'public')
                ->where('status', '=', 'approved')
                ->orderBy('total_student', 'desc')
                ->limit(10)
                ->get();;

            if ($courses->isEmpty()) {
                return $this->respondNotFound('Không có dữ liệu');
            }

            return $this->respondOk('Danh sách khoá học miễn phí', $courses);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Internal Server Error');
        }
    }

    public function getPopularCourses()
    {
        try {
            $courses = Course::query()
                ->with([
                    'category:id,name',
                    'user:id,name,avatar',
                    'chapters' => function ($query) {
                        $query->withCount('lessons');
                    },
                ])
                ->withCount([
                    'chapters',
                    'lessons'
                ])
                ->where('is_popular', '=', 1)
                ->where('visibility', '=', 'public')
                ->where('status', '=', 'approved')
                ->orderBy('total_student', 'desc')
                ->limit(10)
                ->get();;

            if ($courses->isEmpty()) {
                return $this->respondNotFound('Không có dữ liệu');
            }

            return $this->respondOk('Danh sách khoá học nổi bật', $courses);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Internal Server Error');
        }
    }

    public function getTopCategoriesWithMostCourses()
    {
        try {
            $categories = Category::query()
                ->with([
                    'courses' => function ($query) {
                        $query->where('visibility', '=', 'public')
                            ->where('status', '=', 'approved')
                            ->orderBy('total_student', 'desc')
                            ->limit(5);
                    },
                ])
                ->whereHas('courses', function ($query) {
                    $query->where('visibility', '=', 'public')
                        ->where('status', '=', 'approved');
                })
                ->withCount('courses')
                ->having('courses_count', '>=', 5)
                ->limit(5)
                ->get();

            if ($categories->isEmpty()) {
                return $this->respondNotFound('Không có dữ liệu');
            }

            return $this->respondOk('Danh sách danh mục', $categories);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Internal Server Error');
        }
    }

    public function getCourseDetail(Request $request, string $slug)
    {
        try {
            $course = Course::query()
                ->with([
                    'category:id,name,slug',
                    'user:id,name,avatar,created_at',
                    'chapters' => function ($query) {
                        $query->with([
                            'lessons',
                            'lessons.lessonable',
                        ])->withCount('lessons');
                    },
                ])
                ->withCount([
                    'chapters',
                    'lessons'
                ])
                ->where('slug', '=', $slug)
                ->where('visibility', '=', 'public')
                ->where('status', '=', 'approved')
                ->first();

            if (!$course) {
                return $this->respondNotFound('Không có dữ liệu');
            }

            $course->benefits = is_string($course->benefits) ? json_decode($course->benefits, true) : $course->benefits;
            $course->requirements = is_string($course->requirements) ? json_decode($course->requirements, true) : $course->requirements;
            $course->qa = is_string($course->qa) ? json_decode($course->qa, true) : $course->qa;

            $videoLessons = $course->chapters->flatMap(function ($chapter) {
                return $chapter->lessons->where('lessonable_type', Video::class);
            });

            $totalVideoDuration = $videoLessons->sum(function ($lesson) {
                return $lesson->lessonable->duration ?? 0;
            });

            $course->total_video_duration = $totalVideoDuration;

            //            $user = auth('sanctum')->user();
            //            $isCourseOwner = $user && $user->id === $course->user_id;
            //
            //            if (!$isCourseOwner) {
            //                $cacheKey = "course:{$course->id}:views:{$user->id}";
            //
            //                if (!Redis::exists($cacheKey)) {
            //                    $course->increment('views');
            //                    Redis::setex($cacheKey, 3600, true);
            //                }
            //            }

            $course->increment('views');

            return $this->respondOk('Chi tiết khoá học: ' . $course->name, $course);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Internal Server Error');
        }
    }

    public function getOtherCourses(string $slug)
    {
        try {
            $course = Course::where('slug', $slug)->where([
                'visibility' => 'public',
                'status' => 'approved',
            ])->first();

            if (!$course) {
                return $this->respondNotFound('Không tìm thấy thông tin khóa học!');
            }

            $getOtherCourses = DB::table('courses')
                ->select(
                    'courses.id',
                    'courses.name as name_course',
                    'courses.slug',
                    'courses.price',
                    'courses.price_sale',
                    'users.name as name_instructor',
                    'users.code as code_instructor',
                    DB::raw('COUNT(lessons.id) as total_lesson, SUM(videos.duration) as total_duration'),
                )
                ->join('chapters', 'chapters.course_id', '=', 'courses.id')
                ->join('lessons', 'lessons.chapter_id', '=', 'chapters.id')
                ->leftJoin('videos', 'videos.id', '=', 'lessons.lessonable_id')
                ->join('users', 'users.id', '=', 'courses.user_id')
                ->leftJoin('ratings', 'ratings.course_id', '=', 'courses.id')
                ->where([
                    'lessons.lessonable_type' => Video::class,
                    'lessons.type' => 'video',
                    'courses.visibility' => 'public',
                    'courses.status' => 'approved',
                ])
                ->where('courses.price', '>', 0)
                ->where('courses.id', '<>', $slug)
                ->groupBy('courses.id', 'courses.name', 'courses.slug')
                ->orderBy('courses.total_student', 'desc')
                ->limit(9)
                ->get();

            $profileIntructor = DB::table('users')
                ->select(
                    'users.name',
                    'users.code',
                    'profiles.bio',
                    'profiles.about_me',
                    DB::raw('ROUND(AVG(DISTINCT ratings.rate), 1) as avg_rating'),
                    DB::raw('SUM(courses.total_student) as total_student'),
                    DB::raw('COUNT(DISTINCT courses.id) as total_courses')
                )
                ->leftJoin('profiles', 'profiles.user_id', '=', 'users.id')
                ->leftJoin('courses', 'courses.user_id', '=', 'users.id')
                ->leftJoin('ratings', 'ratings.course_id', '=', 'courses.id')
                ->where([
                    'courses.visibility' => 'public',
                    'courses.status' => 'approved',
                    'courses.user_id' => $course->user_id
                ])
                ->groupBy('users.code', 'users.name', 'profiles.bio', 'profiles.about_me')
                ->first();

            return response()->json([
                'message' => 'Kiểm tra hoàn thiện khoá học',
                'getOtherCourse' => $getOtherCourses,
                'profile_instructor' => $profileIntructor
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
