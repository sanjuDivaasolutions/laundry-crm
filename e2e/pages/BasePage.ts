import { Page, Locator, expect } from '@playwright/test';

/**
 * Base Page Object for Laundry CRM.
 * All page objects extend this to get shared navigation and assertion helpers.
 */
export class BasePage {
    readonly page: Page;
    readonly sidebar: Locator;
    readonly header: Locator;
    readonly pageTitle: Locator;
    readonly breadcrumbs: Locator;

    constructor(page: Page) {
        this.page = page;
        this.sidebar = page.locator('#kt_aside, #kt_app_sidebar');
        this.header = page.locator('#kt_header, #kt_app_header');
        this.pageTitle = page.locator('h1, .page-title, .page-heading');
        this.breadcrumbs = page.locator('.breadcrumb');
    }

    async goto(hashPath: string): Promise<void> {
        await this.page.goto(`/admin#/${hashPath}`);
        await this.page.waitForLoadState('networkidle');
    }

    async expectLoaded(): Promise<void> {
        await expect(this.page.locator('#app')).toBeAttached();
    }

    async getSidebarMenuItems(): Promise<string[]> {
        const items = this.sidebar.locator('.menu-link .menu-title, .menu-item .menu-title');
        return items.allTextContents();
    }

    async clickSidebarMenu(menuText: string): Promise<void> {
        await this.sidebar.locator(`.menu-title:has-text("${menuText}")`).first().click();
        await this.page.waitForLoadState('networkidle');
    }

    async getNotificationCount(): Promise<string | null> {
        const badge = this.header.locator('.badge, .notification-count');
        if (await badge.isVisible().catch(() => false)) {
            return badge.textContent();
        }
        return null;
    }
}
