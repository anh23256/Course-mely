<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemFund;
use App\Models\SystemFundTransaction;
use App\Models\Wallet;
use App\Traits\LoggableTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use function Termwind\render;

class WalletController extends Controller
{
    use LoggableTrait;

    public function index(Request $request)
    {
        try {
            $title = 'Ví của ' . Auth::user()->name;

            $search_full = $request->input('search') ?? '';
            $limitSystemFund = $request->input('page') ?? 10;

            $balanceSystem = SystemFund::first();

            $systemFunds = DB::table('system_fund_transactions')
                ->select(
                    DB::raw('DATE(created_at) as day'),
                    'id',
                    'created_at',
                    'total_amount',
                    'retained_amount',
                    'description',
                    'type'
                )
                ->when(!empty($search_full), function ($query) use ($search_full) {
                    $query->where(function ($q) use ($search_full) {
                        $q->where('description', 'LIKE', "%$search_full%");
                        if (preg_match('/\d{2}\/\d{2}\/\d{4}/', $search_full)) {
                            $date = Carbon::createFromFormat('d/m/Y', $search_full)->format('Y-m-d');
                            $q->orWhereDate('created_at', $date);
                        }
                    });
                })
                ->orderBy(DB::raw('DATE(created_at)'), 'DESC')
                ->orderBy('created_at', 'DESC')
                ->when(empty($search_full), function ($query) use ($limitSystemFund) {
                    $query->limit($limitSystemFund);
                })
                ->get();

            $totalIncome = DB::table('system_fund_transactions')
                ->join('transactions', 'system_fund_transactions.transaction_id', '=', 'transactions.id')
                ->where(['system_fund_transactions.type' => 'commission_received', 'transactions.status' => 'Giao dịch thành công'])
                ->sum('system_fund_transactions.retained_amount');

            $totalExpense = DB::table('system_fund_transactions')
                ->join('transactions', 'system_fund_transactions.transaction_id', '=', 'transactions.id')
                ->where(['system_fund_transactions.type' => 'withdrawal', 'transactions.status' => 'Giao dịch thành công'])
                ->sum('system_fund_transactions.total_amount');

            $totalTransactions = DB::table('system_fund_transactions')
                ->join('transactions', 'system_fund_transactions.transaction_id', '=', 'transactions.id')
                ->where('transactions.status', 'Giao dịch thành công')
                ->count();

            if ($request->ajax()) {
                return response()->json([
                    'systemFunds' => view('wallets.includes.list-transaction', compact('systemFunds'))->render(),
                ], 200);
            }

            return view('wallets.index', compact([
                'systemFunds',
                'title',
                'balanceSystem',
                'totalIncome',
                'totalExpense',
                'totalTransactions',
            ]));
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại!');
        }
    }

    public function show(string $id)
    {
        try {
            $title = 'Chi tiết giao dịch';

            $systemFund = SystemFundTransaction::query()->where('id', $id)
                ->with(['transaction', 'user', 'course'])->first();

            if (!$systemFund) {
                abort(404, 'Không tìm thấy giao dịch');
            }

            return view('wallets.show', compact([
                'systemFund',
                'title',
            ]));
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }
}
