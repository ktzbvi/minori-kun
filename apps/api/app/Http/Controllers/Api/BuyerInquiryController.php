<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateBuyerInquiryRequest;
use App\Models\BuyerInquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BuyerInquiryController extends Controller
{
    public function store(CreateBuyerInquiryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $inquiry = DB::transaction(function () use ($request, $data): BuyerInquiry {
            return BuyerInquiry::query()->firstOrCreate(
                [
                    'buyer_id' => $request->user()->id,
                    'idempotency_key' => $data['idempotency_key'],
                ],
                [
                    'reference_number' => $this->referenceNumber(),
                    'subject' => trim((string) $data['subject']),
                    'message' => trim((string) $data['message']),
                    'submitted_at' => now(),
                ],
            );
        });

        return response()->json(['data' => [
            'reference_number' => $inquiry->reference_number,
        ]], $inquiry->wasRecentlyCreated ? 201 : 200);
    }

    private function referenceNumber(): string
    {
        return 'INQ-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
    }
}
