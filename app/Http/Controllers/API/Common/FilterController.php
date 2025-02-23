<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Video;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FilterController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function filter(Request $request)
    {
        try {
            $query = Course::query();

            if ($request->has('categories') && !empty($request->categories) && is_array($request->categories)) {
                $query->whereExists(function ($q) use ($request) {
                    $q->select(DB::raw(1))
                        ->from('categories')
                        ->whereRaw('categories.id = courses.category_id')
                        ->whereIn('categories.slug', $request->categories);
                });
            }

            if ($request->has('rating') && !empty($request->rating) && !is_array($request->rating)) {
                $query->whereExists(function ($query) use ($request) {
                    $query->select(DB::raw(1))
                        ->from('ratings')
                        ->whereRaw('ratings.course_id = courses.id')
                        ->groupBy('ratings.course_id')
                        ->havingRaw('AVG(ratings.rate) >= ?', [$request->rating]);
                });
            }

            if ($request->has('instructors') && !empty($request->instructors) && is_array($request->instructors)) {
                $query->whereExists(function ($q) use ($request) {
                    $q->select(DB::raw(1))
                        ->from('users')
                        ->whereRaw('users.id = courses.user_id')
                        ->whereIn('users.code', $request->instructors);
                });
            }

            if ($request->has('levels') && !empty($request->levels) && is_array($request->levels)) {
                $query->whereIn('level', $request->levels);
            }

            if ($request->has('is_free') && $request->is_free !== null) {
                $query->where('is_free', $request->is_free);
            }

            if ($request->has('video_duration') && !empty($request->video_duration) && is_array($request->video_duration)) {
                $query->whereExists(function ($q) use ($request) {
                    $q->select(DB::raw(1))
                        ->from('lessons')
                        ->join('videos', function ($join) {
                            $join->on('lessons.lessonable_id', '=', 'videos.id')
                                ->where('lessons.lessonable_type', Video::class);
                        })
                        ->whereRaw('lessons.chapter_id IN (SELECT id FROM chapters WHERE chapters.course_id = courses.id)')
                        ->havingRaw(
                            collect($request->video_duration)->map(function ($range) {
                                return '(SUM(videos.duration) BETWEEN ? AND ?)';
                            })->implode(' OR '),
                            collect($request->video_duration)->flatten()->toArray()
                        );
                });
            }

            if ($request->has('features') && !empty($request->features) && is_array($request->features)) {
                $query->whereExists(function ($q) use ($request) {
                    $q->select(DB::raw(1))
                        ->from('lessons')
                        ->whereRaw('lessons.chapter_id IN (SELECT id FROM chapters WHERE chapters.course_id = courses.id)')
                        ->whereIn('lessons.type', $request->features);
                });
            }

            $courseIds = $query->pluck('id');

            $courses = Course::query()
                ->select([
                    'courses.id',
                    'courses.user_id',
                    'courses.category_id',
                    'courses.name',
                    'courses.slug',
                    'courses.thumbnail',
                    'courses.price',
                    'courses.price_sale',
                    'courses.is_free',
                    'courses.total_student',
                    DB::raw('(SELECT CAST(AVG(ratings.rate) AS UNSIGNED) FROM ratings WHERE ratings.course_id = courses.id) as total_rating'),
                    DB::raw('(SELECT COUNT(*) FROM lessons WHERE lessons.chapter_id IN (SELECT id FROM chapters WHERE chapters.course_id = courses.id)) as total_lessons'),
                    DB::raw('(SELECT CAST(SUM(videos.duration) AS UNSIGNED) FROM videos 
                          JOIN lessons ON lessons.lessonable_id = videos.id
                          WHERE lessons.lessonable_type = "App\\\Models\\\Video" 
                          AND lessons.chapter_id IN (SELECT id FROM chapters WHERE chapters.course_id = courses.id)) as total_duration')
                ])
                ->whereIn('courses.id', $courseIds)->with('user:id,name')
                ->get();

            if ($courses->isEmpty()) {
                return $this->respondNotFound('Không có dữ liệu');
            }

            return $this->respondOk('Kết quả tìm kiếm', $courses);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Internal Server Error');
        }
    }

    public function filterOrderBy(Request $request)
    {
        try {
            $query = Course::query();

            $sortBy = $request->sort_by ?? '';

            switch ($sortBy) {
                case 'price_asc':
                    $query->orderByRaw('COALESCE(NULLIF(price_sale, 0), price) ASC');
                    break;

                case 'price_desc':
                    $query->orderByRaw('COALESCE(NULLIF(price_sale, 0), price) DESC');
                    break;

                default:
                    $query->orderByDesc('views');
                    break;
            }

            $courses = $query->select([
                'courses.id',
                'courses.user_id',
                'courses.category_id',
                'courses.name',
                'courses.slug',
                'courses.thumbnail',
                'courses.price',
                'courses.price_sale',
                'courses.is_free',
                'courses.total_student',
                DB::raw('(SELECT CAST(AVG(ratings.rate) AS UNSIGNED) FROM ratings WHERE ratings.course_id = courses.id) as total_rating'),
                DB::raw('(SELECT COUNT(*) FROM lessons WHERE lessons.chapter_id IN (SELECT id FROM chapters WHERE chapters.course_id = courses.id)) as total_lessons'),
                DB::raw('(SELECT CAST(SUM(videos.duration) AS UNSIGNED) FROM videos 
                          JOIN lessons ON lessons.lessonable_id = videos.id
                          WHERE lessons.lessonable_type = "App\\\Models\\\Video" 
                          AND lessons.chapter_id IN (SELECT id FROM chapters WHERE chapters.course_id = courses.id)) as total_duration')
            ])->get();

            if ($courses->isEmpty()) {
                return $this->respondNotFound('Không có dữ liệu');
            }

            return $this->respondOk('Kết quả tìm kiếm', $courses);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Internal Server Error');
        }
    }
}
