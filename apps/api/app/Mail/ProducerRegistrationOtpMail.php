<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ProducerRegistrationOtpMail extends Mailable
{
    use Queueable;

    public function __construct(public readonly string $code) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '生産者登録の確認コード');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.producer-registration-otp', with: ['code' => $this->code]);
    }
}
