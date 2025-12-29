<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
  /**
   * Paginated response
   */
  public static function paginated($paginator, string $message = 'Success', int $code = 200): JsonResponse
  {
    return response()->json([
      'status' => 'success',
      'message' => $message,
      'data' => $paginator->items(),
      'meta' => [
        'total' => $paginator->total(),
        'per_page' => $paginator->perPage(),
        'current_page' => $paginator->currentPage(),
        'last_page' => $paginator->lastPage(),
        'from' => $paginator->firstItem(),
        'to' => $paginator->lastItem(),
      ],
    ], $code);
  }

  /**
   * Success response
   */
  public static function success($data = null, string $message = 'Success', int $code = 200): JsonResponse
  {
    return response()->json([
      'status' => 'success',
      'message' => $message,
      'data' => $data,
    ], $code);
  }

  /**
   * Error response
   */
  public static function error(string $message = 'Error', int $code = 400, $errors = null): JsonResponse
  {
    $response = [
      'status' => 'error',
      'message' => $message,
    ];

    if ($errors !== null) {
      $response['errors'] = $errors;
    }

    return response()->json($response, $code);
  }

  /**
   * Validation error response
   */
  public static function validationError($errors, string $message = 'Validasi gagal'): JsonResponse
  {
    return response()->json([
      'status' => 'error',
      'message' => $message,
      'errors' => $errors,
    ], 422);
  }

  /**
   * Not found response
   */
  public static function notFound(string $message = 'Data tidak ditemukan'): JsonResponse
  {
    return response()->json([
      'status' => 'error',
      'message' => $message,
    ], 404);
  }

  /**
   * Unauthorized response
   */
  public static function unauthorized(string $message = 'Tidak terautentikasi'): JsonResponse
  {
    return response()->json([
      'status' => 'error',
      'message' => $message,
    ], 401);
  }

  /**
   * Forbidden response
   */
  public static function forbidden(string $message = 'Akses ditolak'): JsonResponse
  {
    return response()->json([
      'status' => 'error',
      'message' => $message,
    ], 403);
  }
}
