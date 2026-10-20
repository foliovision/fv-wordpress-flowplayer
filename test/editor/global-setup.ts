import { chromium, request, type FullConfig } from '@playwright/test';
import { OFFLINE, VIDEOS, wordpressCanReach } from './e2e/helpers';

/**
 * Polls the WordPress login page until it's ready before any tests run, so
 * a slow container start doesn't show up as a confusing selector timeout in
 * the first test.
 *
 * Then checks that WordPress can reach the foliovision.com sample videos.
 * The editor makes the server check every new video URL on save, and
 * without network access every such save waits for a 10s timeout and the
 * video duration stays empty.
 */
export default async function globalSetup(config: FullConfig) {
  const baseURL =
    process.env.WP_BASE_URL ??
    `http://localhost:${process.env.WP_PORT ?? '8891'}`;

  await waitForWordPress(baseURL);

  if (OFFLINE) {
    return;
  }

  const api = await request.newContext({ baseURL });
  const reach = await wordpressCanReach(api, VIDEOS.fire.src);
  await api.dispose();

  if (!reach.ok) {
    throw new Error(
      `WordPress cannot reach ${VIDEOS.fire.src} (${reach.error}).\n` +
        `The video checker runs inside the WordPress container, so the container needs ` +
        `outbound network access. On Fedora with firewalld try:\n` +
        `  sudo firewall-cmd --permanent --zone=docker --add-masquerade && sudo firewall-cmd --reload && sudo systemctl restart docker\n` +
        `Or run with FV_PLAYER_TESTS_OFFLINE=1 to skip the video checker assertions.`,
    );
  }
}

async function waitForWordPress(baseURL: string) {
  const browser = await chromium.launch();
  const page = await browser.newPage();

  let lastError: unknown = null;
  const deadline = Date.now() + 60_000;

  while (Date.now() < deadline) {
    try {
      const response = await page.goto(`${baseURL}/wp-login.php`, {
        waitUntil: 'domcontentloaded',
        timeout: 5_000,
      });
      if (response && response.ok()) {
        const html = await page.content();
        if (/WordPress &rsaquo; Installation/.test(html)) {
          throw new Error(
            `WordPress is up at ${baseURL} but not yet installed — ` +
              `run \`npm run wp:up\` to complete installation.`,
          );
        }
        if (await page.locator('#user_login').isVisible()) {
          await browser.close();
          return;
        }
      }
    } catch (err) {
      lastError = err;
    }
    await new Promise((r) => setTimeout(r, 1_000));
  }

  await browser.close();
  throw new Error(
    `WordPress login page did not become ready at ${baseURL} within 60s. ` +
      `Last error: ${String(lastError)}`,
  );
}
