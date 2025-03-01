<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LearnerController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $courses = Course::query()->where('user_id', $user->id)->pluck('id');

            $learners = DB::table('course_users')
                ->join('users', 'course_users.user_id', '=', 'users.id')
                ->whereIn('course_users.course_id', $courses)
                ->select(
                    'users.id',
                    'users.name',
                    'users.email',
                    'users.avatar',
                    'course_users.enrolled_at',
                    DB::raw('COUNT(course_users.course_id) as total_courses')
                )
                ->groupBy('course_users.user_id', 'users.id', 'users.name', 'users.email', 'users.avatar', 'course_users.enrolled_at')
                ->distinct()
                ->orderBy('enrolled_at', 'desc')
                ->get();

            if ($learners->isEmpty()) {
                return $this->respondNotFound('Chưa có học viên');
            }

            return $this->respondOk('Danh sách học viên', $learners);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function infoLearner($id)
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $learner = User::query()->where('id', $id)->first();

            if (!$learner) {
                return $this->respondNotFound('Không tìm thấy học viên');
            }

            $courseUsers = CourseUser::query()
                ->with([
                    'course',
                    'user'
                ])
                ->where('user_id', $learner->id)
                ->get();

            if ($courseUsers->isEmpty()) {
                return $this->respondNotFound('Học viên chưa mua khóa học nào');
            }

            $courseDetails = $courseUsers->map(function ($courseUser) use ($learner) {
                $totalLessons = Lesson::query()
                    ->join('chapters', 'lessons.chapter_id', '=', 'chapters.id')
                    ->where('chapters.course_id', $courseUser->course_id)
                    ->count();

                $completedLessons = LessonProgress::query()
                    ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
                    ->join('chapters', 'lessons.chapter_id', '=', 'chapters.id')
                    ->where('chapters.course_id', $courseUser->course_id)
                    ->where('lesson_progress.user_id', $learner->id)
                    ->where('lesson_progress.is_completed', true)
                    ->count();

                $totalTimeSpent = LessonProgress::query()
                    ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
                    ->join('chapters', 'lessons.chapter_id', '=', 'chapters.id')
                    ->where('chapters.course_id', $courseUser->course_id)
                    ->where('lesson_progress.user_id', $learner->id)
                    ->sum('lesson_progress.last_time_video');

                $lastStudyTime = LessonProgress::query()
                    ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
                    ->join('chapters', 'lessons.chapter_id', '=', 'chapters.id')
                    ->where('lesson_progress.user_id', $learner->id)
                    ->where('chapters.course_id', $courseUser->course_id)
                    ->latest('lesson_progress.updated_at')
                    ->value('lesson_progress.updated_at');

                $comments = Comment::query()
                    ->join('lessons', 'comments.commentable_id', '=', 'lessons.id')
                    ->join('chapters', 'lessons.chapter_id', '=', 'chapters.id')
                    ->where('comments.commentable_type', Lesson::class)
                    ->where('comments.user_id', '=', $learner->id)
                    ->where('chapters.course_id', $courseUser->course_id)
                    ->select('comments.content', 'comments.commentable_id as lesson_id', 'comments.created_at')
                    ->get();

                $total_comments_overall = Comment::query()
                    ->join('lessons', 'comments.commentable_id', '=', 'lessons.id')
                    ->join('chapters', 'lessons.chapter_id', '=', 'chapters.id')
                    ->where('comments.commentable_type', Lesson::class)
                    ->where('comments.user_id', '=', $learner->id)
                    ->count();

                $completion_rate = $totalLessons > 0 ? ($completedLessons / $totalLessons) * 100 : 0;

                $totalCompletedByAllUsers = LessonProgress::query()
                    ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
                    ->join('chapters', 'lessons.chapter_id', '=', 'chapters.id')
                    ->where('lesson_progress.is_completed', true)
                    ->count();

                $totalUsersInCourse = $courseUser
                    ->where('course_id', $courseUser->course_id)
                    ->count();

                $averageCompletionRate = ($totalUsersInCourse > 0 && $totalLessons > 0)
                    ? (($totalCompletedByAllUsers / ($totalUsersInCourse * $totalLessons)) * 100)
                    : 0;

                return [
                    'course_name' => $courseUser->course->name,
                    'course_slug' => $courseUser->course->slug,
                    'enrolled_at' => $courseUser->enrolled_at,
                    'total_lessons' => $totalLessons,
                    'completed_lessons' => $completedLessons,
                    'total_time_spent' => $totalTimeSpent,
                    'last_study_time' => $lastStudyTime,
                    'comments' => $comments,
                    'total_comments_overall' => $total_comments_overall,
                    'completion_rate' => $completion_rate,
                    'average_completion_rate' => $averageCompletionRate,
                    'comparison' => $completion_rate - $averageCompletionRate
                ];
            });

            $totalTimeOverall = $courseDetails->sum('total_time_spent');

            return $this->respondOk('Thông tin tiến độ học tập của: ' . $learner->name, [
                'learner' => [
                    'name' => $learner->name,
                    'email' => $learner->email,
                    'total_courses' => $courseDetails->count(),
                    'total_time_spent' => $totalTimeOverall,
                ],
                'courses' => $courseDetails
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }
}
