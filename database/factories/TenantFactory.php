<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'domain' => Str::slug($name) . '-' . Str::random(5),
            'active' => true,
            'stripe_id' => null,
            'pm_type' => null,
            'pm_last_four' => null,
            'trial_ends_at' => null,
            'settings' => [],
        ];
    }

    /**
     * Indicate that the tenant is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    /**
     * Indicate that the tenant is on trial.
     */
    public function onTrial(int $daysRemaining = 14): static
    {
        return $this->state(fn (array $attributes) => [
            'trial_ends_at' => now()->addDays($daysRemaining),
        ]);
    }

    /**
     * Indicate that the tenant's trial has expired.
     */
    public function trialExpired(): static
    {
        return $this->state(fn (array $attributes) => [
            'trial_ends_at' => now()->subDays(1),
        ]);
    }

    /**
     * Indicate that the tenant has Stripe configured.
     */
    public function withStripe(string $customerId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'stripe_id' => $customerId ?? 'cus_' . Str::random(14),
            'pm_type' => 'card',
            'pm_last_four' => (string) fake()->randomNumber(4, true),
        ]);
    }

    /**
     * Configure tenant with specific settings.
     */
    public function withSettings(array $settings): static
    {
        return $this->state(fn (array $attributes) => [
            'settings' => array_merge($attributes['settings'] ?? [], $settings),
        ]);
    }

    /**
     * Configure tenant with a specific domain.
     */
    public function withDomain(string $domain): static
    {
        return $this->state(fn (array $attributes) => [
            'domain' => $domain,
        ]);
    }
}
