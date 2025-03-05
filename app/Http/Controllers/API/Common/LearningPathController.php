<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Jobs\CreateCertificateJob;
use App\Models\Coding;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\Document;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\UserCodingSubmission;
use App\Models\UserQuizSubmission;
use App\Models\Video;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                ->with('lessons.lessonable')
                ->where('status', 1)
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
            $totalLesson = $lessons->count();

            foreach ($chapters as $chapterIndex => $chapter) {
                $chapterLessons = [];
                $isChapterFirst = $chapterIndex === 0;
                $previousChapterCompleted = true;
                $totalChapterDuration = 0;

                $chapterTotalLessons = $chapter->lessons->count();

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

                    if ($lesson->type === 'video' && $lesson->lessonable_type === Video::class) {
                        $totalChapterDuration += $lesson->lessonable->duration; // Cộng dồn thời lượng video cho chương
                    }

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
                    'total_chapter_duration' => $totalChapterDuration,
                    'total_lessons' => $chapterTotalLessons,
                    'lessons' => $chapterLessons,
                ];
            }

            return $this->respondOk('Danh sách bài học của khoá học: ' . $course->name, [
                'course_name' => $course->name,
                'total_lesson' => $totalLesson,
                'chapter_lessons' => $response,
            ]);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function show(Request $request, $slug, $lessonId)
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

            $lesson = $course->lessons()->where('lessons.id', $lessonId)->first();

            if (!$lesson) {
                return $this->respondNotFound('Bài học không tồn tại');
            }

            $currentChapter = $lesson->chapter;
            $allChapters = $course->chapters()->with(['lessons' => function ($query) {
                $query->orderBy('order', 'asc');
            }])->orderBy('order', 'asc')->get();

            $chaptersWithLessons = $allChapters->filter(function ($chapter) {
                return $chapter->lessons()->count() > 0;
            })->values();

            $currentChapterIndex = $chaptersWithLessons->search(function ($chapter) use ($currentChapter) {
                return $chapter->id === $currentChapter->id;
            });

            $currentChapterLessons = $chaptersWithLessons[$currentChapterIndex]->lessons;
            $currentLessonIndex = $currentChapterLessons->search(function ($chapterLesson) use ($lesson) {
                return $chapterLesson->id === $lesson->id;
            });

            $previousLesson = null;
            if ($currentLessonIndex > 0) {
                $previousLesson = $currentChapterLessons[$currentLessonIndex - 1];
            } else {
                $previousChapterIndex = $currentChapterIndex - 1;
                if ($previousChapterIndex >= 0) {
                    $previousChapter = $chaptersWithLessons[$previousChapterIndex];
                    $previousLesson = $previousChapter->lessons->last();
                }
            }

            $nextLesson = null;
            if ($currentLessonIndex < $currentChapterLessons->count() - 1) {
                $nextLesson = $currentChapterLessons[$currentLessonIndex + 1];
            } else {
                $nextChapterIndex = $currentChapterIndex + 1;
                if ($nextChapterIndex < $chaptersWithLessons->count()) {
                    $nextChapter = $chaptersWithLessons[$nextChapterIndex];
                    $nextLesson = $nextChapter->lessons->first();
                }
            }

            $lessonProcess = LessonProgress::query()
                ->firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'lesson_id' => $lesson->id
                    ],
                    [
                        'is_completed' => 0,
                        'last_time_video' => $lesson->type === 'video' ? 0 : null
                    ]
                );

            $dataLesson = array_merge(
                $lesson->toArray(),
                [
                    'lessonable' => $lesson->lessonable
                        ? array_merge(
                            $lesson->lessonable->toArray(),
                            $lesson->type === 'quiz'
                                ? ['questions' => $lesson->lessonable->questions->load('answers')->toArray()]
                                : []
                        )
                        : null
                ]
            );

            return $this->respondOk('Thông tin bài học: ' . $lesson->title, [
                'lesson' => $dataLesson,
                'lesson_process' => $lessonProcess,
                'next_lesson' => $nextLesson,
                'previous_lesson' => $previousLesson,
            ]);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function updateLastTimeVideo(Request $request, $lessonId)
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

            $videoDuration = $lesson->lessonable->duration;
            if ($lastTime > $videoDuration) {
                return $this->respondBadRequest('Thời gian không thể vượt quá tổng thời lượng video');
            }

            $needsUpdate = false;

            if ($lastTime > $lessonProcess->last_time_video) {
                $lessonProcess->last_time_video = $lastTime;

                $completionThreshold = 2 / 3;
                if ($lastTime >= $videoDuration * $completionThreshold) {
                    $lessonProcess->is_completed = true;
                    $needsUpdate = true;
                }

                $lessonProcess->save();
            }

            if ($needsUpdate) {
                $chapter = $lesson->chapter;
                $courseId = $chapter->course_id;
                $this->updateCourseProgress($courseId, $user->id);
            }

            return $this->respondOk('Lưu tiến trình thời gian thành công');
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

            $chapter = $lesson->chapter;
            $course = $chapter->course;
            $courseLevel = $course->level;

            if ($courseLevel === 'advanced') {
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

                    if ($currentTime < ($videoDuration * 2 / 3)) {
                        return $this->respondError('Bạn cần xem ít nhất 2/3 thời gian video để hoàn thành bài học này');
                    }

                    if ($currentTime > $lessonProgress->last_time_video) {
                        $lessonProgress->last_time_video = $currentTime;
                    }

                    if ($currentTime >= ($videoDuration * 2 / 3)) {
                        $lessonProgress->is_completed = true;
                    } else {
                        $lessonProgress->save();
                        DB::commit();
                        return $this->respondOk('Thời gian đã xem video được cập nhật.', $lessonProgress);
                    }

                    break;

                case  Quiz::class:
                    $answers = $request->input('answers');

                    $quiz = Quiz::query()
                        ->with('questions.answers')
                        ->where('id', $lessonable->id)
                        ->first();

                    if (!$quiz) {
                        return $this->respondNotFound('Không tìm thấy bài kiểm tra.');
                    }

                    $check = 0;
                    $isCorrect = true;
                    foreach ($answers as $answer) {
                        $question = $quiz->questions()->where('id', $answer['question_id'])->first();

                        if (!$question) {
                            $isCorrect = false;
                            break;
                        }

                        // Handle check answer single choice
                        if (is_numeric($answer['answer_id'])) {
                            $selectedAnswer = $question->answers->where('id', $answer['answer_id'])->first();

                            if (!$selectedAnswer || $selectedAnswer->is_correct !== 1) {
                                $check++;
                                $isCorrect = false;
                                break;
                            }
                        }


                        // Handle check answer multiple choice
                        if (is_array($answer['answer_id'])) {
                            $correctAnswers = $question->answers->where('is_correct', true)->pluck('id')->toArray();

                            if (array_diff($answer['answer_id'], $correctAnswers) || array_diff($correctAnswers, $answer['answer_id'])) {
                                $isCorrect = false;
                                break;
                            }
                        }
                    }

                    if (!$isCorrect) {
                        return $this->respondError('Bạn cần trả lời chính xác tất cả các câu hỏi.');
                    }

                    $lessonProgress->is_completed = true;

                    UserQuizSubmission::query()->create([
                        'user_id' => $user->id,
                        'quiz_id' => $lessonable->id,
                        'answers' => json_encode($answers)
                    ]);

                    break;

                case  Document::class:
                    $lessonProgress->is_completed = true;
                    break;

                case Coding::class:
                    $userCodingInput = $request->input('code');
                    $userCodeResult = $request->input('result');

                    if (!$userCodingInput) {
                        return $this->respondError('Vui lòng thực hiện bài kiểm tra.');
                    }

                    $expectedResult = $lessonable->result_code;

                    if (!$userCodeResult || $userCodeResult !== $expectedResult) {
                        return $this->respondError('Kết quả code của bạn chưa đúng.');
                    }

                    $lessonProgress->is_completed = true;

                    UserCodingSubmission::query()->create([
                        'user_id' => $user->id,
                        'coding_id' => $lessonable->id,
                        'code' => $userCodingInput,
                        'result' => $userCodeResult,
                        'is_correct' => $userCodeResult === $expectedResult
                    ]);

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

    public function getQuizSubmission(string $lessonId, string $submissionQuizId)
    {
        try {
            $user = Auth::user();

            $lesson = Lesson::query()->find($lessonId);

            if (!$lesson) {
                return $this->respondNotFound('Bài học không tồn tại');
            }

            $chapter = $lesson->chapter;
            $course = $chapter->course;

            $course = Course::query()->find($course->id);

            if (!$course) {
                return $this->respondNotFound('Khóa học không tồn tại');
            }

            $userPurchaseCourse = $this->hashPurchasedCourse($user->id, $course->id);

            if (!$userPurchaseCourse) {
                return $this->respondForbidden('Bạn chưa mua khoá học này');
            }

            $quizSubmission
                = UserQuizSubmission::query()
                ->with([
                    'quiz.questions.answers'
                ])
                ->where('id', $submissionQuizId)
                ->where('user_id', $user->id)
                ->first();

            if (!$quizSubmission) {
                return $this->respondNotFound('Bạn chưa thực hiện bài kiểm tra');
            }

            $userAnswers = json_decode($quizSubmission->answers, true);
            $formattedAnswers = collect($userAnswers)->map(function ($answer) {
                return [
                    'answer_id' => is_array($answer['answer_id'])
                        ? $answer['answer_id']
                        : $answer['answer_id'],
                    'question_id' => $answer['question_id']
                ];
            })->toArray();

            return $this->respondOk('Thông tin bài kiểm tra', $formattedAnswers);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function getCodingSubmission(string $lessonId, string $submissionCodingId)
    {
        try {
            $user = Auth::user();

            $lesson = Lesson::query()->find($lessonId);

            if (!$lesson) {
                return $this->respondNotFound('Bài học không tồn tại');
            }

            $chapter = $lesson->chapter;
            $course = $chapter->course;

            $course = Course::query()->find($course->id);

            if (!$course) {
                return $this->respondNotFound('Khóa học không tồn tại');
            }

            $userPurchaseCourse = $this->hashPurchasedCourse($user->id, $course->id);

            if (!$userPurchaseCourse) {
                return $this->respondForbidden('Bạn chưa mua khoá học này');
            }

            $codingSubmission = UserCodingSubmission::query()
                ->with('coding')
                ->where('user_id', $user->id)
                ->where('coding_id', $submissionCodingId)
                ->first();

            if (!$codingSubmission) {
                return $this->respondNotFound('Bài thực hành chưa thực hiện');
            }

            $response = [
                'code' => $codingSubmission->code,
                'result' => $codingSubmission->result,
            ];

            return $this->respondOk('Thông tin bài tập bạn đã nộp', $response);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    private function hashPurchasedCourse($userId, $courseId)
    {
        return CourseUser::query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->exists();
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
                CreateCertificateJob::dispatch($userId, $courseId);
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
