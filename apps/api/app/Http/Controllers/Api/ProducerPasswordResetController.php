<?php

namespace App\Http\Controllers\Api;

use App\Domain\Identity\ProducerPasswordResetService;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteProducerPasswordResetRequest;
use App\Http\Requests\ProducerPasswordResetRequest;
use App\Http\Requests\ValidateProducerPasswordResetRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProducerPasswordResetController extends Controller
{
    public function start(ProducerPasswordResetRequest $request, ProducerPasswordResetService $service): JsonResponse
    {
        $service->start($request->validated('email'));

        return response()->json(['data' => ['message' => '該当するアカウントがある場合、パスワード再設定用メールを送信しました。']], 202);
    }

    public function validateToken(ValidateProducerPasswordResetRequest $request, ProducerPasswordResetService $service): JsonResponse
    {
        return $service->valid($request->validated('email'), $request->validated('token'))
            ? response()->json(['data' => ['valid' => true]]) : $this->invalid();
    }

    public function complete(CompleteProducerPasswordResetRequest $request, ProducerPasswordResetService $service): JsonResponse
    {
        $id = $service->complete($request->validated('email'), $request->validated('token'), $request->validated('password'));
        if (! $id) {
            return $this->invalid();
        }
        if (Auth::id() === $id) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json(['data' => ['redirect' => '/login']]);
    }

    private function invalid(): JsonResponse
    {
        return response()->json(['message' => '再設定用リンクが無効または有効期限切れです。もう一度メールを送信してください。', 'code' => 'PASSWORD_RESET_TOKEN_INVALID'], 422);
    }
}
