# Copilot Instructions for Laundry CRM (Multi-Tenant ERP)

## Project Overview
**Mega Sign Rental ERP** - Laravel 11 + Vue 3 multi-tenant SaaS application with JWT authentication, advanced filtering, inventory management, sales/purchase workflows, and Stripe integration. Each tenant is isolated by subdomain with company-scoped data filtering.

## Essential Architecture

### Multi-Tenancy Pattern
- **Tenant Identification**: `IdentifyTenant` middleware extracts subdomain (e.g., `acme.localhost:8000` → tenant `acme`)
- **Data Isolation**: All models use `BelongsToTenant` trait - queries automatically scoped to current tenant
- **Company Scoping**: Within a tenant, additional `company_id` filtering via `CompanyScopeTrait` (departments/buyers)
- **Key Files**: [app/Http/Middleware/IdentifyTenant.php](app/Http/Middleware/IdentifyTenant.php), [routes/tenant_api.php](routes/tenant_api.php)

### JWT Authentication Flow
1. Frontend sends credentials to `POST /api/v1/login` (with tenant resolved via subdomain)
2. Backend returns JWT token + user object with permissions and settings
3. Frontend stores token via `JwtService.saveToken()`, auto-injects in all API requests
4. Admin routes use `jwt.admin.verify` middleware; tenant routes use `jwt.auth` + tenant context
5. **Key Files**: [app/Http/Middleware/JwtAdminMiddleware.php](app/Http/Middleware/JwtAdminMiddleware.php), [resources/metronic/core/services/JwtService.ts](resources/metronic/core/services/JwtService.ts)

### API Route Structure
- **Public**: `/api/v1/login`, `/api/v1/register/*` (no auth)
- **Admin**: `/api/v1/admin/*` (requires `jwt.admin.verify`)
- **Tenant**: `/api/v1/*` (requires valid JWT + tenant scope)
- **Standard CRUD**: `GET /resource`, `POST /resource`, `GET /resource/{id}`, `PUT /resource/{id}`, `DELETE /resource/{id}`
- **Export Routes**: `GET /resource-csv`, `GET /resource-pdf`, `GET /resource-single-pdf/{id}`

## Critical Patterns

### Backend - Service Layer
All business logic lives in `app/Services/` - controllers are thin orchestration layers:
- `AuthService` - JWT, permissions, user responses
- `TenantService` - Tenant lifecycle, registration, suspension
- `CompanyService` - Multi-tenant company operations
- `InventoryService` - Stock tracking, warehouses, batch management
- `InvoiceService` - Tax calculations, invoice processing
- Pattern: Call service methods from controllers, return via Resource objects

### Backend - Advanced Filtering
Models use `HasAdvancedFilter` trait enabling powerful query capabilities:
```php
// app/Models/Item.php trait usage
protected $filterable = ['name', 'status', 'sku'];
protected $orderable = ['id', 'name', 'created_at', 'price'];

// Frontend sends: ?filter={"name":{"contains":"shirt"}}&sort=-id&limit=50
// Backend: $query->advancedFilter() handles all of it
```
**Key Files**: [app/Support/HasAdvancedFilter.php](app/Support/HasAdvancedFilter.php), [app/Traits/SearchFilters.php](app/Traits/SearchFilters.php)

### Frontend - Module Structure
Each business entity (Products, Invoices, etc.) follows predictable structure:
```
resources/modules/{entity}/
├── Index.vue          # Listing with filters, pagination
├── Create.vue         # New record form
├── Edit.vue           # Edit existing record
├── Show.vue           # Detail view
├── FormData.js        # Form field configuration + defaults
├── FormStore.js       # Form state (Pinia)
├── IndexData.js       # List columns, filtering config
├── IndexStore.js      # List state (Pinia)
└── Module.js          # Vue Router route definitions
```
**Pattern**: Use common components from `@common@` (path alias `resources/modules/common`); reuse IndexStore/FormStore patterns; leverage `ApiService` for CRUD.

### Frontend - State Management
Pinia stores per feature; follow existing patterns:
- `useAuthStore` - User, token, permissions; `verifyAuth()` every 20 seconds
- `useAbilityStore` - User abilities/permissions
- Module stores (FormStore, IndexStore) - Feature-specific state
- **Key Files**: [resources/metronic/stores/auth.ts](resources/metronic/stores/auth.ts), [resources/metronic/router/index.ts](resources/metronic/router/index.ts)

## Developer Workflows

### Build & Run
```bash
# Backend
php artisan serve              # Start on http://localhost:8000
npm run dev                    # Frontend dev server (HMR enabled)
php artisan migrate            # Run migrations
php artisan db:seed            # Seed database

# Testing
php artisan test               # Run Pest tests (Laravel 11 default)
php artisan test --watch       # Watch mode

# Frontend if changes not visible in UI
npm run build                  # Production build required for some changes
```

### Key Commands
- `php artisan list` - See all available Artisan commands
- `php artisan tinker` - Interactive PHP shell with app context
- `composer dump-autoload` - Regenerate autoloader after adding new classes
- `php artisan storage:link` - Create storage symlink (one-time setup)
- `php artisan optimize:clear` - Clear all caches

### Testing Pattern
Use Pest (v3) for feature/unit tests in `tests/` directory:
```php
// tests/Feature/SalesInvoiceTest.php
test('user can create sales invoice', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson('/api/v1/sales-invoices', [...]);
    expect($response->status())->toBe(201);
});
```

## Project-Specific Conventions

### Database & Models
- **Migrations**: Use existing pattern; include foreign keys with cascading
- **Soft Deletes**: Most entities use soft deletes - query behavior differs
- **Company Scoping**: Use `company_id` filter when user can access multiple companies
- **Relationships**: Always eager-load via `with()` to prevent N+1 queries
- **Indexes**: Add indexes on frequently queried columns (status, company_id, tenant_id)

### Controllers
- Extend base `Controller` class for consistent behavior
- Use Gate authorization: `Gate::authorize('manage-items')`
- Return Resource collections, not raw models: `ItemResource::collection($items)`
- Form validation via custom Form Request classes in `app/Http/Requests/`

### Frontend Components
- Use TypeScript where possible; Path aliases: `@` (metronic), `@modules@` (modules), `@common@` (common), `@utility@` (utility)
- Implement loading/error states; use `vue-toast-notification` for user feedback
- Leverage `@casl/vue` for permission-based UI rendering
- Export lists/details via `DownloadService.ts` for PDF/CSV

### Configuration
- Core settings in `config/system.php` (currency, date formats)
- Multi-tenancy in `config/tenancy.php`
- JWT in `config/jwt.php` (token expiry, algorithm)
- Custom date scopes in `config/date-scopes.php`

## Integration Points

### Stripe Webhooks
- Route: `POST /api/v1/stripe-webhook` (no auth, CSRF exempt)
- Handles: `payment_intent.succeeded`, subscription events
- Webhook signed with Stripe secret in .env

### Media Library
- Uses Spatie Media Library (v11)
- Attach files to models via `addMedia()` method
- Store policies in model media-related methods

### Queue Processing
- Long operations (exports, notifications) queued
- Run via `php artisan queue:work` (development) or supervisord (production)
- Key jobs in `app/Jobs/`

### Localization
- Multi-language support with `vue-i18n` (frontend) + Laravel locales (backend)
- Dynamic translations loaded from database
- Route: `GET /api/v1/locales/messages` - returns current user's language translations

## Common Pitfalls & Solutions

| Issue | Solution |
|-------|----------|
| Tenant data leaking between subdomains | Always verify `IdentifyTenant` middleware is applied; use `BelongsToTenant` trait on models |
| N+1 queries in resource collections | Use `with()` to eager-load relationships; check Resource classes for nested includes |
| Frontend JWT expired but not refreshed | Token auto-refresh via middleware; check `JwtService` token validation in `verifyAuth()` |
| Form validation errors not showing | Ensure response format matches `{ "data": {...}, "errors": {...} }`; check custom Form Requests |
| Filter queries not working | Verify model has `HasAdvancedFilter` trait + proper `$filterable`/`$orderable` arrays |

## Key Files Reference

| Purpose | Files |
|---------|-------|
| Routes | [routes/api.php](routes/api.php), [routes/tenant_api.php](routes/tenant_api.php) |
| Authentication | [app/Services/AuthService.php](app/Services/AuthService.php), [app/Http/Middleware/JwtAdminMiddleware.php](app/Http/Middleware/JwtAdminMiddleware.php) |
| Multi-tenancy | [app/Http/Middleware/IdentifyTenant.php](app/Http/Middleware/IdentifyTenant.php), [app/Traits/BelongsToTenant.php](app/Traits/BelongsToTenant.php) |
| Filtering | [app/Support/HasAdvancedFilter.php](app/Support/HasAdvancedFilter.php), [app/Traits/SearchFilters.php](app/Traits/SearchFilters.php) |
| Frontend Entry | [resources/metronic/App.vue](resources/metronic/App.vue), [resources/metronic/router/index.ts](resources/metronic/router/index.ts) |
| Frontend Services | [resources/metronic/core/services/ApiService.ts](resources/metronic/core/services/ApiService.ts), [resources/metronic/core/services/JwtService.ts](resources/metronic/core/services/JwtService.ts) |
| Frontend Auth Store | [resources/metronic/stores/auth.ts](resources/metronic/stores/auth.ts) |

## Additional Resources
- Full documentation: [docs/](docs/) folder
- API spec: [docs/06-api-documentation.md](docs/06-api-documentation.md)
- Business logic: [docs/07-business-logic.md](docs/07-business-logic.md)
- Testing guide: [docs/08-testing.md](docs/08-testing.md)
- Testing patterns in code: [CLAUDE.md](CLAUDE.md), [.cursor/rules/laravel-boost.mdc](.cursor/rules/laravel-boost.mdc)
