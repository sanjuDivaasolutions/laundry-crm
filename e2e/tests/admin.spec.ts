import { test, expect } from '@playwright/test';
import { navigateTo, waitForDataTable } from '../fixtures/test-helpers';

/**
 * Admin Modules E2E Tests
 *
 * Tests for roles, users, permissions, languages,
 * companies, countries, states, cities.
 */

test.describe('Roles', () => {
    test('should load roles listing page', async ({ page }) => {
        await navigateTo(page, 'roles');
        await waitForDataTable(page);

        await expect(page).toHaveURL(/admin#\/roles/);
        const table = page.locator('table, .el-table');
        await expect(table.first()).toBeVisible({ timeout: 10000 });
    });

    test('should display role records', async ({ page }) => {
        await navigateTo(page, 'roles');
        await waitForDataTable(page);

        // Should have at least Admin role from seeder
        const rows = page.locator('table tbody tr, .el-table__row');
        const count = await rows.count();
        expect(count).toBeGreaterThan(0);
    });
});

test.describe('Users', () => {
    test('should load users listing page', async ({ page }) => {
        await navigateTo(page, 'users');
        await waitForDataTable(page);

        await expect(page).toHaveURL(/admin#\/users/);
        const table = page.locator('table, .el-table');
        await expect(table.first()).toBeVisible({ timeout: 10000 });
    });

    test('should display user records', async ({ page }) => {
        await navigateTo(page, 'users');
        await waitForDataTable(page);

        // Should have at least the admin user
        const rows = page.locator('table tbody tr, .el-table__row');
        const count = await rows.count();
        expect(count).toBeGreaterThan(0);
    });

    test('should have user search functionality', async ({ page }) => {
        await navigateTo(page, 'users');
        await waitForDataTable(page);

        const searchInput = page.locator(
            'input[type="search"], input[placeholder*="Search"], input[placeholder*="search"]'
        );
        await expect(searchInput.first()).toBeVisible({ timeout: 10000 });
    });
});

test.describe('Permissions', () => {
    test('should load permissions page', async ({ page }) => {
        await navigateTo(page, 'permissions');
        await waitForDataTable(page);

        await expect(page).toHaveURL(/admin#\/permissions/);
    });

    test('should display permission records', async ({ page }) => {
        await navigateTo(page, 'permissions');
        await waitForDataTable(page);

        // Should have many permissions from seeder
        const table = page.locator('table, .el-table');
        await expect(table.first()).toBeVisible({ timeout: 10000 });
    });
});

test.describe('Languages', () => {
    test('should load languages listing page', async ({ page }) => {
        await navigateTo(page, 'languages');
        await waitForDataTable(page);

        await expect(page).toHaveURL(/admin#\/languages/);
    });

    test('should display language records', async ({ page }) => {
        await navigateTo(page, 'languages');
        await waitForDataTable(page);

        const table = page.locator('table, .el-table');
        await expect(table.first()).toBeVisible({ timeout: 10000 });
    });

    test('should have create language option', async ({ page }) => {
        await navigateTo(page, 'languages');
        await waitForDataTable(page);

        const createBtn = page.locator(
            'a:has-text("Create"), button:has-text("Create"), a:has-text("New")'
        );

        if (await createBtn.first().isVisible().catch(() => false)) {
            await createBtn.first().click();
            await page.waitForLoadState('networkidle');
            await expect(page).toHaveURL(/admin#\/languages\/create/);
        }
    });
});

test.describe('Companies', () => {
    test('should load companies listing page', async ({ page }) => {
        await navigateTo(page, 'companies');
        await waitForDataTable(page);

        await expect(page).toHaveURL(/admin#\/companies/);
    });

    test('should display company settings', async ({ page }) => {
        await navigateTo(page, 'companies');
        await waitForDataTable(page);

        const content = page.locator('table, .el-table, .card, form');
        await expect(content.first()).toBeVisible({ timeout: 10000 });
    });
});

test.describe('Countries', () => {
    test('should load countries listing page', async ({ page }) => {
        await navigateTo(page, 'countries');
        await waitForDataTable(page);

        await expect(page).toHaveURL(/admin#\/countries/);
        const table = page.locator('table, .el-table');
        await expect(table.first()).toBeVisible({ timeout: 10000 });
    });
});

test.describe('States', () => {
    test('should load states listing page', async ({ page }) => {
        await navigateTo(page, 'states');
        await waitForDataTable(page);

        await expect(page).toHaveURL(/admin#\/states/);
        const table = page.locator('table, .el-table');
        await expect(table.first()).toBeVisible({ timeout: 10000 });
    });
});

test.describe('Cities', () => {
    test('should load cities listing page', async ({ page }) => {
        await navigateTo(page, 'cities');
        await waitForDataTable(page);

        await expect(page).toHaveURL(/admin#\/cities/);
        const table = page.locator('table, .el-table');
        await expect(table.first()).toBeVisible({ timeout: 10000 });
    });
});
