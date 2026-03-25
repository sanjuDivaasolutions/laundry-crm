import { test as setup, expect } from '@playwright/test';

const AUTH_FILE = './e2e/.auth/user.json';

/**
 * Global setup: authenticates once and saves storage state for all tests.
 * This avoids logging in before every test.
 */
setup('authenticate', async ({ page }) => {
    setup.setTimeout(60000);

    // Capture console errors for debugging
    const consoleMessages: string[] = [];
    page.on('console', (msg) => {
        consoleMessages.push(`[${msg.type()}] ${msg.text()}`);
    });
    page.on('pageerror', (error) => {
        consoleMessages.push(`[PAGE ERROR] ${error.message}`);
    });

    // Navigate to sign-in page (SPA is at /admin#/)
    await page.goto('/admin#/sign-in', { waitUntil: 'load' });

    // Give extra time for JS modules to load
    await page.waitForTimeout(5000);

    // Debug: check what's on the page
    const appHtml = await page.locator('#app').innerHTML().catch(() => 'NO #app FOUND');
    const bodyHtml = await page.locator('body').innerHTML().catch(() => 'NO BODY');
    console.log('=== DEBUG INFO ===');
    console.log('URL:', page.url());
    console.log('#app innerHTML length:', appHtml.length);
    console.log('#app innerHTML preview:', appHtml.substring(0, 300));
    console.log('Console messages:', consoleMessages.slice(0, 10));
    console.log('===================');

    // Wait for the sign-in form to appear
    await page.waitForSelector('input[name="email"]', { state: 'visible', timeout: 30000 });

    // Fill login form
    await page.locator('input[name="email"]').fill('admin@admin.com');
    await page.locator('input[name="password"]').fill('password');

    // Click the Continue/Submit button
    await page.locator('#kt_sign_in_submit').click();

    // Wait for navigation to dashboard after successful login
    await page.waitForURL(/admin#\/dashboard/, { timeout: 30000 });

    // Verify we're on the dashboard
    await expect(page).toHaveURL(/admin#\/dashboard/);

    // Save authentication state (localStorage with JWT token)
    await page.context().storageState({ path: AUTH_FILE });
});
