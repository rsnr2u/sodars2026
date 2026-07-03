<?php

declare(strict_types=1);

namespace App\Core\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{
    /**
     * Return a success JSON response.
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = Response::HTTP_OK,
        mixed $meta = null
    ): JsonResponse {
        $metaArray = array_merge([
            'correlation_id' => \App\Core\Context\TraceContext::correlationId(),
        ], is_array($meta) ? $meta : []);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
            'meta' => $metaArray,
        ], $statusCode);
    }

    /**
     * Return an error JSON response.
     */
    protected function errorResponse(
        string $message = 'Error occurred',
        mixed $errors = null,
        int $statusCode = Response::HTTP_BAD_REQUEST,
        mixed $meta = null
    ): JsonResponse {
        $metaArray = array_merge([
            'correlation_id' => \App\Core\Context\TraceContext::correlationId(),
        ], is_array($meta) ? $meta : []);

        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
            'meta' => $metaArray,
        ], $statusCode);
    }
}
