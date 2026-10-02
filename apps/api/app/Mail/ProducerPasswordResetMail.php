<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProducerPasswordResetMail extends Mailable implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly string $resetUrl)
    {
        // Keep delivery outside the HTTP request even when the default queue is sync.
        $this->onConnection('database');
        $this->afterCommit();
    }

    public function backoff(): array
    {
        return [30, 60, 120];
    }

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addMinutes(config('producer-password-reset.expires_minutes'));
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'みのりくん 生産者パスワード再設定');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.producer-password-reset');
    }
}
