<?php

namespace App\Http\Controllers\Api;

use App\Domain\Orders\ListProducerOrders;
use App\Http\Controllers\Controller;
use App\Http\Requests\IndexProducerOrdersRequest;
use App\Http\Resources\ProducerOrderListItemResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ProducerOrderController extends Controller
{
    public function index(IndexProducerOrdersRequest $request, ListProducerOrders $orders): AnonymousResourceCollection
    {
        return ProducerOrderListItemResource::collection(
            $orders->handle((string) $request->user()->id, $request->validated()),
        );
    }

    public function show(Request $request, string $producerOrder, ListProducerOrders $orders): ProducerOrderListItemResource
    {
        $record = $orders->query((string) $request->user()->id)->whereKey($producerOrder)->firstOrFail();
        Gate::authorize('view', $record);

        return new ProducerOrderListItemResource($record);
    }
}
