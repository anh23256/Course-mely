<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Models\Coding;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\Document;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Quiz;
use App\Models\Video;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LearningPathController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function getLessons(Request $request, $slug)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->respondUnauthorized('Vui lòng đăng nhập để xem nội dung');
            }

            $course = Course::query()->where('slug', $slug)->first();

            if (!$course) {
                return $this->respondNotFound('Khóa học không tồn tại');
            }

            $userPurchaseCourse = CourseUser::query()
                ->where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->exists();

            if (!$userPurchaseCourse) {
                return $this->respondForbidden('Bạn chưa mua khoá học này');
            }

            $chapters = $course->chapters()
                ->with('lessons')
                ->orderBy('order', 'asc')
                ->get();

            $lessons = $course->lessons()
                ->with('lessonable')
                ->orderBy('order', 'asc')
                ->get();

            $userLessonProgress = LessonProgress::query()
                ->where('user_id', $user->id)
                ->whereIn('lesson_id', $lessons->pluck('id'))
                ->pluck('is_completed', 'lesson_id');

            $response = [];
            $courseLevel = $course->level;

            foreach ($chapters as $chapterIndex => $chapter) {
                $chapterLessons = [];
                $isChapterFirst = $chapterIndex === 0;
                $previousChapterCompleted = true;

                if (!$isChapterFirst) {
                    $previousChapter = $chapters[$chapterIndex - 1];
                    $previousChapterCompleted = $previousChapter->lessons->every(function ($lesson) use ($userLessonProgress) {
                        return $userLessonProgress[$lesson->id] ?? false;
                    });
                }

                foreach ($chapter->lessons as $lessonIndex => $lesson) {
                    $isLessonFirst = $lessonIndex === 0;
                    $isUnlocked = true;

                    if ($courseLevel === 'advanced') {
                        if ($isLessonFirst) {
                            $isUnlocked = $previousChapterCompleted;
                        } else {
                            $previousLesson = $chapter->lessons->where('order', $lesson->order - 1)->first();
                            $isUnlocked = $userLessonProgress[$previousLesson->id] ?? false;
                        }
                    }

                    $isCompleted = $userLessonProgress[$lesson->id] ?? false;

                    $chapterLessons[] = [
                        'id' => $lesson->id,
                        'title' => $lesson->title,
                        'type' => $lesson->type,
                        'is_completed' => (bool)$isCompleted,
                        'is_unlocked' => (bool)$isUnlocked,
                        'order' => $lesson->order,
                        'lessonable' => $lesson->lessonable,
                    ];
                }

                $response[] = [
                    'chapter_id' => $chapter->id,
                    'chapter_title' => $chapter->title,
                    'lessons' => $chapterLessons,
                ];
            }

            return $this->respondOk('Danh sách bài học của khoá học: ' . $course->name, $response);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function show(Request $request, $slug, $lesson)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->respondUnauthorized('Vui lòng đăng nhập để xem nội dung');
            }

            $course = Course::query()->where('slug', $slug)->first();

            if (!$course) {
                return $this->respondNotFound('Khóa học không tồn tại');
            }

            $userPurchaseCourse = CourseUser::query()
                ->where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->exists();

            if (!$userPurchaseCourse) {
                return $this->respondForbidden('Bạn chưa mua khoá học này');
            }

            $lesson = $course->lessons()->where('lessons.id', $lesson)
                ->first();

            if (!$lesson) {
                return $this->respondNotFound('Bài học không tồn tại');
            }

            $lessonProcess = LessonProgress::query()
                ->firstOrCreate([
                    'user_id' => $user->id,
                    'lesson_id' => $lesson->id
                ],
                    [
                        'is_completed' => 0,
                        'last_time_video' => $lesson->type === 'video' ? 0 : null
                    ]
                );

            $nextLesson = $course->lessons()->where('lessons.id', '>', $lesson->id)
                ->orderBy('lessons.id', 'asc')
                ->first();

            $previousLesson = $course->lessons()->where('lessons.id', '<', $lesson->id)
                ->orderBy('lessons.id', 'desc')
                ->first();

            return $this->respondOk('Thông tin bài học: ' . $lesson->title, [
                'lesson' => $lesson->load('lessonable'),
                'lesson_process' => $lessonProcess,
                'next_lesson' => $nextLesson,
                'previous_lesson' => $previousLesson
            ]);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function updateLastTimeVdieo(Request $request, $lessonId)
    {
        try {
            $user = Auth::user();

            $lastTime = $request->input('last_time_video');

            $lesson = Lesson::query()->find($lessonId);

            if (!$lesson) {
                return $this->respondNotFound('Bài học không tồn tại');
            }

            if ($lesson->type !== 'video') {
                return $this->respondBadRequest('Bài học không phải là video');
            }

            $lessonProcess = LessonProgress::query()
                ->where('user_id', $user->id)
                ->where('lesson_id', $lessonId)
                ->first();

            if (!$lessonProcess) {
                return $this->respondNotFound('Bài học chưa được bắt đầu');
            }

            $lessonProcess->last_time_video = $lastTime;
            $lessonProcess->save();

            return $this->respondOk('Cập nhật tiến độ thành công', $lessonProcess);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function completeLesson(Request $request, $lessonId)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();

            $lesson = Lesson::query()->find($lessonId);

            if (!$lesson) {
                return $this->respondNotFound('Bài học không tồn tại');
            }

            $previousLesson = Lesson::query()
                ->where('chapter_id', $lesson->chapter_id)
                ->where('order', '<', $lesson->order)
                ->orderBy('order', 'desc')
                ->first();

            if ($previousLesson) {
                $previousLessonProgress = LessonProgress::query()
                    ->where('lesson_id', $previousLesson->id)
                    ->where('user_id', $user->id)
                    ->first();

                if (!$previousLessonProgress || !$previousLessonProgress->is_completed) {
                    return $this->respondOk('Bạn cần hoàn thành bài học trước trước khi tiếp tục.');
                }
            }


            $lessonableType = $lesson->lessonable_type;
            $lessonable = $lesson->lessonable;

            $lessonProgress = LessonProgress::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'lesson_id' => $lessonId,
                ],
                [
                    'is_completed' => false,
                    'last_time_video' => 0,
                ]
            );

            if ($lessonProgress->is_completed) {
                return $this->respondOk('Bài học này đã được hoàn thành trước đó.');
            }

            switch ($lessonableType) {
                case Video::class:
                    $currentTime = $request->input('current_time', 0);

                    $videoDuration = $lessonable->duration;

                    if ($currentTime < $videoDuration) {
                        return $this->respondError('Bạn cần xem hết video để hoàn thành bài học này');
                    }

                    if ($currentTime > $lessonProgress->last_time_video) {
                        $lessonProgress->last_time_video = $currentTime;
                    }

                    if ($currentTime >= $videoDuration) {
                        $lessonProgress->is_completed = true;
                    } else {
                        $lessonProgress->save();
                        DB::commit();
                        return $this->respondOk('Thời gian đã xem video được cập nhật.', $lessonProgress);
                    }

                    break;

                case  Quiz::class:
                    $answers = $request->input('answers');
                    if (!$answers || count($answers) < count($lessonable->questions)) {
                        return $this->respondError('Bạn cần trả lời hết tất cả câu hỏi.');
                    }

                    $isCorrect = true;
                    foreach ($lessonable->questions as $question) {
                        if (!isset($answers[$question->id])
                            || $answers[$question->id]
                            != $question->correct_answer) {
                            $isCorrect = false;
                            break;
                        }
                    }

                    if (!$isCorrect) {
                        return $this->respondError('Bạn cần trả lời chính xác tất cả các câu hỏi.');
                    }

                    $lessonProgress->is_completed = true;

                    break;

                case  Document::class:
                    $lessonProgress->is_completed = true;
                    break;

                case Coding::class:
                    $userCodeResult = $request->input('code');
                    $expectedResult = $lessonable->result_code;

                    if (!$userCodeResult || $userCodeResult !== $expectedResult) {
                        return $this->respondError('Kết quả code của bạn chưa đúng.');
                    }

                    $lessonProgress->is_completed = true;

                    break;

                default:
                    return $this->respondError('Loại bài học không được hỗ trợ.');
            }

            $chapter = $lesson->chapter;
            $courseId = $chapter->course_id;

            $lessonProgress->update([
                'is_completed' => true
            ]);

            $this->updateCourseProgress($courseId, $user->id);

            DB::commit();
            return $this->respondOk('Đánh dấu bài học hoàn thành thành công', $lessonProgress);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    private function updateCourseProgress($courseId, $userId)
    {
        try {
            $totalLessons = Lesson::query()->whereIn('chapter_id', function ($query) use ($courseId) {
                $query->select('id')->from('chapters')->where('course_id', $courseId);
            })->count();

            $completedLessons = LessonProgress::query()->where('user_id', $userId)
                ->where('is_completed', true)
                ->whereIn('lesson_id', function ($query) use ($courseId) {
                    $query->select('id')->from('lessons')->whereIn('chapter_id', function ($q) use ($courseId) {
                        $q->select('id')->from('chapters')->where('course_id', $courseId);
                    });
                })
                ->count();


            $progressPercent = $totalLessons > 0
                ? ($completedLessons / $totalLessons) * 100
                : 0;

            $courseUser = CourseUser::query()->firstOrCreate([
                'user_id' => $userId,
                'course_id' => $courseId,
            ]);

            $courseUser->progress_percent = round($progressPercent, 2);

            if ($progressPercent == 100) {
                $courseUser->completed_at = now();
            } else {
                $courseUser->completed_at = null;
            }

            $courseUser->save();
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }
}
