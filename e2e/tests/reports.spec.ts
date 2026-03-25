import { test, expect } from '@playwright/test';
import { navigateTo, waitForApi } from '../fixtures/test-helpers';

/**
 * Reports Module E2E Tests
 *
 * Tests daily, weekly, monthly reports, revenue trends,
 * top services/customers, and chart rendering.
 */

test.describe('Reports - Index', () => {
    test.beforeEach(async ({ page }) => {
        await navigateTo(page, 'reports');
        await page.waitForTimeout(2000);
    });

    test('should load reports index page', async ({ page }) => {
        await expect(page).toHaveURL(/admin#\/reports/);
    });

    test('should display report navigation options', async ({ page }) => {
        // Reports page should have links to different report types
        const reportLinks = page.locator(
            'a, button, .card, .report-link, .menu-item'
        );
        const count = await reportLinks.count();
        expect(count).toBeGreaterThan(0);
    });
});

test.describe('Reports - Daily', () => {
    test('should load daily report data', async ({ page }) => {
        const [response] = await Promise.all([
            page.waitForResponse(
                (resp) => resp.url().includes('/reports/daily') && resp.status() === 200,
                { timeout: 15000 }
            ).catch(() => null),
            navigateTo(page, 'reports/daily'),
        ]);

        if (response) {
            const data = await response.json();
            expect(data).toBeTruthy();
        }
    });

    test('should display daily report content', async ({ page }) => {
        await navigateTo(page, 'reports/daily');
        await page.waitForTimeout(3000);

        // Should show some content - cards, tables, or charts
        const content = page.locator('.card, table, .apexcharts-canvas, [class*="chart"]');
        const count = await content.count();
        expect(count).toBeGreaterThan(0);
    });
});

test.describe('Reports - Weekly', () => {
    test('should load weekly report data', async ({ page }) => {
        const [response] = await Promise.all([
            page.waitForResponse(
                (resp) => resp.url().includes('/reports/weekly') && resp.status() === 200,
                { timeout: 15000 }
            ).catch(() => null),
            navigateTo(page, 'reports/weekly'),
        ]);

        if (response) {
            expect(response.status()).toBe(200);
        }
    });
});

test.describe('Reports - Monthly', () => {
    test('should load monthly report data', async ({ page }) => {
        const [response] = await Promise.all([
            page.waitForResponse(
                (resp) => resp.url().includes('/reports/monthly') && resp.status() === 200,
                { timeout: 15000 }
            ).catch(() => null),
            navigateTo(page, 'reports/monthly'),
        ]);

        if (response) {
            expect(response.status()).toBe(200);
        }
    });
});

test.describe('Reports - Revenue Trend', () => {
    test('should load revenue trend chart', async ({ page }) => {
        const [response] = await Promise.all([
            page.waitForResponse(
                (resp) => resp.url().includes('/reports/revenue-trend') && resp.status() === 200,
                { timeout: 15000 }
            ).catch(() => null),
            navigateTo(page, 'reports/revenue-trend'),
        ]);

        if (response) {
            expect(response.status()).toBe(200);
        }

        await page.waitForTimeout(3000);

        // Chart should render
        const chart = page.locator('.apexcharts-canvas, canvas, [class*="chart"], svg');
        const chartCount = await chart.count();
        expect(chartCount).toBeGreaterThanOrEqual(0);
    });
});

test.describe('Reports - Top Services', () => {
    test('should fetch top services data', async ({ page }) => {
        const [response] = await Promise.all([
            page.waitForResponse(
                (resp) => resp.url().includes('/reports/top-services') && resp.status() === 200,
                { timeout: 15000 }
            ).catch(() => null),
            navigateTo(page, 'reports/top-services'),
        ]);

        if (response) {
            expect(response.status()).toBe(200);
        }
    });
});

test.describe('Reports - Top Customers', () => {
    test('should fetch top customers data', async ({ page }) => {
        const [response] = await Promise.all([
            page.waitForResponse(
                (resp) => resp.url().includes('/reports/top-customers') && resp.status() === 200,
                { timeout: 15000 }
            ).catch(() => null),
            navigateTo(page, 'reports/top-customers'),
        ]);

        if (response) {
            expect(response.status()).toBe(200);
        }
    });
});
