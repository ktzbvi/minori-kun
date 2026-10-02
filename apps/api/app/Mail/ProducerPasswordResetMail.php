<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ProducerPasswordResetMail extends Mailable
{
    public function __construct(public readonly string $resetUrl) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'みのりくん 生産者パスワード再設定');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.producer-password-reset');
    }
}
