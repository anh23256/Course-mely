<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Traits\FilterTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;

class InvoiceMembershipController extends Controller
{
    use LoggableTrait, FilterTrait;

    public function index(Request $request)
    {
        try {
            $title = 'Gói thành viên đã bán';
            $subTitle = 'Gói thành viên đã bán';

            $queryInvoice = Invoice::query()
                ->with(relations: [
                    'membershipPlan',
                    'user'
                ])
                ->where('invoice_type', 'membership')
                ->latest('id')
                ->where('status', 'Đã thanh toán');

            $queryInvoice = $this->filterSearch($request, $queryInvoice);

            $invoices = $queryInvoice->paginate(10);

            return view('invoices.memberships.index', compact(['title', 'subTitle', 'invoices']));
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function show(string $code)
    {
        try {
            $title = 'Hóa đơn thanh toán';
            $subTitle = 'Thông tin đăng ký';

            $invoice = Invoice::query()
                ->with([
                    'membershipPlan.membershipSubscription',
                    'user'
                ])
                ->latest('id')
                ->where(['status' => 'Đã thanh toán', 'code' => $code])
                ->first();

            return view('invoices.memberships.show', compact(['title', 'subTitle', 'invoice']));
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Không tìm thấy thông tin hóa đơn');
        }
    }

    private function filterSearch(Request $request, $queryInvoice)
    {
        try {
            if ($request->hasAny(['course_user_name', 'user_name_invoice', 'course_name_invoice', 'amount_min', 'amount_max', 'startDate', 'endDate', 'code']))
                $queryInvoice = $this->filter($request, $queryInvoice);

            if ($request->has('search_full'))
                $queryInvoice = $this->search($request->search_full, $queryInvoice);

            return $queryInvoice;
        } catch (\Exception $e) {
            $this->logError($e, $request->all());
        }
    }

    private function filter($request, $query)
    {
        $filters = [
            'code' => ['queryWhere' => 'LIKE'],
            'created_at' => ['attribute' => ['startDate' => '>=', 'endDate' => '<=']],
            'final_total' => ['attribute' => ['amount_min' => '>=', 'amount_max' => '<=']],
            'user_name_invoice' => null,
            'course_name_invoice' => null,
        ];

        $query = $this->filterTrait($filters, $request, $query);

        if ($request->has('course_user_name') && !empty($request->course_user_name)) {
            $query->whereHas('course', function ($q) use ($request) {
                $q->whereHas('user', function ($q1) use ($request) {
                    $q1->where('name', 'LIKE', "%$request->course_user_name%");
                });
            });
        }

        return $query;
    }

    private function search($searchTerm, $query)
    {
        if (!empty($searchTerm)) {
            $query->where(function ($query) use ($searchTerm) {
                $query->whereHas('user', function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%$searchTerm%");
                })
                    ->orWhereHas('course', function ($q1) use ($searchTerm) {
                        $q1->where('name', 'LIKE', "%$searchTerm%")
                            ->orWhereHas('user', function ($q2) use ($searchTerm) {
                                $q2->where('name', 'LIKE', "%$searchTerm%");
                            });
                    });
            });
        }

        return $query;
    }
}
