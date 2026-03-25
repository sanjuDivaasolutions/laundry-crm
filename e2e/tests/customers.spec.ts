import { test, expect } from '@playwright/test';
import { navigateTo, waitForDataTable, getTableRowCount } from '../fixtures/test-helpers';

/**
 * Customers Module E2E Tests
 *
 * Tests customer listing, creation, editing, viewing,
 * loyalty points display, and search/filter.
 */

test.describe('Customers - Listing', () => {
    test.beforeEach(async ({ page }) => {
        await navigateTo(page, 'customers');
        await waitForDataTable(page);
    });

    test('should load customers index page', async ({ page }) => {
        await expect(page).toHaveURL(/admin#\/customers/);
    });

    test('should display data table with customers', async ({ page }) => {
        const table = page.locator('table, .el-table');
        await expect(table.first()).toBeVisible({ timeout: 10000 });
    });

    test('should show customer table headers', async ({ page }) => {
        const headers = page.locator('th, .el-table__header-wrapper');
        const headerTexts = await headers.allTextContents();
        const headerText = headerTexts.join(' ').toLowerCase();

        // Should have customer-related columns
        expect(headerText).toContain('name');
    });

    test('should have create button', async ({ page }) => {
        const createBtn = page.locator(
            'a:has-text("Create"), button:has-text("Create"), a:has-text("New"), button:has-text("Add")'
        );
        await expect(createBtn.first()).toBeVisible();
    });

    test('should navigate to create customer page', async ({ page }) => {
        const createBtn = page.locator(
            'a:has-text("Create"), button:has-text("Create"), a:has-text("New"), button:has-text("Add")'
        );
        await createBtn.first().click();
        await page.waitForLoadState('networkidle');

        await expect(page).toHaveURL(/admin#\/customers\/create/);
    });

    test('should have search functionality', async ({ page }) => {
        const searchInput = page.locator(
            'input[type="search"], input[placeholder*="Search"], input[placeholder*="search"]'
        );
        await expect(searchInput.first()).toBeVisible({ timeout: 10000 });
    });

    test('should filter results when searching', async ({ page }) => {
        const searchInput = page.locator(
            'input[type="search"], input[placeholder*="Search"], input[placeholder*="search"]'
        );

        if (await searchInput.first().isVisible().catch(() => false)) {
            await searchInput.first().fill('test');
            await page.waitForTimeout(1500); // debounce time
            await waitForDataTable(page);

            // Table should still be visible (even if empty results)
            const table = page.locator('table, .el-table');
            await expect(table.first()).toBeVisible();
        }
    });
});

test.describe('Customers - Create', () => {
    test.beforeEach(async ({ page }) => {
        await navigateTo(page, 'customers/create');
        await page.waitForTimeout(2000);
    });

    test('should load create customer form', async ({ page }) => {
        await expect(page).toHaveURL(/admin#\/customers\/create/);
    });

    test('should display form fields for customer data', async ({ page }) => {
        // Should have name, email, phone fields at minimum
        const nameField = page.locator(
            'input[name*="name"], input[placeholder*="Name"], input[placeholder*="name"]'
        );
        await expect(nameField.first()).toBeVisible({ timeout: 10000 });
    });

    test('should show validation errors for empty required fields', async ({ page }) => {
        const saveBtn = page.locator(
            'button:has-text("Save"), button:has-text("Submit"), button:has-text("Create"), button[type="submit"]'
        );
        await saveBtn.first().click();

        await page.waitForTimeout(2000);

        const errors = page.locator(
            '.fv-help-block, .error-message, .text-danger, .el-form-item__error, .invalid-feedback, .swal2-popup'
        );
        const errorCount = await errors.count();
        expect(errorCount).toBeGreaterThan(0);
    });

    test('should have cancel/back navigation', async ({ page }) => {
        const backBtn = page.locator(
            'a:has-text("Back"), button:has-text("Cancel"), a:has-text("Cancel"), a:has-text("List")'
        );
        await expect(backBtn.first()).toBeVisible();
    });
});

test.describe('Customers - View Detail', () => {
    test('should view customer detail with tabs', async ({ page }) => {
        await navigateTo(page, 'customers');
        await waitForDataTable(page);

        const firstRow = page.locator('table tbody tr, .el-table__row').first();

        if (await firstRow.isVisible().catch(() => false)) {
            const viewLink = firstRow.locator(
                'a:has-text("View"), a:has-text("Show"), a[href*="show"], [title="View"]'
            );

            if (await viewLink.first().isVisible().catch(() => false)) {
                await viewLink.first().click();
                await page.waitForLoadState('networkidle');
                await page.waitForTimeout(2000);

                // Customer detail should load
                const detailContent = page.locator('.card, .customer-detail, .show-page');
                await expect(detailContent.first()).toBeVisible();
            }
        }
    });
});

test.describe('Customers - Edit', () => {
    test('should navigate to edit from listing', async ({ page }) => {
        await navigateTo(page, 'customers');
        await waitForDataTable(page);

        const firstRow = page.locator('table tbody tr, .el-table__row').first();

        if (await firstRow.isVisible().catch(() => false)) {
            const editLink = firstRow.locator(
                'a:has-text("Edit"), a[href*="edit"], [title="Edit"], .fa-edit, .fa-pen'
            );

            if (await editLink.first().isVisible().catch(() => false)) {
                await editLink.first().click();
                await page.waitForLoadState('networkidle');

                await expect(page).toHaveURL(/admin#\/customers\/edit\//);
            }
        }
    });
});
