import { Page, expect } from '@playwright/test';

/**
 * Wait for the Vue SPA to fully load on a given hash route.
 */
export async function navigateTo(page: Page, hashRoute: string): Promise<void> {
    await page.goto(`/admin#/${hashRoute}`);
    await page.waitForLoadState('networkidle');
    // Wait for Vue app to mount
    await page.waitForSelector('#app', { state: 'attached' });
}

/**
 * Wait for a data table to load its content.
 */
export async function waitForDataTable(page: Page): Promise<void> {
    // Wait for loading spinners to disappear
    await page.waitForFunction(() => {
        const spinners = document.querySelectorAll('.spinner-border, .el-loading-mask');
        return spinners.length === 0;
    }, { timeout: 15000 });

    // Small buffer for Vue reactivity
    await page.waitForTimeout(500);
}

/**
 * Wait for API response to complete.
 */
export async function waitForApi(page: Page, urlPattern: string | RegExp): Promise<void> {
    await page.waitForResponse(
        (response) => {
            const url = response.url();
            if (typeof urlPattern === 'string') {
                return url.includes(urlPattern) && response.status() === 200;
            }
            return urlPattern.test(url) && response.status() === 200;
        },
        { timeout: 15000 }
    );
}

/**
 * Dismiss SweetAlert dialogs if present.
 */
export async function dismissSwal(page: Page): Promise<void> {
    const swalBtn = page.locator('.swal2-confirm');
    if (await swalBtn.isVisible({ timeout: 2000 }).catch(() => false)) {
        await swalBtn.click();
    }
}

/**
 * Check that no console errors occurred during a test.
 */
export function setupConsoleErrorTracker(page: Page): string[] {
    const errors: string[] = [];
    page.on('console', (msg) => {
        if (msg.type() === 'error') {
            errors.push(msg.text());
        }
    });
    return errors;
}

/**
 * Get the count of rows in a data table.
 */
export async function getTableRowCount(page: Page): Promise<number> {
    await waitForDataTable(page);
    return page.locator('table tbody tr').count();
}

/**
 * Fill a form field by its label text.
 */
export async function fillFormField(page: Page, label: string, value: string): Promise<void> {
    const field = page.locator(`label:has-text("${label}")`).locator('..').locator('input, textarea, select').first();
    await field.fill(value);
}

/**
 * Click a button by its text content.
 */
export async function clickButton(page: Page, text: string): Promise<void> {
    await page.locator(`button:has-text("${text}")`).first().click();
}

/**
 * Verify a toast notification appeared.
 */
export async function expectToast(page: Page, text?: string): Promise<void> {
    const toast = page.locator('.v-toast__item, .el-notification, .swal2-popup');
    await expect(toast.first()).toBeVisible({ timeout: 10000 });
    if (text) {
        await expect(toast.first()).toContainText(text);
    }
}

/**
 * Verify breadcrumb navigation shows expected items.
 */
export async function expectBreadcrumb(page: Page, items: string[]): Promise<void> {
    for (const item of items) {
        await expect(
            page.locator('.breadcrumb, [data-kt-breadcrumb]').locator(`text=${item}`)
        ).toBeVisible();
    }
}
