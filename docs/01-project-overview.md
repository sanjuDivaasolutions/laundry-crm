# Project Overview

## System Description

**Mega Sign Rental ERP 2024** is a comprehensive Enterprise Resource Planning (ERP) system specifically designed for sign rental businesses. Built by Divaa Solutions, this Laravel-based application provides end-to-end management of rental operations, inventory, contracts, and financial processes.

## Technology Stack

### Backend
- **Framework**: Laravel 9 (PHP 8.1+)
- **Authentication**: JWT (php-open-source-saver/jwt-auth)
- **Database**: MySQL with Model Caching (genealabs/laravel-model-caching)
- **File Storage**: Spatie Media Library
- **PDF Generation**: Laravel DomPDF & mPDF
- **Excel Processing**: Maatwebsite Excel
- **Payment Processing**: Laravel Cashier (Stripe)
- **Caching**: Redis support with Predis
- **Queue System**: Laravel Queue
- **Logging**: Clockwork for debugging

### Frontend
- **Framework**: Vue 3 with Composition API
- **Language**: TypeScript support
- **UI Framework**: Metronic 8 (Bootstrap 5.2.2)
- **State Management**: Pinia
- **Build Tool**: Vite 3.1.8
- **Form Validation**: VeeValidate 4.5.11 with Yup
- **HTTP Client**: Axios
- **Date Handling**: Moment.js
- **Charts**: ApexCharts
- **Rich Text**: TinyMCE & CKEditor 5
- **Icons**: Font Awesome, Bootstrap Icons, Line Awesome

### Supporting Libraries
- **Date Scopes**: laracraft-tech/laravel-date-scopes
- **ID Generation**: cbagdawala/laravel-id-generator
- **Multi-language**: Vue i18n
- **Notifications**: Vue Toast Notification
- **Animations**: Animate.css
- **Currency**: Vue Currency Input

## Architecture Patterns

### Backend Architecture
- **Multi-tenant**: Company-scoped data isolation
- **Service Layer**: Business logic separated in service classes
- **Repository Pattern**: Data access abstraction
- **Resource Pattern**: Consistent API responses
- **Trait-based**: Reusable functionality through traits
- **Scope-based Filtering**: Advanced query filtering system
- **Event-driven**: Laravel events and listeners

### Frontend Architecture
- **Modular Design**: Feature-based module organization
- **Composition API**: Modern Vue 3 patterns
- **Store Pattern**: Centralized state management with Pinia
- **Component-based**: Reusable UI components
- **Route-based**: Nested routing for CRUD operations
- **Plugin System**: Extensible functionality

## Core Business Modules

### Inventory Management
- **Products**: Product catalog with variants and pricing
- **Warehouses**: Multi-location inventory tracking
- **Shelves**: Detailed shelf-level inventory
- **Batch Tracking**: Product batch management
- **Stock Adjustments**: Inventory corrections
- **Inwards**: Goods receipt processing

### Sales Operations
- **Quotations**: Estimate management with approval workflow
- **Sales Orders**: Order processing and fulfillment
- **Sales Invoices**: Invoice generation and management
- **Contracts**: Subscription-based rental agreements
- **Payments**: Payment tracking and processing

### Purchase Operations
- **Purchase Orders**: Vendor order management
- **Purchase Invoices**: Supplier invoice processing
- **Supplier Management**: Vendor information and relationships

### Customer Relations
- **Buyers**: Customer management and profiles
- **Contact Addresses**: Multi-address support
- **Communication**: Message and notification system

### Financial Management
- **Payments**: Multi-method payment processing
- **Expenses**: Cost tracking and categorization
- **Reports**: Financial reporting and analytics
- **Tax Management**: Advanced tax calculation system

### System Administration
- **Users**: User account management
- **Roles & Permissions**: Role-based access control
- **Companies**: Multi-company support
- **Languages**: Multi-language support
- **Settings**: System configuration

## Key Features

### Multi-tenancy
- Company-based data isolation
- Department and buyer scopes
- Configurable access controls

### Advanced Filtering
- Custom `HasAdvancedFilter` trait
- Date range filtering
- Multi-field search capabilities
- Export functionality

### Document Management
- PDF generation for all documents
- Excel import/export templates
- Media library integration
- Document versioning

### Integration Capabilities
- Stripe payment processing
- Email notification system
- Queue-based job processing
- API-first architecture

### Reporting System
- Sales performance reports
- Inventory status reports
- Profit & loss statements
- Custom report builder

## Development Environment

### File Structure
```
├── app/                    # Laravel application logic
│   ├── Http/Controllers/   # API controllers
│   ├── Models/            # Eloquent models
│   ├── Services/          # Business logic services
│   └── Traits/            # Reusable traits
├── resources/
│   ├── metronic/          # Vue 3 Metronic theme
│   ├── modules/           # Business module components
│   └── views/             # Blade templates
├── database/
│   ├── migrations/        # Database schema
│   └── seeders/          # Sample data
└── docs/                 # Project documentation
```

### Development Commands
```bash
# Backend Development
php artisan serve              # Start development server
php artisan migrate           # Run migrations
php artisan db:seed          # Seed database
composer install            # Install dependencies

# Frontend Development  
npm run dev                  # Start Vite development server
npm run build               # Build for production
npm install                 # Install dependencies

# Testing
php artisan test            # Run PHPUnit tests
```

## Security Features
- JWT token-based authentication
- Role-based access control
- Company-scoped data isolation
- SQL injection prevention
- XSS protection
- CSRF protection

## Performance Optimizations
- Model caching system
- Query optimization with scopes
- Lazy loading relationships
- Asset optimization with Vite
- Redis caching support

This ERP system provides a robust foundation for sign rental businesses with comprehensive functionality, modern architecture, and scalable design patterns.