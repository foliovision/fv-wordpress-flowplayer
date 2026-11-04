import { test, expect, type Page } from '@playwright/test';
import {
  closeEditor,
  expectSave,
  getPlayer,
  importPlayer,
  loginAs,
  metaValue,
  openPlayer,
  playerField,
  playerRow,
  PLAYERS_SCREEN,
  resetPlayers,
  setEditLock,
  setField,
  USERS,
} from './helpers';

// FV_Player_Db::$edit_lock_timeout_seconds
const LOCK_TIMEOUT = 120;

const ADMIN_LOCK = 'edit_lock_1';

function lockKeys(meta: { meta_key: string }[]): string[] {
  return meta.map((m) => m.meta_key).filter((k) => k.startsWith('edit_lock_'));
}

/** Sends a heartbeat now instead of waiting up to 60s for the next one. */
async function heartbeat(page: Page) {
  const response = page.waitForResponse(
    (r) => r.url().includes('admin-ajax.php') && r.request().postData()?.includes('action=heartbeat') === true,
  );
  await page.evaluate(() => (window as any).wp.heartbeat.connectNow());
  await response;
}

test.describe('FV Player Editor — edit lock', () => {
  let playerId: number;

  test.beforeEach(async ({ request }) => {
    await resetPlayers(request);
    playerId = await importPlayer(request);
  });

  test('opening a player locks it for the current user', async ({ page, request }) => {
    await loginAs(page);
    await page.goto(PLAYERS_SCREEN);
    await openPlayer(page, playerId);

    const meta = (await getPlayer(request, playerId)).meta;
    expect(lockKeys(meta)).toEqual([ADMIN_LOCK]);
    expect(Math.abs(Date.now() / 1000 - Number(metaValue(meta, ADMIN_LOCK)))).toBeLessThan(60);
  });

  test('a player locked by another user cannot be opened', async ({ page, request }) => {
    const editorUserId = await setEditLock(request, playerId, USERS.editor.login, 10);

    await loginAs(page);
    await page.goto(PLAYERS_SCREEN);
    await playerRow(page, playerId).locator('a.fv-player-edit').first().click();

    // The lock owner is named by user_nicename.
    await expect(page.locator('#fv-player-editor-message-overlay')).toContainText(
      'editor is editing this player at the moment. Please try again later.',
    );
    // The other user's lock is left alone.
    expect(lockKeys((await getPlayer(request, playerId)).meta)).toEqual([`edit_lock_${editorUserId}`]);
  });

  test('a lock older than the timeout is taken over', async ({ page, request }) => {
    const editorUserId = await setEditLock(request, playerId, USERS.editor.login, LOCK_TIMEOUT + 30);

    await loginAs(page);
    await page.goto(PLAYERS_SCREEN);
    await openPlayer(page, playerId);

    await expect(playerField(page, 'player_name')).toHaveValue('MP4 Test Playlist');
    const keys = lockKeys((await getPlayer(request, playerId)).meta);
    expect(keys).toContain(ADMIN_LOCK);
    expect(keys).not.toContain(`edit_lock_${editorUserId}`);
  });

  test('closing the editor releases the lock on the next heartbeat', async ({ page, request }) => {
    await loginAs(page);
    await page.goto(PLAYERS_SCREEN);
    await openPlayer(page, playerId);
    expect(lockKeys((await getPlayer(request, playerId)).meta)).toEqual([ADMIN_LOCK]);

    await closeEditor(page);
    // The editor is meant to send a heartbeat 500ms after closing
    // (shortcode-editor.js), but fv-player-editor-modal.js replaces that
    // close handler, so the lock is only removed on the next regular
    // heartbeat. Trigger it rather than waiting for it.
    await heartbeat(page);

    expect(lockKeys((await getPlayer(request, playerId)).meta)).toEqual([]);
  });

  test('the edit lock survives an autosave', async ({ page, request }) => {
    test.fail(true, 'Known bug: fv_player_db_save deletes the edit_lock_* player meta, it only comes back on the next heartbeat.');

    await loginAs(page);
    await page.goto(PLAYERS_SCREEN);
    await openPlayer(page, playerId);

    await expectSave(page, () => setField(playerField(page, 'player_name'), 'Renamed'));

    expect(lockKeys((await getPlayer(request, playerId)).meta)).toEqual([ADMIN_LOCK]);
  });
});

