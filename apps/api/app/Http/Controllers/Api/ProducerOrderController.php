<?php

namespace App\Http\Controllers\Api;

use App\Domain\Orders\ListProducerOrders;
use App\Domain\Orders\UpdateProducerFulfillment;
use App\Http\Controllers\Controller;
use App\Http\Requests\IndexProducerOrdersRequest;
use App\Http\Requests\UpdateProducerFulfillmentRequest;
use App\Http\Resources\ProducerOrderDetailResource;
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

    public function show(Request $request, string $producerOrder, ListProducerOrders $orders): ProducerOrderDetailResource
    {
        $record = $orders->query((string) $request->user()->id)->whereKey($producerOrder)->firstOrFail();
        Gate::authorize('view', $record);

        $record->load(['order.deliveryAddress', 'items' => fn ($query) => $query->where('producer_id', $request->user()->id)->orderBy('id')]);

        return new ProducerOrderDetailResource($record);
    }

    public function updateFulfillment(UpdateProducerFulfillmentRequest $request, string $producerOrder, UpdateProducerFulfillment $action, ListProducerOrders $orders): ProducerOrderDetailResource
    {
        $action->handle($request->user(), $producerOrder, $request->validated());

        return $this->show($request, $producerOrder, $orders);
    }
}
