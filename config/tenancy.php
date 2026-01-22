<?php

return [
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
    | Reserved Domains
    |--------------------------------------------------------------------------
    |
    | List of domain/subdomain names that cannot be used by tenants.
    | These are reserved for system routes and internal services.
    |
    */
    'reserved_domains' => [
        'www',
        'api',
        'admin',
        'app',
        'dashboard',
        'mail',
        'smtp',
        'ftp',
        'sftp',
        'cdn',
        'static',
        'assets',
        'media',
        'img',
        'images',
        'js',
        'css',
        'fonts',
        'support',
        'help',
        'docs',
        'status',
        'blog',
        'news',
        'about',
        'contact',
        'terms',
        'privacy',
        'legal',
        'signup',
        'login',
        'register',
        'auth',
        'oauth',
        'sso',
        'webhook',
        'webhooks',
        'stripe',
        'billing',
        'pay',
        'payment',
        'checkout',
        'internal',
        'system',
        'root',
        'null',
        'undefined',
        'test',
        'demo',
        'staging',
        'dev',
        'development',
        'prod',
        'production',
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
    ],
];
