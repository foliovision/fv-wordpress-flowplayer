import { test, expect } from '@playwright/test';
import {
  closeEditor,
  currentPlayerId,
  expectEditorClosed,
  field,
  getPlayer,
  importPlayer,
  isSaveRequest,
  loginAs,
  openNewPlayer,
  openPlayer,
  playerField,
  playerRow,
  PLAYERS_SCREEN,
  resetPlayers,
  setField,
  setFieldAndSave,
  VIDEOS,
  waitForSaveQueue,
} from './helpers';

test.describe('FV Player Editor — closing', () => {
  test.beforeEach(async ({ request }) => {
    await resetPlayers(request);
  });

  test('closing while a save is running shows a wait message and keeps the editor open', async ({ page, request }) => {
    const playerId = await importPlayer(request);

    await loginAs(page);
    await page.goto(PLAYERS_SCREEN);
    await openPlayer(page, playerId);

    // Hold the save request so it is certainly still running when the
    // close button checks (600ms after the click, fv-player-editor-modal.js).
    let releaseSave!: () => void;
    const saveHeld = new Promise<void>((resolve) => (releaseSave = resolve));
    await page.route((url) => isSaveRequest(url.toString()), async (route) => {
      await saveHeld;
      await route.continue();
    });

    const saveStarted = page.waitForRequest((r) => isSaveRequest(r.url()));
    await setField(playerField(page, 'player_name'), 'Renamed while closing');
    await saveStarted;

    const dialog = page.waitForEvent('dialog');
    await page.locator('#fv-player-editor-modal-close').click();
    const alert = await dialog;
    expect(alert.type()).toBe('alert');
    // Sic, the typo is in the plugin.
    expect(alert.message()).toBe('FV Player Edtior is still saving, please wait.');
    await alert.accept();

    await expect(page.locator('body')).toHaveClass(/is-fv-player-editor-modal-open/);

    releaseSave();
    await waitForSaveQueue(page);
    expect((await getPlayer(request, playerId)).player_name).toBe('Renamed while closing');

    // Once saved, the editor closes normally.
    await closeEditor(page);
  });

  test('a new player closed without Save is kept as a draft', async ({ page, request }) => {
    await loginAs(page);
    await page.goto(PLAYERS_SCREEN);
    await openNewPlayer(page);

    await setFieldAndSave(page, field(page, 'src'), VIDEOS.carly.src);
    const playerId = await currentPlayerId(page);

    await page.locator('#fv-player-editor-modal-close').click();
    await expectEditorClosed(page);

    expect((await getPlayer(request, playerId)).status).toBe('draft');
    // The row is added to the list without a page reload.
    await expect(playerRow(page, playerId)).toContainText('(Draft)');
  });
});
