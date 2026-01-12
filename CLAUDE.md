# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is **Mega Sign Rental ERP 2024**, a Laravel 9 application with Vue 3 frontend for managing sign rental business operations. Built by Divaa Solutions, it includes comprehensive modules for inventory management, sales, purchases, quotations, contracts, and reporting.

## Development Commands

### Backend (Laravel)
- `php artisan serve` - Start development server
- `php artisan migrate` - Run database migrations
- `php artisan db:seed` - Seed database
- `php artisan optimize:clear` - Clear all cached files
- `php artisan storage:link` - Create storage symlink
- `php artisan queue:work` - Process queued jobs
- `composer install` - Install PHP dependencies
- `composer dump-autoload` - Regenerate autoload files

### Frontend (Vue/Vite)
- `npm run dev` - Start Vite development server with HMR
- `npm run build` - Build for production
- `npm install` - Install Node dependencies

### Testing
- `php artisan test` - Run PHPUnit tests (if configured)
- Tests are located in `tests/` directory

## Architecture Overview

### Backend Structure
- **Laravel 9** with custom multi-tenant architecture using company scoping
- **JWT Authentication** via php-open-source-saver/jwt-auth
- **Advanced Filtering System** using custom `HasAdvancedFilter` trait
- **Service Layer Pattern** - Core business logic in `app/Services/`
- **Resource Pattern** - API responses via `app/Http/Resources/`
- **Scope-based Multi-tenancy** - Department and Buyer scopes for data isolation
- **Custom Form Requests** - Validation in `app/Http/Requests/`

### Frontend Structure
- **Vue 3** with Composition API and TypeScript support
- **Metronic Theme** - Located in `resources/metronic/`
- **Modular Architecture** - Feature modules in `resources/modules/`
- **Pinia State Management** - Each module has its own store
- **Vue Router** with nested routes for CRUD operations
- **Vite Build System** with path aliases:
  - `@` → `/resources/metronic`
  - `@modules@` → `/resources/modules`
  - `@common@` → `/resources/modules/common`
  - `@utility@` → `/resources/utility`

### Key Design Patterns
- **Module Pattern** - Each business entity (products, buyers, suppliers, etc.) has its own module with:
  - Index.vue (listing)
  - Create.vue/Edit.vue (forms)
  - Show.vue (detail view)
  - FormData.js (form configuration)
  - FormStore.js (form state management)
  - IndexData.js (listing configuration)
  - IndexStore.js (listing state management)
  - Module.js (route definitions)

### Database Architecture
- **Multi-company setup** with company_id scoping
- **Comprehensive inventory tracking** with warehouses, shelves, and batch management
- **Advanced taxation system** supporting multiple tax classes and rates
- **Audit trail** through activity logging
- **Soft deletes** on most entities

## Business Modules

### Core Entities
- **Products/Services** - Inventory items with batch tracking, shelving, and pricing
- **Buyers/Suppliers** - Customer and vendor management with contact addresses
- **Purchase Orders/Invoices** - Procurement workflow
- **Sales Orders/Invoices** - Sales workflow with contract integration
- **Quotations** - Estimate management with approval workflow
- **Inwards** - Goods receipt and inventory updates
- **Contracts** - Subscription-based agreements with Stripe integration
- **Inventory Adjustments** - Stock corrections and write-offs
- **Expenses** - Cost tracking with categorization
- **Reports** - Sales, profit/loss, and stock reports

### Supporting Systems
- **Multi-language** support with dynamic translations
- **Permission-based access control** with roles and abilities
- **Media management** via Spatie Media Library
- **Email notifications** with queue processing
- **PDF generation** for invoices, orders, and reports
- **Excel import/export** functionality
- **Newsletter management** with subscriber tracking

## Key Services

- **ReportService** - Business intelligence and analytics
- **InventoryService** - Stock management and tracking
- **InvoiceService** - Invoice processing and tax calculations
- **AuthService** - Authentication and user management
- **StripeService** - Payment processing and subscriptions
- **CompanyService** - Multi-tenant company management
- **LanguageService** - Internationalization support

## Configuration Notes

- **Multi-tenancy** configured via `DepartmentScope` and `BuyerScope`
- **Default currency** and system settings in `config/system.php`
- **JWT configuration** in `config/jwt.php`
- **Media library** settings in `config/media.php`
- **Custom date scopes** for filtering via `config/date-scopes.php`

## Development Guidelines

### When Working with Models
- Always check for existing scopes (Department, Buyer, Company)
- Use the `HasAdvancedFilter` trait for listing pages
- Implement proper relationships and eager loading
- Follow the existing naming conventions for attributes and methods

### When Working with Controllers
- Extend base API controllers for consistency
- Use Form Requests for validation
- Implement proper authorization via Gates
- Return Resource collections for API responses
- Use the SearchFilters trait for filtering

### When Working with Frontend
- Follow the existing module structure
- Use the common components from `@common@`
- Implement proper error handling and loading states
- Use Pinia stores for state management
- Follow TypeScript conventions where applicable

### When Working with Database
- All migrations should include proper foreign key constraints
- Use the existing migration naming pattern
- Consider multi-tenancy implications
- Add indexes for frequently queried columns

## Import/Export Templates
Available in `import-templates/`:
- Products template (v3)
- Purchase invoices template (v1)
- Sales invoices template (v1)
- Bank transaction template (v1)
- Petty cash template (v4)

## Deployment
- Uses `deploy.sh` for deployment automation
- Clockwork profiler available for debugging (remove in production)
- Media files stored in `public/storage` (ensure proper permissions)
- Queue processing required for email functionality