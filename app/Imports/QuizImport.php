<?php

namespace App\Imports;

use App\Models\Quiz;
use App\Traits\LoggableTrait;
use App\Traits\UploadToLocalTrait;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuizImport implements ToModel, WithHeadingRow
{
    use LoggableTrait, UploadToLocalTrait;

    const FOLDER_QUIZ = 'quiz';

    protected $quizId;

    public function __construct($quizId)
    {
        $this->quizId = $quizId;
    }

    public function model(array $row)
    {
        try {
            DB::beginTransaction();

            if (empty($row['question']) || empty($row['answer_type'])) {
                throw new \Exception('Dữ liệu không hợp lệ: thiếu thông tin quan trọng');
            }

            $quiz = Quiz::query()->find($this->quizId);
            if (!$quiz) {
                throw new \Exception('Không tìm thấy bài ôn tập với ID: ' . $this->quizId);
            }

            $imagePath = null;
            if (!empty($row['image'])) {
                $imagePath = $this->handleImageImport($row['image']);
            }

            $answers = array_values(array_filter([
                $row['answer1'] ?? null,
                $row['answer2'] ?? null,
                $row['answer3'] ?? null,
                $row['answer4'] ?? null,
            ]));

            if (empty($answers)) {
                throw new \Exception('Không có câu trả lời hợp lệ');
            }

            $questionModel = $quiz->questions()->updateOrCreate(
                ['question' => $row['question']],
                [
                    'image' => $imagePath ?? null,
                    'answer_type' => $row['answer_type'],
                    'description' => $row['description'] ?? null,
                ]
            );

            $questionModel->answers()->delete();

            if ($row['answer_type'] === 'single_choice') {
                $correctAnswerIndex = (int)($row['correct_answer'] ?? -1) - 1;

                if (!isset($answers[$correctAnswerIndex])) {
                    throw new \Exception('Câu trả lời đúng không hợp lệ: ' . $row['correct_answer']);
                }

                foreach ($answers as $index => $answer) {
                    $questionModel->answers()->create([
                        'answer' => $answer,
                        'is_correct' => $index === $correctAnswerIndex,
                    ]);
                }
            } elseif ($row['answer_type'] === 'multiple_choice') {
                $correctAnswers = array_map('trim', explode(',', $row['correct_answer'] ?? ''));
                $correctIndexes = array_map(fn($val) => (int)$val - 1, $correctAnswers);

                foreach ($answers as $index => $answer) {
                    $questionModel->answers()->create([
                        'answer' => $answer,
                        'is_correct' => in_array($index, $correctIndexes),
                    ]);
                }
            } else {
                throw new \Exception('Loại câu hỏi không hợp lệ: ' . $row['answer_type']);
            }

            DB::commit();
            return $questionModel;
        } catch (\Exception $exception) {
            DB::rollBack();

            $this->logError($exception);

            return null;
        }
    }

    private function handleImageImport($imagePath)
    {
        if (empty($imagePath)) {
            return null;
        }

        try {
            if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                $imageContents = file_get_contents($imagePath);
                $tempFile = tmpfile();
                fwrite($tempFile, $imageContents);
                $tempFilePath = stream_get_meta_data($tempFile)['uri'];

                $uploadedFile = new \Illuminate\Http\UploadedFile(
                    $tempFilePath,
                    basename($imagePath),
                    mime_content_type($tempFilePath),
                    null,
                    true
                );

                $result = $this->uploadToLocal($uploadedFile, self::FOLDER_QUIZ);
                fclose($tempFile);
                return $result;
            }

            $fullPath = storage_path('app/imports/' . trim($imagePath, '/'));
            if (!file_exists($fullPath)) {
                throw new \Exception("File không tồn tại: {$fullPath}");
            }

            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $fullPath,
                basename($imagePath),
                mime_content_type($fullPath),
                null,
                true
            );

            return $this->uploadToLocal($uploadedFile, self::FOLDER_QUIZ);

        } catch (\Exception $e) {
            $this->logError($e);
            return null;
        }
    }

}
