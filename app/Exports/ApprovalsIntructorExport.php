<?php

namespace App\Exports;

use App\Models\Approvable;
use App\Models\Approval;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ApprovalsIntructorExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Approvable::with(['user', 'approver'])
            ->whereHas('user.roles', function ($query) {
                $query->where('name', 'instructor');
            })
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tên giảng viên',
            'Email giảng viên',
            'Người kiểm duyệt',
            'Trạng thái',
            'Ngày gửi yêu cầu',
            'Ngày kiểm duyệt',
        ];
    }

    public function map($approval): array
    {
        return [
            $approval->id,
            optional($approval->user)->name,          
            optional($approval->user)->email,          
            optional($approval->approver)->name,       
            $approval->status,                         
            $approval->request_date,                   
            ($approval->status === 'approved')
                ? $approval->updated_at
                : null
        ];
    }
}
