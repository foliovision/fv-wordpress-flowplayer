import { test, expect, type Page } from '@playwright/test';
import {
  acceptNextDialog,
  clickPlaylistItemControl,
  configureVideo,
  expectSave,
  field,
  getPlayer,
  getPlayers,
  importPlayer,
  loginAs,
  openPlayer,
  playlistItem,
  PLAYERS_SCREEN,
  playlistTitles,
  resetPlayers,
  setFieldAndSave,
  VIDEOS,
} from './helpers';

async function dbTitles(request, playerId: number): Promise<string[]> {
  return (await getPlayer(request, playerId)).video_objects.map((v) => v.title);
}

test.describe.serial('FV Player Editor — playlist', () => {
  let playerId: number;

  async function open(page: Page) {
    await loginAs(page);
    await page.goto(PLAYERS_SCREEN);
    await openPlayer(page, playerId);
  }

  test.beforeAll(async ({ request }) => {
    await resetPlayers(request);
    playerId = await importPlayer(request);
  });

  test('moving a video up reorders the playlist', async ({ page, request }) => {
    await open(page);

    await expectSave(page, () => clickPlaylistItemControl(page, 2, '.fv-player-editor-playlist-move-up'));

    const expected = [VIDEOS.fire.title, VIDEOS.carly.title, VIDEOS.paypal.title];
    expect(await playlistTitles(page)).toEqual(expected);
    expect(await dbTitles(request, playerId)).toEqual(expected);
  });

  test('moving a video down reorders the playlist', async ({ page, request }) => {
    await open(page);

    await expectSave(page, () => clickPlaylistItemControl(page, 0, '.fv-player-editor-playlist-move-down'));

    const expected = [VIDEOS.carly.title, VIDEOS.fire.title, VIDEOS.paypal.title];
    expect(await playlistTitles(page)).toEqual(expected);
    expect(await dbTitles(request, playerId)).toEqual(expected);
  });

  test('the video title can be edited inline in the playlist', async ({ page, request }) => {
    await open(page);

    const item = playlistItem(page, 1);
    await item.locator('.fvp_item_video-filename').click();
    const input = item.locator('.fvp_item_video-edit-input');
    await expect(input).toBeVisible();
    await expect(input).toHaveValue(VIDEOS.fire.title);

    // The title is copied into the (hidden) video Title field on each
    // keyup, so type it instead of fill() which fires no key events.
    await expectSave(page, async () => {
      await input.fill('');
      await input.pressSequentially('Fire Dance');
      await input.press('Enter');
    });

    await expect(item.locator('.fvp_item_video-filename')).toHaveText('Fire Dance');
    expect(await dbTitles(request, playerId)).toEqual([VIDEOS.carly.title, 'Fire Dance', VIDEOS.paypal.title]);
  });

  test('removing a video asks for confirmation and removes it from the player', async ({ page, request }) => {
    await open(page);

    const before = await getPlayer(request, playerId);
    const removedId = before.video_objects[1].id;

    const dialog = acceptNextDialog(page);
    await expectSave(page, () => clickPlaylistItemControl(page, 1, '.fvp_item_remove'));
    expect(await dialog).toBe('Would you like to remove this video?');

    expect(await playlistTitles(page)).toEqual([VIDEOS.carly.title, VIDEOS.paypal.title]);

    const after = await getPlayer(request, playerId);
    expect(after.video_objects.map((v) => v.title)).toEqual([VIDEOS.carly.title, VIDEOS.paypal.title]);
    expect(after.videos.split(',')).not.toContain(removedId);
    // The video row itself is deleted too (sent as deleted_videos).
    expect((await getPlayers(request)).video_count).toBe(2);
  });

  test('a new video can be added to the playlist', async ({ page, request }) => {
    await open(page);

    await page.locator('#fv-player-shortcode-editor .playlist_add').click();

    // "+" adds an empty row to the playlist, "Configure video" opens it.
    await expect(playlistItem(page, 2).locator('.fvp_item_video-filename')).toHaveText('Video 3');
    await configureVideo(page, 2);

    const src = field(page, 'src', 2);
    await expect(src).toBeVisible();
    await setFieldAndSave(page, src, VIDEOS.fire.src);

    const after = await getPlayer(request, playerId);
    expect(after.video_objects.map((v) => v.src)).toEqual([VIDEOS.carly.src, VIDEOS.paypal.src, VIDEOS.fire.src]);
    expect((await getPlayers(request)).video_count).toBe(3);
  });
});
