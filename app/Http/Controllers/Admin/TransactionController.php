<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TransactionExport;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TransactionController extends Controller
{
    use LoggableTrait;
    public function index(Request $request)
    {
        try {
            $title = 'Giao dịch thanh toán';
            $subTitle = 'Giao dịch thanh toán';

            $queryTransactions = Transaction::query()
                ->with([
                    'invoice.user',
                    'invoice.course',
                ])
                ->latest('id');

            $countTransactions = Transaction::query()->selectRaw(
    'count(id) as total_transactions,
                sum(type = "invoice") as invoice_transactions,
                sum(type = "withdrawal") as withdrawal_transactions'
            )->first();

            if ($request->hasAny(['user_transaction', 'status', 'type', 'amount_min', 'amount_max', 'created_at', 'updated_at']))
                $queryTransactions = $this->filter($request, $queryTransactions);

            if ($request->has('search_full'))
                $queryTransactions = $this->search($request->search_full, $queryTransactions);

            $transactions = $queryTransactions->paginate(10);

            if ($request->ajax()) {
                $html = view('transactions.table', compact('transactions'))->render();
                return response()->json(['html' => $html]);
            }

            return view('transactions.index', compact(['title', 'subTitle', 'transactions', 'countTransactions']));
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function show(string $transactionCode)
    {
        try {
            $transaction = Transaction::query()
                ->with([
                    'invoice.user',
                    'invoice.course',
                ])
                ->where('transaction_code', $transactionCode)
                ->firstOrFail();

            return view('transactions.show', compact('transaction'));
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


    private function filter($request, $query)
    {
        $filters = [
            'created_at' => ['queryWhere' => '>='],
            'updated_at' => ['queryWhere' => '<='],
            'type' => ['queryWhere' => '='],
            'status' => ['queryWhere' => '='],
            'amount' => ['queryWhere' => 'BETWEEN', 'attribute' => ['amount_min', 'amount_max']],
        ];

        foreach ($filters as $filter => $value) {
            if (!empty($value['queryWhere'])) {
                if ($value['queryWhere'] !== 'BETWEEN') {
                    $filterValue = $request->input($filter);
                    if (!empty($filterValue)) {
                        $filterValue = $value['queryWhere'] === 'LIKE' ? "%$filterValue%" : $filterValue;
                        $query->where($filter, $value['queryWhere'], $filterValue);
                    }
                } else {
                    $filterValueBetweenA = $request->input($value['attribute'][0]);
                    $filterValueBetweenB = $request->input($value['attribute'][1]);

                    if (!empty($filterValueBetweenA) && !empty($filterValueBetweenB)) {
                        $query->whereBetween($filter, [$filterValueBetweenA, $filterValueBetweenB]);
                    }
                }
            }
        }

        if(!empty($request->input('user_transaction'))){
            $userSearchTransaction = $request->input('user_transaction');
            $query = $this->search($userSearchTransaction, $query);
        }

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
