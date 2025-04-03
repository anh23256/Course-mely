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
                'membershipPlan.instructor.profile',
            ])
            ->orderBy('id', 'desc')
            ->where('approvable_type', MembershipPlan::class)
            ->orderBy('id', 'desc');


        // Thống kê số lượng kiểm duyệt theo trạng thái
        $approvalCount = Approvable::query()
            ->selectRaw('
            count(id) as total_approval,
            sum(status = "pending") as pending_approval,
            sum(status = "approved") as approved_approval,
            sum(status = "rejected") as rejected_approval
        ')->where('approvable_type', MembershipPlan::class)->first();

        if ($request->has('search_full')) {
            $queryApprovals = $this->search($request, $queryApprovals);
        }

        if ($request->hasAny([
            'request_start_date',
            'request_end_date',
            'approval_start_date',
            'approval_end_date',
            'status',
            'approver_name_approved',
            'membershipPlan_name_approved',
            'amount_min',
            'amount_max',
            'instructor_email',
            'name_instructor',
            'phone_instructor',
            'membershipPlan_duration_months_approval',
        ])) {
            $queryApprovals = $this->filter($request, $queryApprovals);
        }

        $approvals = $queryApprovals->paginate(10);

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
            'request_date' => ['attribute' => ['request_start_date' => '>=', 'request_end_date' => '<=']],
            'approval_date' => ['filed' => ['approved_at', 'rejected_at'], 'attribute' => ['approval_start_date' => '>=', 'approval_end_date' => '<=']],
            'status' => ['queryWhere' => '='],
            'approver_name_approved' => null,
            'membershipPlan_name_approved' => null,
            'membershipPlan_price_approval' => ['attribute' => ['amount_min' => '>=', 'amount_max' => '<=']],
        ];

        $query = $this->filterTrait($filters, $request, $query);

        $email_instructor = $request->input('instructor_email', '');
        $name_instructor = $request->input('name_instructor', '');
        $phone_instructor = $request->input('phone_instructor', '');
        $duration_month = $request->input('membershipPlan_duration_months_approval', '');

        if (!empty($name_instructor) || !empty($email_instructor) || !empty($phone_instructor)) {
            $query->whereHas('membershipPlan.instructor', function ($query) use ($email_instructor, $name_instructor) {

                if (!empty($name_instructor)) {
                    $query->where('name', 'LIKE', "%{$name_instructor}%");
                }

                if (!empty($email_instructor)) {
                    $query->where('email', 'LIKE', "%{$email_instructor}%");
                }

                if (!empty($phone_instructor)) {
                    $query->whereHas('profile', function ($query) use ($phone_instructor) {
                        $query->where('phone', 'LIKE', "%$phone_instructor%");
                    });
                }
            });
        }

        if (!empty($duration_month)) {
            $query->whereHas('membershipPlan', function ($query) use ($duration_month) {
                $query->where('duration_months', $duration_month);
            });
        }

        return $query;
    }
    private function search($request, $query)
    {
        if (!empty($request->search_full)) {
            $searchTerm = $request->search_full;
            $query->where(function ($query) use ($searchTerm) {
                $query->whereHas('approver', function ($query) use ($searchTerm) {
                    $query->where('name', 'LIKE', "%$searchTerm%");
                })
                    ->orWhereHas('membershipPlan', function ($query) use ($searchTerm) {
                        $query->where('name', 'LIKE', "%$searchTerm%");
                    })
                    ->orWhereHas('membershipPlan.instructor', function ($query) use ($searchTerm) {
                        $query->where('name', 'LIKE', "%$searchTerm%")
                            ->orWhere('email', 'LIKE', "%$searchTerm%")
                            ->orWhereHas('profile', function ($query) use ($searchTerm) {
                                $query->where('phone', 'LIKE', "%$searchTerm%");
                            });
                    });
            });
        }

        return $query;
    }
}
