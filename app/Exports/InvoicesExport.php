<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class InvoicesExport implements FromCollection, WithHeadings, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Invoice::all();
    }

    public function headings(): array
    {
        return [

            'Mã khóa học',
            'ID người mua',
            'ID khoá học',
            'Mã giảm giám',
            'Phiếu giảm giá',
            'Tổng',
            'Tổng thanh toán',
            'Trạng thái',
            'Ngày mua',
            'Ngày cập nhật',
            
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Sheet name
                $event->sheet->getDelegate()->setTitle("Danh sách khóa học đã bán");

                // All headers

                $event->sheet->getDelegate()->getStyle("A1:M2")->getActiveSheet()->getRowDimension('1')->setRowHeight('30');
            },
        ];
    }
}
