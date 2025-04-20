<?php

namespace App\Jobs;

use App\Models\Approvable;
use App\Models\InstructorCommission;
use App\Models\User;
use App\Notifications\InstructorApprovalNotification;
use App\Notifications\InstructorRejectedNotification;
use App\Traits\LoggableTrait;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoApproveInstructorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LoggableTrait;

    protected $user;
    protected $checkInstructorApproval;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, array $checkInstructorApproval)
    {
        $this->user = $user;
        $this->checkInstructorApproval = $checkInstructorApproval;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $user = $this->user;
            $profile = $user->profile;
            $checkInstructorApproval = $this->checkInstructorApproval;

            $approvable = Approvable::where('approvable_id', $user->id)
                ->where('approvable_type', User::class)
                ->first();

            if (!$approvable) {
                Log::error("Không tìm thấy yêu cầu kiểm duyệt của giảng viên: " . $user->id);
                return;
            }

            DB::transaction(function () use ($approvable, $user, $checkInstructorApproval) {
                if ($checkInstructorApproval['progress'] >= 70) {
                    $approvable->update([
                        'status' => 'approved',
                        'approved_at' => now(),
                        'note' => 'Duyệt đăng ký trở thành giảng viên.',
                        'approver_id' => null,
                    ]);

                    $user->syncRoles(['instructor']);

                    $user->notify(new InstructorApprovalNotification($user));
                } else {
                    $approvable->update([
                        'status' => 'rejected',
                        'note' => 'Hồ sơ chưa đủ điều kiện duyệt. Vui lòng bổ sung thông tin.',
                        'rejected_at' => now(),
                        'approver_id' => null,
                    ]);

                    $user->notify(new InstructorRejectedNotification($user));
                }
            });
        } catch (\Exception $e) {
            $this->logError($e);
        }
    }
}
