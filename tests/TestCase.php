<?php

namespace Tests;

use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Set up basic configuration for tests
        config([
            'database.default' => env('DB_CONNECTION', 'sqlite'),
            'cache.default' => 'array',
            'session.driver' => 'array',
            'queue.default' => 'sync',
        ]);
    }

    /**
     * Create an authenticated user with JWT token.
     */
    protected function createAuthenticatedUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        
        // Create a basic role and permissions if needed
        if (!Role::where('title', 'admin')->exists()) {
            $role = Role::create(['title' => 'admin']);
            $user->roles()->attach($role);
        }

        return $user;
    }

    /**
     * Get authentication headers for API requests.
     */
    protected function getAuthHeaders(User $user = null): array
    {
        if (!$user) {
            $user = $this->createAuthenticatedUser();
        }

        $token = JWTAuth::fromUser($user);

        return [
            'Authorization' => "Bearer $token",
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Create a test company for multi-tenant testing.
     */
    protected function createTestCompany(User $user = null): Company
    {
        if (!$user) {
            $user = $this->createAuthenticatedUser();
        }

        return Company::factory()->create([
            'user_id' => $user->id,
        ]);
    }

    /**
     * Assert that the response has the expected pagination structure.
     */
    protected function assertPaginationStructure($response): void
    {
        $response->assertJsonStructure([
            'data',
            'links' => [
                'first',
                'last',
                'prev',
                'next'
            ],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'per_page',
                'to',
                'total'
            ]
        ]);
    }

    /**
     * Assert that the response has the expected error structure.
     */
    protected function assertErrorResponse($response, int $statusCode = 422): void
    {
        $response->assertStatus($statusCode)
                 ->assertJsonStructure([
                     'message',
                     'errors' => []
                 ]);
    }

    /**
     * Assert that the response has the expected success structure.
     */
    protected function assertSuccessResponse($response, int $statusCode = 200): void
    {
        $response->assertStatus($statusCode)
                 ->assertJsonStructure([
                     'data'
                 ]);
    }

    /**
     * Mock external services for testing.
     */
    protected function mockExternalServices(): void
    {
        // Mock Stripe service if needed
        $this->mock(\App\Services\StripeService::class, function ($mock) {
            $mock->shouldReceive('createCustomer')->andReturn('cus_test123');
            $mock->shouldReceive('createSubscription')->andReturn((object)['id' => 'sub_test123']);
        });
        
        // Mock email services
        $this->mock(\Illuminate\Mail\Mailer::class, function ($mock) {
            $mock->shouldReceive('send')->andReturn(true);
        });
    }

    /**
     * Seed basic test data.
     */
    protected function seedBasicData(): void
    {
        // Seed permissions and roles
        if (!Role::exists()) {
            $this->seed(\Database\Seeders\RolesTableSeeder::class);
            $this->seed(\Database\Seeders\PermissionsTableSeeder::class);
            $this->seed(\Database\Seeders\PermissionRoleTableSeeder::class);
        }

        // Seed other basic reference data as needed
    }

    /**
     * Clean up after tests.
     */
    protected function tearDown(): void
    {
        // Clear any cached data
        cache()->flush();
        
        parent::tearDown();
    }
}