import { test, expect } from '@playwright/test';
import { closeEditor, loginAs, openNewPlayer, PLAYERS_SCREEN, resetPlayers } from './helpers';

test.describe('FV Player Editor — smoke', () => {
  test.beforeAll(async ({ request }) => {
    await resetPlayers(request);
  });

  test('the Players screen renders without PHP errors', async ({ page }) => {
    await loginAs(page);
    await page.goto(PLAYERS_SCREEN);

    await expect(page.locator('.wrap h1.wp-heading-inline')).toBeVisible();
    await expect(page.locator('.page-title-action.fv-player-edit[data-add_new]')).toHaveText('Add New');
    await expect(page.locator('.page-title-action.fv-player-import')).toBeVisible();

    // WP_DEBUG is on in the test stack, so notices are printed into the page.
    const body = await page.locator('body').innerText();
    expect(body).not.toMatch(/Fatal error|Warning:|Notice:|Deprecated:/i);
  });

  test('"Add New" opens an empty editor and the close button closes it', async ({ page }) => {
    await loginAs(page);
    await page.goto(PLAYERS_SCREEN);
    await openNewPlayer(page);

    const editor = page.locator('#fv-player-shortcode-editor');
    await expect(editor).toBeVisible();
    // New player starts on the Video tab of the first playlist item, with
    // the "Add your video" intro on the left.
    await expect(editor.locator('.fv-player-tabs-header a.nav-tab-active')).toHaveText('Video');
    await expect(page.locator('#fv-player-shortcode-editor-preview-no [name=hero-src]')).toBeVisible();
    await expect(editor.locator('.fv_player_field_insert-button')).toHaveText('Save');

    await closeEditor(page);
    await expect(editor).toBeHidden();
  });
});
