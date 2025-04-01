<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Approvable;
use App\Models\Invoice;
use App\Models\MembershipPlan;
use App\Models\MembershipSubscription;
use App\Notifications\MembershipApprovalNotification;
use App\Traits\FilterTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalMembershipController extends Controller
{
    //
    use LoggableTrait, FilterTrait;

    public function index(Request $request)
    {
        $title = 'Kiểm duyệt Membership';
        $subTitle = 'Danh sách Membership cần duyệt';

        // Lấy danh sách các Membership Plans cần kiểm duyệt
        $queryApprovals = Approvable::query()
            ->with([
                'approver',
                'membershipPlan.instructor',
                // Lấy thông tin instructor của gói Membership

            ])
            ->orderBy('id', 'desc')
            ->where('approvable_type', MembershipPlan::class)
            ->when($request->filled('membershipPlan_instructor_name'), function ($query) use ($request) {
                $query->whereHas('membershipPlan.instructor', function ($query) use ($request) {
                    $query->where('name', 'LIKE', "%{$request->membershipPlan_instructor_name}%");
                });
            })
            ->when($request->filled('membershipPlan_instructor_email'), function ($query) use ($request) {
                $query->whereHas('membershipPlan.instructor', function ($query) use ($request) {
                    $query->where('email', 'LIKE', "%{$request->membershipPlan_instructor_email}%");
                });
            })
            ->when($request->filled('amount_min') && $request->filled('amount_max'), function ($query) use ($request) {
                $query->whereHas('membershipPlan', function ($query) use ($request) {
                    $query->whereBetween('price', [$request->amount_min, $request->amount_max]);
                });
            })
            ->when($request->filled('request_start_date'), function ($query) use ($request) {
                $query->whereDate('request_date', '>=', $request->request_start_date);
            })
            ->when($request->filled('request_end_date'), function ($query) use ($request) {
                $query->whereDate('request_date', '<=', $request->request_end_date);
            })
            ->when($request->filled('approval_start_date'), function ($query) use ($request) {
                $query->whereDate('approved_at', '>=', $request->approval_start_date);
            })
            ->when($request->filled('approval_end_date'), function ($query) use ($request) {
                $query->whereDate('approved_at', '<=', $request->approval_end_date);
            })
            ->orderBy('id', 'desc');

        // dd($queryApprovals);

        // Thống kê số lượng kiểm duyệt theo trạng thái
        $approvalCount = Approvable::query()
            ->selectRaw('
            count(id) as total_approval,
            sum(status = "pending") as pending_approval,
            sum(status = "approved") as approved_approval,
            sum(status = "rejected") as rejected_approval
        ')->where('approvable_type', MembershipPlan::class)->first();

        // Lọc dữ liệu nếu có request
        if ($request->hasAny([
            'status',

        ])) {
            $queryApprovals = $this->filter($request, $queryApprovals);
        }

        // Tìm kiếm nâng cao
        // if ($request->has('search_full'))
        //     $queryApprovals = $this->search($request, $queryApprovals);

        $approvals = $queryApprovals->paginate(10);

        // Xử lý AJAX (nếu có)
        if ($request->ajax()) {
            $html = view('approval.membership.table', compact('approvals'))->render();
            return response()->json(['html' => $html]);
        }

        return view('approval.membership.index', compact([
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
                ->where('approvable_type', MembershipPlan::class)
                ->with([
                    'approver',
                    'membershipPlan.instructor',
                ])
                ->latest('created_at')
                ->find($id);

            $courses = null;

            if ($approval && $approval->membershipPlan) {
                $courses = $approval->membershipPlan->membershipCourseAccess()
                    ->paginate(5);
            }

            $title = 'Kiểm duyệt gói thành viên';
            $subTitle = 'Thông tin kiểm duyệt';

            return view('approval.membership.show', compact([
                'title',
                'subTitle',
                'approval',
                'courses',
            ]));
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function getCourses(string $id, Request $request)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        try {
            $approval = Approvable::query()
                ->where('approvable_type', MembershipPlan::class)
                ->with(['membershipPlan'])
                ->find($id);

            if (!$approval) {
                return response()->json(['error' => 'Approval not found'], 404);
            }

            $courses = $approval->membershipPlan->membershipCourseAccess()
                ->paginate(5);

            return view('approval.membership.courses-table', compact('courses'));
        } catch (\Exception $e) {
            $this->logError($e);
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function approve(Request $request, string $id)
    {
        return $this->updateApprovalStatus($id, 'approved', 'Gói thành viên đã được duyệt');
    }

    public function reject(Request $request, string $id)
    {
        $note = $request->note ?? 'Gói thành viên đã bị từ chối';
        return $this->updateApprovalStatus($id, 'rejected', $note);
    }

    private function updateApprovalStatus(string $id, string $status, string $note)
    {
        try {
            DB::beginTransaction();

            $approval = Approvable::query()->find($id);

            $approval->status = $status;
            $approval->note = $note;
            $approval->{$status . '_at'} = now();
            $approval->approver_id = auth()->id();
            $approval->save();

            $instructor = $approval->membershipPlan->instructor;

            if ($status === 'approved') {
                $approval->membershipPlan->update([
                    'status' => 'active',
                ]);

                $approval->logApprovalAction(
                    $status,
                    auth()->user(),
                    $note
                );
            } else {
                $approval->membershipPlan->update([
                    'status' => 'draft',
                ]);

                $approval->logApprovalAction(
                    $status,
                    auth()->user(),
                    $note
                );
            }

            $instructor->notify(new MembershipApprovalNotification($status, $note,  $approval->membershipPlan));

            DB::commit();

            return redirect()->back()->with('success', "Gói thành viên đã được $status");
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

        ];

        $query = $this->filterTrait($filters, $request, $query);

        return $query;
    }
}
