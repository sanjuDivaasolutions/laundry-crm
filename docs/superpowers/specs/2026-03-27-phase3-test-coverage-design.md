# Phase 3: Test Coverage Audit Fixes

**Date:** 2026-03-27
**Scope:** 5 test tasks from the audit findings — PosService integration, controller CRUD tests, form request validation
**Source:** `docs/plans/2026-03-26-audit-fixes-implementation.md` (Tasks 16-20, refined)
**Approach:** Pure test code — no production code changes. Each task is an independent test file.

---

## Task 16: PosService Integration Test [T3]

**File:** `tests/Feature/Services/PosServiceIntegrationTest.php`

**Purpose:** Test the end-to-end flow: `createQuickOrder → processPayment → loyalty points awarded`. This is the one flow not fully tested by existing tests.

**Tests:**
- `createQuickOrder` creates an order with correct totals, generates order number, finds/creates customer
- `processPayment` on the created order marks it paid, transitions to Delivered, closes the order
- Customer's `loyalty_points`, `total_orders_count`, `total_spent` are updated after payment

**Setup:** Seed ProcessingStatus + OrderStatus. Create tenant, user, items, services, and service prices (since `createQuickOrder` needs real items/services for price calculation).

**Does NOT duplicate:**
- Payment validation (PosPaymentValidationTest)
- Status transitions (PosStatusTransitionTest)
- Loyalty soft-delete guard (LoyaltyServiceTest)

---

## Task 17: CustomerApiController Tests [T4]

**File:** `tests/Feature/Controllers/Api/CustomerControllerTest.php`

**Purpose:** Full CRUD API test suite for the customers resource.

**Tests:**
- **Index:** GET `/api/v1/customers` returns paginated collection (200)
- **Store:** POST `/api/v1/customers` with valid data creates customer (201), auto-generates customer_code
- **Show:** GET `/api/v1/customers/{id}` returns single customer (200)
- **Edit:** GET `/api/v1/customers/{id}/edit` returns edit resource (200)
- **Update:** PUT `/api/v1/customers/{id}` with valid data updates customer (202)
- **Destroy:** DELETE `/api/v1/customers/{id}` soft-deletes (204), customer absent from subsequent index
- **Authorization:** Each action returns 403 without the corresponding `customer_{action}` gate
- **Validation:** Store with missing name/phone returns 422

**Setup:** Seed statuses, create tenant + user, mock gates for `customer_access`, `customer_create`, `customer_show`, `customer_edit`, `customer_delete`.

---

## Task 18: ServiceApiController Tests [T6]

**File:** `tests/Feature/Controllers/Api/ServiceControllerTest.php`

**Purpose:** Full CRUD API test suite for the services resource. Same pattern as CustomerApiController.

**Tests:**
- **Index:** GET `/api/v1/services` returns collection (200)
- **Store:** POST `/api/v1/services` creates service (201), auto-generates code
- **Show:** GET `/api/v1/services/{id}` returns single service (200)
- **Edit:** GET `/api/v1/services/{id}/edit` returns edit resource (200)
- **Update:** PUT `/api/v1/services/{id}` updates service (202)
- **Destroy:** DELETE `/api/v1/services/{id}` soft-deletes (204)
- **Authorization:** 403 without `service_{action}` gates
- **Validation:** Store with missing name returns 422

**Setup:** Same tenant/user pattern. Gates for `service_access`, `service_create`, `service_show`, `service_edit`, `service_delete`.

---

## Task 19: DeliveryScheduleApiController Tests [T7]

**File:** `tests/Feature/Controllers/Api/DeliveryScheduleControllerTest.php`

**Purpose:** Test all 6 DeliverySchedule API endpoints. This controller has custom logic (not using generic SearchFilters trait).

**Tests:**
- **Index:** GET `/api/v1/deliveries` returns schedules (200), supports date filter
- **Today:** GET `/api/v1/deliveries/today` returns today's schedules with summary (200)
- **Store:** POST `/api/v1/deliveries` creates schedule with valid order+customer (201)
- **Edit:** GET `/api/v1/deliveries/{id}/edit` returns schedule with relations (200)
- **Update:** PUT `/api/v1/deliveries/{id}` updates schedule (200), sets `completed_at` when status becomes `completed`
- **Destroy:** DELETE `/api/v1/deliveries/{id}` soft-deletes (200)
- **Authorization:** 403 without `order_access` gate
- **Validation:** Store with missing order_id or invalid type returns 422, past scheduled_date rejected

**Setup:** Seed statuses, create tenant + user + customer + order (FK dependencies for delivery schedules). Gates for `order_access`, `order_create`, `order_edit`, `order_delete`.

---

## Task 20: Form Request Validation Tests [T1]

**File:** `tests/Feature/Validation/FormRequestValidationTest.php`

**Purpose:** Test validation rules for key form requests by hitting actual API endpoints (full middleware + request + controller chain). Organized in `describe()` blocks per request.

**Sections:**

### StoreOrderRequest
- Required fields: customer_id, order_date, items (422 when missing)
- Max bounds: discount_amount > 99999.99 rejected, tip_amount > 9999.99 rejected
- Nested items: items.*.item_id required, items.*.quantity min:1
- Happy path: valid order data returns 201

### StoreCustomerRequest
- Required fields: name, phone (422 when missing)
- Max lengths: name > 255 rejected, phone > 20 rejected
- Happy path: valid customer data returns 201

### StoreServiceRequest
- Required fields: name (422 when missing)
- Max lengths: name > 100 rejected
- Happy path: valid service data returns 201

**Pattern:** Use Pest datasets for invalid payloads — one dataset row per field/rule combination. Assert 422 with the expected error key in `$response->json('errors')`.

---

## Execution Order

All tasks are independent. Execute in this order:
1. **Task 16** (PosService integration) — most complex, do first
2. **Task 17** (CustomerApiController) — CRUD template
3. **Task 18** (ServiceApiController) — follows same pattern
4. **Task 19** (DeliveryScheduleApiController) — custom logic
5. **Task 20** (Form Request validation) — cross-cutting

Each task gets its own commit. Run `vendor/bin/pint --dirty` before committing.

---

## Test Infrastructure Notes

- All tests use `RefreshDatabase` trait
- Seed ProcessingStatusSeeder + OrderStatusSeeder in beforeEach (FK dependencies)
- Create tenant via `Tenant::factory()->create()` + `TenantService::setTenant()`
- Auth via `$this->actingAs($user, 'admin')`
- Mock gates via `Gate::define('{module}_{action}', fn() => true)`
- Existing factories available: Order, Customer, Service, Item, ServicePrice, Payment, DeliverySchedule, User, Tenant

---

## Out of Scope

- Phase 4 (Performance & code quality) for follow-up
- Additional test coverage beyond the 5 tasks listed (T10-T25 from original audit)
- E2E browser tests (Playwright)
