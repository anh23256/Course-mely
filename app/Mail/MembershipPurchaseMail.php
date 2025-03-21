<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MembershipPurchaseMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $member;
    private $membership;
    private $transaction;
    private $invoice;

    public function __construct($member, $membership, $transaction, $invoice)
    {
        $this->member = $member;
        $this->membership = $membership;
        $this->transaction = $transaction;
        $this->invoice = $invoice;
    }

    public function build()
    {
        return $this->subject('Xác nhận đăng ký gói membership thành công')
            ->view('emails.membership_purchase')
            ->with([
                'member' => $this->member,
                'membership' => $this->membership,
                'transaction' => $this->transaction,
                'invoice' => $this->invoice
            ]);
    }
}
