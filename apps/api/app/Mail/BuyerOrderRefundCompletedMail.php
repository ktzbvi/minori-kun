<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BuyerOrderRefundCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $orderNumber,
        public readonly string $shopName,
        public readonly int $refundAmountYen,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "ご注文 {$this->orderNumber} の返金処理が完了しました");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.buyer-order-refund-completed');
    }
}
