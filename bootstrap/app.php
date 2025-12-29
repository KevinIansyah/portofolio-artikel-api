<?php

use App\Helpers\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        // Throttle API requests
        $middleware->throttleApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        /**
         * 401 UNAUTHENTICATED
         * Priority: Highest - handle before general exceptions
         */
        $exceptions->render(function (AuthenticationException $e, $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::unauthorized('Anda belum terautentikasi');
        });

        /**
         * 403 FORBIDDEN
         * Handles authorization failures from policies, gates, and RoleMiddleware
         */
        $exceptions->render(function (AuthorizationException $e, $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::forbidden($e->getMessage() ?: 'Akses ditolak');
        });

        /**
         * 404 MODEL NOT FOUND
         * When Eloquent model is not found (e.g., User::findOrFail())
         */
        $exceptions->render(function (ModelNotFoundException $e, $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $model = class_basename($e->getModel());

            $messages = [
                'Project'  => 'Project tidak ditemukan',
                'Article'  => 'Artikel tidak ditemukan',
                'Category' => 'Kategori tidak ditemukan',
                'User'     => 'User tidak ditemukan',
                'Recipe'   => 'Resep tidak ditemukan',
            ];

            return ApiResponse::notFound(
                $messages[$model] ?? 'Data tidak ditemukan'
            );
        });

        /**
         * 404 ROUTE NOT FOUND & NESTED MODEL NOT FOUND
         * Handles missing routes and route model binding failures
         */
        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $previous = $e->getPrevious();

            if ($previous instanceof ModelNotFoundException) {
                $model = class_basename($previous->getModel());

                $messages = [
                    'Project'  => 'Project tidak ditemukan',
                    'Article'  => 'Artikel tidak ditemukan',
                    'Category' => 'Kategori tidak ditemukan',
                    'User'     => 'User tidak ditemukan',
                    'Recipe'   => 'Resep tidak ditemukan',
                ];

                return ApiResponse::notFound(
                    $messages[$model] ?? 'Data tidak ditemukan'
                );
            }

            return ApiResponse::notFound('Endpoint tidak ditemukan');
        });

        /**
         * 405 METHOD NOT ALLOWED
         * When using wrong HTTP method (e.g., GET on POST-only route)
         */
        $exceptions->render(function (MethodNotAllowedHttpException $e, $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::error(
                'Metode HTTP tidak diizinkan',
                405
            );
        });

        /**
         * 422 VALIDATION ERROR
         * From FormRequest or Validator failures
         */
        $exceptions->render(function (ValidationException $e, $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::validationError(
                $e->errors(),
                'Validasi gagal'
            );
        });

        /**
         * 429 TOO MANY REQUESTS
         * Rate limiting exceeded
         */
        $exceptions->render(function (ThrottleRequestsException $e, $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::error(
                'Terlalu banyak permintaan. Silakan coba lagi nanti.',
                429,
                [
                    'retry_after' => $e->getHeaders()['Retry-After'] ?? null,
                ]
            );
        });

        /**
         * DATABASE ERRORS
         * Handles database connection and query errors
         */
        $exceptions->render(function (QueryException $e, $request) {
            if (! $request->is('api/*') || config('app.debug')) {
                return null;
            }

            // Duplicate entry error
            if ($e->getCode() === '23000') {
                return ApiResponse::error(
                    'Data sudah ada dalam database',
                    409
                );
            }

            // Foreign key constraint
            if (str_contains($e->getMessage(), 'foreign key constraint')) {
                return ApiResponse::error(
                    'Tidak dapat menghapus data karena masih digunakan',
                    409
                );
            }

            return ApiResponse::error(
                'Terjadi kesalahan pada database',
                500
            );
        });

        /**
         * GENERAL HTTP EXCEPTIONS
         * Handles other HTTP exceptions (400, 403, 500, etc.)
         */
        $exceptions->render(function (HttpException $e, $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $statusCode = $e->getStatusCode();
            $message = $e->getMessage() ?: 'Terjadi kesalahan';

            return ApiResponse::error($message, $statusCode);
        });

        /**
         * 500 INTERNAL SERVER ERROR
         * Catch-all for any other exceptions
         * MUST BE LAST
         */
        $exceptions->render(function (Throwable $e, $request) {
            if (! $request->is('api/*') || config('app.debug')) {
                return null;
            }

            // For Development: Log the exception details
            Log::error('Unhandled exception', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return ApiResponse::error(
                'Terjadi kesalahan pada server',
                500
            );
        });
    })
    ->create();
