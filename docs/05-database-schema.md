# Database Schema & Relationships

## Overview

The database follows a comprehensive ERP design with proper normalization, foreign key constraints, and multi-tenant architecture. The schema supports complex business operations including inventory management, order processing, contract management, and financial tracking.

## Core Architecture

### Multi-tenancy Support
- **Company-based isolation**: All business data is scoped to companies
- **User-company relationships**: Users can belong to multiple companies
- **Data security**: Global scopes ensure data isolation

### Key Features
- **Soft Deletes**: Most entities support soft deletion for audit trails
- **Timestamps**: Created/updated timestamps on all entities
- **Foreign Key Constraints**: Proper referential integrity
- **Polymorphic Relationships**: Flexible associations (taxes, activities)
- **Batch Tracking**: Comprehensive inventory tracking

## Core Entity Groups

### 1. Authentication & Authorization

#### Users (`users`)
```sql
- id (bigint, primary key)
- name (string)
- email (string, unique)
- password (string)
- active (boolean)
- settings (json) -- User preferences and configurations
- email_verified_at (timestamp)
- remember_token (string)
- timestamps
```

#### Roles (`roles`)
```sql
- id (bigint, primary key)
- title (string)
- timestamps
```

#### Permissions (`permissions`)
```sql
- id (bigint, primary key)  
- title (string)
- group_id (foreign key -> permission_groups.id)
- timestamps
```

#### Permission Groups (`permission_groups`)
```sql
- id (bigint, primary key)
- title (string)
- timestamps
```

**Relationships:**
- Users ↔ Roles (Many-to-Many via `role_user`)
- Roles ↔ Permissions (Many-to-Many via `permission_role`)

### 2. Multi-tenant Structure

#### Companies (`companies`)
```sql
- id (bigint, primary key)
- code (string)
- name (string) 
- address_1 (string)
- address_2 (string)
- gst_number (string, nullable)
- phone (string, nullable)
- active (boolean)
- user_id (foreign key -> users.id)
- warehouse_id (foreign key -> warehouses.id, nullable)
- timestamps
```

**Key Features:**
- Each company has a default warehouse
- Users can manage multiple companies
- All business data is company-scoped

### 3. Geographic Data

#### Countries (`countries`)
```sql
- id (bigint, primary key)
- name (string)
- code (string)
- timestamps
```

#### States (`states`)  
```sql
- id (bigint, primary key)
- name (string)
- code (string)
- country_id (foreign key -> countries.id)
- timestamps
```

#### Cities (`cities`)
```sql
- id (bigint, primary key)
- name (string)
- state_id (foreign key -> states.id)
- timestamps
```

### 4. Customer & Supplier Management

#### Buyers/Customers (`buyers`)
```sql
- id (bigint, primary key)
- code (string)
- display_name (string)
- name (string)
- active (boolean)
- remarks (text, nullable)
- phone (string, nullable)  
- email (string, nullable)
- stripe_id (string, nullable, indexed) -- Stripe customer ID
- pm_type (string, nullable) -- Payment method type
- pm_last_four (string(4), nullable) -- Last 4 digits of payment method
- trial_ends_at (timestamp, nullable)
- billing_address_id (foreign key -> contact_addresses.id)
- shipping_address_id (foreign key -> contact_addresses.id)
- company_id (foreign key -> companies.id)
- timestamps
- soft_deletes
```

#### Suppliers (`suppliers`)
```sql
- id (bigint, primary key)
- code (string)
- display_name (string) 
- name (string)
- active (boolean)
- remarks (text, nullable)
- phone (string, nullable)
- email (string, nullable)
- billing_address_id (foreign key -> contact_addresses.id)
- company_id (foreign key -> companies.id)
- timestamps
- soft_deletes
```

#### Contact Addresses (`contact_addresses`)
```sql
- id (bigint, primary key)
- name (string)
- address_1 (string)
- address_2 (string, nullable)
- postal_code (string, nullable)
- phone (string, nullable)
- city_id (foreign key -> cities.id)
- state_id (foreign key -> states.id)
- timestamps
```

### 5. Product Management

#### Categories (`categories`)
```sql
- id (bigint, primary key)
- name (string)
- code (string)
- description (text, nullable)
- active (boolean)
- company_id (foreign key -> companies.id)
- timestamps
```

#### Products (`products`)
```sql
- id (bigint, primary key)
- code (string, unique)
- type (string) -- product/service
- name (string)
- sku (string, unique)
- description (text, nullable)
- active (boolean)
- has_inventory (boolean) -- Whether to track inventory
- manufacturer (string, nullable)
- is_returnable (boolean)
- category_id (foreign key -> categories.id)
- unit_id (foreign key -> units.id)
- company_id (foreign key -> companies.id)
- timestamps
```

#### Product Features (`product_features`)
```sql
- id (bigint, primary key)
- product_id (foreign key -> products.id)
- feature_id (foreign key -> features.id)
- value (string)
- timestamps
```

#### Product Prices (`product_prices`)
```sql
- id (bigint, primary key)
- product_id (foreign key -> products.id)
- price (decimal(10,2))
- cost (decimal(10,2))
- currency_id (foreign key -> currencies.id)
- timestamps
```

#### Units (`units`)
```sql
- id (bigint, primary key)
- name (string)
- symbol (string)
- timestamps
```

### 6. Inventory Management

#### Warehouses (`warehouses`)
```sql
- id (bigint, primary key)
- name (string)
- code (string)
- address (text, nullable)
- active (boolean)
- company_id (foreign key -> companies.id)
- timestamps
```

#### Shelves (`shelves`)
```sql
- id (bigint, primary key)
- name (string)
- warehouse_id (foreign key -> warehouses.id)
- active (boolean)
- company_id (foreign key -> companies.id)
- timestamps
```

#### Product Batches (`product_batches`)
```sql
- id (bigint, primary key)
- batch_number (string)
- expiry_date (date, nullable)
- product_id (foreign key -> products.id)
- company_id (foreign key -> companies.id)
- timestamps
```

#### Product Inventories (`product_inventories`)
```sql
- id (bigint, primary key)
- product_id (foreign key -> products.id)
- warehouse_id (foreign key -> warehouses.id)
- shelf_id (foreign key -> shelves.id)
- batch_id (foreign key -> product_batches.id, nullable)
- quantity (decimal(10,2))
- cost_price (decimal(10,2))
- timestamps
```

#### Product Stocks (`product_stocks`)
```sql
- id (bigint, primary key)
- product_id (foreign key -> products.id)
- warehouse_id (foreign key -> warehouses.id)
- quantity (decimal(10,2))
- reserved_quantity (decimal(10,2))
- available_quantity (decimal(10,2))
- timestamps
```

#### Inventory Adjustments (`inventory_adjustments`)
```sql
- id (bigint, primary key)
- product_id (foreign key -> products.id)
- warehouse_id (foreign key -> warehouses.id)
- adjustment_type (string) -- increase/decrease
- quantity (decimal(10,2))
- reason (text)
- user_id (foreign key -> users.id)
- company_id (foreign key -> companies.id)
- timestamps
```

### 7. Purchase Operations

#### Purchase Orders (`purchase_orders`)
```sql
- id (bigint, primary key)
- po_number (string, unique)
- date (date)
- expected_delivery_date (date, nullable)
- supplier_id (foreign key -> suppliers.id)
- warehouse_id (foreign key -> warehouses.id)
- payment_term_id (foreign key -> payment_terms.id)
- currency_id (foreign key -> currencies.id)
- currency_rate (decimal(11,5), default: 1)
- sub_total (decimal(10,2))
- tax_total (decimal(10,2))
- grand_total (decimal(10,2))
- status (string)
- remark (text, nullable)
- user_id (foreign key -> users.id)
- company_id (foreign key -> companies.id)
- timestamps
```

#### Purchase Order Items (`purchase_order_items`)
```sql
- id (bigint, primary key)
- purchase_order_id (foreign key -> purchase_orders.id)
- product_id (foreign key -> products.id)
- unit_id (foreign key -> units.id)
- quantity (decimal(10,2))
- rate (decimal(10,2))
- amount (decimal(10,2))
- timestamps
```

#### Purchase Invoices (`purchase_invoices`)
```sql
- id (bigint, primary key)
- invoice_number (string)
- date (date)
- due_date (date, nullable)
- purchase_order_id (foreign key -> purchase_orders.id, nullable)
- supplier_id (foreign key -> suppliers.id)
- warehouse_id (foreign key -> warehouses.id)
- payment_term_id (foreign key -> payment_terms.id)
- sub_total (decimal(10,2))
- tax_total (decimal(10,2))
- grand_total (decimal(10,2))
- company_id (foreign key -> companies.id)
- timestamps
```

### 8. Sales Operations

#### Quotations (`quotations`)
```sql
- id (bigint, primary key)
- quotation_number (string, unique)
- date (date)
- valid_until (date, nullable)
- buyer_id (foreign key -> buyers.id)
- warehouse_id (foreign key -> warehouses.id)
- payment_term_id (foreign key -> payment_terms.id)
- sub_total (decimal(10,2))
- tax_total (decimal(10,2))
- grand_total (decimal(10,2))
- status (string)
- user_id (foreign key -> users.id)
- company_id (foreign key -> companies.id)
- timestamps
```

#### Sales Orders (`sales_orders`)
```sql
- id (bigint, primary key)
- so_number (string, unique)
- date (date)
- expected_delivery_date (date, nullable)
- buyer_id (foreign key -> buyers.id)
- warehouse_id (foreign key -> warehouses.id)
- payment_term_id (foreign key -> payment_terms.id)
- quotation_id (foreign key -> quotations.id, nullable)
- sub_total (decimal(10,2))
- tax_total (decimal(10,2))
- grand_total (decimal(10,2))
- status (string)
- user_id (foreign key -> users.id)
- company_id (foreign key -> companies.id)
- timestamps
```

#### Sales Invoices (`sales_invoices`)
```sql
- id (bigint, primary key)
- invoice_number (string)
- date (date)
- due_date (date, nullable)
- sales_order_id (foreign key -> sales_orders.id, nullable)
- buyer_id (foreign key -> buyers.id)
- agent_id (foreign key -> suppliers.id, nullable) -- Sales agent
- warehouse_id (foreign key -> warehouses.id)
- payment_term_id (foreign key -> payment_terms.id)
- state_id (foreign key -> states.id) -- For tax calculation
- type (string) -- pickup/delivery
- order_type (string) -- product/service/contract
- reference_no (string, nullable)
- currency_rate (decimal(11,5), default: 1)
- sub_total (decimal(10,2))
- tax_total (decimal(10,2))
- tax_rate (decimal(10,2), nullable)
- grand_total (decimal(10,2))
- commission (decimal(10,2), nullable) -- Commission percentage
- commission_total (decimal(10,2), nullable) -- Commission amount
- is_taxable (boolean, default: true)
- remark (text, nullable)
- user_id (foreign key -> users.id)
- company_id (foreign key -> companies.id)
- timestamps
```

#### Order Items (for all order types)
```sql
- id (bigint, primary key)
- [order_type]_id (foreign key to parent order)
- product_id (foreign key -> products.id)
- unit_id (foreign key -> units.id)
- shelf_id (foreign key -> shelves.id, nullable)
- quantity (decimal(10,2))
- rate (decimal(10,2))
- amount (decimal(10,2))
- timestamps
```

### 9. Contract Management

#### Contracts (`contracts`)
```sql
- id (bigint, primary key)
- code (string, unique)
- date (date)
- buyer_id (foreign key -> buyers.id)
- other_terms (text, nullable)
- remark (text, nullable)
- stripe_product (integer, nullable) -- Stripe product ID
- stripe_product_price (integer, nullable) -- Stripe price ID
- stripe_subscription_meta (text, nullable) -- Subscription metadata
- company_id (foreign key -> companies.id)
- timestamps
```

#### Contract Items (`contract_items`)
```sql
- id (bigint, primary key)
- contract_id (foreign key -> contracts.id)
- product_id (foreign key -> products.id)
- quantity (decimal(10,2))
- rate (decimal(10,2))
- amount (decimal(10,2))
- timestamps
```

#### Contract Terms (`contract_terms`)
```sql
- id (bigint, primary key)
- title (string)
- description (text)
- active (boolean)
- company_id (foreign key -> companies.id)
- timestamps
```

### 10. Tax Management System

#### Tax Classes (`tax_classes`)
```sql
- id (bigint, primary key)
- name (string)
- description (text, nullable)
- active (boolean)
- company_id (foreign key -> companies.id)
- timestamps
```

#### Tax Rates (`tax_rates`)
```sql
- id (bigint, primary key)
- name (string)
- rate (decimal(5,4)) -- Tax rate percentage
- tax_class_id (foreign key -> tax_classes.id)
- active (boolean)
- company_id (foreign key -> companies.id)
- timestamps
```

#### Order Tax Details (`order_tax_details`)
**Polymorphic relationship for taxes on orders**
```sql
- id (bigint, primary key)
- taxable_type (string) -- Model type (sales_invoices, purchase_invoices, etc.)
- taxable_id (bigint) -- Model ID  
- tax_rate_id (foreign key -> tax_rates.id)
- amount (decimal(15,4)) -- Calculated tax amount
- priority (integer, default: 1) -- Tax calculation order
- timestamps
```

### 11. Financial Management

#### Payment Terms (`payment_terms`)
```sql
- id (bigint, primary key)
- name (string)
- days (integer) -- Payment due days
- active (boolean)
- timestamps
```

#### Payment Modes (`payment_modes`)
```sql
- id (bigint, primary key)
- name (string)
- active (boolean)
- timestamps
```

#### Payments (`payments`)
```sql
- id (bigint, primary key)
- payment_number (string)
- date (date)
- amount (decimal(10,2))
- payment_mode_id (foreign key -> payment_modes.id)
- reference_no (string, nullable)
- remark (text, nullable)
- company_id (foreign key -> companies.id)
- timestamps
```

#### Expenses (`expenses`)
```sql
- id (bigint, primary key)
- expense_number (string)
- date (date)
- amount (decimal(10,2))
- expense_type_id (foreign key -> expense_types.id)
- description (text, nullable)
- user_id (foreign key -> users.id)
- company_id (foreign key -> companies.id)
- timestamps
```

### 12. Logistics & Shipping

#### Shipments (`shipments`)
```sql
- id (bigint, primary key)
- tracking_number (string, nullable)
- shipment_date (date)
- expected_delivery_date (date, nullable)
- shipment_mode_id (foreign key -> shipment_modes.id)
- company_id (foreign key -> companies.id)
- timestamps
```

#### Packages (`packages`)
```sql
- id (bigint, primary key)
- package_number (string)
- sales_invoice_id (foreign key -> sales_invoices.id)
- shipment_id (foreign key -> shipments.id, nullable)
- weight (decimal(8,2), nullable)
- dimensions (string, nullable)
- company_id (foreign key -> companies.id)
- timestamps
```

### 13. Activity Tracking

#### Activity Tables (for audit trails)
- `sales_order_activities`
- `sales_invoice_activities`  
- `estimate_activities`

**Structure:**
```sql
- id (bigint, primary key)
- [parent]_id (foreign key to parent record)
- description (text)
- user_id (foreign key -> users.id)
- timestamps
```

### 14. Media Management

#### Media (`media` - Spatie Media Library)
```sql
- id (bigint, primary key)
- model_type (string) -- Polymorphic model type
- model_id (bigint) -- Polymorphic model ID
- uuid (string)
- collection_name (string)
- name (string)
- file_name (string)
- mime_type (string, nullable)
- disk (string)
- conversions_disk (string)  
- size (bigint)
- manipulations (json)
- custom_properties (json)
- generated_conversions (json)
- responsive_images (json)
- order_column (integer, nullable)
- timestamps
```

### 15. Localization

#### Languages (`languages`)
```sql
- id (bigint, primary key)
- name (string)
- code (string)
- active (boolean)
- timestamps
```

#### Language Terms (`language_terms`)
```sql
- id (bigint, primary key)
- key (string)
- group_id (foreign key -> language_term_groups.id)
- timestamps
```

#### Translations (`translations`)
```sql
- id (bigint, primary key)
- term_id (foreign key -> language_terms.id)
- language_id (foreign key -> languages.id)
- value (text)
- timestamps
```

## Key Relationships Summary

### Multi-tenant Relationships
- Users → Companies (Many-to-Many)
- All business entities → Company (Many-to-One)

### Customer Management
- Buyers → Contact Addresses (One-to-Many for billing/shipping)
- Buyers → Orders/Invoices (One-to-Many)

### Inventory Relationships
- Products → Categories (Many-to-One)
- Products → Product Inventories (One-to-Many)
- Warehouses → Shelves (One-to-Many)
- Product Stocks → Products + Warehouses (Composite)

### Order Processing Flow
- Quotations → Sales Orders → Sales Invoices
- Purchase Orders → Purchase Invoices → Payments

### Polymorphic Relationships
- Order Tax Details → Orders (Polymorphic)
- Media → Any Model (Polymorphic)
- Activities → Orders (Polymorphic)

## Database Constraints

### Foreign Key Constraints
- All relationships have proper foreign key constraints
- Cascade deletes where appropriate
- Restrict deletes for critical references

### Unique Constraints
- Product codes and SKUs
- Order numbers across all order types
- User email addresses

### Indexes
- Foreign key indexes for performance
- Unique indexes on business identifiers
- Composite indexes on frequently queried combinations

This comprehensive schema supports full ERP functionality with proper data integrity, audit trails, and multi-tenant architecture.