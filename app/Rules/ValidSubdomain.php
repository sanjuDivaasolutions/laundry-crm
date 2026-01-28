<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Tenant;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * ValidSubdomain Validation Rule
 *
 * Validates subdomain format, checks against reserved list, and ensures uniqueness.
 *
 * Design Decisions (from interview):
 * - Hardcoded reserved list (maintained in config/tenancy.php)
 * - User chooses subdomain at signup
 * - Grandfather existing tenants if a new word is reserved later
 *
 * Valid subdomain format:
 * - 3-63 characters
 * - Lowercase letters, numbers, and hyphens only
 * - Must start and end with alphanumeric character
 * - Cannot have consecutive hyphens
 *
 * @example Valid: acme, my-laundry, laundry123
 * @example Invalid: -acme, acme-, my--laundry, ab (too short)
 */
class ValidSubdomain implements ValidationRule
{
    /**
     * Minimum subdomain length.
     */
    protected const MIN_LENGTH = 3;

    /**
     * Maximum subdomain length (DNS label limit).
     */
    protected const MAX_LENGTH = 63;

    /**
     * Tenant ID to exclude from uniqueness check (for updates).
     */
    protected ?int $excludeTenantId = null;

    /**
     * Create a new rule instance.
     *
     * @param int|null $excludeTenantId Tenant ID to exclude from uniqueness check
     */
    public function __construct(?int $excludeTenantId = null)
    {
        $this->excludeTenantId = $excludeTenantId;
    }

    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param Closure $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $subdomain = $this->normalize($value);

        // Check if empty after normalization
        if (empty($subdomain)) {
            $fail('The :attribute is required.');
            return;
        }

        // Check minimum length
        if (strlen($subdomain) < self::MIN_LENGTH) {
            $fail('The :attribute must be at least :min characters.')->translate([
                'min' => self::MIN_LENGTH,
            ]);
            return;
        }

        // Check maximum length
        if (strlen($subdomain) > self::MAX_LENGTH) {
            $fail('The :attribute must not exceed :max characters.')->translate([
                'max' => self::MAX_LENGTH,
            ]);
            return;
        }

        // Check format: alphanumeric and hyphens only
        if (!preg_match('/^[a-z0-9][a-z0-9-]*[a-z0-9]$/', $subdomain) && strlen($subdomain) > 2) {
            $fail('The :attribute can only contain lowercase letters, numbers, and hyphens, and must start and end with a letter or number.');
            return;
        }

        // For very short subdomains (exactly 3 chars), allow without ending check
        if (strlen($subdomain) <= 2 && !preg_match('/^[a-z0-9]+$/', $subdomain)) {
            $fail('The :attribute can only contain lowercase letters and numbers.');
            return;
        }

        // Check for consecutive hyphens
        if (strpos($subdomain, '--') !== false) {
            $fail('The :attribute cannot contain consecutive hyphens.');
            return;
        }

        // Check reserved subdomains
        if ($this->isReserved($subdomain)) {
            $fail('The :attribute ":value" is reserved. Please choose another.');
            return;
        }

        // Check uniqueness
        if ($this->isAlreadyTaken($subdomain)) {
            $fail('The :attribute ":value" is already taken. Please choose another.');
            return;
        }
    }

    /**
     * Normalize the subdomain value.
     */
    protected function normalize(mixed $value): string
    {
        if (!is_string($value)) {
            return '';
        }

        return strtolower(trim($value));
    }

    /**
     * Check if subdomain is in the reserved list.
     */
    protected function isReserved(string $subdomain): bool
    {
        $reserved = config('tenancy.reserved_domains', []);

        return in_array($subdomain, $reserved, true);
    }

    /**
     * Check if subdomain is already taken by another tenant.
     */
    protected function isAlreadyTaken(string $subdomain): bool
    {
        $query = Tenant::where('domain', $subdomain);

        if ($this->excludeTenantId) {
            $query->where('id', '!=', $this->excludeTenantId);
        }

        return $query->exists();
    }

    /**
     * Generate a suggested subdomain based on company name.
     *
     * @param string $companyName
     * @return string
     */
    public static function suggest(string $companyName): string
    {
        // Convert to lowercase
        $subdomain = strtolower($companyName);

        // Replace spaces and special characters with hyphens
        $subdomain = preg_replace('/[^a-z0-9]+/', '-', $subdomain);

        // Remove leading/trailing hyphens
        $subdomain = trim($subdomain, '-');

        // Remove consecutive hyphens
        $subdomain = preg_replace('/-+/', '-', $subdomain);

        // Limit length
        if (strlen($subdomain) > self::MAX_LENGTH) {
            $subdomain = substr($subdomain, 0, self::MAX_LENGTH);
            $subdomain = rtrim($subdomain, '-');
        }

        // Ensure minimum length
        if (strlen($subdomain) < self::MIN_LENGTH) {
            $subdomain = $subdomain . '-biz';
        }

        return $subdomain;
    }

    /**
     * Generate unique subdomain by appending numbers if needed.
     *
     * @param string $base Base subdomain
     * @return string
     */
    public static function generateUnique(string $base): string
    {
        $subdomain = $base;
        $counter = 1;

        while (Tenant::where('domain', $subdomain)->exists()) {
            $subdomain = $base . '-' . $counter;
            $counter++;

            // Safety limit
            if ($counter > 100) {
                $subdomain = $base . '-' . substr(uniqid(), -6);
                break;
            }
        }

        return $subdomain;
    }
}
