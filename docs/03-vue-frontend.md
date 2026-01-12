# Vue 3 Frontend Architecture

## Overview

The frontend is built with Vue 3 using the Composition API, TypeScript support, and a modular architecture. It follows modern Vue patterns with Pinia for state management and Vite for building.

## Architecture Structure

### Core Application Structure

```
resources/
├── metronic/              # Core Metronic theme files
│   ├── App.vue           # Main application component
│   ├── router/           # Vue Router configuration  
│   ├── stores/           # Pinia stores
│   ├── layouts/          # Layout components
│   ├── views/            # Page components
│   └── components/       # Reusable UI components
└── modules/              # Business module components
    ├── common/           # Shared components and utilities
    ├── salesInvoices/    # Sales invoice module
    ├── products/         # Product management module
    └── [other modules]/  # Additional business modules
```

## Core Components

### 1. Main Application (`App.vue`)

The root component manages global state and initializes the application:

```vue
<script setup>
import { onBeforeMount, onMounted, nextTick } from "vue";
import { useConfigStore } from "@/stores/config";
import { useThemeStore } from "@/stores/theme";
import { useBodyStore } from "@/stores/body";

onBeforeMount(() => {
    // Override layout config from localStorage
    configStore.overrideLayoutConfig();
    // Set theme mode
    themeStore.setThemeMode(themeMode.value);
});

onMounted(() => {
    nextTick(() => {
        // Initialize Metronic components
        initializeComponents();
        // Remove loading state
        bodyStore.removeBodyClassName("page-loading");
    });
});
</script>
```

### 2. Router Configuration (`router/index.ts`)

Vue Router with hash-based routing and authentication middleware:

```typescript
import { createRouter, createWebHashHistory } from "vue-router";
import { systemRoutes } from "@common@/data/routes";

const router = createRouter({
    history: createWebHashHistory(),
    routes: systemRoutes,
});

router.beforeEach((to, from, next) => {
    // Authentication check
    if (to.meta.middleware == "auth") {
        const verification = authStore.verifyAuth();
        verification.catch(() => {
            next({ name: "sign-in" });
        });
    }
    
    // Set document title
    setDocumentTitle(i18n.global.t(to.meta.pageTitle));
});
```

#### Route Structure
- **Protected Routes**: Wrapped in `MainLayout.vue` with `middleware: "auth"`
- **Auth Routes**: Use `AuthLayout.vue` for login/register pages
- **Error Routes**: Use `SystemLayout.vue` for error pages
- **Module Routes**: Each business module has its own route definitions

## State Management with Pinia

### Core Stores

#### 1. Authentication Store (`stores/auth.ts`)

Manages user authentication, JWT tokens, and user settings:

```typescript
export const useAuthStore = defineStore("auth", () => {
    const isAuthenticated = ref(!!JwtService.getToken());
    const user = ref<User>({} as User);
    const errors = ref({});
    
    // Authentication methods
    function login(credentials: User) { /* ... */ }
    function logout() { /* ... */ }
    function verifyAuth() { /* ... */ }
    
    // User settings management
    function getUserSetting(key: string, defaultValue: any) { /* ... */ }
    function updateUserSetting(key: string, value: any) { /* ... */ }
    
    return { 
        isAuthenticated, user, errors,
        login, logout, verifyAuth,
        getUserSetting, updateUserSetting
    };
});
```

#### Key Features:
- **JWT Token Management**: Automatic token handling and refresh
- **Auto-verification**: Periodic authentication verification (20-second intervals)
- **User Settings**: Persistent user preferences
- **Company Restrictions**: Multi-tenant company switching controls

#### 2. Configuration Store (`stores/config.ts`)
- Layout configuration management
- Theme settings persistence
- Dynamic layout overrides

#### 3. Ability Store (`stores/ability.ts`)  
- Role-based permissions management
- Dynamic UI element visibility control

#### 4. Theme Store (`stores/theme.ts`)
- Dark/light theme management
- Theme mode persistence

## Modular Architecture

### Module Pattern

Each business entity follows a consistent modular structure:

```
modules/[entityName]/
├── Index.vue                    # List view component
├── Create.vue                   # Create form component  
├── Edit.vue                     # Edit form component
├── Show.vue                     # Detail view component
├── [Entity]Form.vue            # Reusable form component
├── ShowComponents/             # Detail view sub-components
│   └── Overview.vue
├── [entity]Module.js           # Module configuration
├── [entity]IndexData.js        # List view configuration
├── [entity]IndexStore.js       # List view state management
├── [entity]FormData.js         # Form configuration  
└── [entity]FormStore.js        # Form state management
```

### Module Configuration Example

```javascript
// salesInvoicesModule.js
const module = {
    id: "sales-invoices",
    slug: "salesInvoice", 
    snakeSlug: "sales_invoice",
    singular: "Sales Invoice",
    plural: "Sales Invoices",
};
```

### Index Component Pattern

```vue
<!-- Index.vue -->
<template>
    <IndexModule
        :index-store="moduleIndexStore"
        :form-store="moduleFormStore"
    />
</template>

<script setup>
import { useModuleIndexStore } from "./salesInvoicesIndexStore";
import { useModuleFormStore } from "./salesInvoicesFormStore";

const moduleIndexStore = useModuleIndexStore();
const moduleFormStore = useModuleFormStore();
</script>
```

## Layout System

### 1. MainLayout (`layouts/main-layout/MainLayout.vue`)

The primary application layout containing:
- **Header**: Navigation, company selector, user menu
- **Sidebar**: Navigation menu with dynamic routes
- **Content**: Main content area with breadcrumbs
- **Footer**: Application footer
- **Modals**: Global modal containers
- **Drawers**: Activity and help drawers

### Layout Components:

#### Header Components:
- `Header.vue`: Main header container
- `Navbar.vue`: Top navigation bar
- `CompanySelection.vue`: Multi-tenant company switcher

#### Sidebar Components:
- `Sidebar.vue`: Main navigation sidebar
- `SidebarMenu.vue`: Dynamic menu generation
- `SidebarLogo.vue`: Company logo display

#### Menu Components:
- `UserAccountMenu.vue`: User dropdown menu
- `NotificationsMenu.vue`: Notification system
- `QuickLinksMenu.vue`: Quick access links

### 2. AuthLayout (`layouts/AuthLayout.vue`)

Layout for authentication pages (login, register, password reset).

### 3. SystemLayout (`layouts/SystemLayout.vue`) 

Layout for error pages and system maintenance.

## Component Architecture

### Common Components (`modules/common/components/`)

#### Core Components:
- **IndexModule.vue**: Generic list/table component for all modules
- **FormFields.vue**: Dynamic form field generator
- **CardContainer.vue**: Standard card wrapper
- **OverviewHeader.vue**: Detail page headers

#### Modal System:
- **ModalContainer.vue**: Base modal wrapper
- **FormModal.vue**: Form-based modals
- **ShowModal.vue**: Detail view modals
- **EasyModalContainer.vue**: Simplified modal creation

#### Form Components:
- **ActivityFormModal.vue**: Activity logging forms
- **PaymentFormModal.vue**: Payment processing forms

### Magic Datatable System

Advanced data table component with:
- **Advanced Filtering**: Multi-column search and filtering
- **Column Chooser**: Dynamic column visibility
- **Export Functions**: CSV/PDF export capabilities
- **Pagination**: Server-side pagination
- **Sorting**: Multi-column sorting
- **Global Search**: Intelligent search across columns

```vue
<template>
    <MagicDatatable 
        :config="datatableConfig"
        :store="indexStore" 
    />
</template>
```

## Services Layer

### Core Services (`core/services/`)

#### 1. ApiService.ts
- HTTP client wrapper around Axios
- Automatic JWT token injection
- Request/response interceptors
- Error handling

#### 2. JwtService.ts
- JWT token storage and management
- Token validation
- Automatic header setup

#### 3. LayoutService.ts
- Dynamic layout configuration
- Theme management
- Layout state persistence

#### 4. DownloadService.ts
- File download handling
- PDF generation
- Export functionality

#### 5. UploadService.ts
- File upload management
- Media handling
- Progress tracking

## Form System

### Form Validation (VeeValidate + Yup)

```vue
<script setup>
import { useForm } from "vee-validate";
import * as yup from "yup";

const schema = yup.object({
    invoice_number: yup.string().required(),
    date: yup.date().required(),
    buyer_id: yup.number().required(),
});

const { handleSubmit, errors } = useForm({
    validationSchema: schema,
});
</script>
```

### Dynamic Form Generation

Form configurations define field types, validation, and behavior:

```javascript
// Form configuration example
const formFields = [
    {
        field: 'invoice_number',
        type: 'input',
        label: 'Invoice Number',
        required: true,
    },
    {
        field: 'buyer_id', 
        type: 'select',
        label: 'Customer',
        optionsUrl: '/api/buyers',
    },
];
```

## Internationalization (i18n)

Multi-language support with Vue i18n:

```typescript
// Translation usage
{{ $t('salesInvoice.title') }}

// Programmatic usage
const { t } = useI18n();
const title = t('salesInvoice.title');
```

### Translation Structure:
- Language files in `lang/[locale]/`
- Dynamic translation loading
- Pluralization support
- Date/number formatting

## Build System (Vite)

### Configuration (`vite.config.js`)

```javascript
export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/sass/app.scss", "resources/js/app.js"],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            "@": "/resources/metronic",
            "@modules@": "/resources/modules", 
            "@common@": "/resources/modules/common",
            "@utility@": "/resources/utility",
        },
    },
});
```

### Path Aliases:
- `@`: Metronic theme files
- `@modules@`: Business modules
- `@common@`: Shared components
- `@utility@`: Utility functions

## Performance Optimizations

### Lazy Loading
- Route-based code splitting
- Dynamic component imports
- Async component loading

### State Management
- Module-based state isolation
- Efficient reactivity
- Memory management

### Asset Optimization
- Vite's built-in optimizations
- Tree shaking
- CSS optimization
- Image optimization

## Development Patterns

### Composition API Usage
- Reactive references with `ref()` and `reactive()`
- Computed properties with `computed()`
- Lifecycle hooks (`onMounted`, `onBeforeUnmount`)
- Custom composables for reusable logic

### TypeScript Integration
- Type-safe store definitions
- Interface definitions for API responses
- Component prop typing
- Service method typing

### Error Handling
- Global error boundaries
- API error interceptors
- User-friendly error messages
- Logging integration

This Vue 3 frontend provides a modern, maintainable, and scalable foundation for the ERP system with excellent developer experience and performance.