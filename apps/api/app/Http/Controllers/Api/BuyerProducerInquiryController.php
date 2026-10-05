<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateBuyerProducerInquiryRequest;
use App\Mail\BuyerProducerInquiryMail;
use App\Models\BuyerInquiry;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BuyerProducerInquiryController extends Controller
{
    public function store(CreateBuyerProducerInquiryRequest $request, string $order): JsonResponse
    {
        $buyerOrder = Order::query()
            ->where('buyer_id', $request->user()->id)
            ->whereKey($order)
            ->with('producerOrders.producer.producerProfile')
            ->firstOrFail();
        $producerOrder = $buyerOrder->producerOrders->first();

        abort_if(! $producerOrder || ! filter_var($producerOrder->producer?->email, FILTER_VALIDATE_EMAIL), 503);

        $data = $request->validated();
        $reference = 'INQ-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
        $inquiry = DB::transaction(function () use ($request, $data, $buyerOrder, $producerOrder, $reference): BuyerInquiry {
            $inquiry = BuyerInquiry::query()->firstOrCreate(
                [
                    'buyer_id' => $request->user()->id,
                    'idempotency_key' => $data['idempotency_key'],
                ],
                [
                    'producer_order_id' => $producerOrder->id,
                    'reference_number' => $reference,
                    'subject' => $data['topic'],
                    'topic' => $data['topic'],
                    'message' => trim($data['message']),
                    'submitted_at' => now(),
                ],
            );

            if (! $inquiry->wasRecentlyCreated && $inquiry->producer_order_id !== $producerOrder->id) {
                throw ValidationException::withMessages([
                    'idempotency_key' => ['この送信キーは別のお問い合わせに使用されています。'],
                ]);
            }

            if ($inquiry->wasRecentlyCreated) {
                Mail::to($producerOrder->producer->email)->send(new BuyerProducerInquiryMail(
                    referenceNumber: $inquiry->reference_number,
                    orderNumber: $buyerOrder->order_number,
                    shopName: $producerOrder->shop_name_snapshot ?? $producerOrder->producer->producerProfile?->farm_name ?? '生産者',
                    topic: $inquiry->topic,
                    inquiryMessage: $inquiry->message,
                ));
            }

            return $inquiry;
        });

        return response()->json(['data' => [
            'reference_number' => $inquiry->reference_number,
        ]], $inquiry->wasRecentlyCreated ? 201 : 200);
    }
}
