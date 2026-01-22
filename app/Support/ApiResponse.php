<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Standardized API response builder.
 *
 * Provides consistent JSON envelope structure for all API responses:
 *
 * Success:
 * {
 *   "success": true,
 *   "data": { ... },
 *   "meta": { "pagination": { ... } },
 *   "message": "Optional success message"
 * }
 *
 * Error:
 * {
 *   "success": false,
 *   "error": {
 *     "code": "error_code",
 *     "message": "Human readable message",
 *     "details": { ... }
 *   }
 * }
 */
class ApiResponse
{
    /**
     * Return a success response with data.
     *
     * @param mixed $data The response data
     * @param string|null $message Optional success message
     * @param int $status HTTP status code
     * @param array $headers Additional headers
     * @return JsonResponse
     */
    public static function success(
        mixed $data = null,
        ?string $message = null,
        int $status = 200,
        array $headers = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'data' => $data,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        return response()->json($response, $status, $headers);
    }

    /**
     * Return a success response with paginated data.
     *
     * @param LengthAwarePaginator $paginator
     * @param string|null $message Optional success message
     * @return JsonResponse
     */
    public static function paginated(
        LengthAwarePaginator $paginator,
        ?string $message = null
    ): JsonResponse {
        $response = [
            'success' => true,
            'data' => $paginator->items(),
            'meta' => [
                'pagination' => [
                    'total' => $paginator->total(),
                    'count' => $paginator->count(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'total_pages' => $paginator->lastPage(),
                    'has_more_pages' => $paginator->hasMorePages(),
                ],
            ],
        ];

        if ($message) {
            $response['message'] = $message;
        }

        return response()->json($response);
    }

    /**
     * Return a success response with a resource.
     *
     * @param JsonResource $resource
     * @param string|null $message Optional success message
     * @param int $status HTTP status code
     * @return JsonResponse
     */
    public static function resource(
        JsonResource $resource,
        ?string $message = null,
        int $status = 200
    ): JsonResponse {
        $response = [
            'success' => true,
            'data' => $resource,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        return response()->json($response, $status);
    }

    /**
     * Return a success response with a resource collection.
     *
     * @param ResourceCollection $collection
     * @param string|null $message Optional success message
     * @return JsonResponse
     */
    public static function collection(
        ResourceCollection $collection,
        ?string $message = null
    ): JsonResponse {
        // Check if the collection is paginated
        $resource = $collection->resource;

        if ($resource instanceof LengthAwarePaginator) {
            $response = [
                'success' => true,
                'data' => $collection,
                'meta' => [
                    'pagination' => [
                        'total' => $resource->total(),
                        'count' => $resource->count(),
                        'per_page' => $resource->perPage(),
                        'current_page' => $resource->currentPage(),
                        'total_pages' => $resource->lastPage(),
                        'has_more_pages' => $resource->hasMorePages(),
                    ],
                ],
            ];
        } else {
            $response = [
                'success' => true,
                'data' => $collection,
            ];
        }

        if ($message) {
            $response['message'] = $message;
        }

        return response()->json($response);
    }

    /**
     * Return a created response (201).
     *
     * @param mixed $data The created resource data
     * @param string|null $message Optional success message
     * @return JsonResponse
     */
    public static function created(
        mixed $data = null,
        ?string $message = 'Resource created successfully'
    ): JsonResponse {
        return static::success($data, $message, 201);
    }

    /**
     * Return a no content response (204).
     *
     * @return JsonResponse
     */
    public static function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Return an error response.
     *
     * @param string $message Human-readable error message
     * @param string $code Machine-readable error code
     * @param int $status HTTP status code
     * @param array|null $details Additional error details
     * @param array $headers Additional headers
     * @return JsonResponse
     */
    public static function error(
        string $message,
        string $code = 'error',
        int $status = 400,
        ?array $details = null,
        array $headers = []
    ): JsonResponse {
        $error = [
            'code' => $code,
            'message' => $message,
        ];

        if ($details !== null) {
            $error['details'] = $details;
        }

        return response()->json([
            'success' => false,
            'error' => $error,
        ], $status, $headers);
    }

    /**
     * Return a validation error response (422).
     *
     * @param array $errors Validation errors (field => [messages])
     * @param string $message Optional overall message
     * @return JsonResponse
     */
    public static function validationError(
        array $errors,
        string $message = 'The given data was invalid.'
    ): JsonResponse {
        return static::error(
            $message,
            'validation_error',
            422,
            ['fields' => $errors]
        );
    }

    /**
     * Return an unauthorized error response (401).
     *
     * @param string $message Error message
     * @return JsonResponse
     */
    public static function unauthorized(
        string $message = 'Unauthorized'
    ): JsonResponse {
        return static::error($message, 'unauthorized', 401);
    }

    /**
     * Return a forbidden error response (403).
     *
     * @param string $message Error message
     * @return JsonResponse
     */
    public static function forbidden(
        string $message = 'Forbidden'
    ): JsonResponse {
        return static::error($message, 'forbidden', 403);
    }

    /**
     * Return a not found error response (404).
     *
     * @param string $message Error message
     * @return JsonResponse
     */
    public static function notFound(
        string $message = 'Resource not found'
    ): JsonResponse {
        return static::error($message, 'not_found', 404);
    }

    /**
     * Return a quota exceeded error response (403).
     *
     * @param string $quotaCode The quota that was exceeded
     * @param int $limit The quota limit
     * @param int $usage Current usage
     * @return JsonResponse
     */
    public static function quotaExceeded(
        string $quotaCode,
        int $limit,
        int $usage
    ): JsonResponse {
        return static::error(
            "You have exceeded the {$quotaCode} limit. Please upgrade your plan.",
            'quota_exceeded',
            403,
            [
                'quota' => $quotaCode,
                'limit' => $limit,
                'usage' => $usage,
            ]
        );
    }

    /**
     * Return a tenant inactive error response (403).
     *
     * @param string $message Error message
     * @return JsonResponse
     */
    public static function tenantInactive(
        string $message = 'Your account has been deactivated. Please contact support.'
    ): JsonResponse {
        return static::error($message, 'tenant_inactive', 403);
    }

    /**
     * Return a subscription required error response (402).
     *
     * @param string $message Error message
     * @return JsonResponse
     */
    public static function subscriptionRequired(
        string $message = 'An active subscription is required to access this feature.'
    ): JsonResponse {
        return static::error($message, 'subscription_required', 402);
    }

    /**
     * Return a server error response (500).
     *
     * @param string $message Error message (sanitized for production)
     * @param \Throwable|null $exception Optional exception for logging
     * @return JsonResponse
     */
    public static function serverError(
        string $message = 'An unexpected error occurred',
        ?\Throwable $exception = null
    ): JsonResponse {
        // In production, don't expose exception details
        if (app()->environment('production') && $exception) {
            $message = 'An unexpected error occurred. Please try again later.';
        }

        return static::error($message, 'server_error', 500);
    }
}
