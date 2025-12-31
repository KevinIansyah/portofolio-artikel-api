<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ApiResponse
{
  /**
   * Return a success JSON response
   *
   * @param mixed $data
   * @param string $message
   * @param int $statusCode
   * @return JsonResponse
   */
  public static function success($data = null, string $message = 'Berhasil', int $statusCode = 200): JsonResponse
  {
    return response()->json([
      'success' => true,
      'message' => $message,
      'data' => $data,
    ], $statusCode);
  }

  /**
   * Return a paginated JSON response
   *
   * @param LengthAwarePaginator $paginator
   * @param string $message
   * @return JsonResponse
   */
  public static function paginated(LengthAwarePaginator $paginator, string $message = 'Berhasil'): JsonResponse
  {
    return response()->json([
      'success' => true,
      'message' => $message,
      'data' => $paginator->items(),
      'meta' => [
        'current_page' => $paginator->currentPage(),
        'from' => $paginator->firstItem(),
        'to' => $paginator->lastItem(),
        'per_page' => $paginator->perPage(),
        'total' => $paginator->total(),
        'last_page' => $paginator->lastPage(),
      ],
      'links' => [
        'first' => $paginator->url(1),
        'last' => $paginator->url($paginator->lastPage()),
        'prev' => $paginator->previousPageUrl(),
        'next' => $paginator->nextPageUrl(),
      ],
    ], 200);
  }

  /**
   * Return a created JSON response (201)
   *
   * @param mixed $data
   * @param string $message
   * @return JsonResponse
   */
  public static function created($data = null, string $message = 'Data berhasil dibuat'): JsonResponse
  {
    return self::success($data, $message, 201);
  }

  /**
   * Return an error JSON response
   *
   * @param string $message
   * @param int $statusCode
   * @param mixed $errors
   * @return JsonResponse
   */
  public static function error(string $message = 'Terjadi kesalahan', int $statusCode = 500, $errors = null): JsonResponse
  {
    $response = [
      'success' => false,
      'message' => $message,
    ];

    if ($errors !== null) {
      $response['errors'] = $errors;
    }

    return response()->json($response, $statusCode);
  }

  /**
   * Return a validation error JSON response (422)
   *
   * @param mixed $errors
   * @param string $message
   * @return JsonResponse
   */
  public static function validationError($errors, string $message = 'Validasi gagal'): JsonResponse
  {
    return response()->json([
      'success' => false,
      'message' => $message,
      'errors' => $errors,
    ], 422);
  }

  /**
   * Return an unauthorized JSON response (401)
   *
   * @param string $message
   * @return JsonResponse
   */
  public static function unauthorized(string $message = 'Anda belum terautentikasi'): JsonResponse
  {
    return self::error($message, 401);
  }

  /**
   * Return a forbidden JSON response (403)
   *
   * @param string $message
   * @return JsonResponse
   */
  public static function forbidden(string $message = 'Akses ditolak'): JsonResponse
  {
    return self::error($message, 403);
  }

  /**
   * Return a not found JSON response (404)
   *
   * @param string $message
   * @return JsonResponse
   */
  public static function notFound(string $message = 'Data tidak ditemukan'): JsonResponse
  {
    return self::error($message, 404);
  }

  /**
   * Return a no content response (204)
   *
   * @return JsonResponse
   */
  public static function noContent(): JsonResponse
  {
    return response()->json(null, 204);
  }
}
