<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ApiResponse
{
  /**
   * Return a success JSON response
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
   * Return an error JSON response
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
   * Return a paginated JSON response
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
   */
  public static function created($data = null, string $message = 'Data berhasil dibuat'): JsonResponse
  {
    return self::success($data, $message, 201);
  }

  /**
   * Return an updated JSON response (200)
   */
  public static function updated($data = null, string $message = 'Data berhasil diperbarui'): JsonResponse
  {
    return self::success($data, $message, 200);
  }

  /**
   * Return a deleted JSON response (200)
   */
  public static function deleted(string $message = 'Data berhasil dihapus'): JsonResponse
  {
    return self::success(null, $message, 200);
  }

  /**
   * Return a no content response (204)
   */
  public static function noContent(string $message = 'Tidak ada konten'): JsonResponse
  {
    return self::success(null, $message, 204);
  }

  /**
   * Return a validation error JSON response (422)
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
   * Return a rate limited JSON response (429)
   */
  public static function rateLimited(string $message = 'Terlalu banyak permintaan'): JsonResponse
  {
    return self::error($message, 429);
  }

  /**
   * Return an unauthorized JSON response (401)
   */
  public static function unauthorized(string $message = 'Anda belum terautentikasi'): JsonResponse
  {
    return self::error($message, 401);
  }

  /**
   * Return a forbidden JSON response (403)
   */
  public static function forbidden(string $message = 'Akses ditolak'): JsonResponse
  {
    return self::error($message, 403);
  }

  /**
   * Return a not found JSON response (404)
   */
  public static function notFound(string $message = 'Data tidak ditemukan'): JsonResponse
  {
    return self::error($message, 404);
  }

  /**
   * Return a method not allowed JSON response (405)
   */
  public static function methodNotAllowed(string $message = 'Method tidak diizinkan'): JsonResponse
  {
    return self::error($message, 405);
  }

  /**
   * Return a conflict error JSON response (409)
   */
  public static function conflict(string $message = 'Data sudah ada'): JsonResponse
  {
    return self::error($message, 409);
  }

  /**
   * Return a server error JSON response (500)
   */
  public static function serverError(string $message = 'Terjadi kesalahan pada server'): JsonResponse
  {
    return self::error($message, 500);
  }
}
