<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Lessons\StoreQuestionMultipleRequest;
use App\Http\Requests\API\Lessons\StoreQuestionRequest;
use App\Http\Requests\API\Lessons\StoreQuizLessonRequest;
use App\Http\Requests\API\Lessons\UpdateQuestionRequest;
use App\Imports\QuizImport;
use App\Models\Chapter;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Quiz;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class QuizController extends Controller
{
    use LoggableTrait, ApiResponseTrait, UploadToCloudinaryTrait;

    const FOLDER_QUIZ = 'quiz';

    public function storeLessonQuiz(StoreQuizLessonRequest $request, string $chapterId)
    {
        try {
            DB::beginTransaction();

            $data = $request->all();

            $data['slug'] = !empty($data['title'])
                ? Str::slug($data['title']) . '-' . Str::uuid()
                : Str::uuid();

            $chapter = Chapter::query()->where('id', $chapterId)->first();

            if (!$chapter) {
                return $this->respondNotFound('Không tìm thấy chương học');
            }

            if ($chapter->course->user_id !== auth()->id()) {
                return $this->respondForbidden('Bạn không có quyền thực hiện thao tác này');
            }

            $data['order'] = $chapter->lessons->max('order') + 1;

            $quiz = Quiz::query()->create([
                'title' => $data['title'],
            ]);

            $lesson = Lesson::query()->create([
                'chapter_id' => $chapter->id,
                'title' => $data['title'],
                'slug' => $data['slug'],
                'type' => 'quiz',
                'lessonable_type' => Quiz::class,
                'lessonable_id' => $quiz->id,
                'order' => $data['order'],
                'content' => $data['content'] ?? null,
                'is_free_preview' => $data['is_free_preview'] ?? false,
            ]);

            DB::commit();

            return $this->respondCreated('Tạo bài ôn tập thành công', $lesson->load('lessonable'));
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e);
            return $this->respondServerError('Không thể tạo câu hỏi, vui lòng thử lại');
        }
    }

    public function storeQuestionMultiple(StoreQuestionMultipleRequest $request, string $quizId)
    {
        try {
            DB::beginTransaction();

            $data = $request->all();

            $quiz = Quiz::query()->find($quizId);

            if (!$quiz) {
                return $this->respondNotFound('Không tìm thấy bài học');
            }

            if (isset($data['questions']) && is_array($data['questions'])) {
                foreach ($data['questions'] as $question) {
                    $questionModel = Question::updateOrCreate(
                        [
                            'quiz_id' => $quiz->id,
                            'question' => $question['question'],
                        ],
                        [
                            'image' => $question['image'] ?? null,
                            'answer_type' => $question['answer_type'],
                            'description' => $question['description'] ?? null,
                        ]
                    );

                    $questionModel->answers()->delete();

                    if (isset($question['options']) && is_array($question['options'])) {
                        foreach ($question['options'] as $option) {
                            $questionModel->answers()->create([
                                'answer' => $option['answer'],
                                'is_correct' => $option['is_correct'] ?? false,
                            ]);
                        }
                    }
                }
            }
            DB::commit();

            return $this->respondCreated('Tạo bài trắc nghiệm thành công', $quiz);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e);

            return $this->respondServerError('Không thể tạo câu hỏi, vui lòng thử lại');
        }
    }

    public function storeQuestionSingle(StoreQuestionRequest $request, string $quizId)
    {
        try {
            $data = $request->validated();

            $quiz = Quiz::query()->find($quizId);

            if (!$quiz) {
                return $this->respondNotFound('Không tìm thấy bài học');
            }

            if ($request->hasFile('image')) {
                $data['image'] = $this->uploadImage($data['image'], self::FOLDER_QUIZ);
            }

            $question = Question::query()->updateOrCreate(
                [
                    'quiz_id' => $quiz->id,
                    'question' => $data['question'],
                ],
                [
                    'image' => $data['image'] ?? null,
                    'answer_type' => $data['answer_type'],
                    'description' => $data['description'] ?? null,
                ]
            );

            if (isset($data['options']) && is_array($data['options'])) {
                foreach ($data['options'] as $option) {
                    $question->answers()->create([
                        'answer' => $option['answer'],
                        'is_correct' => $option['is_correct'] ?? false,
                    ]);
                }
            }

            return $this->respondCreated('Tạo bài trắc nghiệm thành công', $quiz);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Không thể tạo câu hỏi, vui lòng thử lại');
        }
    }

    public function showQuiz(string $quizId)
    {
        try {
            $quiz = Quiz::query()->with('questions.answers')->find($quizId);

            if (!$quiz) {
                return $this->respondNotFound('Không tìm thấy bài trắc nghiệm');
            }

            return $this->respondSuccess('Lấy thông tin bài trắc nghiệm thành công', $quiz);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function showQuestion(string $questionId)
    {
        try {
            $question = Question::query()->with('answers')->find($questionId);

            if (!$question) {
                return $this->respondNotFound('Không tìm thấy câu hỏi');
            }

            return $this->respondSuccess('Thông tin câu hỏi: ' . $questionId, $question);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function updateQuestion(UpdateQuestionRequest $request, $questionId)
    {
        try {
            $data = $request->all();

            $question = Question::query()->find($questionId);

            if (!$question) {
                return $this->respondNotFound('Không tìm thấy câu hỏi');
            }

            $question->update([
                'question' => $data['question'] ?? null,
                'image' => $data['image'] ?? null,
                'answer_type' => $data['answer_type'] ?? null,
                'description' => $data['description'] ?? null,
            ]);

            $question->answers()->delete();

            if (isset($data['options']) && is_array($data['options'])) {
                foreach ($data['options'] as $option) {
                    $question->answers()->create([
                        'answer' => $option['answer'] ?? null,
                        'is_correct' => $option['is_correct'] ?? false,
                    ]);
                }
            }

            return $this->respondOk('Cập nhật câu hỏi thành công', $question);
        } catch (\Exception $e) {

            $this->logError($e, $request->all());
            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function deleteQuestion($questionId)
    {
        try {
            $question = Question::query()->find($questionId);

            if (!$question) {
                return $this->respondNotFound('Không tìm thấy câu hỏi');
            }

            $question->answers()->delete();
            $question->delete();

            return $this->respondSuccess('Xóa câu hỏi thành công');
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function importQuiz(Request $request, string $quizId)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv'
            ]);

            Excel::import(new QuizImport($quizId), $request->file('file'));

            return $this->respondOk('Import câu hỏi thành công');
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function downloadQuizForm()
    {
        try {
            $file = public_path('storage/csv/quiz_import_template.xlsx');

            if (!file_exists($file)) {
                return $this->respondNotFound('Không tìm thấy file mẫu');
            }

            return response()->download($file, 'QuizFormCourseMeLy.csv');
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

}
