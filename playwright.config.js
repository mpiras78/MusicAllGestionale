const { defineConfig, devices } = require('@playwright/test');

const baseURL = (process.env.E2E_BASE_URL || 'http://127.0.0.1/MusicAllGestionale').replace(/\/?$/, '/');

module.exports = defineConfig({
    testDir: './tests/E2E/specs',
    fullyParallel: false,
    forbidOnly: Boolean(process.env.CI),
    retries: process.env.CI ? 2 : 0,
    outputDir: 'test-results/playwright-artifacts',
    reporter: [
        ['list'],
        ['html', { outputFolder: 'test-results/playwright-report', open: 'never' }]
    ],
    use: {
        baseURL,
        testIdAttribute: 'data-testid',
        trace: 'on-first-retry',
        screenshot: 'only-on-failure',
        video: 'retain-on-failure'
    },
    projects: [
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'] }
        }
    ]
});
