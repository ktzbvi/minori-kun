<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProducerOnboardingStatusResource;
use Illuminate\Http\Request;

class ProducerOnboardingController extends Controller
{
    public function show(Request $request): ProducerOnboardingStatusResource
    {
        $eligible = $request->user()->isSellingEligible();

        return new ProducerOnboardingStatusResource([
            'state' => $eligible ? 'eligible' : 'application_required',
            'eligible_to_sell' => $eligible,
            'application_available' => false,
        ]);
    }
}
