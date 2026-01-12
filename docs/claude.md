# CLAUDE.md
## Project Memory — Multi-Tenant SaaS Platform

This project is a production SaaS platform converted from a non-SaaS multi-warehouse inventory system.

The system includes:
- Inventory management
- Multi-warehouse stock tracking
- Batch management
- Sales orders
- Purchase orders
- Invoices
- Customers & buyers
- Projects
- Reporting & analytics

Current stack:
- Backend: Laravel
- Frontend: Vue
- Database: MySQL
- Payments: Stripe (Cashier for customer billing + SaaS billing layer)
- Queue: Redis / Horizon (assumed)
- Auth: Laravel Sanctum / Passport (assumed)

Target scale:
- 1,000+ tenants
- 50k+ products per tenant
- Multi-warehouse per tenant
- Millions of inventory movements
- High concurrency

---

## Core Architectural Principles

### 1. Multi-Tenancy Model
- Single database, shared schema.
- Every tenant-owned table MUST include:
  - tenant_id
  - company_id (business unit / warehouse owner)
- No query may execute without tenant_id filtering unless explicitly inside Admin context.
- Tenant isolation is enforced using Laravel global scopes.
- Admin queries must explicitly bypass scopes and remain read-only.

---

### 2. Tenant Structure
Tables:
- tenants
- companies (belongs to tenant)
- users (belongs to tenant + company)

Future-ready for:
- Franchise hierarchies
- Multi-company tenants
- Rollup analytics

---

### 3. Shared Reference Data
Global tables:
- countries
- states
- cities
- currencies
- tax_rates

Rules:
- These tables are read-only for tenants.
- Tenant customization must be done via override tables:
  - tenant_tax_rates
  - tenant_units
  - tenant_payment_terms
- Resolution order:
  - Tenant override → Global base record

Tenants must never modify shared master tables.

---

### 4. Inventory Performance Rules
- All inventory queries must filter by tenant_id first.
- Mandatory compound indexes:
  - (tenant_id, company_id, warehouse_id, product_id)
  - (tenant_id, product_id)
  - (tenant_id, batch_id)
- Avoid real-time SUM queries on large datasets.
- Prefer pre-aggregated snapshot tables for reporting.

---

### 5. Billing Architecture
Two billing layers exist:

1. Customer Billing (existing):
   - Stripe Cashier
   - Bills tenant’s customers

2. SaaS Billing (new):
   - Bills tenants for platform usage
   - Separate tables:
     - tenant_subscriptions
     - tenant_subscription_items
     - tenant_invoices
   - Stripe products separated by metadata

Never mix these two billing domains.

---

### 6. Subscription Behavior Rules
- Downgrades never delete data.
- Creation of new records is blocked when quota exceeded.
- Grace periods:
  - Soft warning → Read-only → Suspension
- Failed payments never affect customer billing.

---

### 7. Feature Entitlements
Tenant-level feature gating exists separately from user permissions.

Tables:
- tenant_features
- tenant_quotas
- tenant_usage

Enforcement:
- API layer enforcement
- Frontend route gating
- Soft-block on downgrade

---

### 8. Data Safety Rules
- All destructive operations require confirmation and logging.
- No migration may delete data without rollback strategy.
- Dual-write strategy for breaking schema changes.
- All Stripe webhooks must be idempotent.

---

### 9. Security Rules
- Prevent cross-tenant access strictly.
- Validate tenant ownership in all mutations.
- Never expose internal IDs publicly.
- All exports must be tenant-scoped and audited.

---

### 10. Observability & Reliability
- Log all billing events.
- Monitor slow queries.
- Alert on webhook failures.
- Track quota usage trends.

---

## Claude Operating Instructions

Claude must always:
1. Explain architecture before coding.
2. Identify risks and scaling limits.
3. Suggest indexes for any new table.
4. Validate migrations for safety.
5. Consider rollback strategy.
6. Enforce tenant isolation in every query.
7. Avoid shortcuts and demo-quality code.
8. Produce production-ready output only.
9. Ask clarifying questions only if critical.
10. Assume real users and real money.

Claude must never:
- Generate unsafe migrations
- Bypass tenant isolation casually
- Mix billing domains
- Ignore performance impact
- Assume small datasets
- Hard-delete tenant data
- Skip validation and error handling

---

## Project Goals

Primary goals:
- Safe SaaS conversion
- Scalability
- Security
- Maintainability
- Revenue reliability

Secondary goals:
- Developer velocity
- Clean architecture
- Observability
- Cost efficiency

---

## End of Memory
