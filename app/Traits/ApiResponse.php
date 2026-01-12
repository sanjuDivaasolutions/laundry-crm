<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{
    /**
     * Success Response
     */
    protected function success(mixed $data = null, ?string $message = null, int $code = Response::HTTP_OK): JsonResponse
    {
        if ($data instanceof JsonResource) {
            return $data->additional([
                'status' => true,
                'message' => $message,
            ])->response()->setStatusCode($code);
        }

        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Error Response
     */
    protected function error(string $message, int $code = Response::HTTP_BAD_REQUEST, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    /**
     * Validation Error Response
     */
    protected function validationError(mixed $errors): JsonResponse
    {
        return $this->error('Validation failed', Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }

    /**
     * Not Found Response
     */
    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, Response::HTTP_NOT_FOUND);
    }
}
