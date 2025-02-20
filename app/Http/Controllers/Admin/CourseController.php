<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CoursesExport;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Traits\FilterTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class CourseController extends Controller
{
    use FilterTrait;

    public function index(Request $request)
    {
        $title = 'Quản lý khoá học';
        $subTitle = 'Danh sách khoá học trên hệ thống';

        $queryCourses = Course::query();

        if ($request->has('query') && $request->input('query')) {
            $search = $request->input(key: 'query');
            $queryCourses->where('name', 'like', "%$search%")
                ->orWhere('code', 'like', "%$search%");
        }

        if ($request->hasAny(['code', 'name', 'user_name', 'level', 'price', 'created_at', 'updated_at'])) {
            $queryCourses = $this->filter($request, $queryCourses);
        }

        $courses = $queryCourses->orderBy('created_at', 'desc')->paginate(10);

        if ($request->ajax()) {

            $html = view('courses.table', compact('courses'))->render();
            return response()->json(['html' => $html]);
        }

        return view('courses.index', compact('title', 'subTitle', 'courses'));
    }

    public function show(string $id)
    {
        $course = Course::query()
            ->with('user')
            ->findOrFail($id);

        $title = 'Quản lý khoá học';
        $subTitle = 'Thông tin khoá học: ' . $course->name;

        return view('courses.show', compact('title', 'subTitle', 'course'));
    }

    public function export()
    {
        try {
            
            return Excel::download(new CoursesExport, 'Courses.xlsx');

        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    private function filter($request, $query)
    {
        $filters = [
            'code' => ['queryWhere' => 'LIKE'],
            'name' => ['queryWhere' => 'LIKE'],
            // 'user_name' => null,
            'level' => ['queryWhere' => '='],
            'price' => ['queryWhere' => '='],
            'start_date' => ['queryWhere' => '>='],
            'expire_date' => ['queryWhere' => '<='],
            'status' => ['queryWhere' => '='],
            'created_at' => ['attribute' => 'LIKE'],
        ];

        $query = $this->filterTrait($filters, $request, $query);

        return $query;
    }
}
