import { test, expect } from '@playwright/test';

/**
 * Authentication E2E Tests
 *
 * Tests the sign-in page, validation, login flow, logout,
 * forgot password, and auth guard protection.
 */

// These tests do NOT use stored auth state - they test login from scratch
test.use({ storageState: { cookies: [], origins: [] } });

test.describe('Sign In Page', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/admin#/sign-in');
        await page.waitForLoadState('networkidle');
    });

    test('should display sign-in form', async ({ page }) => {
        // Check form elements are visible
        await expect(page.locator('input[name="email"]')).toBeVisible();
        await expect(page.locator('input[name="password"]')).toBeVisible();
        await expect(page.locator('#kt_sign_in_submit')).toBeVisible();
        await expect(page.locator('#kt_sign_in_submit')).toContainText('Continue');
    });

    test('should show validation errors for empty form', async ({ page }) => {
        // Submit empty form
        await page.locator('#kt_sign_in_submit').click();

        // Expect validation errors
        await expect(page.locator('.fv-help-block').first()).toBeVisible({ timeout: 5000 });
    });

    test('should show validation error for invalid email format', async ({ page }) => {
        await page.locator('input[name="email"]').fill('not-an-email');
        await page.locator('input[name="password"]').fill('password');
        await page.locator('#kt_sign_in_submit').click();

        // Expect email validation error
        await expect(page.locator('.fv-help-block').first()).toBeVisible({ timeout: 5000 });
    });

    test('should show validation error for short password', async ({ page }) => {
        await page.locator('input[name="email"]').fill('test@test.com');
        await page.locator('input[name="password"]').fill('ab');
        await page.locator('#kt_sign_in_submit').click();

        // Expect password length validation error
        await expect(page.locator('.fv-help-block').first()).toBeVisible({ timeout: 5000 });
    });

    test('should show error for invalid credentials', async ({ page }) => {
        await page.locator('input[name="email"]').fill('wrong@email.com');
        await page.locator('input[name="password"]').fill('wrongpassword');
        await page.locator('#kt_sign_in_submit').click();

        // SweetAlert error should appear
        await expect(page.locator('.swal2-popup')).toBeVisible({ timeout: 10000 });
        await expect(page.locator('.swal2-icon-error, .swal2-error')).toBeVisible();
    });

    test('should login successfully with valid credentials', async ({ page }) => {
        await page.locator('input[name="email"]').fill('admin@admin.com');
        await page.locator('input[name="password"]').fill('password');
        await page.locator('#kt_sign_in_submit').click();

        // Should redirect to dashboard
        await page.waitForURL(/admin#\/dashboard/, { timeout: 30000 });
        await expect(page).toHaveURL(/admin#\/dashboard/);
    });

    test('should store JWT token in localStorage after login', async ({ page }) => {
        await page.locator('input[name="email"]').fill('admin@admin.com');
        await page.locator('input[name="password"]').fill('password');
        await page.locator('#kt_sign_in_submit').click();

        await page.waitForURL(/admin#\/dashboard/, { timeout: 30000 });

        // Verify JWT token is stored
        const token = await page.evaluate(() => localStorage.getItem('id_token'));
        expect(token).toBeTruthy();
        expect(token!.split('.').length).toBe(3); // JWT has 3 parts
    });

    test('should have forgot password link', async ({ page }) => {
        const forgotLink = page.locator('a:has-text("Forgot Password")');
        await expect(forgotLink).toBeVisible();
        await forgotLink.click();
        await expect(page).toHaveURL(/admin#\/password-reset/);
    });

    test('should disable submit button while logging in', async ({ page }) => {
        await page.locator('input[name="email"]').fill('admin@admin.com');
        await page.locator('input[name="password"]').fill('password');

        const submitBtn = page.locator('#kt_sign_in_submit');
        await submitBtn.click();

        // Button should show loading indicator briefly
        await expect(submitBtn).toHaveAttribute('data-kt-indicator', 'on');
    });
});

test.describe('Auth Guard', () => {
    test('should redirect unauthenticated users to sign-in', async ({ page }) => {
        // Clear any stored auth
        await page.goto('/admin#/dashboard');
        await page.waitForLoadState('networkidle');

        // Should redirect to sign-in
        await expect(page).toHaveURL(/admin#\/sign-in/);
    });

    test('should redirect to sign-in when accessing protected routes', async ({ page }) => {
        const protectedRoutes = [
            '/admin#/orders',
            '/admin#/customers',
            '/admin#/pos',
            '/admin#/items',
            '/admin#/reports',
        ];

        for (const route of protectedRoutes) {
            await page.goto(route);
            await page.waitForLoadState('networkidle');
            await expect(page).toHaveURL(/admin#\/sign-in/);
        }
    });
});

test.describe('Forgot Password Page', () => {
    test('should display forgot password form', async ({ page }) => {
        await page.goto('/admin#/password-reset');
        await page.waitForLoadState('networkidle');

        await expect(page.locator('input[name="email"], input[type="email"]').first()).toBeVisible();
    });
});

test.describe('Error Pages', () => {
    test('should display 404 page for unknown routes', async ({ page }) => {
        await page.goto('/admin#/non-existent-page-xyz');
        await page.waitForLoadState('networkidle');

        await expect(page).toHaveURL(/admin#\/404/);
    });
});
