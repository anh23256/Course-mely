<?php

namespace App\Jobs;

use App\Models\Approvable;
use App\Models\Conversation;
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

    //    public $tries = 10;
    //    public $backoff = 5;

    protected $course;

    /**
     * Create a new job instance.
     */
    public function __construct(Course $course)
    {
        $this->course = $course;
    }

    private function createChatGroup(Course $course): void
    {
        $existingConversation = Conversation::query()
            ->where('conversationable_id', $course->id)
            ->where('conversationable_type', Course::class)
            ->where('type', 'group')
            ->first();

        if ($existingConversation) {
            return;
        }

        $conversation = Conversation::create([
            'conversationable_id' => $course->id,
            'conversationable_type' => Course::class,
            'name' => 'Nhóm học tập cho khóa học ' . $course->name,
            'type' => 'group',
            'owner_id' => $course->user_id
        ]);

        $conversation->users()->attach($course->user_id);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if (!$this->course) {
                Log::error('Course not found during job execution');
                return;
            }

            $course = $this->course;

            $approval = Approvable::query()->where('approvable_id', $this->course->id)
                ->where('approvable_type', Course::class)
                ->first();

            if (!$approval) {
                Log::error('Approval record not found for course: ' . $this->course->id);
                return;
            }

            $errors = CourseValidatorService::validateCourse($course);

            if (!empty($errors)) {
                DB::transaction(function () use ($approval, $course, $errors) {
                    $approval->update([
                        'status' => 'rejected',
                        'note' => 'Khoá học chưa đạt yêu cầu kiểm duyệt.',
                        'rejected_at' => now(),
                        'approver_id' => null,
                    ]);

                    $course->update([
                        'status' => 'rejected',
                        'visibility' => 'private',
                    ]);

                    $approval->logApprovalAction(
                        'rejected',
                        null,
                        'Khoá học chưa đạt yêu cầu kiểm duyệt.',
                        implode(', ', $errors)
                    );
                });

                $this->course->user->notify(new CourseRejectedNotification($this->course));
            } else {
                DB::transaction(function () use ($approval, $course) {
                    $approval->update([
                        'status' => 'approved',
                        'approved_at' => now(),
                        'note' => 'Khoá học đã được kiểm duyệt.',
                        'approver_id' => null,
                    ]);

                    $course->update([
                        'status' => 'approved',
                        'visibility' => 'public',
                        'accepted' => now(),
                    ]);

                    $this->createChatGroup($course);

                    $approval->logApprovalAction(
                        'approved',
                        null,
                        'Khoá học đã được kiểm duyệt.'
                    );
                });

                $this->course->user->notify(new CourseApprovedNotification($this->course));
            }
        } catch (\Exception $e) {
            Log::error("Lỗi tự động duyệt khóa học: " . $e->getMessage());

            return;
        }
    }
}
