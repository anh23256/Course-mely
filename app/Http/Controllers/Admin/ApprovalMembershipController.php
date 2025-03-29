<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Approvable;
use App\Models\Invoice;
use App\Models\MembershipPlan;
use App\Models\MembershipSubscription;
use App\Traits\FilterTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;

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

    private function filter(Request $request, $query)
    {
        $filters = [

            'status' => ['queryWhere' => '='],
            
        ];

        $query = $this->filterTrait($filters, $request, $query);

        return $query;
    }
}
