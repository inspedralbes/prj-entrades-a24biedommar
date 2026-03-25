// @ts-check
const { defineConfig } = require('@playwright/test');

/**
 * Proves E2E (Playwright).
 * Per a l’API Laravel: arrencar `php artisan serve --port=8777` (els ports 8000/8080 sovint estan ocupats).
 * Variable: API_BASE_URL=http://127.0.0.1:8777 (per defecte a sota). Vegeu README.md.
 */
module.exports = defineConfig({
    testDir: '.',
    testMatch: '**/*.spec.js',
    fullyParallel: true,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 1 : 0,
    use: {
        baseURL:
            process.env.API_BASE_URL ||
            process.env.PLAYWRIGHT_BASE_URL ||
            'http://127.0.0.1:8777',
        trace: 'on-first-retry',
    },
});
