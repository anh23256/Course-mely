<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Approvable;
use App\Models\Course;
use App\Models\Video;
use App\Traits\FilterTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalCourseController extends Controller
{
    use LoggableTrait, FilterTrait;

    public function index(Request $request)
    {
        $title = 'Kiểm duyệt khoá học';
        $subTitle = 'Danh sách khoá học ';

        $queryApprovals = Approvable::query()
            ->with([
                'approver',
                'course.user',
                'user'
            ])
            ->where('approvable_type', Course::class);

        $approvalCount = Approvable::query()
            ->selectRaw('
                count(id) as total_approval,
                sum(status = "pending") as pending_approval,
                sum(status = "approved") as approved_approval,
                sum(status = "rejected") as rejected_approval
            ')->where('approvable_type', Course::class)->first();

        if ($request->hasAny([
            'amount_min',
            'amount_max',
            'request_start_date',
            'request_end_date',
            'approval_start_date',
            'approval_end_date',
            'course_name_approved',
            'user_name_approved',
            'approver_name_approved',
            'status'
        ])) {
            $queryApprovals = $this->filter($request, $queryApprovals);
        }

        if ($request->has('search_full'))
            $queryApprovals = $this->search($request, $queryApprovals);

        $approvals = $queryApprovals->paginate(10);

        if ($request->ajax()) {
            $html = view('approval.course.table', compact('approvals'))->render();
            return response()->json(['html' => $html]);
        }

        return view('approval.course.index', compact([
            'title',
            'subTitle',
            'approvals',
            'approvalCount'
        ]));
    }

    public function show(string $id)
    {
        try {
            $approval = Approvable::query()
                ->where('approvable_type', Course::class)
                ->with([
                    'approver',
                    'approvable.user',
                    'approvable.chapters.lessons.lessonable'
                ])
                ->latest('created_at')
                ->findOrFail($id);

            $title = 'Kiểm duyệt khoá học';
            $subTitle = 'Thông tin khoá học: ' . $approval->course->name;

            $totalDuration = $approval->approvable->chapters->flatMap(function ($chapter) {
                return $chapter->lessons;
            })->filter(function ($lesson) {
                return $lesson->lessonable_type === Video::class;
            })->sum(function ($lesson) {
                return $lesson->lessonable->duration ?? 0;
            });

            return view('approval.course.show', compact([
                'title',
                'subTitle',
                'approval',
                'totalDuration'
            ]));
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->route('admin.approvals.courses.index')->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }


    public function approve(Request $request, string $id)
    {
        return $this->updateApprovalStatus($id, 'approved', 'Khoá học đã được duyệt');
    }

    public function reject(Request $request, string $id)
    {
        $note = $request->note ?? 'Khoá học đã bị từ chối';
        return $this->updateApprovalStatus($id, 'rejected', $note);
    }

    private function updateApprovalStatus(string $id, string $status, string $note)
    {
        try {
            DB::beginTransaction();

            $approval = Approvable::query()->findOrFail($id);

            $approval->status = $status;
            $approval->note = $note;
            $approval->{$status . '_at'} = now();
            $approval->approver_id = auth()->id();
            $approval->save();

            if ($status === 'approved') {
                $approval->course->update([
                    'status' => $status,
                    'accepted' => now()
                ]);
            } else {
                $approval->course->update([
                    'status' => $status,
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', "Khoá học đã được $status");
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e);

            return redirect()->route('admin.approvals.courses.index')
                ->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }


    private function filter(Request $request, $query)
    {
        $filters = [
            'status' => ['queryWhere' => '='],
            'course_name_approved' => null,
            'user_name_approved' => null,
            'approver_name_approved' => null,
            'course_price_approved' => ['attribute' => ['amount_min' => '>=', 'amount_max' => '<=']],
            'request_date' => ['attribute' => ['request_start_date' => '>=', 'request_end_date' => '<=']],
            'approval_date' => ['filed' => ['approved_at', 'rejected_at'], 'attribute' => ['approval_start_date' => '>=', 'approval_end_date' => '<=']],
        ];

        $query = $this->filterTrait($filters, $request, $query);

        return $query;
    }

    private function search($request, $query)
    {
        if (!empty($request->search_full)) {
            $searchTerm = $request->search_full;
            $query->where(function ($query) use ($searchTerm) {
                $query->where('note', 'LIKE', "%$searchTerm%")
                    ->orWhereHas('approver', function ($query) use ($searchTerm) {
                        $query->where('name', 'LIKE', "%$searchTerm%");
                    })
                    ->orWhereHas('user', function ($query) use ($searchTerm) {
                        $query->where('name', 'LIKE', "%$searchTerm%");
                    })
                    ->orWhereHas('course', function ($query) use ($searchTerm) {
                        $query->where('name', 'LIKE', "%$searchTerm%");
                    });
            });
        }

        return $query;
    }
}
