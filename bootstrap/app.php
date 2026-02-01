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
            'locale' => \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
            \App\Http\Middleware\SetLocale::class,
        ]);

        // $middleware->throttleApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $setLocale = function ($request) {
            $locale = $request->header('Accept-Language')
                ?? $request->query('lang')
                ?? 'id';

            $supportedLocales = ['id', 'en'];
            if (!in_array($locale, $supportedLocales)) {
                $locale = 'id';
            }

            app()->setLocale($locale);
        };

        /**
         * 401 UNAUTHENTICATED
         * Priority: Highest - handle before general exceptions
         */
        $exceptions->render(function (AuthenticationException $e, $request) use ($setLocale) {
            if (! $request->is('api/*')) {
                return null;
            }

            $setLocale($request);
            return ApiResponse::unauthorized(__('messages.auth.unauthorized'));
        });

        /**
         * 403 FORBIDDEN
         * Handles authorization failures from policies, gates, and RoleMiddleware
         */
        $exceptions->render(function (AuthorizationException $e, $request) use ($setLocale) {
            if (! $request->is('api/*')) {
                return null;
            }

            $setLocale($request);
            return ApiResponse::forbidden($e->getMessage() ?: __('messages.auth.forbidden'));
        });

        /**
         * 404 MODEL NOT FOUND
         * When Eloquent model is not found (e.g., User::findOrFail())
         */
        $exceptions->render(function (ModelNotFoundException $e, $request) use ($setLocale) {
            if (! $request->is('api/*')) {
                return null;
            }

            $setLocale($request);

            $model = class_basename($e->getModel());

            $messages = [
                'Project'  => __('messages.projects.not_found'),
                'Article'  => __('messages.articles.not_found'),
                'Category' => __('messages.categories.not_found'),
                'User'     => __('messages.general.not_found'),
            ];

            return ApiResponse::notFound(
                $messages[$model] ?? __('messages.general.not_found')
            );
        });

        /**
         * 404 ROUTE NOT FOUND & NESTED MODEL NOT FOUND
         * Handles missing routes and route model binding failures
         */
        $exceptions->render(function (NotFoundHttpException $e, $request) use ($setLocale) {
            if (! $request->is('api/*')) {
                return null;
            }

            $setLocale($request);

            $previous = $e->getPrevious();

            // Check if it's a model not found from route binding
            if ($previous instanceof ModelNotFoundException) {
                $model = class_basename($previous->getModel());

                $messages = [
                    'Project'  => __('messages.projects.not_found'),
                    'Article'  => __('messages.articles.not_found'),
                    'Category' => __('messages.categories.not_found'),
                    'User'     => __('messages.general.not_found'),
                ];

                return ApiResponse::notFound(
                    $messages[$model] ?? __('messages.general.not_found')
                );
            }

            return ApiResponse::notFound(__('messages.general.endpoint_not_found'));
        });

        /**
         * 405 METHOD NOT ALLOWED
         * When using wrong HTTP method (e.g., GET on POST-only route)
         */
        $exceptions->render(function (MethodNotAllowedHttpException $e, $request) use ($setLocale) {
            if (! $request->is('api/*')) {
                return null;
            }

            $setLocale($request);
            return ApiResponse::methodNotAllowed(
                __('messages.general.method_not_allowed'),
                405
            );
        });

        /**
         * 422 VALIDATION ERROR
         * From FormRequest or Validator failures
         */
        $exceptions->render(function (ValidationException $e, $request) use ($setLocale) {
            if (! $request->is('api/*')) {
                return null;
            }

            $setLocale($request);
            return ApiResponse::validationError(
                $e->errors(),
                __('messages.general.validation_error')
            );
        });

        /**
         * 429 TOO MANY REQUESTS
         * Rate limiting exceeded
         */
        $exceptions->render(function (ThrottleRequestsException $e, $request) use ($setLocale) {
            if (! $request->is('api/*')) {
                return null;
            }

            $setLocale($request);
            return ApiResponse::rateLimited(
                __('messages.general.too_many_requests'),
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
        $exceptions->render(function (QueryException $e, $request) use ($setLocale) {
            if (! $request->is('api/*') || config('app.debug')) {
                return null;
            }

            $setLocale($request);

            // Duplicate entry error
            if ($e->getCode() === '23000') {
                return ApiResponse::conflict(
                    __('messages.general.duplicate_entry'),
                    409
                );
            }

            // Foreign key constraint
            if (str_contains($e->getMessage(), 'foreign key constraint')) {
                return ApiResponse::conflict(
                    __('messages.general.relation_constraint'),
                    409
                );
            }

            return ApiResponse::serverError(
                __('messages.general.server_error'),
                500
            );
        });

        /**
         * GENERAL HTTP EXCEPTIONS
         * Handles other HTTP exceptions (400, 403, 500, etc.)
         */
        $exceptions->render(function (HttpException $e, $request) use ($setLocale) {
            if (! $request->is('api/*')) {
                return null;
            }

            $setLocale($request);

            $statusCode = $e->getStatusCode();
            $message = $e->getMessage() ?: __('messages.general.error');

            return ApiResponse::error($message, $statusCode);
        });

        /**
         * 500 INTERNAL SERVER ERROR
         * Catch-all for any other exceptions
         * MUST BE LAST
         */
        $exceptions->render(function (Throwable $e, $request) use ($setLocale) {
            if (! $request->is('api/*') || config('app.debug')) {
                return null;
            }

            $setLocale($request);

            // Log the error for debugging
            Log::error('Unhandled exception', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return ApiResponse::serverError(
                __('messages.general.server_error'),
                500
            );
        });
    })
    ->create();
