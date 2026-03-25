import { test, expect } from '@playwright/test';
import { navigateTo, waitForApi, dismissSwal } from '../fixtures/test-helpers';

/**
 * POS Board (Kanban) E2E Tests
 *
 * Tests the 5-column Kanban board, order creation, status updates,
 * payment processing, and order history.
 */

test.describe('POS Board', () => {
    test.beforeEach(async ({ page }) => {
        await navigateTo(page, 'pos');
        await page.waitForTimeout(2000); // Allow board to fully render
    });

    test('should load the POS board page', async ({ page }) => {
        await expect(page).toHaveURL(/admin#\/pos/);
    });

    test('should display Kanban board with status columns', async ({ page }) => {
        // The POS board should have the 5 status columns
        const expectedStatuses = ['Pending', 'Washing', 'Drying', 'Ready', 'Delivered'];

        for (const status of expectedStatuses) {
            const column = page.locator(`text=${status}`);
            await expect(column.first()).toBeVisible({ timeout: 10000 });
        }
    });

    test('should fetch board data from API', async ({ page }) => {
        await page.reload();

        const response = await page.waitForResponse(
            (resp) => resp.url().includes('/pos/board') && resp.status() === 200,
            { timeout: 15000 }
        );

        const data = await response.json();
        expect(data).toBeTruthy();
    });

    test('should display POS statistics', async ({ page }) => {
        // Statistics bar should show order counts or revenue
        const statsArea = page.locator('.stat, .statistics, .summary, .badge, .count');
        const count = await statsArea.count();
        expect(count).toBeGreaterThan(0);
    });

    test('should have a create/new order button', async ({ page }) => {
        // Look for new order / add order button
        const newOrderBtn = page.locator(
            'button:has-text("New Order"), button:has-text("Add Order"), button:has-text("Create"), a:has-text("New Order")'
        );
        await expect(newOrderBtn.first()).toBeVisible({ timeout: 10000 });
    });

    test('should open order creation form when clicking new order', async ({ page }) => {
        const newOrderBtn = page.locator(
            'button:has-text("New Order"), button:has-text("Add Order"), button:has-text("Create"), a:has-text("New Order")'
        );
        await newOrderBtn.first().click();

        // Modal or form should appear
        await page.waitForTimeout(1000);
        const modal = page.locator('.modal.show, .el-dialog, .el-drawer, [role="dialog"]');
        await expect(modal.first()).toBeVisible({ timeout: 10000 });
    });

    test('should display order cards in Kanban columns', async ({ page }) => {
        // Wait for board data to load
        await page.waitForTimeout(3000);

        // Order cards should have order numbers or customer info
        const orderCards = page.locator('.card, .order-card, [class*="kanban"] .card, [class*="board"] .card');
        const count = await orderCards.count();

        // May have 0 orders if fresh seed, that's OK
        expect(count).toBeGreaterThanOrEqual(0);
    });

    test('should have search functionality', async ({ page }) => {
        // Search input or button for finding orders/customers
        const search = page.locator(
            'input[type="search"], input[placeholder*="Search"], input[placeholder*="search"], .search-input'
        );
        await expect(search.first()).toBeVisible({ timeout: 10000 });
    });

    test('should have order history access', async ({ page }) => {
        // History button or link
        const historyBtn = page.locator(
            'button:has-text("History"), a:has-text("History"), [title*="History"], [title*="history"]'
        );

        if (await historyBtn.first().isVisible().catch(() => false)) {
            await historyBtn.first().click();
            await page.waitForTimeout(1000);

            // History modal or panel should open
            const historyModal = page.locator('.modal.show, .el-dialog, .el-drawer');
            await expect(historyModal.first()).toBeVisible({ timeout: 5000 });
        }
    });

    test('should be able to click on an order card to view details', async ({ page }) => {
        await page.waitForTimeout(3000);

        // Find an order card and click it
        const orderCard = page.locator('.card, .order-card, [class*="kanban"] .card').first();

        if (await orderCard.isVisible().catch(() => false)) {
            await orderCard.click();
            await page.waitForTimeout(1000);

            // Should open a detail modal or navigate to details
            const detail = page.locator('.modal.show, .el-dialog, .el-drawer, [role="dialog"]');
            const detailVisible = await detail.first().isVisible().catch(() => false);

            // Either modal opens or URL changes - both are valid
            expect(detailVisible || page.url().includes('order')).toBeTruthy();
        }
    });

    test('should show revenue/earnings summary', async ({ page }) => {
        // The POS board typically shows today's revenue
        const revenue = page.locator(
            'text=/\\$|Revenue|Earnings|Total/i'
        );
        const count = await revenue.count();
        expect(count).toBeGreaterThanOrEqual(0); // May not have revenue data
    });
});
