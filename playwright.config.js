// playwright.config.js minimal
const { defineConfig, devices } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './tests/endtoend',
  timeout: 30000,
  use: {
    baseURL: 'http://localhost:8888/it-expect',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] }
    }
  ]
});