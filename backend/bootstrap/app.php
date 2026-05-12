<?php

declare(strict_types=1);

use App\Exceptions\ApiException;
use App\Http\Middleware\RequestId;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Prepend RequestId so every other middleware (and log line) sees the id.
        $middleware->append(RequestId::class);

        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Render all API exceptions through the standard error envelope.
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null; // let the default renderer handle non-API traffic
            }

            return match (true) {
                $e instanceof ValidationException => ApiResponse::error(
                    code: 'VALIDATION_ERROR',
                    message: $e->getMessage(),
                    details: $e->errors(),
                    status: 422,
                ),
                $e instanceof AuthenticationException => ApiResponse::error(
                    code: 'UNAUTHENTICATED',
                    message: $e->getMessage(),
                    status: 401,
                ),
                $e instanceof AuthorizationException => ApiResponse::error(
                    code: 'FORBIDDEN',
                    message: $e->getMessage() !== '' ? $e->getMessage() : 'This action is unauthorized.',
                    status: 403,
                ),
                $e instanceof ModelNotFoundException, $e instanceof NotFoundHttpException => ApiResponse::error(
                    code: 'NOT_FOUND',
                    message: 'Resource not found.',
                    status: 404,
                ),
                $e instanceof ApiException => ApiResponse::error(
                    code: $e->errorCode,
                    message: $e->getMessage(),
                    details: $e->details,
                    status: $e->status,
                ),
                $e instanceof HttpExceptionInterface => ApiResponse::error(
                    code: 'HTTP_ERROR',
                    message: $e->getMessage() !== '' ? $e->getMessage() : 'Request failed.',
                    status: $e->getStatusCode(),
                ),
                default => ApiResponse::error(
                    code: 'SERVER_ERROR',
                    message: config('app.debug') ? $e->getMessage() : 'An unexpected error occurred.',
                    details: config('app.debug') ? ['exception' => $e::class, 'file' => $e->getFile(), 'line' => $e->getLine()] : [],
                    status: 500,
                ),
            };
        });
    })->create();
