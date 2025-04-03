<?php

namespace App\Http\Controllers\Admin;

use App\Exports\InvoicesExport;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Traits\FilterTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class InvoiceController extends Controller
{
    use LoggableTrait, FilterTrait;

    public function index(Request $request)
    {
        try {
            $title = 'Khóa học đã bán';
            $subTitle = 'Khóa học đã bán';

            $queryInvoice = Invoice::query()
                ->with([
                    'course',
                    'user.profile'
                ])
                ->where('invoice_type', 'course')
                ->latest('id')
                ->where('status', 'Đã thanh toán');

            $queryInvoice = $this->filterSearch($request, $queryInvoice);

            $invoices = $queryInvoice->paginate(10);

            if ($request->ajax()) {
                $html = view('invoices.table', compact('invoices'))->render();
                return response()->json(['html' => $html]);
            }

            return view('invoices.index', compact(['title', 'subTitle', 'invoices']));
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function show(string $code)
    {
        try {
            $title = 'Hóa đơn thanh toán';
            $subTitle = 'Hóa đơn thanh toán';

            $invoice = Invoice::query()
                ->with([
                    'course',
                    'user'
                ])
                ->latest('id')
                ->where(['status' => 'Đã thanh toán', 'code' => $code])->firstOrFail();

            return view('invoices.show', compact(['title', 'subTitle', 'invoice']));
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Không tìm thấy thông tin hóa đơn');
        }
    }

    private function filterSearch(Request $request, $queryInvoice)
    {
        try {
            if ($request->hasAny(['course_user_name', 'phone_user', 'course_user_email', 'course_user_name', 'user_name_invoice', 'course_name_invoice', 'amount_min', 'amount_max', 'startDate', 'endDate', 'code', 'user_email_invoice']))
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
            'user_email_invoice' => null,
            'course_name_invoice' => null,
        ];

        $query = $this->filterTrait($filters, $request, $query);

        $course_user_email = $request->input('course_user_email', '');
        $course_user_name = $request->input('course_user_name', '');
        $phone_user = $request->input('phone_user', '');

        if (!empty($course_user_name) || !empty($course_user_email)) {
            $query->whereHas('course', function ($q) use ($course_user_name, $course_user_email) {
                $q->whereHas('user', function ($q1) use ($course_user_name, $course_user_email) {
                    if (!empty($course_user_name)) {
                        $q1->where('name', 'LIKE', "%$course_user_name%");
                    }

                    if (!empty($course_user_email)) {
                        $q1->where('email', 'LIKE', "%$course_user_email%");
                    }
                });
            });
        }

        if (!empty($phone_user)) {
            $query->whereHas('user.profile', function ($query) use ($phone_user) {
                $query->where('phone', 'LIKE', "%$phone_user%");
            });
        }

        return $query;
    }

    public function export()
    {
        try {

            return Excel::download(new InvoicesExport, 'Invoices.xlsx');
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    private function search($searchTerm, $query)
    {
        if (!empty($searchTerm)) {
            $query->where(function ($query) use ($searchTerm) {
                $query->whereHas('user', function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%$searchTerm%")
                        ->orWhere('email', 'LIKE', "%$searchTerm%")
                        ->orWhereHas('profile', function ($query) use ($searchTerm) {
                            $query->where('phone', 'LIKE', "%$searchTerm%");
                        });
                })
                    ->orWhereHas('course', function ($q1) use ($searchTerm) {
                        $q1->where('name', 'LIKE', "%$searchTerm%")
                            ->orWhereHas('user', function ($q2) use ($searchTerm) {
                                $q2->where('name', 'LIKE', "%$searchTerm%")
                                    ->orWhere('email', 'LIKE', "%$searchTerm%");
                            });
                    });
            });
        }

        return $query;
    }
}
