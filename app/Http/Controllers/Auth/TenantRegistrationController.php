<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Models\User;
use App\Notifications\TenantEmailVerificationNotification;
use App\Notifications\TenantWelcomeNotification;
use App\Rules\ValidSubdomain;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * TenantRegistrationController
 *
 * Handles self-service tenant registration/signup.
 *
 * Design Decisions (from interview):
 * - Self-service + admin can create tenants
 * - Payment required (14-day trial, then must pay)
 * - Email invite for new users
 * - Minimal seed for new tenants
 * - User chooses subdomain at signup
 */
class TenantRegistrationController extends Controller
{
    /**
     * Check if a subdomain is available.
     *
     * GET /api/v1/register/check-subdomain
     */
    public function checkSubdomain(Request $request): JsonResponse
    {
        $request->validate([
            'subdomain' => ['required', 'string', 'min:3', 'max:63'],
        ]);

        $subdomain = strtolower(trim($request->subdomain));

        // Check reserved list
        $reserved = config('tenancy.reserved_domains', []);
        if (in_array($subdomain, $reserved, true)) {
            return response()->json([
                'available' => false,
                'message' => 'This subdomain is reserved.',
                'suggestion' => ValidSubdomain::generateUnique($subdomain),
            ]);
        }

        // Check if taken
        if (Tenant::where('domain', $subdomain)->exists()) {
            return response()->json([
                'available' => false,
                'message' => 'This subdomain is already taken.',
                'suggestion' => ValidSubdomain::generateUnique($subdomain),
            ]);
        }

        // Check format
        if (!preg_match('/^[a-z0-9][a-z0-9-]*[a-z0-9]$/', $subdomain) && strlen($subdomain) > 2) {
            return response()->json([
                'available' => false,
                'message' => 'Invalid format. Use only lowercase letters, numbers, and hyphens.',
                'suggestion' => ValidSubdomain::suggest($subdomain),
            ]);
        }

        return response()->json([
            'available' => true,
            'message' => 'Subdomain is available!',
        ]);
    }

    /**
     * Suggest a subdomain based on company name.
     *
     * GET /api/v1/register/suggest-subdomain
     */
    public function suggestSubdomain(Request $request): JsonResponse
    {
        $request->validate([
            'company_name' => ['required', 'string', 'max:100'],
        ]);

        $suggestion = ValidSubdomain::suggest($request->company_name);

        // Ensure uniqueness
        if (Tenant::where('domain', $suggestion)->exists()) {
            $suggestion = ValidSubdomain::generateUnique($suggestion);
        }

        return response()->json([
            'suggestion' => $suggestion,
        ]);
    }

    /**
     * Register a new tenant.
     *
     * POST /api/v1/register
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:100'],
            'subdomain' => ['required', 'string', new ValidSubdomain()],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'timezone' => ['required', 'timezone'],
            'currency' => ['required', 'string', 'size:3'],
        ]);

        try {
            $result = DB::transaction(function () use ($validated) {
                // 1. Create tenant
                $tenant = Tenant::create([
                    'name' => $validated['company_name'],
                    'domain' => strtolower($validated['subdomain']),
                    'active' => true,
                    'trial_ends_at' => now()->addDays(config('tenancy.trial.days', 14)),
                    'timezone' => $validated['timezone'],
                    'currency' => strtoupper($validated['currency']),
                    'settings' => [
                        'onboarding_completed' => false,
                        'registered_at' => now()->toIso8601String(),
                    ],
                ]);

                // 2. Create admin user (directly insert to bypass tenant scope)
                $user = new User();
                $user->tenant_id = $tenant->id;
                $user->name = $validated['name'] ?? 'Admin';
                $user->email = $validated['email'];
                $user->password = Hash::make($validated['password']);
                $user->email_verified_at = null; // Requires verification
                $user->save();

                // 3. Assign admin role
                if (method_exists($user, 'assignRole')) {
                    $user->assignRole('admin');
                }

                // 4. Create default company for tenant
                $company = new Company();
                $company->tenant_id = $tenant->id;
                $company->name = $validated['company_name'];
                $company->code = 'MAIN';
                $company->address_1 = '';
                $company->active = true;
                $company->user_id = $user->id;
                $company->save();

                // 5. Seed default settings
                TenantSetting::seedDefaults($tenant->id);

                // 6. Store additional settings
                if (!empty($validated['phone'])) {
                    TenantSetting::setValue($tenant->id, 'company_phone', $validated['phone'], 'string', 'general');
                }

                // 7. Send verification email
                $user->notify(new TenantEmailVerificationNotification());

                // 8. Send welcome notification (queued)
                $user->notify(new TenantWelcomeNotification($tenant));

                // 9. Log the registration
                logger()->info('New tenant registered', [
                    'tenant_id' => $tenant->id,
                    'subdomain' => $tenant->domain,
                    'user_email' => $user->email,
                    'ip' => request()->ip(),
                ]);

                return [
                    'tenant' => $tenant,
                    'user' => $user,
                ];
            });

            $tenant = $result['tenant'];
            $user = $result['user'];

            return response()->json([
                'success' => true,
                'message' => 'Registration successful! Please check your email to verify your account.',
                'data' => [
                    'tenant' => [
                        'id' => $tenant->id,
                        'name' => $tenant->name,
                        'subdomain' => $tenant->domain,
                        'url' => $tenant->getUrl(),
                        'trial_ends_at' => $tenant->trial_ends_at->toIso8601String(),
                        'trial_days_remaining' => $tenant->trialDaysRemaining(),
                    ],
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'email_verified' => $user->hasVerifiedEmail(),
                    ],
                ],
            ], 201);

        } catch (\Exception $e) {
            logger()->error('Tenant registration failed', [
                'error' => $e->getMessage(),
                'email' => $validated['email'] ?? 'unknown',
                'subdomain' => $validated['subdomain'] ?? 'unknown',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal error',
            ], 500);
        }
    }

    /**
     * Get list of available timezones.
     *
     * GET /api/v1/register/timezones
     */
    public function getTimezones(): JsonResponse
    {
        $timezones = collect(\DateTimeZone::listIdentifiers())
            ->mapWithKeys(function ($tz) {
                $timezone = new \DateTimeZone($tz);
                $offset = $timezone->getOffset(new \DateTime('now', $timezone));
                $hours = intdiv($offset, 3600);
                $minutes = abs($offset % 3600 / 60);
                $offsetStr = sprintf('%+03d:%02d', $hours, $minutes);

                return [$tz => "(UTC{$offsetStr}) {$tz}"];
            })
            ->sortKeys()
            ->toArray();

        return response()->json([
            'timezones' => $timezones,
        ]);
    }

    /**
     * Get list of supported currencies.
     *
     * GET /api/v1/register/currencies
     */
    public function getCurrencies(): JsonResponse
    {
        $currencies = [
            'USD' => 'US Dollar ($)',
            'EUR' => 'Euro (€)',
            'GBP' => 'British Pound (£)',
            'INR' => 'Indian Rupee (₹)',
            'CAD' => 'Canadian Dollar (C$)',
            'AUD' => 'Australian Dollar (A$)',
            'JPY' => 'Japanese Yen (¥)',
            'CNY' => 'Chinese Yuan (¥)',
            'AED' => 'UAE Dirham (د.إ)',
            'SAR' => 'Saudi Riyal (﷼)',
            'SGD' => 'Singapore Dollar (S$)',
            'MYR' => 'Malaysian Ringgit (RM)',
            'THB' => 'Thai Baht (฿)',
            'PHP' => 'Philippine Peso (₱)',
            'IDR' => 'Indonesian Rupiah (Rp)',
            'VND' => 'Vietnamese Dong (₫)',
            'BDT' => 'Bangladeshi Taka (৳)',
            'PKR' => 'Pakistani Rupee (₨)',
            'LKR' => 'Sri Lankan Rupee (Rs)',
            'NPR' => 'Nepalese Rupee (रू)',
        ];

        return response()->json([
            'currencies' => $currencies,
        ]);
    }

    /**
     * Verify email address from verification link.
     *
     * GET /api/v1/register/verify-email/{id}/{hash}
     */
    public function verifyEmail(Request $request, int $id, string $hash): JsonResponse
    {
        // Find user across all tenants
        $user = User::withoutGlobalScope('tenant')->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        // Verify hash
        if (!hash_equals(sha1($user->email), $hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification link.',
            ], 400);
        }

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email already verified.',
                'redirect_url' => $user->tenant->getUrl(),
            ]);
        }

        // Mark as verified
        $user->markEmailAsVerified();

        logger()->info('User email verified', [
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully!',
            'redirect_url' => $user->tenant->getUrl(),
        ]);
    }

    /**
     * Resend verification email.
     *
     * POST /api/v1/register/resend-verification
     */
    public function resendVerification(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Find user across all tenants
        $user = User::withoutGlobalScope('tenant')
            ->where('email', $request->email)
            ->first();

        if (!$user) {
            // Don't reveal if email exists
            return response()->json([
                'success' => true,
                'message' => 'If this email is registered, a verification link has been sent.',
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email is already verified.',
            ]);
        }

        // Rate limit: only allow resend every 60 seconds
        $cacheKey = "email_verification_sent:{$user->id}";
        if (cache()->has($cacheKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Please wait before requesting another verification email.',
            ], 429);
        }

        $user->sendEmailVerificationNotification();
        cache()->put($cacheKey, true, 60);

        return response()->json([
            'success' => true,
            'message' => 'Verification email sent.',
        ]);
    }
}
