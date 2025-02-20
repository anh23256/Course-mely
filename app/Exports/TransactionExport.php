<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class TransactionExport implements FromCollection ,WithHeadings, WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Transaction::all();
    }

    public function headings():array
    {
        return [
            'ID',
            'Mã người dùng',
            'Số tiền',
            'Loại giao dịch',
            'Trạng thái',
            'Loại giao dịch liên quan',
            'ID giao dịch liên quan',
            'Ngày tạo',
            'Ngày cập nhật',
        ];
    }


    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Sheet name
                $event->sheet->getDelegate()->setTitle("Danh sách giao dịch");

                // All headers

                $event->sheet->getDelegate()->getStyle("A1:M2")->getActiveSheet()->getRowDimension('1')->setRowHeight('30');
            },
        ];
    }
}
