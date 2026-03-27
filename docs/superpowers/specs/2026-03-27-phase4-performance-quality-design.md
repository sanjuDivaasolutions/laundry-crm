# Phase 4: Performance & Code Quality Audit Fixes

**Date:** 2026-03-27
**Scope:** 7 tasks from the audit findings — frontend dependencies, backend performance, code quality cleanup
**Source:** `docs/plans/2026-03-26-audit-fixes-implementation.md` (Tasks 21-29, refined)
**Approach:** Mix of frontend dependency changes, backend query optimization, and code cleanup. No new features.

---

## Task 1: Remove TinyMCE Dead Dependency [P2]

**Files:** `package.json`, `package-lock.json`

**Problem:** `@tinymce/tinymce-vue` v4.0.4 is installed but has zero imports or usage anywhere in the codebase.

**Fix:** `npm uninstall @tinymce/tinymce-vue`

**Note:** Quill stays — it's actively used in `Widget1.vue`.

**Verification:** `npm run build` succeeds.

---

## Task 2: Replace moment.js with dayjs [P3]

**Files:**
- `package.json` (install dayjs, uninstall moment)
- `resources/js/bootstrap.js` — moment import + calls
- `resources/metronic/core/data/events.ts` — moment date formatting
- `resources/modules/common/components/form/FormDatepicker.vue` — moment formatting
- `resources/modules/common/components/form/FormDateRangePicker.vue` — heavy date range logic
- `resources/modules/common/components/form/FormMonthpicker.vue` — month formatting

**Problem:** Moment.js is ~67KB gzipped. Dayjs is ~2KB and API-compatible for the formatting/parsing/comparison use cases in this codebase.

**Migration pattern:**
- `import moment from 'moment'` → `import dayjs from 'dayjs'`
- `moment()` → `dayjs()`
- `moment(date).format('YYYY-MM-DD')` → `dayjs(date).format('YYYY-MM-DD')`
- `moment(date).isAfter(other)` → `dayjs(date).isAfter(other)`
- `moment(date).isBefore(other)` → `dayjs(date).isBefore(other)`
- `moment(date).startOf('month')` → `dayjs(date).startOf('month')` (requires dayjs plugin if used)

**Dayjs plugins needed:** Check each file for methods that require plugins (e.g., `isSameOrBefore`, `customParseFormat`). Import only what's needed.

**Verification:** `npm run build` succeeds. Date pickers still render correctly (manual check).

---

## Task 3: Fix N+1 in calculateOrderTotals [P4]

**Files:** `app/Services/PosService.php` (calculateOrderTotals method)

**Problem:** Inside the loop, `Item::find()` and `ServicePrice::where()->first()` execute per item — 2N+1 queries for N items.

**Fix:** Batch-load before the loop:
```php
$itemIds = array_column($items, 'item_id');
$itemModels = Item::whereIn('id', $itemIds)->get()->keyBy('id');
$servicePrices = ServicePrice::whereIn('item_id', $itemIds)
    ->where('service_id', $serviceId)->get()->keyBy('item_id');
```

Then lookup from collections: `$itemModels[$itemData['item_id']]` and `$servicePrices[$item->id]`.

Reduces 2N+1 queries to exactly 3 queries regardless of item count.

**Tests:** Existing PosServiceIntegrationTest must still pass.

---

## Task 4: Fix gettype() Anti-Pattern [Q3]

**Files:** `app/Traits/ControllerRequest.php:85`

**Problem:** `gettype($request) == 'array'` uses loose comparison with string output instead of proper type checking.

**Fix:** Replace with `is_array($request)`.

**Tests:** Existing test suite must pass.

---

## Task 5: Remove Commented-Out Code [Q15]

**Files:**
- `app/Traits/ControllerRequest.php` — commented deletion logic block (~20 lines)
- `app/Http/Requests/QuotationRequest.php` — `dd($this->all())` debug line
- `app/Support/FilterQueryBuilder.php` — old `makeOrder()` method, old `orWhereHas()` blocks (~30 lines)

**Problem:** Dead commented code adds noise and confusion. It's in git history if ever needed.

**Fix:** Remove all commented-out code blocks from these 3 files.

**Tests:** Existing test suite must pass.

---

## Task 6: Remove console.log from Production Code [Q9]

**Files:** 7 files with 13 console.log instances

**Application code:**
- `resources/modules/common/components/form/FormSelectAjax.vue:200` — error catch (handled separately in Task 7)
- `resources/modules/common/components/form/FormSubItems.vue:108`
- `resources/modules/common/components/FormFields.vue:331,333`
- `resources/modules/common/components/Ledger.vue:160`
- `resources/modules/common/components/show/ShowValues.vue:190`
- `resources/metronic/components/magic-datatable/components/DatatableActions.vue:117`
- `resources/metronic/components/magic-datatable/components/HeaderSettings.vue:120`
- `resources/metronic/components/magic-datatable/MagicDatatable.vue:82,113`

**Metronic theme code:**
- `resources/metronic/assets/ts/components/_StepperComponent.ts:309`
- `resources/metronic/assets/ts/components/_ToggleComponent.ts:85,166,175,180`

**Fix:** Remove all 13 instances. These are debug logs with no functional purpose.

**Note:** The `FormSelectAjax.vue` console.log is replaced with a toast in Task 7 — don't just delete it in this task, leave it for Task 7.

So this task removes 12 instances (all except FormSelectAjax).

**Verification:** `npm run build` succeeds.

---

## Task 7: Fix FormSelectAjax Error Swallowing [Q10]

**Files:** `resources/modules/common/components/form/FormSelectAjax.vue:200`

**Problem:** API errors in `fetchOptions()` are caught and only `console.log(error)` — users get no feedback when option loading fails.

**Fix:** Replace `console.log(error)` with user-visible feedback. Use the project's toast notification system if available (`this.$toastError` or similar), or set an error state on the component.

**Verification:** `npm run build` succeeds.

---

## Execution Order

1. **Task 1** (remove TinyMCE) — standalone, quick
2. **Task 2** (moment→dayjs) — frontend, standalone
3. **Task 3** (N+1 fix) — backend, standalone
4. **Task 4** (gettype fix) — trivial, standalone
5. **Task 5** (commented code) — cleanup, standalone
6. **Task 6** (console.log removal) — must come before Task 7
7. **Task 7** (FormSelectAjax error handling) — builds on Task 6

Each task gets its own commit. Run `vendor/bin/pint --dirty` for PHP changes, `npm run build` for frontend changes.

---

## Out of Scope

- Quill removal (actively used in Widget1.vue)
- CustomerApiController eager loading (needs product input on which relations)
- Final cleanup task (redundant — pint/tests/build run after every task)
- Larger refactoring (Q1: POS Board split, Q2: ResourceController, Q7: Vue store factory, Q13: PosService split)
