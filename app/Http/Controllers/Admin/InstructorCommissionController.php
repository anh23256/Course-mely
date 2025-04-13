<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstructorCommission;
use Illuminate\Http\Request;

class InstructorCommissionController extends Controller
{
    //
    public function index(Request $request)
    {


        $title = 'Quản lý hoa hồng';
        $subTitle = 'Danh sách hoa hồng giảng viên';

        $queryInstructorCommission = InstructorCommission::query()->with(['instructor', 'course']);

        if ($request->hasAny(['id', 'status', 'startDate', 'endDate']))
            $queryInstructorCommission = $this->filter($request, $queryInstructorCommission);

        if ($request->has('query') && $request->query('query')) {
            $searchTerm = $request->query('query');
            $queryInstructorCommission->whereHas('instructor', function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%');
            });
        }


        $instructorCommissions = $queryInstructorCommission->paginate(10);

        if ($request->ajax()) {
            $html = view('instructor-commissions.table', compact(['instructorCommissions']))->render();
            return response()->json(['html' => $html]);
        }
        return view('instructor-commissions.index', compact('instructorCommissions', 'title', 'subTitle'));
    }
}
