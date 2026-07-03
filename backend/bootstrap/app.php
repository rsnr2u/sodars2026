<?php

use App\Core\Exceptions\ApiException;
use App\Core\Exceptions\BusinessException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        __DIR__.'/../app/Core/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\CorrelationIdMiddleware::class);
        $middleware->api(append: [
            \App\Http\Middleware\IdempotencyMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $statusCode = 500;
                $message = $e->getMessage();
                $errors = null;

                if ($e instanceof ValidationException) {
                    $statusCode = 422;
                    $message = 'Validation failed.';
                    $errors = $e->errors();
                } elseif ($e instanceof BusinessException) {
                    $statusCode = $e->getStatusCode();
                    $message = $e->getMessage();
                } elseif ($e instanceof ApiException) {
                    $statusCode = $e->getStatusCode();
                    $message = $e->getMessage();
                    $errors = $e->getErrors();
                } elseif ($e instanceof AuthenticationException) {
                    $statusCode = 401;
                    $message = 'Unauthenticated.';
                } elseif ($e instanceof ModelNotFoundException) {
                    $statusCode = 404;
                    $message = 'Resource not found.';
                } elseif ($e instanceof HttpExceptionInterface) {
                    $statusCode = $e->getStatusCode();
                    $message = $e->getMessage();
                }

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'data' => null,
                    'errors' => $errors,
                    'meta' => [
                        'correlation_id' => \App\Core\Context\TraceContext::correlationId(),
                    ],
                ], $statusCode);
            }
        });
    })->create();
