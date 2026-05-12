<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

/**
 * Unauthenticated liveness/readiness endpoint.
 *
 * Probes core dependencies (database, redis). External services like MinIO
 * and Meilisearch are checked via separate readiness probes — keeping this
 * cheap so docker-compose healthchecks can poll it frequently.
 */
final class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->check(static function (): bool {
                DB::connection()->getPdo();

                return true;
            }),
            'redis' => $this->check(static fn (): bool => Redis::connection()->ping() !== false),
        ];

        $allOk = ! in_array(false, array_column($checks, 'ok'), true);

        return ApiResponse::success(
            data: [
                'status' => $allOk ? 'ok' : 'degraded',
                'checks' => $checks,
                'version' => config('app.version', 'dev'),
                'time' => now()->toIso8601String(),
            ],
            status: $allOk ? 200 : 503,
        );
    }

    /**
     * @return array{ok: bool, error?: string}
     */
    private function check(callable $probe): array
    {
        try {
            return ['ok' => (bool) $probe()];
        } catch (Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
