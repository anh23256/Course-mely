<?php

namespace Database\Seeders;

use App\Models\Course;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseMetadataSeeder extends Seeder
{

    public function run(): void
    {
        $faker = Factory::create('vi_VN');

        $courses = Course::all();

        foreach ($courses as $course) {
            $requirementsCount = $faker->numberBetween(4, 10);
            $requirements = [];
            for ($i = 0; $i < $requirementsCount; $i++) {
                $requirements[] = $this->generateRequirement($faker);
            }

            $benefitsCount = $faker->numberBetween(4, 10);
            $benefits = [];
            for ($i = 0; $i < $benefitsCount; $i++) {
                $benefits[] = $this->generateBenefit($faker);
            }

            $qaCount = $faker->numberBetween(2, 8);
            $qa = [];
            for ($i = 0; $i < $qaCount; $i++) {
                $qa[] = $this->generateQA($faker);
            }

            $course->update([
                'requirements' => json_encode($requirements),
                'benefits' => json_encode($benefits),
                'qa' => json_encode($qa)
            ]);
        }
    }

    private function generateRequirement($faker)
    {
        $requirementTemplates = [
            'Máy tính cá nhân hoặc laptop',
            'Kết nối internet ổn định',
            'Trình duyệt web mới nhất',
            'Không cần kiến thức chuyên sâu',
            'Động lực học tập và phát triển bản thân',
            'Khả năng đọc hiểu tiếng Việt tốt',
            'Thiết bị di động (tuỳ chọn)',
            'Phần mềm miễn phí (sẽ hướng dẫn cài đặt)',
            'Thời gian dành cho học tập',
            'Tinh thần ham học hỏi'
        ];

        return $requirementTemplates[array_rand($requirementTemplates)];
    }

    private function generateBenefit($faker)
    {
        $benefitTemplates = [
            'Nắm vững kiến thức chuyên môn',
            'Phát triển kỹ năng thực tế',
            'Tăng cơ hội việc làm',
            'Xây dựng portfolio chuyên nghiệp',
            'Kết nối với cộng đồng chuyên nghiệp',
            'Học từ các chuyên gia có kinh nghiệm',
            'Áp dụng ngay vào công việc',
            'Cải thiện năng lực cá nhân',
            'Chứng chỉ hoàn thành khóa học',
            'Hỗ trợ trực tiếp từ giảng viên'
        ];

        return $benefitTemplates[array_rand($benefitTemplates)];
    }

    private function generateQA($faker)
    {
        $questions = [
            'Khóa học phù hợp với đối tượng nào?',
            'Thời lượng khóa học là bao lâu?',
            'Tôi có thể học trực tuyến không?',
            'Chi phí của khóa học như thế nào?',
            'Có hỗ trợ sau khóa học không?',
            'Điều kiện hoàn thành khóa học?',
            'Có giảm giá cho học viên không?',
            'Làm thế nào để đăng ký?',
            'Khóa học có áp dụng thực tế không?',
            'Tôi có thể học lại nhiều lần không?'
        ];

        $answers = [
            'Phù hợp với mọi đối tượng quan tâm',
            'Khoảng 4-6 tuần học',
            'Hoàn toàn có thể học trực tuyến',
            'Chi phí rất hợp lý, đáng đầu tư',
            'Hỗ trợ trực tiếp qua email và diễn đàn',
            'Hoàn thành trên 70% bài tập và bài kiểm tra',
            'Có chương trình ưu đãi cho học viên mới',
            'Đăng ký ngay trên trang khóa học',
            '100% kiến thức áp dụng thực tế',
            'Được học lại không giới hạn số lần'
        ];

        return [
            'question' => $questions[array_rand($questions)],
            'answer' => $answers[array_rand($answers)]
        ];
    }
}
