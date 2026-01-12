# Laravel 9 to 11 & Vue 3 Migration Guide

> [!IMPORTANT]
> This guide provides a comprehensive, step-by-step approach to migrating the **Pharma POS 2025** application from Laravel 9 to Laravel 11 with the latest packages, upgrading Node.js, and migrating to the latest Vue.js version. Follow each step carefully and test thoroughly at each stage.

## 🚀 Migration Progress

**Current Status:** Laravel 10 ✅ **TESTED & COMMITTED** | Next: Laravel 10 → 11

### Completed Steps
- ✅ Created migration branch `feature/laravel-11-migration`
- ✅ Created git tag `v1.0-laravel9` 
- ✅ Backed up `.env` file
- ✅ Updated `composer.json` for Laravel 10
- ✅ Resolved dependency conflicts (genealabs, spatie, zipstream)
- ✅ Ran `composer update` successfully
- ✅ **Laravel upgraded: 9.52.21 → 10.50.0**
- ✅ Verified service providers compatibility  
- ✅ Cleared caches (optimize:clear, config:cache)
- ✅ Tested Laravel 10 - working correctly
- ✅ Committed Laravel 10 changes to git

### Packages Upgraded in Laravel 10
- `laravel/framework`: v9.52.21 → v10.50.0
- `laravel/sanctum`: v3.0 → v3.2
- `geneal abs/laravel-model-caching`: 0.12.5 → 0.13.9
- `spatie/laravel-medialibrary`: 10.15.0 → 11.17.8
- `maennchen/zipstream-php`: 2.1 → 3.1
- `phpunit/phpunit`: 9.6.31 → 10.5.60
- `monolog/monolog`: 2.10.0 → 3.10.0
- `nunomaduro/collision`: 6.4.0 → 7.12.0

### Next Steps - Laravel 11 Upgrade
- ✅ Update `composer.json` for Laravel 11
- ✅ Resolve Laravel 11 dependency conflicts (temporarily removed model-caching/date-scopes)
- ✅ Run `composer update` successfully
- ✅ Verified Laravel 11 installation **(v11.47.0)**
- ⏳ Commit Laravel 11 changes
- ⏳ Restore removed packages
- ⏳ Frontend Migration

---

---

## Project-Specific Information

### Current System Configuration

**Backend Stack:**
- **Laravel:** 9.19 → Target: 11.x
- **PHP:** 8.2.28 (meets Laravel 11 requirements ✓)
- **Composer:** 2.x

**Frontend Stack:**
- **Vue.js:** 3.2.41 → Target: 3.4.x (latest)
- **Node.js:** v20.19.0 (LTS ✓)
- **Build Tool:** Vite 4.5.0 → Target: 5.x
- **State Management:** Pinia 2.0.23 → Target: 2.1.x
- **Router:** Vue Router 4.1.5 → Target: 4.2.x

### Key Project Packages

**Authentication & Authorization:**
- `php-open-source-saver/jwt-auth` ^2.0 (JWT authentication)
- `laravel/sanctum` ^3.0 → Target: ^4.0
- `@casl/vue` ^2.2.1 (Ability/Permission management)

**Media & Files:**
- `spatie/laravel-medialibrary` ^10.0.7 → Target: ^11.0
- `barryvdh/laravel-dompdf` ^2.0
- `maatwebsite/excel` ^3.1 → Target: ^3.1 (latest)
- `maennchen/zipstream-php` ^2.1

**UI Components:**
- `element-plus` ^2.2.4 → Target: ^2.5.x (latest)
- `@vueform/multiselect` ^1.2.5
- `@vuepic/vue-datepicker` ^7.4.0
- `sweetalert2` ^11.6.13
- `vue-toast-notification` 3.0

**Form Handling:**
- `vee-validate` ^4.5.11 → Target: ^4.12.x

**Utilities:**
- `genealabs/laravel-model-caching` ^0.12.5
- `itsgoingd/clockwork` ^5.2 (debugging)
- `opcodesio/log-viewer` ^v3.14.0
- `predis/predis` ^2.2 (Redis client)

**Custom Packages:**
- `cbagdawala/laravel-id-generator` 1.*
- `laracraft-tech/laravel-date-scopes` v1.0.6
- `riskihajar/terbilang` ^2.0 (number to words)

### Project Structure

**Custom Module System:**
```
resources/
├── modules/          # Feature modules (117+ Vue components)
│   ├── common/       # Shared components & utilities
│   ├── Product/      # Product management
│   ├── buyers/       # Buyer management
│   ├── contracts/    # Contract management
│   ├── dashboard/    # Dashboard views
│   └── ... (40+ feature modules)
├── metronic/         # Metronic theme integration
├── utility/          # Utility functions
└── router/           # Vue Router config
```

**Custom Vite Aliases:**
- `@` → `/resources/metronic`
- `@modules@` → `/resources/modules`
- `@common@` → `/resources/modules/common`
- `@utility@` → `/resources/utility`

**Custom Middleware:**
- `JwtAdminMiddleware` (JWT verification)
- `AdminAuthGates` (CASL ability gates)

**Custom Helpers:**
- `app/Helpers/Auth.php`
- `app/Helpers/System.php`
- `app/Helpers/Response.php`

> [!NOTE]
> This project is already using Vue 3 with Composition API and Pinia, which simplifies the migration. The main focus will be on Laravel framework upgrade and package updates.

## Table of Contents

1. [Pre-Migration Assessment](#1-pre-migration-assessment)
2. [Backup Strategy](#2-backup-strategy)
3. [PHP & System Requirements](#3-php--system-requirements)
4. [Laravel Upgrade Path](#4-laravel-upgrade-path)
5. [Node.js & npm Upgrade](#5-nodejs--npm-upgrade)
6. [Vue.js Migration](#6-vuejs-migration)
7. [Package Updates](#7-package-updates)
8. [Testing & Verification](#8-testing--verification)
9. [Deployment Preparation](#9-deployment-preparation)

---

## 1. Pre-Migration Assessment

### 1.1 Current State Analysis

**Current Pharma POS 2025 versions (as analyzed):**

```bash
# Laravel version
php artisan --version
# Output: Laravel Framework 9.19

# PHP version
php -v
# Output: PHP 8.2.28 (cli) ✓ Ready for Laravel 11

# Node.js version
node -v
# Output: v20.19.0 ✓ Latest LTS

# npm version
npm -v

# Composer version
composer --version
# Should be 2.x
```

**Document current dependencies:**

```bash
# Create a snapshot of current dependencies
composer show > docs/migration-backup/composer-packages-before.txt
npm list --depth=0 > docs/migration-backup/npm-packages-before.txt

# Check Laravel version
grep "laravel/framework" composer.json
```

### 1.2 Compatibility Check

**Review critical dependencies:**

- Check all custom packages for Laravel 11 compatibility
- Review third-party packages on Packagist for Laravel 11 support
- Identify deprecated features you're currently using
- Check Vue.js plugin compatibility with Vue 3

**Create compatibility matrix:**

```bash
# List all packages
composer outdated

# Check for security vulnerabilities
composer audit
```

---

## 2. Backup Strategy

### 2.1 Create Complete Backup

```bash
# Create backup directory
mkdir -p backups/pre-migration-$(date +%Y%m%d)

# Backup database
php artisan backup:run  # if using spatie/laravel-backup
# OR
mysqldump -u username -p database_name > backups/pre-migration-$(date +%Y%m%d)/database.sql

# Backup .env file
cp .env backups/pre-migration-$(date +%Y%m%d)/.env

# Create git tag
git tag -a v1.0-laravel9 -m "Pre-migration snapshot"
git push origin v1.0-laravel9

# Create full project backup
git stash
git stash apply
```

### 2.2 Create Migration Branch

```bash
# Create feature branch for migration
git checkout -b feature/laravel-11-vue3-migration

# Ensure branch is clean
git status
```

---

## 3. PHP & System Requirements

### 3.1 PHP Version Requirements

| Laravel Version | Minimum PHP Version | Recommended PHP Version |
|----------------|---------------------|------------------------|
| Laravel 9      | PHP 8.0             | PHP 8.1                |
| Laravel 10     | PHP 8.1             | PHP 8.2                |
| Laravel 11     | PHP 8.2             | PHP 8.3                |

### 3.2 Upgrade PHP

**For Windows (using XAMPP/Laragon):**

```bash
# Download PHP 8.3 from php.net
# Update system PATH to point to new PHP version
# Verify installation
php -v
```

**For Linux/Ubuntu:**

```bash
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.3 php8.3-cli php8.3-common php8.3-mysql php8.3-xml php8.3-curl php8.3-mbstring php8.3-zip php8.3-gd php8.3-intl php8.3-bcmath
sudo update-alternatives --set php /usr/bin/php8.3
```

**Verify PHP extensions:**

```bash
php -m | grep -E 'pdo|mbstring|tokenizer|xml|ctype|json|bcmath|fileinfo|openssl'
```

### 3.3 Update Composer

```bash
# Update Composer to latest version
composer self-update

# Verify version (should be 2.x)
composer --version
```

---

## 4. Laravel Upgrade Path

> [!WARNING]
> Laravel must be upgraded incrementally: 9 → 10 → 11. Do NOT skip versions!

### 4.1 Stage 1: Laravel 9 to Laravel 10

#### 4.1.1 Update composer.json

```json
{
    "require": {
        "php": "^8.1",
        "laravel/framework": "^10.0",
        "laravel/sanctum": "^3.2",
        "laravel/tinker": "^2.8"
    },
    "require-dev": {
        "fakerphp/faker": "^1.21",
        "laravel/pint": "^1.0",
        "laravel/sail": "^1.21",
        "mockery/mockery": "^1.5.1",
        "nunomaduro/collision": "^7.0",
        "phpunit/phpunit": "^10.0",
        "spatie/laravel-ignition": "^2.0"
    }
}
```

#### 4.1.2 Update Dependencies

```bash
# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Update dependencies
composer update

# If you encounter conflicts
composer update --with-all-dependencies
```

#### 4.1.3 Laravel 10 Breaking Changes

**Update Service Providers:**

```php
// app/Providers/RouteServiceProvider.php
// REMOVE this constant (no longer needed in Laravel 10+)
// public const HOME = '/home';

// Update namespace
protected $namespace = 'App\\Http\\Controllers'; // Remove this line in Laravel 10

// The boot method should look like:
public function boot(): void
{
    $this->routes(function () {
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));

        Route::middleware('web')
            ->group(base_path('routes/web.php'));
    });
}
```

**Update Exception Handler:**

```php
// app/Exceptions/Handler.php
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    // Remove $dontReport and $dontFlash arrays
    
    // Add register method if not exists
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
```

**Update Middleware:**

```php
// app/Http/Middleware/TrustProxies.php
protected $proxies = '*'; // Changed from null

// app/Http/Middleware/TrustHosts.php
use Illuminate\Http\Middleware\TrustHosts as Middleware;
```

**Update Eloquent Models:**

```php
// All models should use proper type hints
use Illuminate\Database\Eloquent\Factories\HasFactory;

class YourModel extends Model
{
    use HasFactory;
    
    // Use array or string for $casts (not deprecated)
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];
}
```

#### 4.1.4 Configuration Updates

**Update config/database.php:**

```php
// For MySQL
'mysql' => [
    'driver' => 'mysql',
    'url' => env('DATABASE_URL'),
    // ... other config
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
    ]) : [],
],
```

**Update config/sanctum.php:**

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --force
```

#### 4.1.5 Test Laravel 10 Migration

```bash
# Run migrations
php artisan migrate

# Run tests
php artisan test

# Clear and rebuild caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start server
php artisan serve
```

### 4.2 Stage 2: Laravel 10 to Laravel 11

#### 4.2.1 Update composer.json

```json
{
    "require": {
        "php": "^8.2",
        "laravel/framework": "^11.0",
        "laravel/sanctum": "^4.0",
        "laravel/tinker": "^2.9"
    },
    "require-dev": {
        "fakerphp/faker": "^1.23",
        "laravel/pint": "^1.13",
        "laravel/sail": "^1.26",
        "mockery/mockery": "^1.6",
        "nunomaduro/collision": "^8.0",
        "phpunit/phpunit": "^11.0",
        "spatie/laravel-ignition": "^2.4"
    }
}
```

#### 4.2.2 Update Dependencies

```bash
composer update --with-all-dependencies
```

#### 4.2.3 Laravel 11 Breaking Changes

**New Application Structure:**

Laravel 11 introduces a streamlined application structure. You have two options:

**Option A: Keep Current Structure (Recommended for existing apps)**

Continue using your current structure. This is safer for existing applications.

**Option B: Adopt New Structure**

Laravel 11 consolidates service providers and middleware. If adopting:

```php
// bootstrap/app.php (NEW in Laravel 11)
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

**Update Middleware Registration:**

```php
// If using new structure, middleware is now in bootstrap/app.php
// If keeping old structure, update app/Http/Kernel.php

// Remove RedirectIfAuthenticated from global middleware
// Add to route-specific middleware instead
```

**Database Changes:**

```php
// config/database.php - Update SQLite configuration
'sqlite' => [
    'driver' => 'sqlite',
    'url' => env('DATABASE_URL'),
    'database' => env('DB_DATABASE', database_path('database.sqlite')),
    'prefix' => '',
    'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
    'busy_timeout' => null,
    'journal_mode' => null,
    'synchronous' => null,
],
```

**Model Casts Update:**

```php
// Models now support AsEnumCollection
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;

protected $casts = [
    'status' => StatusEnum::class,
    'tags' => AsEnumCollection::class.':'.TagEnum::class,
];
```

**Mail Configuration:**

```php
// config/mail.php - Support for multiple mailers
'mailers' => [
    'smtp' => [
        'transport' => 'smtp',
        'url' => env('MAIL_URL'),
        // ...
    ],
    'log' => [
        'transport' => 'log',
        'channel' => env('MAIL_LOG_CHANNEL'),
    ],
],
```

#### 4.2.4 Publish New Configuration Files

```bash
# Publish updated configs
php artisan config:publish

# Update specific configs as needed
php artisan vendor:publish --tag=laravel-assets --force
```

#### 4.2.5 Test Laravel 11 Migration

```bash
# Clear all caches
php artisan optimize:clear

# Run migrations
php artisan migrate

# Run tests
php artisan test

# Verify application
php artisan serve
```

---

## 5. Node.js & npm Upgrade

### 5.1 Install Latest Node.js LTS

**Windows:**

```bash
# Download from nodejs.org (LTS version - currently 20.x or 22.x)
# Or use nvm-windows
nvm install lts
nvm use lts
```

**Linux/Mac:**

```bash
# Using nvm
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
nvm install --lts
nvm use --lts

# Verify
node -v  # Should show v20.x or v22.x
npm -v   # Should show v10.x
```

### 5.2 Update npm

```bash
# Update npm to latest
npm install -g npm@latest

# Verify
npm -v
```

### 5.3 Clear npm Cache

```bash
# Clear npm cache
npm cache clean --force

# Remove node_modules and package-lock.json
rm -rf node_modules
rm package-lock.json
```

---

## 6. Vue.js Migration

> [!CAUTION]
> Vue 2 to Vue 3 migration involves breaking changes. Budget significant time for this upgrade.

### 6.1 Assess Current Vue Setup

**Check current Vue version:**

```bash
grep "vue" package.json
```

**Common Vue 2 stack:**
- Vue 2.x
- Vue Router 3.x
- Vuex 3.x
- vue-template-compiler

**Target Vue 3 stack:**
- Vue 3.x (latest)
- Vue Router 4.x
- Pinia (recommended over Vuex)
- @vitejs/plugin-vue

### 6.2 Update package.json

```json
{
  "devDependencies": {
    "@vitejs/plugin-vue": "^5.0.0",
    "autoprefixer": "^10.4.17",
    "axios": "^1.6.0",
    "laravel-vite-plugin": "^1.0.0",
    "postcss": "^8.4.33",
    "sass": "^1.70.0",
    "vite": "^5.0.0",
    "vue": "^3.4.0"
  },
  "dependencies": {
    "@inertiajs/vue3": "^1.0.0",
    "pinia": "^2.1.7",
    "vue-router": "^4.2.5"
  }
}
```

### 6.3 Migration Breaking Changes

#### 6.3.1 Vue Instance Creation

**Vue 2:**
```javascript
// resources/js/app.js
import Vue from 'vue'
import App from './App.vue'

new Vue({
  render: h => h(App),
}).$mount('#app')
```

**Vue 3:**
```javascript
// resources/js/app.js
import { createApp } from 'vue'
import App from './App.vue'

const app = createApp(App)
app.mount('#app')
```

#### 6.3.2 Vue Router Migration

**Vue 2 (Router 3.x):**
```javascript
import Vue from 'vue'
import VueRouter from 'vue-router'

Vue.use(VueRouter)

const router = new VueRouter({
  mode: 'history',
  routes: [...]
})
```

**Vue 3 (Router 4.x):**
```javascript
import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(),
  routes: [...]
})
```

#### 6.3.3 Vuex to Pinia Migration

**Vue 2 (Vuex 3.x):**
```javascript
// store/index.js
import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    count: 0
  },
  mutations: {
    increment(state) {
      state.count++
    }
  },
  actions: {
    increment({ commit }) {
      commit('increment')
    }
  }
})
```

**Vue 3 (Pinia):**
```javascript
// stores/counter.js
import { defineStore } from 'pinia'

export const useCounterStore = defineStore('counter', {
  state: () => ({
    count: 0
  }),
  actions: {
    increment() {
      this.count++
    }
  }
})
```

#### 6.3.4 Component API Changes

**Filters Removed (Use methods or computed instead):**

**Vue 2:**
```vue
<template>
  <div>{{ price | currency }}</div>
</template>

<script>
export default {
  filters: {
    currency(value) {
      return '$' + value
    }
  }
}
</script>
```

**Vue 3:**
```vue
<template>
  <div>{{ formatCurrency(price) }}</div>
</template>

<script setup>
const formatCurrency = (value) => {
  return '$' + value
}
</script>
```

**Event Bus Removed (Use mitt or provide/inject):**

**Vue 2:**
```javascript
// event-bus.js
import Vue from 'vue'
export const EventBus = new Vue()

// Usage
EventBus.$emit('event-name', data)
EventBus.$on('event-name', handler)
```

**Vue 3:**
```javascript
// Using mitt
import mitt from 'mitt'
export const emitter = mitt()

// Usage
emitter.emit('event-name', data)
emitter.on('event-name', handler)
```

**v-model Changes:**

**Vue 2:**
```vue
<ChildComponent v-model="pageTitle" />

<!-- Child -->
<script>
export default {
  props: ['value'],
  methods: {
    updateValue(val) {
      this.$emit('input', val)
    }
  }
}
</script>
```

**Vue 3:**
```vue
<ChildComponent v-model="pageTitle" />

<!-- Child -->
<script setup>
const props = defineProps(['modelValue'])
const emit = defineEmits(['update:modelValue'])

const updateValue = (val) => {
  emit('update:modelValue', val)
}
</script>
```

#### 6.3.5 Lifecycle Hooks

**Vue 2 Options API:**
```javascript
export default {
  beforeCreate() {},
  created() {},
  beforeMount() {},
  mounted() {},
  beforeUpdate() {},
  updated() {},
  beforeDestroy() {},
  destroyed() {}
}
```

**Vue 3 Composition API:**
```javascript
import { 
  onBeforeMount, 
  onMounted, 
  onBeforeUpdate, 
  onUpdated, 
  onBeforeUnmount, 
  onUnmounted 
} from 'vue'

export default {
  setup() {
    onMounted(() => {
      console.log('mounted!')
    })
    
    onUnmounted(() => {
      console.log('unmounted!')
    })
  }
}
```

### 6.4 Update Vite Configuration

**Create/Update vite.config.js:**

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
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
            '@': path.resolve(__dirname, './resources/js'),
            '~': path.resolve(__dirname, './resources'),
        },
    },
});
```

### 6.5 Update Main App File

**resources/js/app.js:**

```javascript
import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';

// Import global components
import Button from './components/Button.vue';
import Input from './components/Input.vue';

const app = createApp(App);

// Use plugins
app.use(createPinia());
app.use(router);

// Register global components
app.component('Button', Button);
app.component('Input', Input);

// Mount app
app.mount('#app');
```

### 6.6 Convert Components to Vue 3

**Update each component:**

1. Remove `Vue.extend` or `export default`
2. Use `<script setup>` syntax (recommended)
3. Update lifecycle hooks
4. Replace `$emit` declarations
5. Update `v-model` usage

**Example Component Migration:**

**Vue 2:**
```vue
<template>
  <div>
    <h1>{{ title }}</h1>
    <button @click="increment">Count: {{ count }}</button>
  </div>
</template>

<script>
export default {
  name: 'Counter',
  props: {
    initialCount: {
      type: Number,
      default: 0
    }
  },
  data() {
    return {
      count: this.initialCount,
      title: 'Counter'
    }
  },
  methods: {
    increment() {
      this.count++
      this.$emit('updated', this.count)
    }
  },
  mounted() {
    console.log('Component mounted')
  }
}
</script>
```

**Vue 3 (Composition API with <script setup>):**
```vue
<template>
  <div>
    <h1>{{ title }}</h1>
    <button @click="increment">Count: {{ count }}</button>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
  initialCount: {
    type: Number,
    default: 0
  }
})

const emit = defineEmits(['updated'])

const count = ref(props.initialCount)
const title = ref('Counter')

const increment = () => {
  count.value++
  emit('updated', count.value)
}

onMounted(() => {
  console.log('Component mounted')
})
</script>
```

### 6.7 Install Dependencies

```bash
# Remove old dependencies
npm uninstall vue-template-compiler vue-loader@15

# Install new dependencies
npm install

# If using Inertia.js
npm install @inertiajs/vue3

# If using Element Plus (UI library)
npm install element-plus

# If using event bus
npm install mitt
```

---

## 7. Package Updates

### 7.1 Pharma POS Specific Package Updates

**Update project-specific packages:**

```bash
# Core Laravel packages
composer require laravel/sanctum:^4.0
composer require laravel/tinker:^2.9

# Spatie Media Library (critical for file management)
composer require spatie/laravel-medialibrary:^11.0

# PDF & Excel (critical for reports)
composer require barryvdh/laravel-dompdf:^3.0
composer require maatwebsite/excel:^3.1

# JWT Authentication (check compatibility)
composer require php-open-source-saver/jwt-auth:^2.3

# Model Caching
composer require genealabs/laravel-model-caching:^0.13

# Development tools
composer require laravel/pint:^1.13 --dev
composer require itsgoingd/clockwork:^5.2
composer require opcodesio/log-viewer:^3.14 --dev

# Custom packages (verify compatibility first)
composer require cbagdawala/laravel-id-generator:1.*
composer require laracraft-tech/laravel-date-scopes:v1.0.6
composer require riskihajar/terbilang:^2.0

# Other utilities
composer require picqer/php-barcode-generator:^2.4
composer require predis/predis:^2.2
composer require maennchen/zipstream-php:^3.0
```

> [!WARNING]
> **Critical Package Notes:**
> - `cbagdawala/laravel-id-generator` - Custom package, verify Laravel 11 compatibility
> - `genealabs/laravel-model-caching` - May need updates for Laravel 11
> - `php-open-source-saver/jwt-auth` - Essential for authentication, test thoroughly
> - `laracraft-tech/laravel-date-scopes` - Check for Laravel 11 support

### 7.2 Pharma POS Frontend Package Updates

```bash
# Core Vue ecosystem
npm install vue@latest                    # 3.2.41 → 3.4.x
npm install vue-router@latest             # 4.1.5 → 4.2.x
npm install pinia@latest                  # 2.0.23 → 2.1.x

# Build tools
npm install vite@latest                   # 4.5.0 → 5.x
npm install @vitejs/plugin-vue@latest     # 4.5.0 → 5.x
npm install laravel-vite-plugin@latest    # 0.7.8 → 1.x

# UI Components (critical for application)
npm install element-plus@latest           # 2.2.4 → 2.5.x
npm install @vueform/multiselect@latest   # 1.2.5 → latest
npm install @vuepic/vue-datepicker@latest # 7.4.0 → latest
npm install sweetalert2@latest            # 11.6.13 → latest
npm install vue-toast-notification@latest # 3.0 → latest

# Form validation
npm install vee-validate@latest           # 4.5.11 → 4.12.x

# Charts & Calendar
npm install apexcharts@latest             # 4.7.0 → latest
npm install vue3-apexcharts@latest        # 1.8.0 → latest
npm install @fullcalendar/core@latest
npm install @fullcalendar/vue3@latest

# Utilities
npm install axios@latest                  # 1.1.3 → 1.6.x
npm install mitt@latest                   # 3.0.0 → latest
npm install moment@latest                 # 2.29.1 → latest

# UI Framework
npm install bootstrap@latest              # 5.2.2 → 5.3.x
npm install @popperjs/core@latest
npm install bootstrap-icons@latest
npm install @fortawesome/fontawesome-free@latest

# Editors
npm install @ckeditor/ckeditor5-vue@latest
npm install @ckeditor/ckeditor5-build-classic@latest
npm install @tinymce/tinymce-vue@latest
npm install quill@latest

# Abilities & i18n
npm install @casl/vue@latest              # 2.2.1 → latest
npm install @casl/ability@latest          # 6.4.0 → latest
npm install vue-i18n@latest               # 9.1.8 → 9.x

# Dev dependencies
npm install sass@latest --save-dev
npm install typescript@latest --save-dev
npm install vue-tsc@latest --save-dev
```

> [!IMPORTANT]
> **Breaking Changes to Watch:**
> - **Vite 4 → 5**: Configuration changes, check vite.config.js
> - **Element Plus 2.2 → 2.5**: Minor API changes, check component usage
> - **VeeValidate 4.5 → 4.12**: Improved TypeScript support, test all forms

### 7.3 Check for Breaking Changes

**Review package changelogs:**

- Check each package's GitHub releases
- Review migration guides
- Test critical functionality after each package update

---

## 8. Testing & Verification

### 8.1 Run All Tests

```bash
# PHP Unit tests
php artisan test

# With coverage
php artisan test --coverage

# Frontend tests (if configured)
npm run test
```

### 8.2 Pharma POS Manual Testing Checklist

**Authentication & Authorization:**
- [ ] JWT Admin login/logout (`JwtAdminMiddleware`)
- [ ] Sanctum API authentication
- [ ] CASL ability/permission gates (`@casl/vue`)
- [ ] Session management

**Core Business Functions:**
- [ ] Product management (Create/Edit/View/Delete)
- [ ] Purchase invoice creation and management
- [ ] Sales invoice creation and management
- [ ] Inventory adjustments
- [ ] Contract management (Create/Edit/View)
- [ ] Buyer/Agent management
- [ ] Order processing

**Media & File Operations:**
- [ ] File uploads via Spatie Media Library
- [ ] PDF generation (invoices, reports) - `barryvdh/laravel-dompdf`
- [ ] Excel import/export - `maatwebsite/excel`
- [ ] Barcode generation - `picqer/php-barcode-generator`

**UI Components:**
- [ ] Element Plus components rendering correctly
- [ ] Multiselect dropdowns (`@vueform/multiselect`)
- [ ] Date pickers (`@vuepic/vue-datepicker`)
- [ ] Toast notifications (`vue-toast-notification`)
- [ ] Sweet alerts (`sweetalert2`)
- [ ] Charts/Dashboards (ApexCharts)
- [ ] Full Calendar views

**Forms & Validation:**
- [ ] All forms using VeeValidate
- [ ] Form submissions and error handling
- [ ] Custom validation rules

**Internationalization:**
- [ ] Language switching (vue-i18n)
- [ ] English/German language support
- [ ] Number formatting (terbilang)

**Module-Specific Tests:**
- [ ] Dashboard loading and data display
- [ ] All 40+ feature modules accessible
- [ ] Custom Vite aliases working (`@modules@`, `@common@`, etc.)
- [ ] Metronic theme components

**Backend Services:**
- [ ] API endpoints responding correctly
- [ ] Model caching (`genealabs/laravel-model-caching`)
- [ ] Redis integration (`predis/predis`)
- [ ] Log viewer (`opcodesio/log-viewer`)
- [ ] Clockwork debugging
- [ ] Custom helper functions (Auth, System, Response)

### 8.3 Performance Testing

```bash
# Clear all caches
php artisan optimize:clear

# Build for production
npm run build

# Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Test page load times
# Monitor memory usage
# Check database query performance
```

### 8.4 Error Monitoring

**Check logs:**

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Check for deprecation warnings
grep -r "deprecated" storage/logs/
```

---

## 9. Deployment Preparation

### 9.1 Update Environment Files

**Update .env.example:**

```env
APP_NAME=Laravel
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://localhost

# Laravel 11 specific
APP_MAINTENANCE_DRIVER=file
APP_MAINTENANCE_STORE=database

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=

# Cache
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# New in Laravel 11
FILESYSTEM_DISK=local
```

### 9.2 Update Deployment Scripts

**Update composer scripts:**

```json
{
  "scripts": {
    "post-autoload-dump": [
      "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
      "@php artisan package:discover --ansi",
      "@php artisan vendor:publish --tag=laravel-assets --ansi --force"
    ],
    "post-update-cmd": [
      "@php artisan vendor:publish --tag=laravel-assets --ansi --force"
    ],
    "post-root-package-install": [
      "@php -r \"file_exists('.env') || copy('.env.example', '.env');\""
    ],
    "post-create-project-cmd": [
      "@php artisan key:generate --ansi",
      "@php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\"",
      "@php artisan migrate --graceful --ansi"
    ]
  }
}
```

### 9.3 Server Requirements

**Update server requirements:**

- PHP >= 8.2
- MySQL >= 8.0 or MariaDB >= 10.3
- Node.js >= 20.x
- Composer >= 2.5
- Redis (recommended for cache/sessions)
- Supervisor (for queues)

### 9.4 Deployment Checklist

```bash
# On production server

# 1. Pull latest code
git pull origin main

# 2. Install dependencies
composer install --optimize-autoloader --no-dev
npm ci

# 3. Build assets
npm run build

# 4. Run migrations
php artisan migrate --force

# 5. Clear and cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6. Restart services
php artisan queue:restart
sudo supervisorctl restart all

# 7. Restart web server
sudo systemctl reload nginx
# OR
sudo systemctl reload apache2
```

---

## Common Issues & Solutions

### Issue 1: Composer Memory Limit

```bash
# Increase memory limit temporarily
COMPOSER_MEMORY_LIMIT=-1 composer update
```

### Issue 2: Node.js Version Conflicts

```bash
# Use specific Node.js version
nvm use 20
# OR specify in package.json
"engines": {
  "node": ">=20.0.0"
}
```

### Issue 3: Vue 3 Component Not Rendering

**Check:**
- Is component registered globally or imported locally?
- Are you using `<script setup>` correctly?
- Check browser console for errors

### Issue 4: Laravel Mix to Vite Migration

**Replace Laravel Mix with Vite:**

```bash
# Remove Mix
npm uninstall laravel-mix

# Install Vite
npm install --save-dev vite laravel-vite-plugin

# Update blade files
# Replace: <script src="{{ mix('js/app.js') }}">
# With: @vite(['resources/css/app.css', 'resources/js/app.js'])
```

### Issue 5: Route Model Binding Changes

**Laravel 11 uses string keys by default for route model binding:**

```php
// If using integer IDs, explicitly configure
Route::bind('user', function ($value) {
    return User::where('id', $value)->firstOrFail();
});
```

---

## Additional Resources

### Official Documentation
- [Laravel 10 Upgrade Guide](https://laravel.com/docs/10.x/upgrade)
- [Laravel 11 Upgrade Guide](https://laravel.com/docs/11.x/upgrade)
- [Vue 3 Migration Guide](https://v3-migration.vuejs.org/)
- [Vue Router Migration](https://router.vuejs.org/guide/migration/)
- [Pinia Documentation](https://pinia.vuejs.org/)

### Video Tutorials
- Laracasts: Laravel 11 What's New
- Vue Mastery: Vue 3 Composition API
- YouTube: Laravel 11 Migration Series

### Community Support
- Laravel Discord
- Vue.js Discord
- Stack Overflow
- Laravel.io Forums

---

## Migration Timeline Estimate (Pharma POS 2025)

| Phase | Duration | Description |
|-------|----------|-------------|
| **Assessment** | ✓ Complete | Project analyzed, current state documented |
| **Backup & Setup** | 0.5 day | Create backups, setup migration branch |
| **Laravel 9→10** | 2-3 days | Upgrade Laravel, test thoroughly |
| **Laravel 10→11** | 3-4 days | Upgrade Laravel, custom packages, test |
| **Node.js Upgrade** | ✓ Complete | Already on v20.19.0 LTS |
| **Vue Package Updates** | 3-5 days | Update 40+ Vue packages, test components |
| **Custom Package Testing** | 2-3 days | Verify `cbagdawala/laravel-id-generator`, etc. |
| **Module Testing** | 5-7 days | Test 117+ Vue components across 40+ modules |
| **Form Validation** | 2-3 days | Test all VeeValidate forms |
| **Integration Testing** | 3-4 days | JWT, CASL, Media Library, PDF/Excel |
| **Documentation** | 1-2 days | Update docs, deployment guides |
| **Total** | **19-32 days** | For this large-scale application |

> [!NOTE]
> **Project Complexity Factors:**
> - 117+ Vue components to test
> - 40+ feature modules
> - Custom module system with aliases
> - JWT + CASL authentication stack
> - Heavy use of third-party UI components
> - PDF/Excel generation critical for business
> - Custom package compatibility verification needed

> [!TIP]
> Plan for 20-40% buffer time above these estimates for unexpected issues and edge cases.

---

## Post-Migration Optimization

### 1. Performance Optimization

```bash
# Use PHP 8.3 JIT compilation
# Add to php.ini:
opcache.enable=1
opcache.jit_buffer_size=100M
opcache.jit=1255

# Optimize Composer autoloader
composer dump-autoload --optimize --classmap-authoritative

# Enable Laravel Octane (optional)
composer require laravel/octane
php artisan octane:install
```

### 2. Security Hardening

```bash
# Update all security packages
composer audit
npm audit fix

# Enable HTTPS only
# Force HTTPS in AppServiceProvider
URL::forceScheme('https');

# Update CSP headers
# Add security headers in middleware
```

### 3. Monitoring Setup

```bash
# Install Laravel Telescope
composer require laravel/telescope
php artisan telescope:install
php artisan migrate

# Install Laravel Horizon (for queues)
composer require laravel/horizon
php artisan horizon:install
```

---

## Conclusion

This migration is a significant undertaking but will modernize your application with:
- Latest security patches
- Improved performance
- Modern development experience
- Better type safety
- Enhanced developer tools

**Key Takeaways:**
1. ✅ Test thoroughly at each stage
2. ✅ Keep backups before major changes
3. ✅ Update incrementally, not all at once
4. ✅ Read official upgrade guides
5. ✅ Budget adequate time for Vue 3 migration

Good luck with your migration! 🚀
