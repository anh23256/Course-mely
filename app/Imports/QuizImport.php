<?php

namespace App\Imports;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAnswer;
use App\Traits\LoggableTrait;
use App\Traits\UploadToLocalTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class QuizImport implements ToCollection, WithHeadingRow, WithChunkReading, WithBatchInserts
{
    use LoggableTrait, UploadToLocalTrait;

    const FOLDER_QUIZ = 'quiz';

    protected $quizId;
    protected $importType;
    protected $errors = [];

    public function __construct($quizId, $importType = 'add')
    {
        $this->quizId = $quizId;
        $this->importType = $importType;
    }

    public function collection(Collection $rows)
    {
        try {
            DB::beginTransaction();

            $quiz = Quiz::query()->find($this->quizId);
            if (!$quiz) {
                throw new \Exception('Không tìm thấy bài ôn tập với ID: ' . $this->quizId);
            }

            if ($this->importType === 'overwrite') {
                $quiz->questions()->each(function ($question) {
                    if (!empty($question->image)) {
                        $this->deleteImageFile($question->image);
                    }

                    $question->answers()->delete();
                });
                $quiz->questions()->delete();
            }

            $lastOrder = $quiz->questions()->max('order') ?? 0;

            $rowIndex = 0;

            foreach ($rows as $row) {
                $rowIndex++;

                if (empty($row['question']) || empty($row['answer_type'])) {
                    $this->errors[] = "Dòng {$rowIndex}: Dữ liệu không hợp lệ - thiếu thông tin quan trọng";
                    continue;
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
                    $row['answer5'] ?? null,
                ]));

                if (count($answers) < 2) {
                    $this->errors[] = "Câu hỏi phải có ít nhất 2 đáp án hợp lệ";
                    continue;
                }

                if (empty($answers)) {
                    $this->errors[] = "Dòng {$rowIndex}: Không có câu trả lời hợp lệ";
                    continue;
                }

                if ($row['answer_type'] === 'single_choice') {
                    $correctAnswerIndex = (int)($row['correct_answer'] ?? -1) - 1;

                    if (!isset($answers[$correctAnswerIndex])) {
                        $this->errors[] = "Dòng {$rowIndex}: Câu trả lời đúng không hợp lệ: " . $row['correct_answer'];
                        continue;
                    }
                } elseif ($row['answer_type'] === 'multiple_choice') {
                    if (empty($row['correct_answer'])) {
                        $this->errors[] = "Dòng {$rowIndex}: Không có câu trả lời đúng cho câu hỏi nhiều lựa chọn";
                        continue;
                    }
                } else {
                    $this->errors[] = "Dòng {$rowIndex}: Loại câu hỏi không hợp lệ: " . $row['answer_type'];
                    continue;
                }

                $existingQuestion = null;
                if ($this->importType === 'add') {
                    $existingQuestion = $quiz->questions()->where('question', $row['question'])->first();

                    if ($existingQuestion) {
                        $existingQuestion->update([
                            'image' => $imagePath ?? $existingQuestion->image,
                            'answer_type' => $row['answer_type'],
                            'description' => $row['description'] ?? $existingQuestion->description,
                        ]);

                        $existingQuestion->answers()->delete();

                        if ($row['answer_type'] === 'single_choice') {
                            $correctAnswerIndex = (int)($row['correct_answer'] ?? -1) - 1;

                            foreach ($answers as $index => $answer) {
                                $existingQuestion->answers()->create([
                                    'answer' => $answer,
                                    'is_correct' => $index === $correctAnswerIndex,
                                ]);
                            }
                        } elseif ($row['answer_type'] === 'multiple_choice') {
                            $correctAnswers = array_map('trim', explode(',', $row['correct_answer'] ?? ''));
                            $correctIndexes = array_map(fn($val) => (int)$val - 1, $correctAnswers);

                            foreach ($answers as $index => $answer) {
                                $existingQuestion->answers()->create([
                                    'answer' => $answer,
                                    'is_correct' => in_array($index, $correctIndexes),
                                ]);
                            }
                        }

                        continue;
                    }
                }

                $lastOrder++;

                $newQuestion = $quiz->questions()->create([
                    'question' => $row['question'],
                    'image' => $imagePath,
                    'answer_type' => $row['answer_type'],
                    'description' => $row['description'] ?? null,
                    'order' => $lastOrder,
                ]);

                if ($row['answer_type'] === 'single_choice') {
                    $correctAnswerIndex = (int)($row['correct_answer'] ?? -1) - 1;

                    foreach ($answers as $index => $answer) {
                        $newQuestion->answers()->create([
                            'answer' => $answer,
                            'is_correct' => $index === $correctAnswerIndex,
                        ]);
                    }
                } elseif ($row['answer_type'] === 'multiple_choice') {
                    $correctAnswers = array_map('trim', explode(',', $row['correct_answer'] ?? ''));
                    $correctIndexes = array_map(fn($val) => (int)$val - 1, $correctAnswers);

                    foreach ($answers as $index => $answer) {
                        $newQuestion->answers()->create([
                            'answer' => $answer,
                            'is_correct' => in_array($index, $correctIndexes),
                        ]);
                    }
                }
            }

            DB::commit();

            if (!empty($this->errors)) {
                $this->logError(new \Exception('Có lỗi xảy ra khi import dữ liệu: ' . implode(', ', $this->errors)));
            }

            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            $this->logError($exception);
            throw $exception;
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
            $this->errors[] = "Lỗi xử lý hình ảnh: " . $e->getMessage();
            return null;
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function deleteImageFile($imagePath)
    {
        try {
            if (empty($imagePath)) {
                return;
            }

            $fullPath = storage_path('app/public/' . $imagePath);

            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        } catch (\Exception $e) {
            $this->logError($e);
            $this->errors[] = "Lỗi xóa file hình ảnh: " . $e->getMessage();
        }
    }
}
