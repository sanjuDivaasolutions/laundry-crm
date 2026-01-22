<?php

declare(strict_types=1);

use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

describe('ARCH-002: Standardized API Response Envelope', function () {

    describe('Success Responses', function () {

        it('returns success response with correct structure', function () {
            $response = ApiResponse::success(['id' => 1, 'name' => 'Test']);

            expect($response)->toBeInstanceOf(JsonResponse::class);
            expect($response->getStatusCode())->toBe(200);

            $data = $response->getData(true);
            expect($data)->toHaveKey('success', true);
            expect($data)->toHaveKey('data');
            expect($data['data'])->toBe(['id' => 1, 'name' => 'Test']);
        });

        it('includes optional message in success response', function () {
            $response = ApiResponse::success(['id' => 1], 'Resource retrieved');

            $data = $response->getData(true);
            expect($data)->toHaveKey('message', 'Resource retrieved');
        });

        it('supports custom status codes', function () {
            $response = ApiResponse::success(null, null, 202);

            expect($response->getStatusCode())->toBe(202);
        });

        it('returns created response with 201 status', function () {
            $response = ApiResponse::created(['id' => 1]);

            expect($response->getStatusCode())->toBe(201);

            $data = $response->getData(true);
            expect($data['success'])->toBeTrue();
            expect($data['message'])->toBe('Resource created successfully');
        });

        it('returns no content response with 204 status', function () {
            $response = ApiResponse::noContent();

            expect($response->getStatusCode())->toBe(204);
        });

    });

    describe('Paginated Responses', function () {

        it('returns paginated response with meta information', function () {
            $items = collect([
                ['id' => 1, 'name' => 'Item 1'],
                ['id' => 2, 'name' => 'Item 2'],
            ]);

            $paginator = new LengthAwarePaginator(
                $items,
                total: 50,
                perPage: 2,
                currentPage: 1
            );

            $response = ApiResponse::paginated($paginator);

            $data = $response->getData(true);

            expect($data['success'])->toBeTrue();
            expect($data)->toHaveKey('data');
            expect($data)->toHaveKey('meta');
            expect($data['meta'])->toHaveKey('pagination');

            $pagination = $data['meta']['pagination'];
            expect($pagination['total'])->toBe(50);
            expect($pagination['per_page'])->toBe(2);
            expect($pagination['current_page'])->toBe(1);
            expect($pagination['total_pages'])->toBe(25);
            expect($pagination['has_more_pages'])->toBeTrue();
        });

    });

    describe('Error Responses', function () {

        it('returns error response with correct structure', function () {
            $response = ApiResponse::error('Something went wrong', 'general_error');

            expect($response->getStatusCode())->toBe(400);

            $data = $response->getData(true);
            expect($data['success'])->toBeFalse();
            expect($data)->toHaveKey('error');
            expect($data['error']['code'])->toBe('general_error');
            expect($data['error']['message'])->toBe('Something went wrong');
        });

        it('includes error details when provided', function () {
            $response = ApiResponse::error(
                'Validation failed',
                'validation_error',
                422,
                ['field' => 'email', 'issue' => 'invalid format']
            );

            $data = $response->getData(true);
            expect($data['error'])->toHaveKey('details');
            expect($data['error']['details']['field'])->toBe('email');
        });

        it('returns validation error with 422 status', function () {
            $errors = [
                'email' => ['The email field is required.'],
                'name' => ['The name must be at least 3 characters.'],
            ];

            $response = ApiResponse::validationError($errors);

            expect($response->getStatusCode())->toBe(422);

            $data = $response->getData(true);
            expect($data['error']['code'])->toBe('validation_error');
            expect($data['error']['details']['fields'])->toBe($errors);
        });

        it('returns unauthorized error with 401 status', function () {
            $response = ApiResponse::unauthorized('Invalid credentials');

            expect($response->getStatusCode())->toBe(401);

            $data = $response->getData(true);
            expect($data['error']['code'])->toBe('unauthorized');
        });

        it('returns forbidden error with 403 status', function () {
            $response = ApiResponse::forbidden('Access denied');

            expect($response->getStatusCode())->toBe(403);

            $data = $response->getData(true);
            expect($data['error']['code'])->toBe('forbidden');
        });

        it('returns not found error with 404 status', function () {
            $response = ApiResponse::notFound('User not found');

            expect($response->getStatusCode())->toBe(404);

            $data = $response->getData(true);
            expect($data['error']['code'])->toBe('not_found');
        });

    });

    describe('SaaS-Specific Responses', function () {

        it('returns quota exceeded error with quota details', function () {
            $response = ApiResponse::quotaExceeded('api_calls', 1000, 1000);

            expect($response->getStatusCode())->toBe(403);

            $data = $response->getData(true);
            expect($data['error']['code'])->toBe('quota_exceeded');
            expect($data['error']['details']['quota'])->toBe('api_calls');
            expect($data['error']['details']['limit'])->toBe(1000);
            expect($data['error']['details']['usage'])->toBe(1000);
        });

        it('returns tenant inactive error', function () {
            $response = ApiResponse::tenantInactive();

            expect($response->getStatusCode())->toBe(403);

            $data = $response->getData(true);
            expect($data['error']['code'])->toBe('tenant_inactive');
        });

        it('returns subscription required error with 402 status', function () {
            $response = ApiResponse::subscriptionRequired();

            expect($response->getStatusCode())->toBe(402);

            $data = $response->getData(true);
            expect($data['error']['code'])->toBe('subscription_required');
        });

        it('returns server error with 500 status', function () {
            $response = ApiResponse::serverError('Database connection failed');

            expect($response->getStatusCode())->toBe(500);

            $data = $response->getData(true);
            expect($data['error']['code'])->toBe('server_error');
        });

        it('sanitizes server error message in production', function () {
            // Simulate production environment
            app()->detectEnvironment(fn() => 'production');

            $exception = new \Exception('SQL Error: syntax error at line 42');
            $response = ApiResponse::serverError('Detailed error', $exception);

            $data = $response->getData(true);

            // Should not expose detailed error in production
            expect($data['error']['message'])->not->toContain('SQL Error');
        });

    });

    describe('Response Consistency', function () {

        it('always includes success boolean', function () {
            $successResponse = ApiResponse::success(['test' => true]);
            $errorResponse = ApiResponse::error('Error');

            expect($successResponse->getData(true))->toHaveKey('success', true);
            expect($errorResponse->getData(true))->toHaveKey('success', false);
        });

        it('success responses always have data key', function () {
            $response = ApiResponse::success(null);

            $data = $response->getData(true);
            expect($data)->toHaveKey('data');
            expect($data['data'])->toBeNull();
        });

        it('error responses always have error object', function () {
            $response = ApiResponse::error('Test error');

            $data = $response->getData(true);
            expect($data)->toHaveKey('error');
            expect($data['error'])->toHaveKeys(['code', 'message']);
        });

    });

});
