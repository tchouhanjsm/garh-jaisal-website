const { defineConfig } = require('@playwright/test');

const configuredTarget = (process.env.STAGING_BASE_URL || '').trim();
const baseURL = configuredTarget ? new URL(configuredTarget).origin : 'http://127.0.0.1:8081';

module.exports = defineConfig({
  testDir: './tests/staging',
  fullyParallel: false,
  forbidOnly: Boolean(process.env.CI),
  retries: process.env.CI ? 1 : 0,
  reporter: process.env.CI ? [['list'], ['html', { outputFolder: 'artifacts/staging-qa/playwright-report', open: 'never' }]] : 'list',
  outputDir: 'artifacts/staging-qa/test-results',
  timeout: 20 * 60 * 1000,
  expect: { timeout: 10_000 },
  use: {
    baseURL,
    browserName: 'chromium',
    viewport: { width: 1280, height: 800 },
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    actionTimeout: 10_000,
  },
  projects: [{ name: 'staging-qa', use: { viewport: { width: 1280, height: 800 } } }],
  ...(configuredTarget ? {} : {
    webServer: {
      command: 'php -S 127.0.0.1:8081 -t .',
      url: 'http://127.0.0.1:8081/',
      reuseExistingServer: !process.env.CI,
      timeout: 15_000,
      stdout: 'pipe',
      stderr: 'pipe',
    },
  }),
});
