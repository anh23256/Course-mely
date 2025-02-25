<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Courses\StoreCourseRequest;
use App\Http\Requests\API\Courses\UpdateContentCourse;
use App\Http\Requests\API\Courses\UpdateCourseObjectives;
use App\Models\Coding;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\Document;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Video;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
use App\Traits\UploadToMuxTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CourseController extends Controller
{
    use LoggableTrait, UploadToCloudinaryTrait, UploadToMuxTrait, ApiResponseTrait;

    const FOLDER_COURSE_THUMBNAIL = 'courses/thumbnail';
    const FOLDER_COURSE_INTRO = 'courses/intro';

    public function index(Request $request)
    {
        try {
            $query = $request->input('q');

            $courses = Course::query()
                ->where('user_id', Auth::id())
                ->select([
                    'id', 'category_id', 'name', 'slug', 'thumbnail',
                    'intro', 'price', 'price_sale', 'total_student',
                    'status'
                ])
                ->with([
                    'category:id,name,slug,parent_id',
                    'chapters:id,course_id,title,order',
                    'chapters.lessons:id,chapter_id,title,slug,order'
                ])
                ->search($query)
                ->orderBy('created_at')
                ->get();

            if ($courses->isEmpty()) {
                return $this->respondNotFound('Không tìm thấy khoá học');
            }

            return $this->respondOk('Danh sách khoá học của: ' . Auth::user()->name,
                $courses
            );
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại',
            );
        }
    }

    public function courseListOfUser(string $slug)
    {
        try {
            $user = Auth::user();
            $course = Course::query()
                ->select('id', 'user_id', 'code', 'category_id', 'name', 'slug', 'thumbnail', 'intro', 'level', 'price', 'price_sale', 'total_student', 'accepted')
                ->where('slug', $slug)
                ->with([
                    'user:id,name,email,avatar,created_at',
                    'category:id,name,slug,parent_id',
                ])
                ->first();

            if (!$course) {
                return $this->respondNotFound('Không tìm thấy khoá học');
            }

            if ($course->user_id !== $user->id) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $totalChapter = $course->chapters()->get();

            $totalLesson = $totalChapter->sum(function ($chapter) {
                return $chapter->lessons->count();
            });

            $studentProgress = CourseUser::query()->where('course_id', $course->id)
                ->with(['user:id,name,avatar'])
                ->get()
                ->map(function ($enrollment) {
                    return [
                        'user_id' => $enrollment->user_id,
                        'user_name' => $enrollment->user->name,
                        'user_avatar' => $enrollment->user->avatar,
                        'progress_percent' => $enrollment->progress_percent ?? 0,
                        'enrolled_at' => $enrollment->enrolled_at,
                        'completed_at' => $enrollment->completed_at
                    ];
                });

            return $this->respondOk('Thông tin khoá học:' . $course->name, [
                'course' => $course,
                'total_chapter' => $totalChapter->count(),
                'total_lesson' => $totalLesson,
                'student_progress' => $studentProgress
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại',
            );
        }
    }

    public function getCourseOverView(string $slug)
    {
        try {
            $course = Course::query()
                ->where('slug', $slug)
                ->with([
                    'user:id,name,email,avatar,created_at',
                    'category:id,name,slug,parent_id',
                    'chapters:id,course_id,title,order',
                    'chapters.lessons'
                ])
                ->first();

            if (!$course) {
                return $this->respondNotFound('Không tìm thấy khoá học');
            }

            if ($course->user_id !== Auth::id()) {
                return $this->respondForbidden('Không có quyền thực hiện thao tác');
            }

            $course->benefits = is_string($course->benefits) ? json_decode($course->benefits, true) : $course->benefits;
            $course->requirements = is_string($course->requirements) ? json_decode($course->requirements, true) : $course->requirements;
            $course->qa = is_string($course->qa) ? json_decode($course->qa, true) : $course->qa;

            return $this->respondOk('Thông tin khoá học: ' . $course->name,
                $course
            );
        } catch (\Exception $e) {

            $this->logError($e);
            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại',
            );
        }
    }

    public function store(StoreCourseRequest $request)
    {
        try {
            $data = $request->validated();

            $data['user_id'] = Auth::id();

            if ($data['user_id'] !== Auth::id()) {
                return $this->respondForbidden('Không có quyền thực hiện thao tác');
            }

            do {
                $data['code'] = (string)Str::uuid();
                $exits = Course::query()->where('code', $data['code'])->exists();
            } while ($exits);

            $data['slug'] = !empty($data['name'])
                ? Str::slug($data['name']) . '-' . $data['code']
                : $data['code'];

            $course = Course::query()->create([
                'user_id' => $data['user_id'],
                'category_id' => $data['category_id'],
                'code' => $data['code'],
                'name' => $data['name'],
                'slug' => $data['slug'],
                'benefits' => json_encode([]),
                'requirements' => json_encode([]),
                'qa' => json_encode([]),
            ]);

            return $this->respondCreated('Tạo khoá học thành công',
                $course->load('category')
            );
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function updateCourseOverView(UpdateContentCourse $request, string $slug)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            $course = Course::query()
                ->where('slug', $slug)
                ->first();

            if (!$course) {
                return $this->respondNotFound('Không tìm thấy khoá học');
            }

            if ($course->user_id !== auth()->id()) {
                return $this->respondForbidden('Không có quyền thực hiện thao tác');
            }

            $thumbnailOld = $course->thumbnail ?? null;
            $introOld = $course->intro ?? null;

            $data['slug'] = !empty($data['name'])
                ? Str::slug($data['name']) . '-' . $course->code
                : $course->slug;

            $data['thumbnail'] = $request->hasFile('thumbnail')
                ? $this->handleFileUpload(
                    $request->file('thumbnail'),
                    $thumbnailOld,
                    self::FOLDER_COURSE_THUMBNAIL,
                    'image')
                : $thumbnailOld;

            $data['intro'] = $request->hasFile('intro')
                ? $this->handleFileUpload(
                    $request->file('intro'),
                    $introOld,
                    self::FOLDER_COURSE_INTRO,
                    'video')
                : $introOld;

            $course->update($data);

            DB::commit();

            return $this->respondOk('Thao tác thành công',
                $course->load('category')
            );
        } catch (\Exception $e) {
            DB::rollBack();

            $this->rollbackFileUploads($data, $thumbnailOld, $introOld);

            $this->logError($e, $request->all());

            if ($e instanceof ValidationException) {
                return $this->respondFailedValidation('Dữ liệu không hợp lệ', $e->errors());
            }

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function updateCourseObjectives(UpdateCourseObjectives $request, string $slug)
    {
        try {
            $data = $request->validated();

            $course = Course::query()
                ->where('slug', $slug)
                ->first();

            if (!$course) {
                return $this->respondNotFound('Không tìm thấy khoá học');
            }

            if ($course->user_id !== Auth::id()) {
                return $this->respondForbidden('Không có quyền thực hiện thao tác');
            }

            $data['requirements'] = array_key_exists('requirements', $data)
                ? (is_string($data['requirements']) ? json_decode($data['requirements'], true) : $data['requirements'])
                : $course->requirements;

            $data['benefits'] = array_key_exists('benefits', $data)
                ? (is_string($data['benefits']) ? json_decode($data['benefits'], true) : $data['benefits'])
                : $course->benefits;

            $data['qa'] = array_key_exists('qa', $data)
                ? (is_string($data['qa']) ? json_decode($data['qa'], true) : $data['qa'])
                : $course->qa;

            $course->update($data);

            return $this->respondOk('Cập nhật mục tiêu khoá học thành công',
                $course->load('category')
            );
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    private function handleFileUpload($newFile, $oldFile, $folder, $type)
    {
        $uploadFile = $type === 'image'
            ? $this->uploadImage($newFile, $folder)
            : $this->uploadVideo($newFile, $folder);

        if (!empty($oldFile) && filter_var($oldFile, FILTER_VALIDATE_URL)) {
            $type === 'image'
                ? $this->deleteImage($oldFile, $folder)
                : $this->deleteVideo($oldFile, $folder);
        }

        return $uploadFile;
    }

    private function rollbackFileUploads(array $data, $thumbnailOld, $introOld)
    {
        if (!empty($data['thumbnail']) && filter_var($data['thumbnail'], FILTER_VALIDATE_URL)) {
            $this->deleteImage($data['thumbnail'], self::FOLDER_COURSE_THUMBNAIL);
        }

        if (!empty($data['intro']) && filter_var($data['intro'], FILTER_VALIDATE_URL)) {
            $this->deleteVideo($data['intro'], self::FOLDER_COURSE_INTRO);
        }

        $this->deleteFileIfValid($thumbnailOld, self::FOLDER_COURSE_THUMBNAIL, 'image');
        $this->deleteFileIfValid($introOld, self::FOLDER_COURSE_INTRO, 'video');
    }

    private function deleteFileIfValid($file, $folder, $type)
    {
        if (!empty($file) && filter_var($file, FILTER_VALIDATE_URL)) {
            $type === 'image' ? $this->deleteImage($file, $folder) : $this->deleteVideo($file, $folder);
        }
    }

    public function deleteCourse(string $slug)
    {
        try {
            $course = Course::query()
                ->where('slug', $slug)
                ->first();

            if (!$course) {
                return $this->respondNotFound('Không tìm thấy khoá học');
            }

            if ($course->chapters()->count() > 0) {
                return $this->respondError('Khoá học đang chứa chương học, không thể xóa');
            }

            $course->delete();

            return $this->respondOk('Xóa khoá học thành công');
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function getChapters(string $slug)
    {
        try {
            $course = Course::query()
                ->where('slug', $slug)
                ->first();

            if (!$course) {
                return $this->respondNotFound('Không tìm thấy khoá học');
            }

            $chapters = $course->chapters()
                ->select([
                    'id', 'course_id', 'title', 'slug', 'order'
                ])
                ->orderBy('order')
                ->get();

            return $this->respondOk('Danh sách chương học của khoá học: ' . $course->name,
                $chapters
            );
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function validateCourse(string $slug)
    {
        try {
            $course = $this->findCourseBySlug($slug);

            if (!$course) {
                return $this->respondNotFound('Không tìm thấy khoá học');
            }

            $authError = $this->authorizeCourseAccess($course);
            if ($authError) return $authError;

            $completionStatus = $this->getCourseCompletionStatus($course);
            $progress = $this->calculateProgress($completionStatus);

            return $this->respondOk('Kiểm tra hoàn thiện khoá học', [
                'progress' => $progress,
                'completion_status' => $completionStatus,
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function checkCourseComplete(string $slug)
    {
        try {
            $course = $this->findCourseBySlug($slug);

            if (!$course) {
                return $this->respondNotFound('Không tìm thấy khoá học');
            }

            $authError = $this->authorizeCourseAccess($course);
            if ($authError) return $authError;

            return $this->respondOk('Kiểm tra hoàn thiện khoá học', $this->getCourseCompletionStatus($course));
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    private function calculateProgress(array $completionStatus): float
    {
        $totalSteps = count($completionStatus);
        $completedSteps = 0;

        foreach ($completionStatus as $step) {
            if ($step['status']) {
                $completedSteps++;
            }
        }

        return $totalSteps > 0 ? ($completedSteps / $totalSteps) * 100 : 0;
    }

    private function findCourseBySlug(string $slug)
    {
        return Course::query()->where('slug', $slug)->first();
    }

    private function authorizeCourseAccess(Course $course)
    {
        if ($course->user_id !== Auth::id()) {
            return $this->respondForbidden('Không có quyền thực hiện thao tác');
        }
        return null;
    }

    private function getCourseCompletionStatus(Course $course): array
    {
        return [
            'course_overview' => $this->checkCourseOverView($course),
            'course_objectives' => $this->checkCourseObjectives($course),
            'course_curriculum' => $this->checkCurriculum($course),
        ];
    }

    private function checkCourseOverView(Course $course)
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

        return [
            'status' => empty($errors),
            'errors' => $errors
        ];
    }

    private function checkCourseObjectives(Course $course)
    {
        $errors = [];

        $benefits = $this->decodeJson($course->benefits);
        $requirements = $this->decodeJson($course->requirements);

        if (count($benefits) < 4 || count($benefits) > 10) {
            $errors[] = 'Lợi ích khóa học phải có từ 4 đến 10 mục.';
        }
        if (count($requirements) < 4 || count($requirements) > 10) {
            $errors[] = 'Yêu cầu khóa học phải có từ 4 đến 10 mục.';
        }

        return [
            'status' => empty($errors),
            'errors' => $errors
        ];
    }

    private function checkCurriculum(Course $course)
    {
        $errors = [];

        $chapters = $course->chapters()->get();

        if ($chapters->count() === 0) {
            $errors[] = "Khóa học phải có nội dung học tập";
        } else {
            $errors = array_merge($errors, $this->validateChapters($chapters));
        }

        return [
            'status' => empty($errors),
            'errors' => $errors
        ];
    }

    private function validateChapters($chapters)
    {
        $errors = [];

        if ($chapters->count() > 0) {
            if ($chapters->count() < 3) {
                $errors[] = "Khóa học phải có ít nhất 3 chương học. Hiện tại có {$chapters->count()} chương.";
            }
        }

        foreach ($chapters as $index => $chapter) {
            if (empty($chapter->title)) {
                $errors[] = "Chương học ID {$chapter->id} không có tiêu đề.";
            }

            if ($index === 0) {
                continue;
            }

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

    private function validateChapterVideoDuration($chapter)
    {
        $errors = [];

        $totalVideoDuration = Video::query()->whereIn('id', function ($query) use ($chapter) {
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


    private function decodeJson($value)
    {
        return is_string($value) ? json_decode($value, true) : (array)$value;
    }
}
