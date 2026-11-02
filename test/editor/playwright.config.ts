import { defineConfig, devices } from '@playwright/test';

/**
 * All specs share one WordPress DB and each one resets the FV Player tables
 * in `beforeAll`, so we keep a single worker and disable `fullyParallel`.
 */
export default defineConfig({
  testDir: './e2e',
  globalSetup: require.resolve('./global-setup'),
  fullyParallel: false,
  workers: 1,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  // Saving a video makes the server check the remote foliovision.com file
  // (models/checker.php) and the editor only flushes its save queue every
  // 1.5s, so a single editor action can take several seconds.
  timeout: 90_000,
  expect: { timeout: 15_000 },
  reporter: [
    ['list'],
    ['html', { outputFolder: 'playwright-report', open: 'never' }],
  ],
  use: {
    baseURL:
      process.env.WP_BASE_URL ??
      `http://localhost:${process.env.WP_PORT ?? '8891'}`,
    navigationTimeout: 30_000,
    trace: 'on',
    screenshot: { mode: 'on', fullPage: true },
    video: 'retain-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
