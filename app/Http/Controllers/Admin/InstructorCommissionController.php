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
        

        $title = 'Quản lý danh mục';
        $subTitle = 'Danh sách danh mục';

        $queryInstructorCommission = InstructorCommission::query()->with(['instructor', 'course']);

        if ($request->hasAny(['id', 'status', 'startDate', 'endDate']))
            $queryInstructorCommission = $this->filter($request, $queryInstructorCommission);

        if ($request->has('search_full'))
            $queryInstructorCommission = $this->search($request->search_full, $queryInstructorCommission);

        $instructorCommissions = $queryInstructorCommission->paginate(10);

        if ($request->ajax()) {
            $html = view('instructor-commissions.table', compact(['instructorCommissions']))->render();
            return response()->json(['html' => $html]);
        }
        return view('instructor-commissions.index', compact('instructorCommissions', 'title', 'subTitle'));
    }
}
