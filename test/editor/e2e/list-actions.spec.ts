import * as fs from 'fs';
import { test, expect, type Page } from '@playwright/test';
import {
  expectEditorClosed,
  getPlayer,
  getPlayers,
  importPlayer,
  loginAs,
  PLAYER_FIXTURE_PATH,
  playerFixture,
  playerRow,
  PLAYERS_SCREEN,
  resetPlayers,
} from './helpers';

/** Clicks a row action link; they only show while the row is hovered. */
async function clickRowAction(page: Page, playerId: number, selector: string) {
  const row = playerRow(page, playerId);
  await row.hover();
  await row.locator(`.row-actions ${selector}`).click();
}

test.describe('FV Player Editor — Players screen actions', () => {
  test.beforeEach(async ({ request }) => {
    await resetPlayers(request);
  });

  test('Import creates a player from JSON and lists it', async ({ page, request }) => {
    await loginAs(page);
    await page.goto(PLAYERS_SCREEN);

    await page.locator('.page-title-action.fv-player-import').click();
    const overlay = page.locator('#fv-player-editor-import-overlay');
    await expect(overlay).toBeVisible();

    await page.locator('#fv_player_import_data').fill(fs.readFileSync(PLAYER_FIXTURE_PATH, 'utf8'));
    const imported = page.waitForResponse(
      (r) => r.url().includes('admin-ajax.php') && r.request().postData()?.includes('action=fv_player_db_import') === true,
    );
    await page.locator('#fv-player-editor-import-overlay-import').click();
    const playerId = Number(await (await imported).text());
    expect(playerId).toBeGreaterThan(0);

    await expectEditorClosed(page);
    await expect(playerRow(page, playerId)).toContainText('MP4 Test Playlist');

    const player = await getPlayer(request, playerId);
    expect(player.video_objects.map((v) => v.src)).toEqual(playerFixture().videos.map((v) => v.src));
  });

  test('Import rejects invalid JSON', async ({ page, request }) => {
    await loginAs(page);
    await page.goto(PLAYERS_SCREEN);

    await page.locator('.page-title-action.fv-player-import').click();
    await page.locator('#fv_player_import_data').fill('{not json');
    await page.locator('#fv-player-editor-import-overlay-import').click();

    await expect(page.locator('#fv-player-editor-import-overlay .fv-player-editor-overlay-notice')).toHaveText('Bad JSON format!');
    expect((await getPlayers(request)).players).toHaveLength(0);
  });

  test('Export shows the player JSON', async ({ page, request }) => {
    const playerId = await importPlayer(request);

    await loginAs(page);
    await page.goto(PLAYERS_SCREEN);
    await clickRowAction(page, playerId, '.fv-player-export');

    const textarea = page.locator('#fv-player-editor-export-overlay textarea');
    await expect(textarea).not.toHaveValue('');
    const exported = JSON.parse(await textarea.inputValue());

    const fixture = playerFixture();
    expect(exported.player_name).toBe(fixture.player_name);
    expect(exported.videos.map((v) => [v.src, v.title, v.splash])).toEqual(
      fixture.videos.map((v) => [v.src, v.title, v.splash]),
    );
    expect(exported.videos[1].meta).toEqual(
      expect.arrayContaining([expect.objectContaining({ meta_key: 'subtitles_en', meta_value: fixture.videos[1].meta[0].meta_value })]),
    );
  });

  test('Clone creates a copy with its own videos', async ({ page, request }) => {
    const playerId = await importPlayer(request);

    await loginAs(page);
    await page.goto(PLAYERS_SCREEN);

    const cloned = page.waitForResponse(
      (r) => r.url().includes('admin-ajax.php') && r.request().postData()?.includes('action=fv_player_db_clone') === true,
    );
    await clickRowAction(page, playerId, '.fv-player-clone');
    const cloneId = Number(await (await cloned).text());
    expect(cloneId).toBeGreaterThan(playerId);

    await expect(playerRow(page, cloneId)).toBeVisible();

    const original = await getPlayer(request, playerId);
    const clone = await getPlayer(request, cloneId);
    expect(clone.player_name).toBe(original.player_name);
    expect(clone.video_objects.map((v) => v.src)).toEqual(original.video_objects.map((v) => v.src));
    // New video rows, not shared with the original.
    expect(clone.video_objects.map((v) => v.id)).not.toEqual(original.video_objects.map((v) => v.id));
    expect((await getPlayers(request)).video_count).toBe(6);
  });

  test('Delete asks "Are you sure?" and removes the player', async ({ page, request }) => {
    const playerId = await importPlayer(request);

    await loginAs(page);
    await page.goto(PLAYERS_SCREEN);

    await clickRowAction(page, playerId, '.fv-player-remove');
    const confirm = playerRow(page, playerId).locator('.fv-player-remove-confirm');
    await expect(confirm).toHaveText('Are you sure?');

    const removed = page.waitForResponse(
      (r) => r.url().includes('admin-ajax.php') && r.request().postData()?.includes('action=fv_player_db_remove') === true,
    );
    await confirm.click();
    await removed;

    await expect(playerRow(page, playerId)).toHaveCount(0);
    expect((await getPlayers(request)).players).toHaveLength(0);
  });
});
