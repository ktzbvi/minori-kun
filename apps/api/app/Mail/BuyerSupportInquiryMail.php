<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BuyerSupportInquiryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $referenceNumber,
        public readonly string $inquirySubject,
        public readonly string $inquiryMessage,
        public readonly string $buyerName,
        public readonly string $buyerEmail,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[お問い合わせ {$this->referenceNumber}] {$this->inquirySubject}",
            replyTo: [new Address($this->buyerEmail, $this->buyerName)],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.buyer-support-inquiry');
    }
}
