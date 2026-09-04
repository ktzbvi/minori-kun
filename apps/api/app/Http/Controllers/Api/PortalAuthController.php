<?php

namespace App\Http\Controllers\Api;

use App\Enums\AccountState;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\PortalLoginRequest;
use App\Http\Resources\CurrentSessionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalAuthController extends Controller
{
    public function login(PortalLoginRequest $request): CurrentSessionResource|JsonResponse
    {
        $role = UserRole::from((string) $request->route('portal_role'));
        $credentials = [
            'email' => mb_strtolower(trim((string) $request->validated('email'))),
            'password' => $request->validated('password'),
            'role' => $role->value,
            'account_state' => AccountState::Active->value,
        ];

        if (! Auth::attempt($credentials, false)) {
            return response()->json([
                'message' => 'メールアドレスまたはパスワードを確認してください。',
                'code' => 'AUTHENTICATION_FAILED',
                'errors' => new \stdClass,
            ], 422);
        }

        $request->session()->regenerate();
        $request->user()->forceFill(['last_login_at' => now()])->save();

        return new CurrentSessionResource($request->user());
    }

    public function me(Request $request): CurrentSessionResource
    {
        return new CurrentSessionResource($request->user());
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Auth::forgetGuards();

        return response()->json(['data' => ['logged_out' => true]]);
    }
}
