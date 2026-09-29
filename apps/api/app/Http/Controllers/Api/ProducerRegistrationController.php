<?php

namespace App\Http\Controllers\Api;

use App\Domain\ProducerRegistration\ProducerRegistrationService;
use App\Domain\ProducerRegistration\RegistrationMutationResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteProducerRegistrationRequest;
use App\Http\Requests\RequestProducerRegistrationCodeRequest;
use App\Http\Requests\UploadProducerRegistrationPhotoRequest;
use App\Http\Requests\VerifyProducerRegistrationCodeRequest;
use App\Http\Resources\CurrentSessionResource;
use App\Http\Resources\ProducerRegistrationDetailsResource;
use App\Http\Resources\ProducerRegistrationPhotoResource;
use App\Http\Resources\ProducerRegistrationStateResource;
use App\Http\Resources\ProducerTermsResource;
use App\Http\Support\ProducerRegistrationCookie;
use App\Models\ProducerRegistrationAttempt;
use App\Models\ProducerRegistrationPhoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProducerRegistrationController extends Controller
{
    public function __construct(private readonly ProducerRegistrationService $registration) {}

    public function show(Request $request): ProducerRegistrationStateResource
    {
        return new ProducerRegistrationStateResource($this->registration->state($this->attempt($request)));
    }

    public function requestCode(RequestProducerRegistrationCodeRequest $request): ProducerRegistrationStateResource
    {
        return $this->mutationResponse($request, $this->registration->requestCode(
            $this->attempt($request), $request->validated('email'),
        ));
    }

    public function resend(Request $request): ProducerRegistrationStateResource
    {
        return $this->mutationResponse($request, $this->registration->resend($this->attempt($request)));
    }

    public function verify(VerifyProducerRegistrationCodeRequest $request): ProducerRegistrationStateResource
    {
        return $this->mutationResponse($request, $this->registration->verify(
            $this->attempt($request), $request->validated('code'),
        ));
    }

    public function destroy(Request $request): JsonResponse
    {
        $this->registration->cancel($this->attempt($request));
        ProducerRegistrationCookie::forget();

        return response()->json(['data' => ['deleted' => true]]);
    }

    public function details(Request $request): ProducerRegistrationDetailsResource
    {
        return new ProducerRegistrationDetailsResource($this->registration->details($this->attempt($request)));
    }

    public function terms(): ProducerTermsResource
    {
        return new ProducerTermsResource($this->registration->terms());
    }

    public function uploadPhoto(UploadProducerRegistrationPhotoRequest $request): ProducerRegistrationPhotoResource
    {
        $photo = $this->registration->uploadPhoto($this->attempt($request), $request->file('photo'));

        return new ProducerRegistrationPhotoResource([
            'id' => $photo->id,
            'preview_url' => route('producer.registration.photo', ['id' => $photo->id]),
            'expires_at' => $photo->expires_at->toIso8601String(),
        ]);
    }

    public function previewPhoto(Request $request, string $id): StreamedResponse
    {
        return $this->imageResponse($this->registration->photoForPreview($this->attempt($request), $id));
    }

    public function deletePhoto(Request $request, string $id): JsonResponse
    {
        $this->registration->deletePhoto($this->attempt($request), $id);

        return response()->json(['data' => ['deleted' => true]]);
    }

    public function complete(CompleteProducerRegistrationRequest $request): CurrentSessionResource
    {
        $user = $this->registration->complete($this->attempt($request), $request->validated());

        return $this->authenticateCompletedRegistration($request, $user);
    }

    public function recover(Request $request): CurrentSessionResource
    {
        $user = $this->registration->recoverCompleted($this->attempt($request));

        return $this->authenticateCompletedRegistration($request, $user);
    }

    private function authenticateCompletedRegistration(Request $request, \App\Models\User $user): CurrentSessionResource
    {
        Auth::guard('web')->login($user);
        $request->session()->regenerate();
        ProducerRegistrationCookie::forget();

        return new CurrentSessionResource($user);
    }

    public function publicPhoto(string $id): StreamedResponse
    {
        $photo = ProducerRegistrationPhoto::query()
            ->whereKey($id)->whereNotNull('claimed_at')
            ->whereNotNull('claimed_by_producer_id')->whereNull('deleted_at')->firstOrFail();

        return $this->imageResponse($photo);
    }

    private function attempt(Request $request): ?ProducerRegistrationAttempt
    {
        return $request->attributes->get('producer_registration_attempt');
    }

    private function mutationResponse(Request $request, RegistrationMutationResult $result): ProducerRegistrationStateResource
    {
        if ($result->cookieToken !== null) {
            ProducerRegistrationCookie::queue(
                $result->cookieToken,
                $result->attempt->verified_at ? $result->attempt->grant_expires_at : $result->attempt->expires_at,
                $request,
            );
        }

        return new ProducerRegistrationStateResource($result->state);
    }

    private function imageResponse(ProducerRegistrationPhoto $photo): StreamedResponse
    {
        abort_unless($photo->storage_path && Storage::disk('local')->exists($photo->storage_path), 404);

        return Storage::disk('local')->response($photo->storage_path, null, [
            'Content-Type' => $photo->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'no-store, private',
        ]);
    }
}
