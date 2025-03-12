<?php

namespace App\Http\Controllers\API\Common;

use App\Models\Category;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\Rating;
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

            $courseRatings = Rating::whereIn('course_id', $courses->pluck('id'))
                ->groupBy('course_id')
                ->select(
                    'course_id',
                    DB::raw('COUNT(*) as ratings_count'),
                    DB::raw('ROUND(AVG(rate), 1) as average_rating')
                )
                ->get()
                ->keyBy('course_id');

            if ($courses->isEmpty()) {
                return $this->respondNotFound('Không có dữ liệu');
            }

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
                    'is_free' => $course->is_free,
                    'price' => $course->price,
                    'price_sale' => $course->price_sale,
                    'lessons_count' => $course->lessons_count,
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
                        'avatar' => $course->user->avatar ?? null
                    ]
                ];
            });

            if ($courses->isEmpty()) {
                return $this->respondNotFound('Không có dữ liệu');
            }

            return $this->respondOk('Danh sách khoá học đang giảm giá', $result);
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

            if ($courses->isEmpty()) {
                return $this->respondNotFound('Không có dữ liệu');
            }

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
                    'is_free' => $course->is_free,
                    'price' => $course->price,
                    'price_sale' => $course->price_sale,
                    'lessons_count' => $course->lessons_count,
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
                        'avatar' => $course->user->avatar ?? null
                    ]
                ];
            });

            return $this->respondOk('Danh sách khoá học miễn phí', $result);
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

            if ($courses->isEmpty()) {
                return $this->respondNotFound('Không có dữ liệu');
            }

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
                    'is_free' => $course->is_free,
                    'price' => $course->price,
                    'price_sale' => $course->price_sale,
                    'lessons_count' => $course->lessons_count,
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
                        'avatar' => $course->user->avatar ?? null
                    ]
                ];
            });

            return $this->respondOk('Danh sách khoá học nổi bật', $result);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Internal Server Error');
        }
    }

    public function getTopCategoriesWithMostCourses()
    {
        try {
            $categories = Category::query()
                ->select('id', 'name', 'slug', 'parent_id')
                ->with([
                    'courses' => function ($query) {
                        $query->select(
                            'courses.name',
                            'courses.code',
                            'courses.category_id',
                            'courses.slug',
                            'courses.thumbnail',
                            'courses.price',
                            'courses.price_sale',
                            'courses.total_student',
                            DB::raw('ROUND(AVG(DISTINCT ratings.rate), 1) as avg_rating'),
                            DB::raw('COUNT(DISTINCT ratings.rate) as total_rating'),
                        )
                            ->where('visibility', '=', 'public')
                            ->whereRaw('courses.id IN (
                                SELECT id FROM (
                                    SELECT id, ROW_NUMBER() OVER (PARTITION BY category_id ORDER BY total_student DESC) as row_num
                                    FROM courses
                                    WHERE visibility = "public" AND status = "approved"
                                ) as ranked WHERE row_num <= 7
                            )')
                            ->where('status', '=', 'approved')
                            ->orderBy('total_student', 'desc')
                            ->leftJoin('ratings', 'ratings.course_id', '=', 'courses.id')
                            ->groupBy('courses.name', 'courses.slug', 'courses.code');
                    },
                ])
                ->whereHas('courses', function ($query) {
                    $query->where('visibility', '=', 'public')
                        ->where('status', '=', 'approved');
                })
                ->withCount('courses')
                ->orderBy('courses_count', 'desc')
                ->limit(3)
                ->get();

            if ($categories->isEmpty()) {
                return $this->respondNotFound('Không có dữ liệu');
            }

            return $this->respondOk('Danh sách danh mục', $categories);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra vui lòng thử lại');
        }
    }

    public function getCourseDetail(Request $request, string $slug)
    {
        try {
            $user = auth('sanctum')->user();

            $course = Course::query()
                ->with([
                    'category:id,name,slug',
                    'user' => function ($query) {
                        $query->select(['id', 'name', 'avatar', 'created_at'])
                            ->with(['profile:id,user_id,about_me,bio']);
                    },
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

            $course->is_enrolled = false;

            if ($user) {
                $enrollment = CourseUser::query()->where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->exists();

                $course->is_enrolled = $enrollment;

                $course->is_enrolled = $course->is_enrolled || $user->id === $course->user_id;
            }

            $course->benefits = is_string($course->benefits) ? json_decode($course->benefits, true) : $course->benefits;
            $course->requirements = is_string($course->requirements) ? json_decode($course->requirements, true) : $course->requirements;
            $course->qa = is_string($course->qa) ? json_decode($course->qa, true) : $course->qa;

            $totalVideoDuration = 0;
            $course->chapters = $course->chapters->map(function ($chapter) use (&$totalVideoDuration) {
                $chapterVideoDuration = 0;

                foreach ($chapter->lessons as $lesson) {
                    if ($lesson->lessonable_type === Video::class && isset($lesson->lessonable->duration)) {
                        $duration = $lesson->lessonable->duration;
                        $chapterVideoDuration += $duration;
                        $totalVideoDuration += $duration;
                    }
                }

                $chapter->total_video_duration = $chapterVideoDuration;
                return $chapter;
            });

            $course->total_video_duration = $totalVideoDuration;

            $courseRating = Rating::query()->where('course_id', $course->id)
                ->select(
                    DB::raw('COUNT(*) as ratings_count'),
                    DB::raw('ROUND(AVG(rate), 1) as avg_rating')
                )
                ->first();

            $course->ratings_count = $courseRating ? $courseRating->ratings_count : 0;
            $course->avg_rating = $courseRating ? $courseRating->avg_rating : 0;

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

    public function getRelatedCourses(string $courseSlug)
    {
        try {
            $currentCourse = Course::query()->where('slug', $courseSlug)
                ->where('visibility', 'public')
                ->where('status', 'approved')
                ->first();

            if (!$currentCourse) {
                return $this->respondNotFound('Khóa học không tồn tại');
            }

            $relatedCourses = Course::query()
                ->where('category_id', $currentCourse->category_id)
                ->where('id', '!=', $currentCourse->id)
                ->where('visibility', 'public')
                ->where('status', 'approved')
                ->select([
                    'id',
                    'name',
                    'slug',
                    'thumbnail',
                    'level',
                    'category_id',
                    'is_free',
                    'price',
                    'price_sale',
                    'total_student',
                    'created_at',
                    'user_id'
                ])
                ->with([
                    'category:id,name',
                    'user:id,name,avatar',
                    'chapters.lessons.lessonable'
                ])
                ->withCount(['lessons', 'chapters'])
                ->when($currentCourse->level, function ($query) use ($currentCourse) {
                    return $query->orderByRaw(
                        'CASE WHEN level = ? THEN 0 ELSE 1 END',
                        [$currentCourse->level]
                    )
                        ->orderBy('total_student', 'desc');
                }, function ($query) {
                    return $query->orderBy('total_student', 'desc');
                })
                ->limit(5)
                ->get();

            $courseRatings = Rating::query()
                ->whereIn('course_id', $relatedCourses->pluck('id'))
                ->groupBy('course_id')
                ->select(
                    'course_id',
                    DB::raw('COUNT(*) as ratings_count'),
                    DB::raw('ROUND(AVG(rate), 1) as average_rating')
                )
                ->get()
                ->keyBy('course_id');

            $relatedCourses->transform(function ($course) {
                $totalVideoDuration = $course->chapters->flatMap(function ($chapter) {
                    return $chapter->lessons->filter(function ($lesson) {
                        return $lesson->lessonable_type === Video::class;
                    });
                })->sum(function ($lesson) {
                    return $lesson->lessonable->duration ?? 0;
                });

                $course->total_video_duration = $totalVideoDuration;

                return $course;
            });

            $relatedCourses->each(function ($course) use ($courseRatings) {
                $rating = $courseRatings->get($course->id);
                $course->ratings_count = $rating ? $rating->ratings_count : 0;
                $course->average_rating = $rating ? $rating->average_rating : 0;

                unset($course->chapters);
            });

            return $this->respondOk('Danh sách khóa học liên quan', [
                'current_course' => [
                    'id' => $currentCourse->id,
                    'name' => $currentCourse->name,
                    'category' => $currentCourse->category->name
                ],
                'related_courses' => $relatedCourses->map(function ($course) {
                    return [
                        'id' => $course->id,
                        'name' => $course->name,
                        'slug' => $course->slug,
                        'thumbnail' => $course->thumbnail,
                        'level' => $course->level,
                        'is_free' => $course->is_free,
                        'price' => $course->price,
                        'price_sale' => $course->price_sale,
                        'lessons_count' => $course->lessons_count,
                        'total_student' => $course->total_student,
                        'total_video_duration' => $course->total_video_duration,
                        'ratings_count' => $course->ratings_count,
                        'average_rating' => $course->average_rating,
                        'category' => $course->category,
                        'user' => $course->user
                    ];
                })
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
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
                    'courses.code',
                    'courses.name',
                    'courses.slug',
                    'courses.price',
                    'courses.price_sale',
                    'courses.thumbnail',
                    'courses.is_free',
                    'users.name as name_instructor',
                    'users.code as code_instructor',
                    DB::raw('COUNT(lessons.id) as total_lesson, SUM(videos.duration) as total_duration'),
                    DB::raw('ROUND(AVG(DISTINCT ratings.rate), 1) as avg_rating'),
                    DB::raw('COUNT(DISTINCT ratings.rate) as total_rating'),
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
                ->where('courses.slug', '<>', $slug)
                ->groupBy('courses.code', 'courses.name', 'courses.slug')
                ->orderBy('courses.total_student', 'desc')
                ->limit(9)
                ->get();

            $profileIntructor = DB::table('users')
                ->select(
                    'users.name',
                    'users.code',
                    'users.avatar',
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
                'message' => 'Danh sách khóa học và thông tin giảng viên',
                'get_other_courses' => $getOtherCourses,
                'profile_instructor' => $profileIntructor
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
