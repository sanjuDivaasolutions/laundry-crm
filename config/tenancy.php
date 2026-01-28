<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Base Domain
    |--------------------------------------------------------------------------
    |
    | The base domain for tenant subdomains. Tenants will be accessible at:
    | {subdomain}.{base_domain}
    |
    | Example: If base_domain is 'laundry-crm.com', tenant 'acme' would be
    | accessible at 'acme.laundry-crm.com'
    |
    */
    'base_domain' => env('TENANT_BASE_DOMAIN', 'localhost'),

    /*
    |--------------------------------------------------------------------------
    | Central App Subdomain
    |--------------------------------------------------------------------------
    |
    | The subdomain used for the central application (login, signup, etc.)
    | Users without a tenant context will be redirected here.
    |
    */
    'central_subdomain' => env('TENANT_CENTRAL_SUBDOMAIN', 'app'),

    /*
    |--------------------------------------------------------------------------
    | Strict Tenant Scope
    |--------------------------------------------------------------------------
    |
    | When enabled, queries on tenant-scoped models will fail-safe when
    | no tenant context is available. This prevents accidental data leakage.
    |
    | Recommended: true for production
    |
    */
    'strict_scope' => env('TENANCY_STRICT_SCOPE', true),

    /*
    |--------------------------------------------------------------------------
    | Missing Context Action
    |--------------------------------------------------------------------------
    |
    | Determines what happens when a query is made without tenant context
    | in strict mode.
    |
    | Options:
    | - 'throw': Throw TenantResolutionException (most secure)
    | - 'empty': Return empty result set (safe but silent)
    | - 'log': Log warning and continue without filtering (least secure)
    |
    */
    'missing_context_action' => env('TENANCY_MISSING_CONTEXT_ACTION', 'empty'),

    /*
    |--------------------------------------------------------------------------
    | Trial Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the free trial period offered to new tenants.
    |
    */
    'trial' => [
        // Number of days for free trial
        'days' => env('TENANT_TRIAL_DAYS', 14),

        // Features available during trial (same as highest plan)
        'full_access' => true,

        // Maximum users during trial
        'max_users' => 3,

        // Show warning banner when trial is ending (days before expiry)
        'warning_days' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Grace Period Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the grace period when subscription payment fails.
    |
    */
    'grace_period' => [
        // Number of days to allow access after payment failure
        'days' => env('TENANT_GRACE_PERIOD_DAYS', 7),

        // Number of retry attempts before suspension
        'max_retries' => 3,

        // Send reminder emails at these days remaining
        'reminder_days' => [5, 3, 1],
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Plans
    |--------------------------------------------------------------------------
    |
    | Definition of available subscription plans with limits and pricing.
    | Prices are in cents (e.g., 2900 = $29.00)
    |
    */
    'plans' => [
        'starter' => [
            'name' => 'Starter',
            'price' => 2900,
            'stripe_price_id' => env('STRIPE_STARTER_PRICE_ID'),
            'users_included' => 3,
            'extra_user_price' => 500,
            'limits' => [
                'items' => 100,
                'orders_per_month' => 500,
                'customers' => 200,
            ],
            'features' => [
                'reports' => true,
                'api_access' => false,
                'priority_support' => false,
                'custom_branding' => false,
            ],
        ],

        'professional' => [
            'name' => 'Professional',
            'price' => 7900,
            'stripe_price_id' => env('STRIPE_PROFESSIONAL_PRICE_ID'),
            'users_included' => 10,
            'extra_user_price' => 500,
            'limits' => [
                'items' => -1, // Unlimited
                'orders_per_month' => -1,
                'customers' => -1,
            ],
            'features' => [
                'reports' => true,
                'api_access' => true,
                'priority_support' => false,
                'custom_branding' => true,
            ],
        ],

        'enterprise' => [
            'name' => 'Enterprise',
            'price' => 19900,
            'stripe_price_id' => env('STRIPE_ENTERPRISE_PRICE_ID'),
            'users_included' => 25,
            'extra_user_price' => 300,
            'limits' => [
                'items' => -1,
                'orders_per_month' => -1,
                'customers' => -1,
            ],
            'features' => [
                'reports' => true,
                'api_access' => true,
                'priority_support' => true,
                'custom_branding' => true,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Tenant Settings
    |--------------------------------------------------------------------------
    |
    | Default settings applied to new tenants. These can be overridden
    | in the tenant's settings.
    |
    */
    'defaults' => [
        'timezone' => 'UTC',
        'currency' => 'USD',
        'locale' => 'en',
        'date_format' => 'Y-m-d',
        'time_format' => 'H:i',
    ],

    /*
    |--------------------------------------------------------------------------
    | Reserved Domains
    |--------------------------------------------------------------------------
    |
    | List of domain/subdomain names that cannot be used by tenants.
    | These are reserved for system routes and internal services.
    |
    */
    'reserved_domains' => [
        // System subdomains
        'www',
        'api',
        'admin',
        'app',
        'dashboard',

        // Infrastructure
        'mail',
        'smtp',
        'ftp',
        'sftp',
        'ssh',
        'git',

        // Static assets
        'cdn',
        'static',
        'assets',
        'media',
        'img',
        'images',
        'js',
        'css',
        'fonts',

        // Support & Documentation
        'support',
        'help',
        'docs',
        'status',
        'blog',
        'news',

        // Legal & Info pages
        'about',
        'contact',
        'terms',
        'privacy',
        'legal',

        // Authentication
        'signup',
        'login',
        'register',
        'auth',
        'oauth',
        'sso',

        // Webhooks & Billing
        'webhook',
        'webhooks',
        'stripe',
        'billing',
        'pay',
        'payment',
        'checkout',
        'invoice',
        'invoices',

        // Internal
        'internal',
        'system',
        'root',
        'null',
        'undefined',

        // Environment names
        'test',
        'demo',
        'staging',
        'dev',
        'development',
        'prod',
        'production',
        'local',
        'localhost',

        // Common names that could cause confusion
        'laundry',
        'crm',
        'saas',
        'tenant',
        'tenants',
        'user',
        'users',
        'account',
        'accounts',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Identification
    |--------------------------------------------------------------------------
    |
    | Configuration for how tenants are identified from incoming requests.
    |
    */
    'identification' => [
        // Enable header-based identification for internal services
        'allow_header' => env('TENANCY_ALLOW_HEADER', false),

        // Enable super-admin impersonation
        'allow_impersonation' => env('TENANCY_ALLOW_IMPERSONATION', true),

        // Header names
        'tenant_header' => 'X-Tenant-ID',
        'signature_header' => 'X-Tenant-Signature',
        'impersonate_header' => 'X-Impersonate-Tenant',

        // Signature expiry in seconds (5 minutes)
        'signature_expiry' => 300,
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Isolation
    |--------------------------------------------------------------------------
    |
    | Configuration for tenant session isolation.
    |
    | SECURITY: Sessions MUST be isolated per tenant subdomain to prevent:
    | - Cross-tenant session hijacking
    | - Session fixation attacks between tenants
    | - Unauthorized access to other tenants' data
    |
    */
    'session' => [
        // Ensure session domain is NOT set to a wildcard
        // null = cookies are subdomain-specific (recommended)
        'domain' => null,

        // Include tenant_id in session data for additional validation
        'validate_tenant' => true,

        // Invalidate session if user's tenant_id doesn't match session tenant
        'strict_validation' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for tenant-scoped caching to prevent cache pollution.
    |
    */
    'cache' => [
        // Prefix all cache keys with tenant context
        'prefix_keys' => true,

        // Default TTL for tenant cache (in seconds)
        'default_ttl' => 3600,

        // Cache the tenant model itself for faster resolution
        'cache_tenant' => true,
        'tenant_cache_ttl' => 300,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit & Logging
    |--------------------------------------------------------------------------
    |
    | Settings for tenant-aware logging and audit trails.
    |
    */
    'audit' => [
        // Include tenant_id in all log entries
        'log_tenant_context' => true,

        // Log tenant scope bypasses (withoutTenantScope usage)
        'log_scope_bypass' => true,

        // Log cross-tenant access attempts (should never happen)
        'log_cross_tenant_attempts' => true,

        // Full audit trail with before/after values
        'full_audit' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limiting configuration per tenant.
    |
    */
    'rate_limits' => [
        // API requests per minute (global for all tenants)
        'api_per_minute' => 60,

        // Read operations per minute (higher limit)
        'read_per_minute' => 120,

        // Auth attempts per minute (strict limit)
        'auth_per_minute' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Branding Options
    |--------------------------------------------------------------------------
    |
    | What tenants can customize about their appearance.
    | Basic branding = name + logo only (as per interview decision)
    |
    */
    'branding' => [
        'allow_logo' => true,
        'allow_name' => true,
        'allow_colors' => false, // Decided: Basic branding only
        'allow_email_templates' => false,
        'max_logo_size' => 2048, // KB
        'logo_dimensions' => [
            'max_width' => 400,
            'max_height' => 100,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Notification settings for tenants.
    |
    */
    'notifications' => [
        // Channels: email only (as per interview decision)
        'channels' => ['email'],

        // System email (not per-tenant SMTP)
        'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@laundry-crm.com'),
        'from_name' => env('MAIL_FROM_NAME', 'Laundry CRM'),
    ],
];
