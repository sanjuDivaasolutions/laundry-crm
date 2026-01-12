# Language Files Reference

This folder contains organized language translation files for the application.

## Structure

```
lang/
├── en/                   # English translations
│   ├── cruds.php        # Main CRUD translations (modules, fields, actions)
│   ├── custom-cruds.php # Custom/additional CRUD translations
│   ├── validation.php   # Laravel validation messages
│   ├── auth.php         # Authentication messages
│   ├── passwords.php    # Password reset messages
│   └── pagination.php   # Pagination messages
├── de/                   # German translations (Deutsch)
│   ├── cruds.php        # Haupt-CRUD-Übersetzungen
│   ├── custom-cruds.php # Benutzerdefinierte CRUD-Übersetzungen
│   ├── validation.php   # Laravel-Validierungsnachrichten
│   ├── auth.php         # Authentifizierungsnachrichten
│   ├── passwords.php    # Passwort-Zurücksetzen-Nachrichten
│   └── pagination.php   # Paginierungsnachrichten
└── README.md             # This file
```

## Available Languages

- **English (en)** - Default language
- **German (de)** - Deutsche Übersetzungen

## How to Use

### In PHP/Laravel:
```php
// Using translation helper with cruds.php structure
__('cruds.buyer.title')                  // Returns: Buyer (en) / Käufer (de)
__('cruds.buyer.title_singular')         // Returns: Buyer (en) / Käufer (de)
__('cruds.buyer.fields.name')            // Returns: Name (en) / Name (de)
__('cruds.general.fields.dashboard')     // Returns: Dashboard (en) / Dashboard (de)
__('cruds.general.fields.save')          // Returns: Save (en) / Speichern (de)
__('cruds.product.title')                // Returns: Products (en) / Produkte (de)
__('cruds.product.fields.sku')           // Returns: SKU (en) / SKU (de)
```

### In Vue/JavaScript:
```javascript
// Using $t() or i18n with cruds.php structure
$t('cruds.buyer.title')
$t('cruds.buyer.fields.name')
$t('cruds.general.fields.dashboard')
$t('cruds.product.title')
$t('cruds.product.fields.sku')
```

## Adding New Languages

To add a new language (e.g., French):

1. Create a new folder: `lang/fr/`
2. Copy all PHP files from `lang/en/` to `lang/fr/`
3. Translate the values (right side of `=>`) to French
4. Add the language to the database:
   ```php
   Language::create([
       'name' => 'Français',
       'locale' => 'fr',
       'active' => 1
   ]);
   ```
5. Run the translation sync script to populate the database

## Database Sync

The application uses a **database-driven translation system**. The PHP files in this folder serve as the **source of truth** for all translations.

### Sync Workflow

To sync the database from the PHP files, run:
```bash
php sync_from_cruds.php
```

This script:
- Reads all translations from `cruds.php` and `custom-cruds.php`
- Creates or updates language terms in the database
- Maintains both English and German translations
- Preserves the nested structure (buyer.title, buyer.fields.name, etc.)

### When to Sync

Run the sync script whenever you:
- Add new modules or fields to cruds.php
- Update existing translations
- Deploy to a new environment
- Add a new language

## File Organization

### cruds.php
Main file containing all CRUD-related translations organized by module:

**Structure:**
```php
'moduleName' => [
    'title' => 'Module Title (plural)',
    'title_singular' => 'Module Title (singular)',
    'fields' => [
        'field_name' => 'Field Label',
        ...
    ],
],
```

**Contains:**
- `general.fields.*` - Common UI elements (dashboard, save, cancel, etc.)
- `buyer.*` - Buyer/Customer module translations
- `product.*` - Product module translations
- `salesInvoice.*` - Sales invoice translations
- `purchaseOrder.*` - Purchase order translations
- ... (all other modules)

Each module includes:
- `title` - Plural form (e.g., "Buyers", "Products")
- `title_singular` - Singular form (e.g., "Buyer", "Product")
- `fields.*` - All field labels for that module

### custom-cruds.php
Additional custom translations not in the main cruds.php:
- Extended module fields
- Custom business logic translations
- Company-specific translations

### validation.php
Laravel validation error messages (standard Laravel file)

### auth.php
Authentication-related messages (standard Laravel file)

### passwords.php
Password reset messages (standard Laravel file)

## Translation Guidelines

1. **Keep it consistent** - Use the same translation for the same term across all files
2. **Context matters** - Some words may need different translations based on context
3. **Length considerations** - German words are often longer; consider UI space
4. **Professional tone** - Use formal address ("Sie" in German) for business software
5. **Test in UI** - Always verify translations look good in the actual interface

## Maintenance

These files should be updated when:
- New features are added
- New modules are created
- Field labels change
- UI text is modified

After updating these files, always run:
```bash
php sync_from_cruds.php
```

## Notes

- **cruds.php and custom-cruds.php are the source of truth**
- The database stores translations for runtime use
- Changes to database translations should be reflected back to PHP files
- Always keep PHP files and database in sync
- Use version control for the PHP files
- Database syncs automatically from PHP files via sync script

## Current Statistics

After latest sync:
- **678 total translation keys**
- **503 new terms created**
- **81 existing terms updated**
- Organized across 76+ modules
- Full English and German support
