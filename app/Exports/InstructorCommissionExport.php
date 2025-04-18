<?php

namespace App\Exports;

use App\Models\InstructorCommission;
use Maatwebsite\Excel\Concerns\FromCollection;

class InstructorCommissionExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $instructorCommissions = InstructorCommission::with('instructor')->get();

        return $instructorCommissions->map(function ($commission, $index) {
            return $this->mapInstructorCommission($commission, $index);
        });
    }

    public function headings(): array
    {
        return [
            'STT',
            'Mã giảng viên',
            'Tên giảng viên',
            'Email',
            'Ngày tham gia',
            'Hoa hồng',
        ];
    }

    private function mapInstructorCommission($commission, $index)
    {
        return [
            'STT' => $index + 1,
            'Mã giảng viên' => $commission->instructor->code ?? '',
            'Tên giảng viên' => $commission->instructor->name ?? '',
            'Email' => $commission->instructor->email ?? '',
            'Ngày tham gia' => optional($commission->instructor->created_at)->format('d/m/Y H:i') ?? '',
            'Hoa hồng' => $commission->commission_amount,
        ];
    }
}
