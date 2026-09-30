<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeBuyerPasswordRequest;
use App\Http\Requests\UpdateBuyerProfileRequest;
use App\Mail\BuyerEmailChangeVerificationMail;
use App\Models\BuyerAddress;
use App\Models\BuyerProfile;
use App\Models\EmailVerificationToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BuyerAccountController extends Controller
{
    private const EMAIL_CHANGE_PURPOSE = 'buyer_email_change';

    public function show(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->profile($request->user())]);
    }

    public function update(UpdateBuyerProfileRequest $request): JsonResponse
    {
        $buyer = $request->user();
        $data = $request->validated();
        $email = mb_strtolower(trim((string) $data['email']));
        $emailChanged = $email !== $buyer->email;
        $emailChangeToken = null;

        if ($emailChanged && User::query()->where('email', $email)->whereKeyNot($buyer->id)->exists()) {
            throw ValidationException::withMessages([
                'email' => ['このメールアドレスはすでに利用されています。'],
            ]);
        }

        DB::transaction(function () use ($buyer, $data, $email, $emailChanged, &$emailChangeToken): void {
            BuyerProfile::query()->updateOrCreate(
                ['user_id' => $buyer->id],
                [
                    'name' => $data['name'],
                    'name_phonetic' => $data['name_phonetic'],
                    'phone' => $data['phone'],
                ],
            );

            BuyerAddress::query()->updateOrCreate(
                ['buyer_id' => $buyer->id, 'is_default' => true],
                [
                    'recipient_name' => $data['name'],
                    'phone' => $data['phone'],
                    'postal_code' => $data['postal_code'],
                    'prefecture' => $data['prefecture'],
                    'city' => $data['city'],
                    'address_line1' => $data['address_line1'],
                    'address_line2' => $data['address_line2'] ?: null,
                ],
            );

            if (! $emailChanged) {
                return;
            }

            $buyer->update(['pending_email' => $email]);
            EmailVerificationToken::query()
                ->where('user_id', $buyer->id)
                ->where('purpose', self::EMAIL_CHANGE_PURPOSE)
                ->whereNull('consumed_at')
                ->update(['invalidated_at' => now()]);

            $emailChangeToken = Str::random(64);
            EmailVerificationToken::query()->create([
                'user_id' => $buyer->id,
                'purpose' => self::EMAIL_CHANGE_PURPOSE,
                'token_hash' => hash('sha256', $emailChangeToken),
                'expires_at' => now()->addMinutes(30),
            ]);
        });

        if ($emailChanged && $emailChangeToken) {
            Mail::to($email)->send(new BuyerEmailChangeVerificationMail(
                $this->emailVerificationUrl($emailChangeToken),
            ));
        }

        return response()->json(['data' => [
            'profile' => $this->profile($buyer->fresh()),
            'email_change_pending' => $emailChanged,
        ]]);
    }

    public function changePassword(ChangeBuyerPasswordRequest $request): JsonResponse
    {
        $buyer = $request->user();
        $currentPassword = (string) $request->validated('current_password');

        if (! Hash::check($currentPassword, $buyer->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['現在のパスワードが正しくありません。'],
            ]);
        }

        Auth::guard('web')->logoutOtherDevices($currentPassword);
        $buyer->forceFill([
            'password' => $request->validated('password'),
            'password_changed_at' => now(),
        ])->save();
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return response()->json(['data' => ['changed' => true]]);
    }

    public function verifyEmailChange(string $token): RedirectResponse
    {
        $record = EmailVerificationToken::query()
            ->where('purpose', self::EMAIL_CHANGE_PURPOSE)
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('consumed_at')
            ->whereNull('invalidated_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $record || ! $record->user?->pending_email) {
            return redirect($this->buyerWebUrl('/my-page/member-info?emailChange=invalid'));
        }

        $verified = DB::transaction(function () use ($record): bool {
            $token = EmailVerificationToken::query()->lockForUpdate()->find($record->id);
            $buyer = $token?->user()->lockForUpdate()->first();

            if (! $token || ! $buyer || $token->consumed_at || $token->invalidated_at || $token->expires_at->isPast() || ! $buyer->pending_email) {
                return false;
            }

            if (User::query()->where('email', $buyer->pending_email)->whereKeyNot($buyer->id)->exists()) {
                return false;
            }

            $buyer->update([
                'email' => $buyer->pending_email,
                'pending_email' => null,
                'email_verified_at' => now(),
            ]);
            $token->update(['consumed_at' => now()]);

            return true;
        });

        return redirect($this->buyerWebUrl('/my-page/member-info?emailChange='.($verified ? 'success' : 'invalid')));
    }

    /** @return array<string, mixed> */
    private function profile(User $buyer): array
    {
        $buyer->loadMissing('buyerProfile');
        $address = BuyerAddress::query()->where('buyer_id', $buyer->id)->where('is_default', true)->first();

        return [
            'member_id' => Str::upper(Str::substr($buyer->id, -8)),
            'name' => $buyer->buyerProfile?->name ?? '',
            'name_phonetic' => $buyer->buyerProfile?->name_phonetic ?? '',
            'email' => $buyer->email,
            'pending_email' => $buyer->pending_email,
            'phone' => $buyer->buyerProfile?->phone ?? '',
            'postal_code' => $address?->postal_code ?? '',
            'prefecture' => $address?->prefecture ?? '',
            'city' => $address?->city ?? '',
            'address_line1' => $address?->address_line1 ?? '',
            'address_line2' => $address?->address_line2 ?? '',
        ];
    }

    private function emailVerificationUrl(string $token): string
    {
        return rtrim((string) config('app.url'), '/')
            .'/api/v1/buyer/account/email-verifications/'.rawurlencode($token);
    }

    private function buyerWebUrl(string $path): string
    {
        return rtrim((string) config('app.buyer_web_url'), '/').$path;
    }
}
