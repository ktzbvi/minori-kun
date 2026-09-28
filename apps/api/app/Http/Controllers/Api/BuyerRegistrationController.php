<?php

namespace App\Http\Controllers\Api;

use App\Enums\AccountState;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteBuyerRegistrationRequest;
use App\Http\Requests\StartBuyerRegistrationRequest;
use App\Http\Requests\VerifyBuyerRegistrationOtpRequest;
use App\Mail\BuyerRegistrationOtpMail;
use App\Models\BuyerAddress;
use App\Models\BuyerProfile;
use App\Models\BuyerRegistrationAttempt;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class BuyerRegistrationController extends Controller
{
    public function start(StartBuyerRegistrationRequest $request): JsonResponse
    {
        $email = mb_strtolower(trim((string) $request->validated('email')));
        $now = now();

        $attempt = BuyerRegistrationAttempt::query()->where('email', $email)->first();

        if ($attempt?->resend_available_at->isFuture()) {
            $request->session()->put('buyer_registration_attempt_id', $attempt->id);

            return response()->json([
                'message' => 'A registration code was already sent.',
                'code' => 'OTP_RESEND_NOT_AVAILABLE',
                'data' => [
                    'email' => $email,
                    'resend_available_at' => $attempt->resend_available_at->toIso8601String(),
                ],
            ], 429);
        }

        $otp = (string) random_int(100000, 999999);

        $attempt = DB::transaction(function () use ($attempt, $email, $now, $otp): BuyerRegistrationAttempt {
            $attributes = [
                'otp_hash' => Hash::make($otp),
                'otp_sent_at' => $now,
                'otp_expires_at' => $now->copy()->addMinutes(3),
                'resend_available_at' => $now->copy()->addMinutes(3),
                'registration_session_expires_at' => $now->copy()->addDay(),
                'verified_at' => null,
                'consumed_at' => null,
            ];

            if ($attempt) {
                $attempt->update($attributes);

                return $attempt->refresh();
            }

            return BuyerRegistrationAttempt::query()->create(['email' => $email, ...$attributes]);
        });

        $request->session()->put('buyer_registration_attempt_id', $attempt->id);
        Mail::to($email)->send(new BuyerRegistrationOtpMail($otp));

        return response()->json([
            'data' => [
                'email' => $email,
                'next' => 'verify-otp',
                'otp_expires_at' => $attempt->otp_expires_at->toIso8601String(),
                'resend_available_at' => $attempt->resend_available_at->toIso8601String(),
            ],
        ], 202);
    }

    public function status(Request $request): JsonResponse
    {
        $attempt = $this->attempt($request);

        return response()->json(['data' => [
            'email' => $attempt->email,
            'verified' => $attempt->verified_at !== null,
            'otp_expires_at' => $attempt->otp_expires_at->toIso8601String(),
            'resend_available_at' => $attempt->resend_available_at->toIso8601String(),
        ]]);
    }

    public function resend(Request $request): JsonResponse
    {
        $attempt = $this->attempt($request);

        if ($attempt->resend_available_at->isFuture()) {
            return response()->json([
                'message' => 'A registration code was already sent.',
                'code' => 'OTP_RESEND_NOT_AVAILABLE',
                'data' => [
                    'resend_available_at' => $attempt->resend_available_at->toIso8601String(),
                ],
            ], 429);
        }

        $otp = (string) random_int(100000, 999999);
        $now = now();

        $attempt->update([
            'otp_hash' => Hash::make($otp),
            'otp_sent_at' => $now,
            'otp_expires_at' => $now->copy()->addMinutes(3),
            'resend_available_at' => $now->copy()->addMinutes(3),
            'verified_at' => null,
        ]);

        Mail::to($attempt->email)->send(new BuyerRegistrationOtpMail($otp));

        return response()->json(['data' => [
            'email' => $attempt->email,
            'otp_expires_at' => $attempt->otp_expires_at->toIso8601String(),
            'resend_available_at' => $attempt->resend_available_at->toIso8601String(),
        ]], 202);
    }

    public function verify(VerifyBuyerRegistrationOtpRequest $request): JsonResponse
    {
        $attempt = $this->attempt($request);

        if ($attempt->otp_expires_at->isPast() || ! Hash::check($request->validated('code'), $attempt->otp_hash)) {
            return response()->json(['message' => 'The verification code is invalid or expired.', 'code' => 'OTP_INVALID'], 422);
        }

        $attempt->update(['verified_at' => now()]);
        $request->session()->regenerate();
        $request->session()->put('buyer_registration_attempt_id', $attempt->id);

        return response()->json(['data' => ['next' => 'registration-details']]);
    }

    public function complete(CompleteBuyerRegistrationRequest $request): JsonResponse
    {
        $attemptId = $request->session()->get('buyer_registration_attempt_id');
        $details = $request->validated();

        $buyer = DB::transaction(function () use ($attemptId, $details): User {
            $attempt = BuyerRegistrationAttempt::query()->lockForUpdate()->find($attemptId);

            abort_unless(
                $attempt
                    && $attempt->verified_at
                    && ! $attempt->consumed_at
                    && ! $attempt->registration_session_expires_at->isPast(),
                422,
                'Registration session is unavailable.',
            );

            abort_if(User::query()->where('email', $attempt->email)->exists(), 422, 'Registration is unavailable.');

            $buyer = User::query()->create([
                'role' => UserRole::Buyer,
                'email' => $attempt->email,
                'password' => $details['password'],
                'account_state' => AccountState::Active,
                'email_verified_at' => now(),
                'password_changed_at' => now(),
            ]);

            BuyerProfile::query()->create([
                'user_id' => $buyer->id,
                'name' => $details['name'],
                'name_phonetic' => $details['name_phonetic'] ?? null,
                'phone' => $details['phone'],
            ]);

            BuyerAddress::query()->create([
                'buyer_id' => $buyer->id,
                'recipient_name' => $details['name'],
                'phone' => $details['phone'],
                'postal_code' => $details['postal_code'],
                'prefecture' => $details['prefecture'],
                'city' => $details['city'],
                'address_line1' => $details['address_line1'],
                'address_line2' => $details['address_line2'] ?? null,
                'is_default' => true,
            ]);

            $attempt->update(['consumed_at' => now()]);

            return $buyer;
        });

        Auth::login($buyer);
        $request->session()->regenerate();

        return response()->json(['data' => ['redirect' => '/']]);
    }

    private function attempt(Request $request): BuyerRegistrationAttempt
    {
        $id = $request->session()->get('buyer_registration_attempt_id');
        $attempt = BuyerRegistrationAttempt::query()->find($id);

        abort_unless(
            $attempt && ! $attempt->consumed_at && ! $attempt->registration_session_expires_at->isPast(),
            422,
            'Registration session is unavailable.',
        );

        return $attempt;
    }
}
