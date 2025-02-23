<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserBuyCourseMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct(Invoice $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject('Bạn đã đặt hàng thành công! Hãy bắt đầu khóa học của bạn ngay hôm nay.')
            ->view('emails.userBuyCourse')
            ->with('data', $this->data);
    }
}
