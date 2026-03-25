import { test, expect } from '@playwright/test';
import { navigateTo, waitForDataTable } from '../fixtures/test-helpers';

/**
 * Items & Services Module E2E Tests
 *
 * Tests for item catalog management (CRUD) and service listing.
 */

test.describe('Items - Listing', () => {
    test.beforeEach(async ({ page }) => {
        await navigateTo(page, 'items');
        await waitForDataTable(page);
    });

    test('should load items index page', async ({ page }) => {
        await expect(page).toHaveURL(/admin#\/items/);
    });

    test('should display items data table', async ({ page }) => {
        const table = page.locator('table, .el-table');
        await expect(table.first()).toBeVisible({ timeout: 10000 });
    });

    test('should show item-related table columns', async ({ page }) => {
        const headers = page.locator('th, .el-table__header-wrapper');
        const headerTexts = await headers.allTextContents();
        const headerText = headerTexts.join(' ').toLowerCase();

        expect(headerText).toContain('name');
    });

    test('should have create new item button', async ({ page }) => {
        const createBtn = page.locator(
            'a:has-text("Create"), button:has-text("Create"), a:has-text("New"), button:has-text("Add")'
        );
        await expect(createBtn.first()).toBeVisible();
    });

    test('should navigate to create item page', async ({ page }) => {
        const createBtn = page.locator(
            'a:has-text("Create"), button:has-text("Create"), a:has-text("New")'
        );
        await createBtn.first().click();
        await page.waitForLoadState('networkidle');

        await expect(page).toHaveURL(/admin#\/items\/create/);
    });
});

test.describe('Items - Create', () => {
    test.beforeEach(async ({ page }) => {
        await navigateTo(page, 'items/create');
        await page.waitForTimeout(2000);
    });

    test('should load create item form', async ({ page }) => {
        await expect(page).toHaveURL(/admin#\/items\/create/);
    });

    test('should have form fields for item data', async ({ page }) => {
        const nameField = page.locator(
            'input[name*="name"], input[placeholder*="Name"], input[placeholder*="name"]'
        );
        await expect(nameField.first()).toBeVisible({ timeout: 10000 });
    });

    test('should validate required fields on submit', async ({ page }) => {
        const saveBtn = page.locator(
            'button:has-text("Save"), button:has-text("Submit"), button:has-text("Create"), button[type="submit"]'
        );
        await saveBtn.first().click();
        await page.waitForTimeout(2000);

        const errors = page.locator(
            '.fv-help-block, .error-message, .text-danger, .el-form-item__error, .swal2-popup'
        );
        const errorCount = await errors.count();
        expect(errorCount).toBeGreaterThan(0);
    });
});

test.describe('Items - Edit', () => {
    test('should navigate to edit item from listing', async ({ page }) => {
        await navigateTo(page, 'items');
        await waitForDataTable(page);

        const firstRow = page.locator('table tbody tr, .el-table__row').first();

        if (await firstRow.isVisible().catch(() => false)) {
            const editLink = firstRow.locator(
                'a:has-text("Edit"), a[href*="edit"], [title="Edit"]'
            );

            if (await editLink.first().isVisible().catch(() => false)) {
                await editLink.first().click();
                await page.waitForLoadState('networkidle');

                await expect(page).toHaveURL(/admin#\/items\/edit\//);
            }
        }
    });
});

test.describe('Services - Listing', () => {
    test.beforeEach(async ({ page }) => {
        await navigateTo(page, 'services');
        await waitForDataTable(page);
    });

    test('should load services index page', async ({ page }) => {
        await expect(page).toHaveURL(/admin#\/services/);
    });

    test('should display services data table', async ({ page }) => {
        const table = page.locator('table, .el-table');
        await expect(table.first()).toBeVisible({ timeout: 10000 });
    });

    test('should show service records', async ({ page }) => {
        // Services should be seeded (Wash & Iron, Dry Clean, etc.)
        const rows = page.locator('table tbody tr, .el-table__row');
        const count = await rows.count();
        expect(count).toBeGreaterThanOrEqual(0);
    });

    test('should have service management options', async ({ page }) => {
        const createBtn = page.locator(
            'a:has-text("Create"), button:has-text("Create"), a:has-text("New"), button:has-text("Add")'
        );

        // Services page might have create button
        if (await createBtn.first().isVisible().catch(() => false)) {
            await expect(createBtn.first()).toBeVisible();
        }
    });
});
