<?php

use App\Helpers\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        /**
         * 404 DATA (MODEL NOT FOUND)
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
            ];

            return ApiResponse::notFound(
                $messages[$model] ?? 'Data tidak ditemukan'
            );
        });

        /**
         * 401 UNAUTHENTICATED
         */
        $exceptions->render(function (AuthenticationException $e, $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::unauthorized('Anda belum terautentikasi');
        });

        /**
         * 403 FORBIDDEN
         */
        $exceptions->render(function (AuthorizationException $e, $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::forbidden('Akses ditolak');
        });

        /**
         * 404 ENDPOINT OR MODEL NOT FOUND
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
                ];

                return ApiResponse::notFound(
                    $messages[$model] ?? 'Data tidak ditemukan'
                );
            }

            return ApiResponse::notFound('Endpoint tidak ditemukan');
        });


        /**
         * 422 VALIDATION ERROR
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
         * 500 INTERNAL SERVER ERROR
         */
        $exceptions->render(function (Throwable $e, $request) {
            if (! $request->is('api/*') || config('app.debug')) {
                return null;
            }

            return ApiResponse::error(
                'Terjadi kesalahan pada server',
                500
            );
        });
    })
    ->create();
