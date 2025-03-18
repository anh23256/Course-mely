<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberShipPlanRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $membershipPlan;
    protected $instructor;
    protected $approvalRequest;

    public function __construct($membershipPlan, $instructor, $approvalRequest)
    {
        $this->membershipPlan = $membershipPlan;
        $this->instructor = $instructor;
        $this->approvalRequest = $approvalRequest;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = 'ahihi';

        return (new MailMessage)
            ->subject('Yêu cầu kiểm duyệt gói thành viên đã được gửi')
            ->view('emails.membership-request', [
                    'instructor' => $this->instructor,
                    'membershipPlan' => $this->membershipPlan,
                    'approvalRequest' => $this->approvalRequest,
                    'url' => $url,
                    'courseCount' => $this->membershipPlan->membershipCourseAccess->count(),
                ]
            );
    }

    public function toDatabase(object $notifiable)
    {
        if ($notifiable->id === $this->instructor->id) {
            return [
                'message' => "Giảng viên {$this->instructor->name} đã gửi yêu cầu kiểm duyệt gói thành viên",
                'content' => "Yêu cầu kiểm duyệt gói thành viên \"{$this->membershipPlan->name}\" của bạn đã được gửi thành công",
                'type' => 'register_course',
                'membership_plan_id' => $this->membershipPlan->id,
                'membership_plan_code' => $this->membershipPlan->code,
                'membership_plan_name' => $this->membershipPlan->name,
                'approval_request_id' => $this->approvalRequest->id,
                'created_at' => now(),
            ];
        }

        return [
            'message' => "Giảng viên {$this->instructor->name} đã gửi yêu cầu kiểm duyệt gói thành viên",
            'type' => 'register_course',
            'membership_plan_id' => $this->membershipPlan->id,
            'membership_plan_code' => $this->membershipPlan->code,
            'membership_plan_name' => $this->membershipPlan->name,
            'instructor_id' => $this->instructor->id,
            'instructor_name' => $this->instructor->name,
            'approval_request_id' => $this->approvalRequest->id,
            'course_count' => $this->membershipPlan->membershipCourseAccess->count(),
            'created_at' => now(),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $data = [
            'message' => "Giảng viên {$this->instructor->name} đã gửi yêu cầu kiểm duyệt gói thành viên",
            'type' => 'register_course',
            'membership_plan_id' => $this->membershipPlan->id,
            'membership_plan_code' => $this->membershipPlan->code,
            'membership_plan_name' => $this->membershipPlan->name,
            'instructor_id' => $this->instructor->id,
            'instructor_name' => $this->instructor->name,
            'approval_request_id' => $this->approvalRequest->id,
            'course_count' => $this->membershipPlan->membershipCourseAccess->count(),
            'created_at' => now(),
        ];

        return new BroadcastMessage($data);
    }
}
