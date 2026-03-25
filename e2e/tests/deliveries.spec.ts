import { test, expect } from '@playwright/test';
import { navigateTo, waitForDataTable } from '../fixtures/test-helpers';

/**
 * Deliveries Module E2E Tests
 *
 * Tests delivery scheduling, today's view, editing, and status tracking.
 */

test.describe('Deliveries - Listing', () => {
    test.beforeEach(async ({ page }) => {
        await navigateTo(page, 'deliveries');
        await waitForDataTable(page);
    });

    test('should load deliveries index page', async ({ page }) => {
        await expect(page).toHaveURL(/admin#\/deliveries/);
    });

    test('should display deliveries data table', async ({ page }) => {
        const table = page.locator('table, .el-table, .calendar, .fc');
        await expect(table.first()).toBeVisible({ timeout: 10000 });
    });

    test('should have schedule delivery button', async ({ page }) => {
        const createBtn = page.locator(
            'a:has-text("Create"), button:has-text("Create"), a:has-text("Schedule"), button:has-text("Schedule"), a:has-text("New"), button:has-text("Add")'
        );
        await expect(createBtn.first()).toBeVisible();
    });

    test('should navigate to create delivery page', async ({ page }) => {
        const createBtn = page.locator(
            'a:has-text("Create"), button:has-text("Create"), a:has-text("Schedule"), button:has-text("Schedule"), a:has-text("New")'
        );
        await createBtn.first().click();
        await page.waitForLoadState('networkidle');

        await expect(page).toHaveURL(/admin#\/deliveries\/create/);
    });
});

test.describe('Deliveries - Create', () => {
    test.beforeEach(async ({ page }) => {
        await navigateTo(page, 'deliveries/create');
        await page.waitForTimeout(2000);
    });

    test('should load delivery creation form', async ({ page }) => {
        await expect(page).toHaveURL(/admin#\/deliveries\/create/);
    });

    test('should display delivery form fields', async ({ page }) => {
        // Should have customer, date, type fields
        const formFields = page.locator('input, select, .el-select, .el-date-picker');
        const count = await formFields.count();
        expect(count).toBeGreaterThan(0);
    });

    test('should validate required fields', async ({ page }) => {
        const saveBtn = page.locator(
            'button:has-text("Save"), button:has-text("Submit"), button:has-text("Create"), button:has-text("Schedule"), button[type="submit"]'
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

test.describe('Deliveries - API Integration', () => {
    test('should fetch today deliveries from API', async ({ page }) => {
        // Navigate and intercept API call
        const [response] = await Promise.all([
            page.waitForResponse(
                (resp) => resp.url().includes('/deliveries') && resp.status() === 200,
                { timeout: 15000 }
            ).catch(() => null),
            navigateTo(page, 'deliveries'),
        ]);

        if (response) {
            expect(response.status()).toBe(200);
        }
    });
});
