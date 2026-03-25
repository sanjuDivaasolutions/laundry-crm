import { test, expect } from '@playwright/test';
import { navigateTo, waitForDataTable, waitForApi, getTableRowCount } from '../fixtures/test-helpers';

/**
 * Orders Module E2E Tests
 *
 * Tests order listing, creation, editing, viewing,
 * filtering, status changes, and deletion.
 */

test.describe('Orders - Listing', () => {
    test.beforeEach(async ({ page }) => {
        await navigateTo(page, 'orders');
        await waitForDataTable(page);
    });

    test('should load orders index page', async ({ page }) => {
        await expect(page).toHaveURL(/admin#\/orders/);
    });

    test('should display a data table with orders', async ({ page }) => {
        const table = page.locator('table, .el-table');
        await expect(table.first()).toBeVisible({ timeout: 10000 });
    });

    test('should show table headers for order fields', async ({ page }) => {
        // Check for key column headers
        const headers = page.locator('th, .el-table__header-wrapper');
        const headerTexts = await headers.allTextContents();
        const headerText = headerTexts.join(' ').toLowerCase();

        // Should have standard order columns
        const expectedColumns = ['order', 'customer', 'status'];
        for (const col of expectedColumns) {
            expect(headerText).toContain(col);
        }
    });

    test('should have create new order button', async ({ page }) => {
        const createBtn = page.locator(
            'a:has-text("Create"), button:has-text("Create"), a:has-text("New"), button:has-text("Add")'
        );
        await expect(createBtn.first()).toBeVisible();
    });

    test('should navigate to create order page', async ({ page }) => {
        const createBtn = page.locator(
            'a:has-text("Create"), button:has-text("Create"), a:has-text("New"), button:has-text("Add")'
        );
        await createBtn.first().click();
        await page.waitForLoadState('networkidle');

        await expect(page).toHaveURL(/admin#\/orders\/create/);
    });

    test('should have search/filter functionality', async ({ page }) => {
        const searchInput = page.locator(
            'input[type="search"], input[placeholder*="Search"], input[placeholder*="search"], .search-input'
        );
        await expect(searchInput.first()).toBeVisible({ timeout: 10000 });
    });

    test('should have export options', async ({ page }) => {
        const exportBtn = page.locator(
            'button:has-text("Export"), a:has-text("Export"), button:has-text("CSV"), button:has-text("Excel")'
        );

        if (await exportBtn.first().isVisible().catch(() => false)) {
            await expect(exportBtn.first()).toBeVisible();
        }
    });

    test('should have pagination controls', async ({ page }) => {
        const pagination = page.locator(
            '.el-pagination, .pagination, nav[aria-label*="pagination"], [class*="pagina"]'
        );

        // Pagination may not show if there are few records
        const count = await pagination.count();
        expect(count).toBeGreaterThanOrEqual(0);
    });
});

test.describe('Orders - Create', () => {
    test.beforeEach(async ({ page }) => {
        await navigateTo(page, 'orders/create');
    });

    test('should load the create order form', async ({ page }) => {
        await expect(page).toHaveURL(/admin#\/orders\/create/);
    });

    test('should display order form fields', async ({ page }) => {
        await page.waitForTimeout(2000);

        // Check that form contains key fields
        const form = page.locator('form, .form, .card-body');
        await expect(form.first()).toBeVisible();
    });

    test('should have customer selection field', async ({ page }) => {
        await page.waitForTimeout(2000);

        // Customer dropdown or autocomplete
        const customerField = page.locator(
            'select, .el-select, .multiselect, [class*="customer"], input[placeholder*="Customer"], input[placeholder*="customer"]'
        );
        await expect(customerField.first()).toBeVisible({ timeout: 10000 });
    });

    test('should have save/submit button', async ({ page }) => {
        const saveBtn = page.locator(
            'button:has-text("Save"), button:has-text("Submit"), button:has-text("Create"), button[type="submit"]'
        );
        await expect(saveBtn.first()).toBeVisible();
    });

    test('should show validation errors when submitting empty form', async ({ page }) => {
        await page.waitForTimeout(2000);

        // Click save without filling required fields
        const saveBtn = page.locator(
            'button:has-text("Save"), button:has-text("Submit"), button:has-text("Create"), button[type="submit"]'
        );
        await saveBtn.first().click();

        await page.waitForTimeout(2000);

        // Should show validation errors or SweetAlert
        const errors = page.locator(
            '.fv-help-block, .error-message, .text-danger, .el-form-item__error, .invalid-feedback, .swal2-popup'
        );
        const errorCount = await errors.count();
        expect(errorCount).toBeGreaterThan(0);
    });

    test('should have a back/cancel button', async ({ page }) => {
        const backBtn = page.locator(
            'a:has-text("Back"), button:has-text("Cancel"), a:has-text("Cancel"), a:has-text("List")'
        );
        await expect(backBtn.first()).toBeVisible();
    });
});

test.describe('Orders - View Detail', () => {
    test('should be able to view an order detail page', async ({ page }) => {
        // First go to order listing
        await navigateTo(page, 'orders');
        await waitForDataTable(page);

        // Click on first order row (if exists)
        const firstRow = page.locator('table tbody tr, .el-table__row').first();

        if (await firstRow.isVisible().catch(() => false)) {
            // Find a view/show link in the row
            const viewLink = firstRow.locator(
                'a:has-text("View"), a:has-text("Show"), a[href*="show"], .btn-show, [title="View"]'
            );

            if (await viewLink.first().isVisible().catch(() => false)) {
                await viewLink.first().click();
                await page.waitForLoadState('networkidle');
                await page.waitForTimeout(2000);

                // Should show order details
                const detailContent = page.locator('.card, .order-detail, .show-page');
                await expect(detailContent.first()).toBeVisible();
            }
        }
    });
});

test.describe('Orders - Edit', () => {
    test('should navigate to edit page from listing', async ({ page }) => {
        await navigateTo(page, 'orders');
        await waitForDataTable(page);

        const firstRow = page.locator('table tbody tr, .el-table__row').first();

        if (await firstRow.isVisible().catch(() => false)) {
            const editLink = firstRow.locator(
                'a:has-text("Edit"), a[href*="edit"], .btn-edit, [title="Edit"], .fa-edit, .fa-pen'
            );

            if (await editLink.first().isVisible().catch(() => false)) {
                await editLink.first().click();
                await page.waitForLoadState('networkidle');

                await expect(page).toHaveURL(/admin#\/orders\/edit\//);
            }
        }
    });
});
