<?php

namespace App\Exports;

use App\Models\Post;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;


class PostsExport implements FromCollection, WithHeadings, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Post::select(
            'id',
            'title',
            'slug',
            'description',
            'content',
            'thumbnail',
            'status',
            'views',
            'published_at',
            'created_at',
            'updated_at'
        )->get();
    }

    public function headings(): array
    {
        return [

            'Mã người dùng',
            'Mã danh mục',
            'Tiêu đề',
            'Slug',
            'Mô tả',
            'Nội dung',
            'Trạng thái',
            'Số lượt xem',
            'Hot',
            'Ngày xuất bản',
            'Ngày tạo',
            'Ngày cập nhật',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Sheet name
                $event->sheet->getDelegate()->setTitle("List khóa học");

                // All headers

                $event->sheet->getDelegate()->getStyle("A1:M2")->getActiveSheet()->getRowDimension('1')->setRowHeight('30');

                
                
            },
        ];
    }
}
