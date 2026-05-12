<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Builds the project's standard JSON response envelope.
 *
 * Success:
 *   { "success": true,  "data": ..., "message": ..., "meta": { ... } }
 *
 * Error:
 *   { "success": false, "error": { "code": ..., "message": ..., "details": [...] } }
 */
final class ApiResponse
{
    /**
     * @param  array<string, mixed>|JsonResource|null  $data
     * @param  array<string, mixed>  $meta
     */
    public static function success(
        array|JsonResource|null $data = null,
        ?string $message = null,
        array $meta = [],
        int $status = 200,
    ): JsonResponse {
        $payload = [
            'success' => true,
            'data' => $data instanceof JsonResource ? $data->resolve() : $data,
        ];

        if ($message !== null) {
            $payload['message'] = $message;
        }

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        return new JsonResponse($payload, $status);
    }

    /**
     * Wraps a paginator into the envelope with pagination meta.
     *
     * @param  LengthAwarePaginator<int, mixed>|ResourceCollection  $paginator
     */
    public static function paginated(
        LengthAwarePaginator|ResourceCollection $paginator,
        ?string $message = null,
    ): JsonResponse {
        if ($paginator instanceof ResourceCollection) {
            /** @var LengthAwarePaginator<int, mixed> $underlying */
            $underlying = $paginator->resource;
            $items = $paginator->collection?->all() ?? [];
        } else {
            $underlying = $paginator;
            $items = $paginator->items();
        }

        return self::success(
            data: ['items' => $items],
            message: $message,
            meta: [
                'pagination' => [
                    'total' => $underlying->total(),
                    'per_page' => $underlying->perPage(),
                    'current_page' => $underlying->currentPage(),
                    'last_page' => $underlying->lastPage(),
                    'from' => $underlying->firstItem(),
                    'to' => $underlying->lastItem(),
                ],
            ],
        );
    }

    /**
     * @param  array<int|string, mixed>  $details
     * @param  array<string, string>  $headers
     */
    public static function error(
        string $code,
        string $message,
        array $details = [],
        int $status = 400,
        array $headers = [],
    ): JsonResponse {
        $payload = [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ];

        if ($details !== []) {
            $payload['error']['details'] = $details;
        }

        return new JsonResponse($payload, $status, $headers);
    }
}
