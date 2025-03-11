<?php

namespace App\Console\Commands;

use App\Models\WithdrawalRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoConfirmWithdrawalCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'withdrawal-request:auto-confirm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hệ thống tự động kiểm duyệt những yêu cầu rút tiền thành công mà chưa được xác nhân.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::beginTransaction();

        try {
            $withdrawalRequests = WithdrawalRequest::query()
                ->where('status', 'Đã xử lý')
                ->where(function ($query) {
                    $query->where('instructor_confirmation', 'not_received')
                        ->orWhereNull('instructor_confirmation');
                })
                ->where('created_at', '<=', now()->subMinute())
                ->lockForUpdate()
                ->get();

            Log::info('Số yêu cầu rút tiền cần tự động xác nhận: ' . $withdrawalRequests->count());

            if ($withdrawalRequests->isEmpty()) {
                DB::rollBack();
                return;
            }

            foreach ($withdrawalRequests as $request) {
                $request->update([
                    'status' => 'Hoàn thành',
                    'instructor_confirmation' => 'confirmed',
                    'instructor_confirmation_note' => 'Hệ thống đã xác nhận do thời gian xác nhận đã quá thời gian cho phép.',
                    'instructor_confirmation_date' => now(),
                    'is_received' => true
                ]);

                Log::info("Tự động xác nhận yêu cầu rút tiền #{$request->id}");
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::info("Tự động xác nhận yêu cầu rút tiền: " . $e->getMessage());
        }
    }
}
