<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TransactionExport;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Traits\FilterTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TransactionController extends Controller
{
    use LoggableTrait, FilterTrait;
    public function index()
    {
        try {
            $title = 'Giao dịch thanh toán';
            $subTitle = 'Giao dịch thanh toán';

            $transactions = Transaction::query()
                ->with('user')
                ->latest('id')->paginate(10);

            $countTransactions = Transaction::query()->selectRaw(
                'count(id) as total_transactions,
                sum(type = "invoice") as invoice_transactions,
                sum(type = "withdrawal") as withdrawal_transactions'
            )->first();

            return view('transactions.index', compact(['title', 'subTitle', 'transactions', 'countTransactions']));
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function show(string $transactionCode)
    {
        try {
            $title = 'Chi tiết giao dịch';

            $transaction = Transaction::query()
                ->with([
                    'user',
                    'invoice.course.user',
                ])
                ->where('transaction_code', $transactionCode)
                ->firstOrFail();

            return view('transactions.show', compact(['transaction', 'title']));
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Không tìm thấy giao dịch');
        }
    }

    public function checkTransaction(Request $request)
    {
        try {
            $transaction = Transaction::query()
                ->with([
                    'invoice.user',
                    'invoice.course',
                ])
                ->where('transaction_code', $request->transaction_code)
                ->firstOrFail();

            return response()->json(['transaction' => $transaction]);
        } catch (\Exception $e) {
            $this->logError($e);

            return response()->json(['error' => 'Không tìm thấy giao dịch']);
        }
    }

    public function filterSearch(Request $request)
    {
        try {
            $queryTransactions = Transaction::query()
                ->with('user')
                ->latest('id');

            $queryTransactions = $this->filter($request, $queryTransactions);

            $queryTransactions = $this->search($request->search_full, $queryTransactions);

            $transactions = $queryTransactions->paginate(10);

            if ($request->ajax()) {
                $html = view('transactions.table', compact('transactions'))->render();
                return response()->json(['html' => $html]);
            }
        } catch (\Exception $e) {
            $this->logError($e, $request->all());
        }
    }


    private function filter($request, $query)
    {
        $filters = [
            'user_name_transaction' => null,
            'transaction_code' => ['queryWhere' => 'LIKE'],
            'created_at' => ['attribute' => ['startDate' => '>=', 'endDate' => '<=']],
            'type' => ['queryWhere' => '='],
            'status' => ['queryWhere' => '='],
            'amount' => ['attribute' => ['amount_min' => '>=', 'amount_max' => '<=']],
        ];

        $query = $this->filterTrait($filters, $request, $query);

        return $query;
    }

    public function export()
    {
        try {

            return Excel::download(new TransactionExport, 'transaction.xlsx');
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }
    private function search($searchTerm, $query)
    {
        if (!empty($searchTerm)) {
            $query->whereHas('user', function ($query) use ($searchTerm) {
                $query->where('name', 'LIKE', "%$searchTerm%");
            });
        }

        return $query;
    }
}
