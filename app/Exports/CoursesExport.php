<?php

namespace App\Exports;

use App\Models\Course;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class CoursesExport implements FromCollection, WithHeadings, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public function collection()
    {
        return Course::select('code', 'name', 'slug', 'intro', 'price', 'price_sale', 'description', 'level', 'total_student', 'status', 'created_at')->get();
    }

    public function headings(): array
    {
        return [

            'Mã khóa học',
            'Tên khóa học',
            'Slug',
            'Giới thiệu',
            'Giá',
            'Giá bán',
            'Mô tả',
            'Cấp độ',
            'Số học viên',
            'Trạng thái',
            'Ngày tạo'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Sheet name
                $event->sheet->getDelegate()->setTitle("Danh sách khóa học");

                // All headers

                $event->sheet->getDelegate()->getStyle("A1:M2")->getActiveSheet()->getRowDimension('1')->setRowHeight('30');

                // set width column

                $event->sheet->getDelegate()->getStyle("A")->getActiveSheet()->getColumnDimension("A")->setWidth(15);
                $event->sheet->getDelegate()->getStyle("B")->getActiveSheet()->getColumnDimension("B")->setWidth(50);
                $event->sheet->getDelegate()->getStyle("C")->getActiveSheet()->getColumnDimension("C")->setWidth(50);
                $event->sheet->getDelegate()->getStyle("D")->getActiveSheet()->getColumnDimension("D")->setWidth(50);
                $event->sheet->getDelegate()->getStyle("E")->getActiveSheet()->getColumnDimension("E")->setWidth(15);
                $event->sheet->getDelegate()->getStyle("F")->getActiveSheet()->getColumnDimension("F")->setWidth(15);
                $event->sheet->getDelegate()->getStyle("G")->getActiveSheet()->getColumnDimension("G")->setWidth(25);
                $event->sheet->getDelegate()->getStyle("H")->getActiveSheet()->getColumnDimension("H")->setWidth(15);
                $event->sheet->getDelegate()->getStyle("I")->getActiveSheet()->getColumnDimension("I")->setWidth(15);
                $event->sheet->getDelegate()->getStyle("J")->getActiveSheet()->getColumnDimension("J")->setWidth(15);
                $event->sheet->getDelegate()->getStyle("K")->getActiveSheet()->getColumnDimension("K")->setWidth(30);
            },
        ];
    }
}
