import { test, expect } from '@playwright/test';
import {
  clickSave,
  currentPlayerId,
  field,
  getPlayer,
  getPlayers,
  loginAs,
  OFFLINE,
  openNewPlayer,
  openPlayer,
  playerRow,
  PLAYERS_SCREEN,
  resetPlayers,
  setFieldAndSave,
  VIDEOS,
} from './helpers';

const FIRE_FILENAME = 'dominika-960-31.mp4';

test.describe.serial('FV Player Editor — create a player', () => {
  let playerId: number;

  test.beforeAll(async ({ request }) => {
    await resetPlayers(request);
  });

  test('entering a video link autosaves a draft player', async ({ page, request }) => {
    await loginAs(page);
    await page.goto(PLAYERS_SCREEN);
    await openNewPlayer(page);

    const response = await setFieldAndSave(page, field(page, 'src'), VIDEOS.fire.src);

    playerId = await currentPlayerId(page);
    expect(playerId).toBeGreaterThan(0);
    expect(Number(response.id)).toBe(playerId);

    const player = await getPlayer(request, playerId);
    expect(player.status).toBe('draft');
    expect(player.video_objects).toHaveLength(1);
    expect(player.video_objects[0].src).toBe(VIDEOS.fire.src);

    // The preview on the left is refreshed from the save response.
    await expect(page.locator('#fv-player-shortcode-editor-preview-target .flowplayer')).toBeVisible();

    // Splash is autosaved into the same player and video. (The Title field
    // is hidden unless the "playlist titles" interface option is on.)
    await setFieldAndSave(page, field(page, 'splash'), VIDEOS.fire.splash);

    const updated = await getPlayer(request, playerId);
    expect(updated.video_objects).toHaveLength(1);
    expect(updated.video_objects[0].id).toBe(player.video_objects[0].id);
    expect(updated.video_objects[0].splash).toBe(VIDEOS.fire.splash);

    // Autosaving must not have created extra players or videos.
    const all = await getPlayers(request);
    expect(all.players).toHaveLength(1);
    expect(all.video_count).toBe(1);
  });

  test('the server checked the video file on save', async ({ request }) => {
    test.skip(OFFLINE, 'needs network access from WordPress to foliovision.com');

    const video = (await getPlayer(request, playerId)).video_objects[0];
    expect(Number(video.duration)).toBeGreaterThan(0);
    expect(Number(video.width)).toBeGreaterThan(0);
    expect(Number(video.height)).toBeGreaterThan(0);
  });

  test('Save publishes the player and adds it to the list', async ({ page, request }) => {
    await loginAs(page);
    await page.goto(PLAYERS_SCREEN);

    // The draft from the previous test is listed, named after the video file
    // as it has no player name or video title.
    await expect(playerRow(page, playerId)).toContainText(`${FIRE_FILENAME} (Draft)`);

    await openPlayer(page, playerId);
    await clickSave(page);

    expect((await getPlayer(request, playerId)).status).toBe('published');

    const row = playerRow(page, playerId);
    await expect(row).toContainText(FIRE_FILENAME);
    await expect(row).not.toContainText('(Draft)');
  });
});
