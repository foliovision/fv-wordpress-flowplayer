import { test, expect } from '@playwright/test';
import {
  closeEditor,
  currentPlayerId,
  getPlayer,
  getPlayers,
  importPlayer,
  loginAs,
  openPlayer,
  playerField,
  playerRow,
  PLAYERS_SCREEN,
  playlistTitles,
  resetPlayers,
  setFieldAndSave,
  VIDEOS,
} from './helpers';

test.describe.serial('FV Player Editor — edit an existing player', () => {
  let playerId: number;

  test.beforeAll(async ({ request }) => {
    await resetPlayers(request);
    playerId = await importPlayer(request);
  });

  test('opening the player loads its playlist and name', async ({ page }) => {
    await loginAs(page);
    await page.goto(PLAYERS_SCREEN);
    await openPlayer(page, playerId);

    expect(await currentPlayerId(page)).toBe(playerId);
    await expect(page.locator('#fv-player-shortcode-editor')).toHaveClass(/is-playlist-active/);
    expect(await playlistTitles(page)).toEqual([VIDEOS.fire.title, VIDEOS.paypal.title, VIDEOS.carly.title]);
    await expect(playerField(page, 'player_name')).toHaveValue('MP4 Test Playlist');
  });

  test('renaming the playlist autosaves the same player', async ({ page, request }) => {
    await loginAs(page);
    await page.goto(PLAYERS_SCREEN);
    await openPlayer(page, playerId);

    const before = await getPlayer(request, playerId);

    const response = await setFieldAndSave(page, playerField(page, 'player_name'), 'Renamed Playlist');
    expect(Number(response.id)).toBe(playerId);

    const after = await getPlayer(request, playerId);
    expect(after.player_name).toBe('Renamed Playlist');
    // Same videos, nothing duplicated.
    expect(after.videos).toBe(before.videos);
    const all = await getPlayers(request);
    expect(all.players).toHaveLength(1);
    expect(all.video_count).toBe(3);

    // Closing on the Players screen refreshes the player's row.
    await closeEditor(page);
    await expect(playerRow(page, playerId)).toContainText('Renamed Playlist');
  });

  test('reopening the player shows the saved name', async ({ page }) => {
    await loginAs(page);
    await page.goto(PLAYERS_SCREEN);
    await openPlayer(page, playerId);

    await expect(playerField(page, 'player_name')).toHaveValue('Renamed Playlist');
  });
});
