<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Courses\StoreCourseRequest;
use App\Http\Requests\API\Courses\UpdateContentCourse;
use App\Http\Requests\API\Courses\UpdateCourseObjectives;
use App\Models\Course;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Video;
use App\Services\CourseValidatorService;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
use App\Traits\UploadToMuxTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

        if (empty($course->name)) {
            $errors[] = 'Khóa học phải có tên.';
        }
        if (empty($course->description)) {
            $errors[] = 'Khóa học phải có mô tả.';
        }
        if (empty($course->thumbnail)) {
            $errors[] = 'Khóa học phải có hình ảnh.';
        }
        if (empty($course->level)) {
            $errors[] = 'Khóa học phải có cấp độ.';
        }
        if (empty($course->category_id)) {
            $errors[] = 'Khóa học phải có danh mục.';
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
        $qa = $this->decodeJson($course->qa);

        if (count($benefits) < 4 || count($benefits) > 10) {
            $errors[] = 'Lợi ích khóa học phải có từ 4 đến 10 mục.';
        }
        if (count($requirements) < 4 || count($requirements) > 10) {
            $errors[] = 'Yêu cầu khóa học phải có từ 4 đến 10 mục.';
        }
        if (count($qa) < 1 || count($qa) > 5) {
            $errors[] = 'Câu hỏi và trả lời phải có từ 1 đến 5 mục.';
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

        if ($chapters->count() < 3) {
            $errors[] = 'Khóa học phải có ít nhất 3 chương học.';
        }

        foreach ($chapters as $chapter) {
            if (empty($chapter->title)) {
                $errors[] = "Chương học ID {$chapter->id} không có tiêu đề.";
            }

            if ($chapter->lessons()->count() < 3) {
                $errors[] = "Chương học '{$chapter->title}' phải có ít nhất 3 bài học. Hiện tại có {$chapter->lessons()->count()} bài.";
            }

            $lessons = $chapter->lessons()->get();

            foreach ($lessons as $lesson) {
                if (empty($lesson->title)) {
                    $errors[] = "Bài học giảng {$lesson->title} trong chương
                     '{$chapter->title}' thiếu tiêu đề hoặc nội dung";
                }
                if ($lesson->lessonable_type === Video::class) {
                    $video = Video::query()->find($lesson->lessonable_id);

                    if ($video && $video->duration < 900) {
                        $errors[] = "Bài giảng '{$lesson->title}' trong chương
                             '{$chapter->title}' có video dưới 15 phút.";
                    }

                    if ($lesson->lessonable_type === Quiz::class) {
                        $quiz = Quiz::query()->find($lesson->lessonable_id);
                        if ($quiz) {
                            $questions = Question::query()->where('quiz_id', $quiz->id)->get();
                            if ($questions->count() < 1 || $questions->count() > 5) {
                                $errors[] = "Bài kiểm tra '{$lesson->title}' (ID {$lesson->id}) trong chương
                                     '{$chapter->title}' phải có từ 1 đến 5 câu hỏi. Hiện tại có {$questions->count()} câu.";
                            }
                        }
                    }
                }
            }
        }

        return [
            'status' => empty($errors),
            'errors' => $errors
        ];
    }

    private function decodeJson($value)
    {
        return is_string($value) ? json_decode($value, true) : (array)$value;
    }
}
