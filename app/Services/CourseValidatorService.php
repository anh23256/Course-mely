<?php

namespace App\Services;

use App\Models\Chapter;
use App\Models\Coding;
use App\Models\Course;
use App\Models\Document;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Video;

class CourseValidatorService
{
    public static function validateCourse(Course $course): array
    {
        $errors = [];

        $errors = array_merge($errors, self::validateBasicInfo($course));

        $errors = array_merge($errors, self::validateChapters($course));

        return $errors;
    }

    private static function validateBasicInfo(Course $course): array
    {
        $errors = [];

        if (!$course->name || !$course->description || !$course->thumbnail || !$course->level || !$course->category_id) {
            $errors[] = 'Khoá học phải có đầy đủ thông tin cơ bản.';
        }

        if (!$course->is_free && (!$course->price || $course->price <= 0)) {
            $errors[] = "Khóa học có phí phải có giá hợp lệ.";
        }

        $benefits = json_decode($course->benefits, true) ?? [];
        $requirements = json_decode($course->requirements, true) ?? [];
        $qa = json_decode($course->qa, true) ?? [];

        if (count($benefits) < 4 || count($benefits) > 10) {
            $errors[] = "Khóa học phải có từ 4 đến 10 lợi ích.";
        }
        if (count($requirements) < 4 || count($requirements) > 10) {
            $errors[] = "Khóa học phải có từ 4 đến 10 yêu cầu.";
        }

        return $errors;
    }

    private static function validateChapters(Course $course): array
    {
        $errors = [];
        $chapters = Chapter::where('course_id', $course->id)->get();

        if ($chapters->count() < 3) {
            $errors[] = "Khóa học phải có ít nhất 3 chương học. Hiện tại có {$chapters->count()} chương.";
        }

        foreach ($chapters as $chapter) {
            if (!$chapter->title) {
                $errors[] = "Chương học ID {$chapter->id} không có tiêu đề.";
            }

//            $totalVideoDuration = Video::whereIn('id', function ($query) use ($chapter) {
//                $query->select('lessonable_id')
//                    ->from('lessons')
//                    ->where('chapter_id', $chapter->id)
//                    ->where('lessonable_type', Video::class);
//            })->sum('duration');
//
//            if ($totalVideoDuration < 1800) {
//                $errors[] = "Tổng thời lượng video trong chương '{$chapter->title}' phải lớn hơn 30 phút. Hiện tại chỉ có " . round($totalVideoDuration / 60, 2) . " phút.";
//            }

            $errors = array_merge($errors, self::validateLessons($chapter->id, $chapter->title));
        }

        return $errors;
    }

    private static function validateLessons(int $chapterId, string $chapterTitle): array
    {
        $errors = [];
        $lessons = Lesson::where('chapter_id', $chapterId)->get();

        if ($lessons->count() < 3) {
            $errors[] = "Chương '{$chapterTitle}' cần ít nhất 3 bài giảng. Hiện tại có {$lessons->count()} bài.";
        }

        foreach ($lessons as $lesson) {
            if (!$lesson->title || !$lesson->content) {
                $errors[] = "Bài giảng '{$lesson->title}' (ID {$lesson->id}) trong chương '{$chapterTitle}' thiếu tiêu đề hoặc nội dung.";
            }

            if ($lesson->lessonable_type === Video::class) {
                $errors = array_merge($errors, self::validateVideo($lesson->lessonable_id, $lesson->title, $chapterTitle));
            }

            if ($lesson->lessonable_type === Quiz::class) {
                $errors = array_merge($errors, self::validateQuiz($lesson->lessonable_id, $lesson->title, $chapterTitle));
            }

            if ($lesson->lessonable_type === Coding::class) {
                $errors = array_merge($errors, self::validateCoding($lesson->lessonable_id, $lesson->title, $chapterTitle));
            }

            if ($lesson->lessonable_type === Document::class) {
                $errors = array_merge($errors, self::validateDocument($lesson->lessonable_id, $lesson->title, $chapterTitle));
            }
        }

        return $errors;
    }

    private static function validateVideo(int $videoId, string $lessonTitle, string $chapterTitle): array
    {
        $errors = [];
        $video = Video::find($videoId);

        if (!$video) {
            $errors[] = "Bài giảng '{$lessonTitle}' trong chương '{$chapterTitle}' không có video.";
            return $errors;
        }

        if ($video->duration < 300) {
            $errors[] = "Bài giảng '{$lessonTitle}' trong chương '{$chapterTitle}' có video dưới 5 phút.";
        }

        return $errors;
    }

    private static function validateQuiz(int $quizId, string $lessonTitle, string $chapterTitle): array
    {
        $errors = [];
        $quiz = Quiz::find($quizId);

        if ($quiz) {
            $questions = Question::where('quiz_id', $quiz->id)->get();
            if ($questions->count() < 1 || $questions->count() > 5) {
                $errors[] = "Bài kiểm tra '{$lessonTitle}' trong chương '{$chapterTitle}' phải có từ 1 đến 5 câu hỏi. Hiện tại có {$questions->count()} câu.";
            }
        }

        return $errors;
    }

    private static function validateCoding(int $codingId, string $lessonTitle, string $chapterTitle): array
    {
        $errors = [];
        $coding = Coding::query()->find($codingId);

        if ($coding) {
            if (!$coding->title || !$coding->language) {
                $errors[] = "Bài tập lập trình '{$lessonTitle}' trong chương '{$chapterTitle}' thiếu tiêu đề hoặc ngôn ngữ lập trình.";
            }

            if (!$coding->solution_code || !$coding->sample_code) {
                $errors[] = "Bài tập lập trình '{$lessonTitle}' trong chương '{$chapterTitle}' thiếu mã lời giải hoặc mã mẫu.";
            }
        }

        return [];
    }

    private static function validateDocument(int $documentId, string $lessonTitle, string $chapterTitle): array
    {
        $errors = [];
        $document = Document::query()->find($documentId);

        if ($document) {
            if (!$document->title || !$document->content) {
                $errors[] = "Tài liệu '{$lessonTitle}' trong chương '{$chapterTitle}' thiếu tiêu đề hoặc nội dung.";
            }
        }

        return $errors;
    }

}
