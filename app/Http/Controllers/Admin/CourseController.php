<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CoursesExport;
use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\LessonProgress;
use App\Models\Rating;
use App\Models\Transaction;
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

        $queryCourses = Course::query()->with(['user', 'category']);

        if ($request->has('query') && $request->input('query')) {
            $search = $request->input(key: 'query');
            $queryCourses->where('name', 'like', "%$search%")
                ->orWhere('code', 'like', "%$search%");
        }

        $queryCourses = $this->filter($request, $queryCourses);

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

        $courseUsers = CourseUser::query()
            ->where('course_id', $id)
            ->with(['user', 'rating'])
            ->paginate(10);

        // dd($course); 
        $userCounts = CourseUser::query()
            ->where('course_id', $id)
            ->selectRaw('
                COUNT(*) as total_students,
                COUNT(CASE WHEN completed_at IS NOT NULL THEN 1 END) as completed_students,
                COUNT(CASE WHEN completed_at IS NULL AND progress_percent > 0 THEN 1 END) as in_progress_students,
                COUNT(CASE WHEN progress_percent = 0 THEN 1 END) as not_started_students
            ')
            ->first();

        $recentLessons = LessonProgress::query()
            ->selectRaw('lesson_progress.user_id, lessons.title as lesson_title, MAX(lesson_progress.updated_at) as last_updated')
            ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
            ->join('chapters', 'lessons.chapter_id', '=', 'chapters.id')
            ->whereIn('lesson_progress.user_id', collect($courseUsers->items())->pluck('user_id')->toArray()) // Chỉ lấy user trong trang hiện tại
            ->where('chapters.course_id', $id)
            ->groupBy('lesson_progress.user_id', 'lessons.title')
            ->orderBy('last_updated', 'desc')
            ->get()
            ->keyBy('user_id');


        $ratingsData = Rating::where('course_id', $id)
            ->whereNotNull('rate')
            ->selectRaw('rate, COUNT(*) as total')
            ->groupBy('rate')
            ->orderByDesc('rate')
            ->get();

        $chapterProgressStats = Chapter::query()
            ->where('course_id', $id)
            ->leftJoin('lessons', 'chapters.id', '=', 'lessons.chapter_id')
            ->leftJoin('lesson_progress', 'lessons.id', '=', 'lesson_progress.lesson_id')
            ->selectRaw('chapters.title as chapter_title, 
                        ROUND(AVG(CASE WHEN lesson_progress.is_completed = 1 THEN 100 ELSE lesson_progress.last_time_video END), 2) as avg_progress')
            ->groupBy('chapters.id', 'chapters.title')
            ->get();

        $monthlyRevenue = Transaction::query()
            ->where('transactionable_type', Course::class) // Chỉ lấy giao dịch liên quan đến khóa học
            ->where('transactionable_id', $id) // Chỉ lấy giao dịch của khóa học hiện tại
            ->where('status', 'completed') // Chỉ tính giao dịch đã hoàn thành
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(amount) as total_revenue')
            ->groupBy('year', 'month')
            ->orderByRaw('year ASC, month ASC')
            ->get();


        // dd($chapterProgressStats->toArray());


        $title = 'Quản lý khoá học';
        $subTitle = 'Thông tin khoá học: ' . $course->name;

        return view('courses.show', compact(
            'title',
            'subTitle',
            'course',
            'courseUsers',
            'userCounts',
            'recentLessons',
            'ratingsData',
            'chapterProgressStats',
            'monthlyRevenue'
        ));
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
            'user_name_course' => null,
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
