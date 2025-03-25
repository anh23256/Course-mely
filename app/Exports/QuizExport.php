<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class QuizExport implements FromCollection, WithHeadings
{
    protected $quiz;
    protected $maxAnswers = 0;

    public function __construct($quiz)
    {
        $this->quiz = $quiz;

        // Xác định số lượng đáp án lớn nhất trong tất cả câu hỏi
        foreach ($this->quiz['questions'] as $question) {
            $this->maxAnswers = max($this->maxAnswers, count($question['answers']));
        }
    }

    public function collection()
    {
        $exportData = [];

        foreach ($this->quiz['questions'] as $index => $question) {
            $correctIndexes = [];
            $answers = [];

            foreach ($question['answers'] as $answerIndex => $answer) {
                $answers[] = $answer['answer'];
                if ($answer['is_correct'] == 1) {
                    $correctIndexes[] = $answerIndex + 1;
                }
            }

            // Nếu số lượng đáp án ít hơn max, thêm giá trị rỗng để đảm bảo cột không bị lệch
            while (count($answers) < $this->maxAnswers) {
                $answers[] = null;
            }

            $exportData[] = array_merge([
                'stt' => $index + 1,
                'question' => $question['question'],
                'image' => $question['image'] ? 'http://127.0.0.1:8000/storage/' . $question['image'] : null,
                'answer_type' => $question['answer_type'] ?? 'single_choice',
                'description' => $question['description'],
            ], $answers, [
                'correct_answer' => implode(', ', $correctIndexes),
            ]);
        }

        return collect($exportData);
    }

    public function headings(): array
    {
        $answerHeaders = [];

        for ($i = 1; $i <= $this->maxAnswers; $i++) {
            $answerHeaders[] = "answer$i";
        }

        return array_merge([
            'STT',
            'question',
            'image',
            'answer_type',
            'description',
        ], $answerHeaders, [
            'correct_answer'
        ]);
    }
}
