import { test, expect, type Page } from '@playwright/test';
import {
  closeEditor,
  expectSave,
  getPlayer,
  importPlayer,
  loginAs,
  openPlayer,
  playerField,
  PLAYERS_SCREEN,
  resetPlayers,
  setField,
  showTab,
} from './helpers';

const REDIRECT_URL = 'https://foliovision.com/player';

test.describe.serial('FV Player Editor — End of Video Action', () => {
  let playerId: number;

  async function openActions(page: Page) {
    await loginAs(page);
    await page.goto(PLAYERS_SCREEN);
    await openPlayer(page, playerId);
    await showTab(page, 'actions');
  }

  test.beforeAll(async ({ request }) => {
    await resetPlayers(request);
    playerId = await importPlayer(request);
  });

  test('enabling the action and picking Redirect saves the redirect URL', async ({ page, request }) => {
    await openActions(page);

    const endActions = playerField(page, 'end_actions');
    const redirect = playerField(page, 'redirect');
    await expect(endActions).toBeHidden();

    await expectSave(page, () => playerField(page, 'toggle_end_action').click());
    // Gutenberg-style toggle: the wrapper of the transparent checkbox gets `is-checked`.
    await expect(playerField(page, 'toggle_end_action').locator('xpath=..')).toHaveClass(/is-checked/);
    await expect(endActions).toBeVisible();
    // The Redirect URL input only appears for the Redirect action.
    await expect(redirect).toBeHidden();

    await expectSave(page, () => endActions.selectOption('redirect'));
    await expect(redirect).toBeVisible();
    await expectSave(page, () => setField(redirect, REDIRECT_URL));

    const player = await getPlayer(request, playerId);
    expect(player.toggle_end_action).toBe('true');
    expect(player.end_actions).toBe('redirect');
    expect(player.end_action_value).toBe(REDIRECT_URL);
  });

  test('the saved action is shown when the player is reopened', async ({ page }) => {
    await openActions(page);

    await expect(playerField(page, 'toggle_end_action')).toBeChecked();
    await expect(playerField(page, 'end_actions')).toHaveValue('redirect');
    await expect(playerField(page, 'redirect')).toHaveValue(REDIRECT_URL);
  });

  test('switching to another action hides the Redirect URL', async ({ page, request }) => {
    test.fail(
      true,
      'Known bug: on load text_and_select_worker() calls .show() on the Redirect wrapper because it has a value, ' +
        'and the end_actions change handler only adds the fv_player_interface_hide class, which the inline style overrides.',
    );

    await openActions(page);

    await expectSave(page, () => playerField(page, 'end_actions').selectOption('loop'));
    expect((await getPlayer(request, playerId)).end_actions).toBe('loop');

    await expect(playerField(page, 'redirect')).toBeHidden({ timeout: 2_000 });
  });

  test('disabling the action is saved and hides its settings', async ({ page, request }) => {
    await openActions(page);

    await expectSave(page, () => playerField(page, 'toggle_end_action').click());
    await expect(playerField(page, 'toggle_end_action')).not.toBeChecked();
    await expect(playerField(page, 'end_actions')).toBeHidden();

    expect((await getPlayer(request, playerId)).toggle_end_action).toBe('');

    await closeEditor(page);
    await openPlayer(page, playerId);
    await showTab(page, 'actions');
    await expect(playerField(page, 'toggle_end_action')).not.toBeChecked();
  });
});
