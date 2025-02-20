<?php

namespace App\Http\Controllers\Admin;

use App\Exports\InvoicesExport;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class InvoiceController extends Controller
{
    use LoggableTrait;

    public function index(Request $request)
    {
        try {
            $title = 'Khóa học đã bán';
            $subTitle = 'Khóa học đã bán';

            $queryInvoice = Invoice::query()
                ->with([
                    'course',
                    'user'
                ])
                ->latest('id')
                ->where('status', 'Đã thanh toán');

            if ($request->hasAny(['user_name_invoice', 'course_code_invoice', 'course_name_invoice', 'amount_min', 'amount_max', 'created_at', 'updated_at']))
                $queryInvoice = $this->filter($request, $queryInvoice);

            if ($request->has('search_full'))
                $queryInvoice = $this->search($request->search_full, $queryInvoice);

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

    private function filter($request, $query)
    {
        $filters = [
            'created_at' => ['queryWhere' => '>='],
            'updated_at' => ['queryWhere' => '<='],
            'final_total' => ['queryWhere' => 'BETWEEN', 'attribute' => ['amount_min', 'amount_max']],
            'user_name_invoice' => null,
            'course_code_invoice' => null,
            'course_name_invoice' => null,
        ];

        foreach ($filters as $filter => $value) {
            $filterValue = $request->input($filter);

            if (!empty($filterValue)) {

                if (is_array($value) && !empty($value['queryWhere'])) {

                    if ($value['queryWhere'] !== 'BETWEEN') {
                        $query->where($filter, $value['queryWhere'], $filterValue);
                    } else {
                        $filterValueBetweenA = $request->input($value['attribute'][0]);
                        $filterValueBetweenB = $request->input($value['attribute'][1]);

                        if (!empty($filterValueBetweenA) && !empty($filterValueBetweenB)) {
                            $query->whereBetween($filter, [$filterValueBetweenA, $filterValueBetweenB]);
                        }
                    }
                } else {
                    if (str_contains($filter, '_')) {
                        $elementFilter = explode('_', $filter);
                        $relation = $elementFilter[0];
                        $field = $elementFilter[1];

                        if (method_exists($query->getModel(), $relation)) {

                            $query->whereHas($relation, function ($query) use ($field, $filterValue) {
                                $query->where($field, 'LIKE', "%$filterValue%");
                            });
                        }
                    }
                }
            }
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
                $query->whereHas('user', function ($query) use ($searchTerm) {
                    $query->where('name', 'LIKE', "%$searchTerm%");
                })
                    ->orWhereHas('course', function ($query) use ($searchTerm) {
                        $query->where('name', 'LIKE', "%$searchTerm%")
                            ->orWhere('code', 'LIKE', "%$searchTerm%");
                    });
            });
        }

        return $query;
    }
}
