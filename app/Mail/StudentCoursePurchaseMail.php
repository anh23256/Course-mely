<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentCoursePurchaseMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $student;
    private $course;
    private $transaction;

    /**
     * Create a new message instance.
     */
    public function __construct($student, $course, $transaction)
    {
        $this->student = $student;
        $this->course = $course;
        $this->transaction = $transaction;
    }

    public function build()
    {
        return $this->subject('Xác nhận mua khóa học thành công')
            ->view('emails.course_purchase')
            ->with([
                'student' => $this->student,
                'course' => $this->course,
                'transaction' => $this->transaction,
            ]);
    }
}
