<?php

namespace App\Console\Commands;

use App\Models\SystemFund;
use App\Models\SystemFundTransaction;
use App\Models\Transaction;
use App\Models\WithdrawalRequest;
use App\Traits\LoggableTrait;
use Illuminate\Console\Command;

class CheckWithDrawalRequestTransactionStatus extends Command
{
    use LoggableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'with-drawal-transation:check {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron job kiểm tra trạng thái giao dịch.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = $this->argument('id');

        $withDrawal = WithdrawalRequest::query()->find($id);

        if ($withDrawal) {
            $this->error('Không tìm thấy yêu cầu rút tiền: ' . $id);
            return;
        }

        $transation = Transaction::query()
            ->where('transactionable_type ', WithdrawalRequest::class)
            ->where('transactionable_id', $id)
            ->first();

        $systemFundTransaction = SystemFundTransaction::query()
            ->where('transaction_id', $transation->id)
            ->first();

        if ($transation && $transation->status === 'Đang xử lý') {
            $transation->status = 'Giao dịch thành công';
            $transation->save();
        }

        $this->info(json_encode([
            'withdrawal_request' => $withDrawal,
            'transation' => $transation,
            'system_fund_transaction' => $systemFundTransaction,
        ]));
    }
}
