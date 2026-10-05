<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BuyerProducerInquiryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $referenceNumber,
        public readonly string $orderNumber,
        public readonly string $shopName,
        public readonly string $topic,
        public readonly string $inquiryMessage,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "[生産者へのお問い合わせ {$this->referenceNumber}] {$this->topic}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.buyer-producer-inquiry');
    }
}
