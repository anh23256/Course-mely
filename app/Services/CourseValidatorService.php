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
        $validator = new self();
        $errors = [];

        $errors = array_merge($errors, $validator->validateBasicInfo($course));

        if ($course->is_practical_course) {
            $errors = array_merge($errors, $validator->checkPracticalCurriculum($course));
        } else {
            $errors = array_merge($errors, $validator->checkCurriculum($course));
            $errors = array_merge($errors, $validator->validateTotalDuration($course));
        }


        return $errors;
    }

    private function validateBasicInfo(Course $course): array
    {
        $errors = [];

        if (empty($course->name) || strlen($course->name) < 5) {
            $errors[] = "Khóa học phải có tên với tối thiểu 5 ký tự.";
        }

        if (empty($course->description) || strlen($course->description) < 100) {
            $errors[] = "Mô tả khóa học phải có tối thiểu 100 ký tự.";
        }

        if (empty($course->thumbnail)) {
            $errors[] = "Khóa học phải có hình đại diện.";
        }

        if ($course->category()->count() == 0) {
            $errors[] = "Khóa học phải có danh mục.";
        }

        if (!$course->level) {
            $errors[] = "Khoá học phải có mức độ khó.";
        }

        if (!$course->is_free && (!$course->price || $course->price <= 0)) {
            $errors[] = "Khóa học có phí phải có giá hợp lệ.";
        }

        $benefits = json_decode($course->benefits, true) ?? [];
        $requirements = json_decode($course->requirements, true) ?? [];

        if (count($benefits) < 4 || count($benefits) > 10) {
            $errors[] = "Khóa học phải có từ 4 đến 10 lợi ích.";
        }
        if (count($requirements) < 4 || count($requirements) > 10) {
            $errors[] = "Khóa học phải có từ 4 đến 10 yêu cầu.";
        }

        return $errors;
    }

    private function checkCurriculum(Course $course)
    {
        $errors = [];
        $chapters = $course->chapters()->get();
        $errors = array_merge($errors, $this->validateChapters($chapters));
        return $errors;
    }

    private function checkPracticalCurriculum(Course $course): array
    {
        $errors = [];
        $chapters = $course->chapters()->get();

        $errors = array_merge($errors, $this->validatePracticalChapters($chapters));

        return $errors;
    }

    private function validatePracticalChapters($chapters): array
    {
        $errors = [];

        if ($chapters->count() == 0) {
            $errors[] = "Khóa học thực hành phải có ít nhất 1 chương học.";
        }

        foreach ($chapters as $chapter) {
            if (!$chapter->title) {
                $errors[] = "Chương học ID {$chapter->id} không có tiêu đề.";
            }

            $errors = array_merge($errors, $this->validatePracticalLessons($chapter));
        }

        return $errors;
    }

    private function validatePracticalLessons($chapter): array
    {
        $errors = [];
        $lessons = $chapter->lessons()->get();
        $hasQuiz = false;

        foreach ($lessons as $lesson) {
            if (empty($lesson->title)) {
                $errors[] = "Bài học ID {$lesson->id} trong chương '{$chapter->title}' thiếu tiêu đề.";
            }

            if ($lesson->lessonable_type == Quiz::class) {
                $hasQuiz = true;
                $errors = array_merge($errors, $this->validatePracticalQuiz($lesson, $chapter));
            }
        }

        if (!$hasQuiz && $lessons->count() > 0) {
            $errors[] = "Chương học '{$chapter->title}' cần có ít nhất một bài kiểm tra (quiz).";
        }

        return $errors;
    }

    private function validatePracticalQuiz($lesson, $chapter)
    {
        $errors = [];
        $quiz = Quiz::query()->find($lesson->lessonable_id);

        if ($quiz) {
            $questions = Question::query()->where('quiz_id', $quiz->id)->get();
            if ($questions->count() < 10 || $questions->count() > 50) {
                $errors[] = "Bài kiểm tra '{$lesson->title}' (ID {$lesson->id}) trong chương '{$chapter->title}' phải có từ 10 đến 50 câu hỏi. Hiện tại có {$questions->count()} câu.";
            }
        }

        return $errors;
    }

    private function validateChapters($chapters): array
    {
        $errors = [];
        if ($chapters->count() > 0 && $chapters->count() < 3) {
            $errors[] = "Khóa học phải có ít nhất 3 chương học. Hiện tại có {$chapters->count()} chương.";
        }

        foreach ($chapters as $index => $chapter) {
            if (!$chapter->title) {
                $errors[] = "Chương học ID {$chapter->id} không có tiêu đề.";
            }

            if ($index === 0) continue;

            if ($chapter->lessons()->count() < 3) {
                $errors[] = "Chương học '{$chapter->title}' phải có ít nhất 3 bài học. Hiện tại có {$chapter->lessons()->count()} bài.";
            }

            if ($index > 1) {
                $errors = array_merge($errors, $this->validateChapterVideoDuration($chapter));
            }

            $errors = array_merge($errors, $this->validateLessons($chapter));
        }

        return $errors;
    }

    private function validateChapterVideoDuration(Chapter $chapter): array
    {
        $errors = [];
        $totalVideoDuration = Video::whereIn('id', function ($query) use ($chapter) {
            $query->select('lessonable_id')
                ->from('lessons')
                ->where('chapter_id', $chapter->id)
                ->where('lessonable_type', Video::class);
        })->sum('duration');

        if ($totalVideoDuration < 1800) {
            $errors[] = "Tổng thời lượng video trong chương '{$chapter->title}' phải lớn hơn 30 phút. Hiện tại chỉ có " . round($totalVideoDuration / 60, 2) . " phút.";
        }

        return $errors;
    }

    private function validateLessons($chapter)
    {
        $errors = [];
        $lessons = $chapter->lessons()->get();

        foreach ($lessons as $lesson) {
            if (empty($lesson->title)) {
                $errors[] = "Bài học ID {$lesson->id} trong chương '{$chapter->title}' thiếu tiêu đề.";
            }

            $errors = array_merge($errors, $this->validateLessonType($lesson, $chapter));
        }

        return $errors;
    }

    private function validateLessonType($lesson, $chapter)
    {
        $errors = [];

        switch ($lesson->lessonable_type) {
            case Video::class:
                $errors = array_merge($errors, $this->validateVideo($lesson, $chapter));
                break;
            case Quiz::class:
                $errors = array_merge($errors, $this->validateQuiz($lesson, $chapter));
                break;
            case Coding::class:
                $errors = array_merge($errors, $this->validateCoding($lesson, $chapter));
                break;
            case Document::class:
                $errors = array_merge($errors, $this->validateDocument($lesson, $chapter));
                break;
        }

        return $errors;
    }

    private function validateTotalDuration(Course $course): array
    {
        $errors = [];
        $totalDuration = Video::whereIn('id', function ($query) use ($course) {
            $query->select('lessonable_id')
                ->from('lessons')
                ->whereIn('chapter_id', function ($subQuery) use ($course) {
                    $subQuery->select('id')
                        ->from('chapters')
                        ->where('course_id', $course->id);
                })
                ->where('lessonable_type', Video::class);
        })->sum('duration');

        if ($totalDuration < 7200) {
            $errors[] = "Tổng thời lượng video trong khóa học phải lớn hơn 2 giờ. Hiện tại có " . round($totalDuration / 60, 2) . " phút.";
        }

        return $errors;
    }

    private function validateVideo($lesson, $chapter)
    {
        $errors = [];
        $video = Video::query()->find($lesson->lessonable_id);

        if ($this->isIntroductionLesson($chapter)) {
            return $errors;
        }

        if ($video) {
            if ($video->duration < 120) {
                $errors[] = "Bài giảng '{$lesson->title}' trong chương '{$chapter->title}' có video dưới 2 phút.";
            }

            if ($video->duration > 2400) {
                $errors[] = "Bài giảng '{$lesson->title}' trong chương '{$chapter->title}' có video quá dài (trên 40 phút).";
            }
        }

        return $errors;
    }

    private function validateQuiz($lesson, $chapter)
    {
        $errors = [];
        $quiz = Quiz::query()->find($lesson->lessonable_id);

        if ($quiz) {
            $questions = Question::query()->where('quiz_id', $quiz->id)->get();
            if ($questions->count() < 1 || $questions->count() > 10) {
                $errors[] = "Bài kiểm tra '{$lesson->title}' (ID {$lesson->id}) trong chương '{$chapter->title}' phải có từ 1 đến 10 câu hỏi. Hiện tại có {$questions->count()} câu.";
            }
        }

        return $errors;
    }

    private function validateCoding($lesson, $chapter)
    {
        $errors = [];
        $coding = Coding::query()->find($lesson->lessonable_id);

        if ($coding) {
            if (empty($coding->title)) {
                $errors[] = "Bài tập coding trong chương '{$chapter->title}' thiếu tiêu đề.";
            }
            if (empty($coding->language)) {
                $errors[] = "Bài tập coding '{$coding->title}' trong chương '{$chapter->title}' chưa chọn ngôn ngữ lập trình.";
            }
            if (empty($coding->sample_code)) {
                $errors[] = "Bài tập coding '{$coding->title}' trong chương '{$chapter->title}' thiếu đoạn mã mẫu (sample code).";
            }
        }

        return $errors;
    }

    private function validateDocument($lesson, $chapter)
    {
        $errors = [];
        $document = Document::query()->find($lesson->lessonable_id);

        if ($document) {
            if (empty($document->title)) {
                $errors[] = "Tài liệu '{$document->title}' trong chương '{$chapter->title}' thiếu tiêu đề.";
            }
            if (empty($document->content)) {
                $errors[] = "Tài liệu '{$document->title}' trong chương '{$chapter->title}' thiếu nội dung.";
            }
        }

        return $errors;
    }

    private function isIntroductionLesson($lesson)
    {
        $introductionKeywords = ['Giới thiệu', 'Introduction', 'Overview'];

        if (isset($lesson->is_introduction) && $lesson->is_introduction) {
            return true;
        }

        foreach ($introductionKeywords as $keyword) {
            if (stripos($lesson->title, $keyword) !== false) {
                return $this->isSimpleIntroduction($lesson);
            }
        }

        if (isset($lesson->is_introduction) && $lesson->is_introduction) {
            return true;
        }

        return false;
    }

    private function isSimpleIntroduction($lesson)
    {
        $nonIntroductionTypes = [Quiz::class, Coding::class];
        if (in_array($lesson->lessonable_type, $nonIntroductionTypes)) {
            return false;
        }

        return true;
    }
}
