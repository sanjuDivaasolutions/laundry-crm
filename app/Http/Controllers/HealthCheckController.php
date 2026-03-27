<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthCheckController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [];
        $debug = config('app.debug');

        // Database connectivity
        try {
            DB::connection()->getPdo();
            $checks['database'] = 'ok';
        } catch (\Exception $e) {
            $checks['database'] = $debug ? 'failed: '.$e->getMessage() : 'failed';
        }

        // Cache read/write
        try {
            Cache::put('health-check', true, 10);
            Cache::get('health-check');
            $checks['cache'] = 'ok';
        } catch (\Exception $e) {
            $checks['cache'] = $debug ? 'failed: '.$e->getMessage() : 'failed';
        }

        $allOk = ! collect($checks)->contains(fn ($v) => str_starts_with($v, 'failed'));

        return response()->json([
            'status' => $allOk ? 'healthy' : 'degraded',
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
        ], $allOk ? 200 : 503);
    }
}
