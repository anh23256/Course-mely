<?php

namespace App\Console\Commands;

use App\Models\CourseUser;
use App\Models\MembershipSubscription;
use App\Traits\LoggableTrait;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeactivateExpiredMembershipsCommand extends Command
{
    use LoggableTrait;

    protected $signature = 'memberships:deactivate-expired';

    protected $description = 'Kiểm tra gói thành viên xem còn hạn không.';

    public function handle()
    {
        try {
            DB::beginTransaction();

            $expiredSubscriptions = MembershipSubscription::query()->where('end_date', '<', Carbon::now()->toDateString())
                ->where('status', '!=', 'cancelled')
                ->get();

            foreach ($expiredSubscriptions as $subscription) {
                $courses = $subscription->membershipPlan->membershipCourseAccess;

                foreach ($courses as $course) {
                    CourseUser::query()->where('user_id', $subscription->user_id)
                        ->where('course_id', $course->id)
                        ->where('source', 'membership')
                        ->update(['access_status' => 'inactive']);
                }

                $subscription->status = 'expired';
                $subscription->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e);

            return;
        }

    }
}
