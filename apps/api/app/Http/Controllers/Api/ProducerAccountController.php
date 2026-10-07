<?php

namespace App\Http\Controllers\Api;

use App\Domain\ProducerAccount\UpdateShopPhoto;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProducerShopPhotoRequest;
use App\Http\Resources\ProducerAccountResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProducerAccountController extends Controller
{
    public function show(Request $request): ProducerAccountResource
    {
        Gate::authorize('view', $request->user()->producerProfile);

        return new ProducerAccountResource($request->user());
    }

    /** @throws \Symfony\Component\HttpKernel\Exception\ConflictHttpException */
    public function updatePhoto(UpdateProducerShopPhotoRequest $request, UpdateShopPhoto $update): ProducerAccountResource
    {
        return new ProducerAccountResource($update->execute($request->user(), $request->file('photo'), $request->validated('expected_photo_id')));
    }
}
