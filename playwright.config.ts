import { defineConfig, devices } from '@playwright/test';

/**
 * Playwright E2E Test Configuration for Laundry CRM
 *
 * Prerequisites:
 * 1. Laravel server running: php artisan serve
 * 2. Vite dev server running: npm run dev (or npm run build)
 * 3. Database seeded: php artisan migrate --seed
 *
 * Usage:
 *   npx playwright test                    # Run all tests
 *   npx playwright test auth               # Run auth tests only
 *   npx playwright test --headed           # Run with browser visible
 *   npx playwright test --ui               # Interactive UI mode
 *   npx playwright show-report             # View HTML report
 */
export default defineConfig({
    testDir: './e2e',
    fullyParallel: false,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    workers: 1,
    reporter: [
        ['html', { outputFolder: 'e2e-report' }],
        ['list'],
    ],
    use: {
        baseURL: process.env.APP_URL || 'http://127.0.0.1:8000',
        trace: 'on-first-retry',
        screenshot: 'only-on-failure',
        video: 'retain-on-failure',
        actionTimeout: 15000,
        navigationTimeout: 30000,
    },
    projects: [
        {
            name: 'setup',
            testMatch: /global-setup\.ts/,
        },
        {
            name: 'chromium',
            use: {
                ...devices['Desktop Chrome'],
                storageState: './e2e/.auth/user.json',
            },
            dependencies: ['setup'],
        },
        {
            name: 'mobile',
            use: {
                ...devices['iPhone 14'],
                storageState: './e2e/.auth/user.json',
            },
            dependencies: ['setup'],
        },
    ],
    /* Run local dev server before tests if needed */
    // webServer: {
    //     command: 'php artisan serve',
    //     url: 'http://127.0.0.1:8000',
    //     reuseExistingServer: true,
    // },
});
