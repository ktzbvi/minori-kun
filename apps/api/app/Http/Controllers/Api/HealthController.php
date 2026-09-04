<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

class HealthController extends Controller
{
    public function live(): JsonResponse
    {
        return response()->json(['data' => ['status' => 'ok']]);
    }

    public function ready(): JsonResponse
    {
        try {
            DB::select('select 1');
            if (! app()->environment('testing')) {
                Redis::connection()->ping();
            }
            if (! is_writable(storage_path())) {
                throw new \RuntimeException('Storage is not writable.');
            }

            return response()->json(['data' => ['status' => 'ready']]);
        } catch (Throwable) {
            return response()->json(['message' => 'Service is not ready.', 'code' => 'NOT_READY', 'errors' => new \stdClass], 503);
        }
    }
}
