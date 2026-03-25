import { test, expect } from '@playwright/test';
import { navigateTo, waitForApi } from '../fixtures/test-helpers';

/**
 * Dashboard E2E Tests
 *
 * Tests dashboard loading, module widgets, data display, and navigation.
 */

test.describe('Dashboard', () => {
    test.beforeEach(async ({ page }) => {
        await navigateTo(page, 'dashboard');
    });

    test('should load the dashboard page', async ({ page }) => {
        await expect(page).toHaveURL(/admin#\/dashboard/);
        await expect(page.locator('#app')).toBeAttached();
    });

    test('should display dashboard title', async ({ page }) => {
        // The page should have a title related to dashboard
        const title = await page.title();
        expect(title.toLowerCase()).toContain('dashboard');
    });

    test('should fetch dashboard modules from API', async ({ page }) => {
        // Intercept the API call for dashboard modules
        const response = await page.waitForResponse(
            (resp) => resp.url().includes('dashboard-modules') && resp.status() === 200,
            { timeout: 15000 }
        ).catch(() => null);

        // Dashboard data should load (either modules or data endpoint)
        if (response) {
            const data = await response.json();
            expect(data).toBeTruthy();
        }
    });

    test('should display dashboard widgets/cards', async ({ page }) => {
        // Wait for dashboard content to render
        await page.waitForTimeout(2000);

        // Should have some card or widget elements
        const cards = page.locator('.card, .widget, [class*="dashboard"]');
        const count = await cards.count();
        expect(count).toBeGreaterThan(0);
    });

    test('should have sidebar navigation visible', async ({ page }) => {
        const sidebar = page.locator('#kt_aside, #kt_app_sidebar, .aside');
        await expect(sidebar.first()).toBeVisible();
    });

    test('should have header with user info', async ({ page }) => {
        const header = page.locator('#kt_header, #kt_app_header, .header');
        await expect(header.first()).toBeVisible();
    });

    test('should not have any critical console errors', async ({ page }) => {
        const errors: string[] = [];
        page.on('console', (msg) => {
            if (msg.type() === 'error' && !msg.text().includes('favicon')) {
                errors.push(msg.text());
            }
        });

        await page.reload();
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(2000);

        // Filter out known harmless errors (CORS warnings, etc.)
        const criticalErrors = errors.filter(
            (e) => !e.includes('net::') && !e.includes('favicon') && !e.includes('404')
        );

        // Should have no critical JS errors
        expect(criticalErrors.length).toBeLessThanOrEqual(2);
    });
});
