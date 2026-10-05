<?php

namespace App\Jobs;

use App\Mail\BuyerOrderCancelledMail;
use App\Mail\BuyerOrderRefundCompletedMail;
use App\Models\BuyerOrderEmailOutbox;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendBuyerOrderRefundEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(public readonly string $outboxId) {}

    public function backoff(): array
    {
        return [30, 120, 600, 1800];
    }

    public function handle(): void
    {
        $message = DB::transaction(function (): ?BuyerOrderEmailOutbox {
            $row = BuyerOrderEmailOutbox::query()->lockForUpdate()->findOrFail($this->outboxId);
            if ($row->state === 'sent' || ($row->state === 'sending' && $row->locked_until?->isFuture())) {
                return null;
            }

            $row->update([
                'state' => 'sending',
                'attempts' => $row->attempts + 1,
                'locked_until' => now()->addMinutes(5),
            ]);

            return $row;
        });

        if (! $message) {
            return;
        }

        if ($message->event_type === 'refund_completed') {
            $cancellationNotice = BuyerOrderEmailOutbox::query()
                ->where('order_id', $message->order_id)
                ->where('event_type', 'order_cancelled')
                ->first();
            if (! $cancellationNotice?->sent_at) {
                $message->update(['state' => 'queued', 'locked_until' => null]);
                $this->release(30);

                return;
            }
        }

        $mail = match ($message->event_type) {
            'order_cancelled' => new BuyerOrderCancelledMail(...$message->payload),
            'refund_completed' => new BuyerOrderRefundCompletedMail(...$message->payload),
            default => throw new \LogicException('Unsupported buyer order email event.'),
        };

        try {
            Mail::to($message->recipient_email)->send($mail);
            $message->update(['state' => 'sent', 'sent_at' => now(), 'locked_until' => null]);
        } catch (Throwable $exception) {
            $message->update(['state' => 'queued', 'locked_until' => null]);
            throw $exception;
        }
    }
}
