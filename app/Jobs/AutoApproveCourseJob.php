<?php

namespace App\Jobs;

use App\Models\Approvable;
use App\Models\Course;
use App\Models\User;
use App\Notifications\CourseApprovedNotification;
use App\Notifications\CourseRejectedNotification;
use App\Notifications\CourseSubmittedNotification;
use App\Services\CourseValidatorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoApproveCourseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $course;

    /**
     * Create a new job instance.
     */
    public function __construct(Course $course)
    {
        $this->course = $course;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $course = $this->course;

            $approval = Approvable::query()->where('approvable_id', $this->course->id)
                ->where('approvable_type', Course::class)
                ->first();

            if (!$approval) {
                return;
            }

            $errors = CourseValidatorService::validateCourse($course);

            if (!empty($errors)) {
                $approval->update([
                    'status' => 'rejected',
                    'note' => 'Khoá học chưa đạt yêu cầu kiểm duyệt.',
                    'rejected_at' => now(),
                    'approver_id' => null,
                ]);

                $this->course->update(['status' => 'rejected']);

                $this->course->user->notify(new CourseRejectedNotification($this->course));

            } else {
                $approval->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'note' => 'Khoá học đã được kiểm duyệt.',
                    'approver_id' => null,
                ]);

                $this->course->update([
                    'status' => 'approved',
                    'visibility' => 'public',
                    'accepted' => now(),
                ]);

                $this->course->user->notify(new CourseApprovedNotification($this->course));
            }

            $managers = User::query()->role([
                'admin',
            ])->get();

            foreach ($managers as $manager) {
                $manager->notify(new CourseApprovedNotification($this->course));
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Lỗi tự động duyệt khóa học: " . $e->getMessage());

            return;
        }
    }
}
