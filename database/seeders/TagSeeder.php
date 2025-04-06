<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $techTags = [
            'Trí tuệ nhân tạo',
            'Học máy',
            'Blockchain',
            'Lập trình web',
            'Điện toán đám mây',
            'An ninh mạng',
            'Khoa học dữ liệu',
            'Internet vạn vật',
            'Phát triển ứng dụng di động',
            'DevOps',
            'Dữ liệu lớn',
            'Thực tế ảo',
            'Thực tế tăng cường',
            'Công nghệ 5G',
            'Marketing số',
            'Thiết kế UI/UX',
            'Kỹ thuật phần mềm',
            'Học sâu',
            'Xử lý ngôn ngữ tự nhiên',
            'Quản trị cơ sở dữ liệu',
            'Bảo mật mạng',
            'Điện toán lượng tử',
            'Điện toán biên',
            'Phát triển API',
            'Kiến trúc vi dịch vụ',
            'Lập trình Full Stack',
            'Ứng dụng di động',
            'Phát triển Android',
            'Phát triển iOS',
            'Phát triển game',
            'Robot học',
            'Tự động hóa',
            'Lập trình Frontend',
            'Lập trình Backend',
            'Công nghệ thông tin',
            'Chuyển đổi số',
            'Phân tích dữ liệu',
            'Thanh toán điện tử',
            'Tiền điện tử',
            'Công nghệ sinh trắc học'
        ];

        foreach ($techTags as $tag) {
            Tag::create([
                'name' => $tag,
                'slug' => Str::slug($tag) . '-' . Str::random(8),
            ]);
        }
    }
}