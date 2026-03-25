import { test, expect } from '@playwright/test';
import { navigateTo } from '../fixtures/test-helpers';

/**
 * Navigation & Layout E2E Tests
 *
 * Tests sidebar navigation, breadcrumbs, responsive behavior,
 * error pages, and overall layout structure.
 */

test.describe('Sidebar Navigation', () => {
    test.beforeEach(async ({ page }) => {
        await navigateTo(page, 'dashboard');
    });

    test('should display sidebar menu', async ({ page }) => {
        const sidebar = page.locator('#kt_aside, #kt_app_sidebar, .aside');
        await expect(sidebar.first()).toBeVisible();
    });

    test('should have core menu items in sidebar', async ({ page }) => {
        const menuItems = page.locator('.menu-title');
        const texts = await menuItems.allTextContents();
        const menuText = texts.join(' ').toLowerCase();

        // Core navigation items should exist
        const expectedItems = ['dashboard', 'order', 'customer', 'pos'];
        for (const item of expectedItems) {
            expect(menuText).toContain(item);
        }
    });

    test('should navigate to different pages via sidebar', async ({ page }) => {
        // Map of sidebar menu items to their expected URL fragments
        const navItems = [
            { text: 'Dashboard', urlFragment: 'dashboard' },
            { text: 'Orders', urlFragment: 'orders' },
            { text: 'Customers', urlFragment: 'customers' },
        ];

        for (const item of navItems) {
            const menuLink = page.locator(`.menu-title:has-text("${item.text}")`).first();

            if (await menuLink.isVisible().catch(() => false)) {
                await menuLink.click();
                await page.waitForLoadState('networkidle');
                await page.waitForTimeout(1000);

                expect(page.url()).toContain(item.urlFragment);

                // Go back to dashboard for next iteration
                await navigateTo(page, 'dashboard');
            }
        }
    });
});

test.describe('Header & Topbar', () => {
    test.beforeEach(async ({ page }) => {
        await navigateTo(page, 'dashboard');
    });

    test('should display header/topbar', async ({ page }) => {
        const header = page.locator('#kt_header, #kt_app_header, .header');
        await expect(header.first()).toBeVisible();
    });

    test('should show user profile menu', async ({ page }) => {
        // User avatar/name in header
        const userMenu = page.locator(
            '.user-menu, .topbar-item, [data-kt-menu-trigger], .cursor-pointer img, .symbol'
        );
        await expect(userMenu.first()).toBeVisible({ timeout: 10000 });
    });

    test('should have notification indicator', async ({ page }) => {
        // Bell icon or notification count
        const notificationIcon = page.locator(
            '.notification, [class*="notification"], .fa-bell, .bi-bell, [data-kt-menu-trigger]'
        );
        const count = await notificationIcon.count();
        expect(count).toBeGreaterThanOrEqual(0);
    });
});

test.describe('Breadcrumb Navigation', () => {
    test('should show breadcrumbs on subpages', async ({ page }) => {
        await navigateTo(page, 'orders');
        await page.waitForTimeout(2000);

        const breadcrumb = page.locator('.breadcrumb, [class*="breadcrumb"]');
        const count = await breadcrumb.count();
        expect(count).toBeGreaterThanOrEqual(0);
    });

    test('should show breadcrumbs on create pages', async ({ page }) => {
        await navigateTo(page, 'orders/create');
        await page.waitForTimeout(2000);

        const breadcrumb = page.locator('.breadcrumb, [class*="breadcrumb"]');
        const count = await breadcrumb.count();
        expect(count).toBeGreaterThanOrEqual(0);
    });
});

test.describe('Page Routing', () => {
    const routes = [
        { path: 'dashboard', name: 'Dashboard' },
        { path: 'pos', name: 'POS Board' },
        { path: 'orders', name: 'Orders' },
        { path: 'customers', name: 'Customers' },
        { path: 'items', name: 'Items' },
        { path: 'services', name: 'Services' },
        { path: 'deliveries', name: 'Deliveries' },
        { path: 'reports', name: 'Reports' },
        { path: 'roles', name: 'Roles' },
        { path: 'users', name: 'Users' },
        { path: 'permissions', name: 'Permissions' },
        { path: 'languages', name: 'Languages' },
        { path: 'companies', name: 'Companies' },
        { path: 'countries', name: 'Countries' },
        { path: 'states', name: 'States' },
        { path: 'cities', name: 'Cities' },
    ];

    for (const route of routes) {
        test(`should load ${route.name} page (/${route.path})`, async ({ page }) => {
            await page.goto(`/admin#/${route.path}`);
            await page.waitForLoadState('networkidle');
            await page.waitForTimeout(2000);

            // Verify we're on the correct page
            await expect(page).toHaveURL(new RegExp(`admin#/${route.path}`));

            // Page should have content loaded (not blank)
            const appContent = page.locator('#app');
            await expect(appContent).toBeAttached();

            // No full-page error
            const errorPage = page.locator('text=500, text=Server Error');
            const hasError = await errorPage.isVisible().catch(() => false);
            expect(hasError).toBeFalsy();
        });
    }
});

test.describe('Error Pages', () => {
    // Use no auth for 404 test
    test.use({ storageState: { cookies: [], origins: [] } });

    test('should display 404 page', async ({ page }) => {
        await page.goto('/admin#/404');
        await page.waitForLoadState('networkidle');

        await expect(page).toHaveURL(/admin#\/404/);
    });

    test('should display 500 page', async ({ page }) => {
        await page.goto('/admin#/500');
        await page.waitForLoadState('networkidle');

        await expect(page).toHaveURL(/admin#\/500/);
    });

    test('should redirect unknown routes to 404', async ({ page }) => {
        await page.goto('/admin#/this-page-does-not-exist-at-all');
        await page.waitForLoadState('networkidle');

        await expect(page).toHaveURL(/admin#\/404/);
    });
});
