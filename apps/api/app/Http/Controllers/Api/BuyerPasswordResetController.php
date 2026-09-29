<?php

namespace App\Http\Controllers\Api;

use App\Enums\AccountState;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteBuyerPasswordResetRequest;
use App\Http\Requests\StartBuyerPasswordResetRequest;
use App\Mail\BuyerPasswordResetMail;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class BuyerPasswordResetController extends Controller
{
    public function start(StartBuyerPasswordResetRequest $request): JsonResponse
    {
        $email = mb_strtolower(trim((string) $request->validated('email')));
        $buyer = User::query()
            ->where('email', $email)
            ->where('role', UserRole::Buyer->value)
            ->where('account_state', AccountState::Active->value)
            ->first();

        if ($buyer) {
            $token = Str::random(64);

            DB::transaction(function () use ($buyer, $token): void {
                PasswordResetToken::query()->where('user_id', $buyer->id)->delete();
                PasswordResetToken::query()->create([
                    'user_id' => $buyer->id,
                    'token_hash' => hash('sha256', $token),
                    'expires_at' => now()->addMinutes(30),
                ]);
            });

            Mail::to($buyer->email)->send(new BuyerPasswordResetMail($this->resetUrl($email, $token)));
        }

        return response()->json([
            'data' => ['message' => 'パスワード再設定用メールを送信しました。'],
        ], 202);
    }

    public function complete(CompleteBuyerPasswordResetRequest $request): JsonResponse
    {
        $email = mb_strtolower(trim((string) $request->validated('email')));
        $tokenHash = hash('sha256', (string) $request->validated('token'));

        $completed = DB::transaction(function () use ($email, $tokenHash, $request): bool {
            $buyer = User::query()
                ->where('email', $email)
                ->where('role', UserRole::Buyer->value)
                ->where('account_state', AccountState::Active->value)
                ->lockForUpdate()
                ->first();

            if (! $buyer) {
                return false;
            }

            $resetToken = PasswordResetToken::query()
                ->where('user_id', $buyer->id)
                ->where('token_hash', $tokenHash)
                ->whereNull('consumed_at')
                ->where('expires_at', '>', now())
                ->lockForUpdate()
                ->first();

            if (! $resetToken) {
                return false;
            }

            $buyer->forceFill([
                'password' => $request->validated('password'),
                'password_changed_at' => now(),
            ])->save();
            $resetToken->update(['consumed_at' => now()]);

            return true;
        });

        if (! $completed) {
            return response()->json([
                'message' => '再設定用リンクが無効または有効期限切れです。もう一度メールを送信してください。',
                'code' => 'PASSWORD_RESET_TOKEN_INVALID',
            ], 422);
        }

        return response()->json(['data' => ['redirect' => '/login']]);
    }

    private function resetUrl(string $email, string $token): string
    {
        $origin = rtrim((string) config('app.buyer_web_url'), '/');

        return $origin.'/password-reset/confirm?'.http_build_query([
            'email' => $email,
            'token' => $token,
        ]);
    }
}
