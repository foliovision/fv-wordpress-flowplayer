import { test, expect, type APIRequestContext, type Page } from '@playwright/test';
import {
  acceptNextDialog,
  backToPlaylist,
  clickPlaylistItemControl,
  configureVideo,
  expectSave,
  field,
  getPlayer,
  getPlayers,
  importPlayer,
  isSaveRequest,
  loginAs,
  openPlayer,
  playerField,
  playlistItem,
  PLAYERS_SCREEN,
  resetPlayers,
  setField,
  setFieldAndSave,
  VIDEOS,
  waitForSaveQueue,
} from './helpers';

/*
 * Video IDs while adding and removing playlist items.
 *
 * The editor only learns the ID of a new video from the save response, and
 * it looks up the IDs to send by the playlist item's data-index. Both can go
 * wrong when the playlist changes, which leaves orphan video rows or saves
 * one video over another.
 */

// Distinct URLs of a real file, so the video checker can still fetch them.
const NEW_SRC_1 = `${VIDEOS.fire.src}?playlist-saving=1`;
const NEW_SRC_2 = `${VIDEOS.fire.src}?playlist-saving=2`;

/**
 * The player's videos in playlist order, plus checks that every ID in the
 * player's `videos` column is a real, distinct video row and that no other
 * video rows exist (no orphans).
 */
async function expectPlayerVideos(request: APIRequestContext, playerId: number, srcs: string[]): Promise<void> {
  const player = await getPlayer(request, playerId);
  const ids = player.videos.split(',');

  expect(player.video_objects.map((v) => v.src)).toEqual(srcs);
  expect(new Set(ids).size, `duplicate video IDs in the player: ${player.videos}`).toBe(ids.length);
  expect(player.video_objects.map((v) => v.id)).toEqual(ids);
  expect((await getPlayers(request)).video_count, 'video rows in the DB').toBe(srcs.length);
}

/** "+" adds an empty playlist item, then its Video tab is opened. */
async function addPlaylistItem(page: Page, index: number): Promise<void> {
  await page.locator('#fv-player-shortcode-editor .playlist_add').click();
  await expect(playlistItem(page, index).locator('.fvp_item_video-filename')).toHaveText(`Video ${index + 1}`);
  await configureVideo(page, index);
}

test.describe('FV Player Editor — playlist saving', () => {
  let playerId: number;

  test.beforeEach(async ({ page, request }) => {
    await resetPlayers(request);
    playerId = await importPlayer(request);

    await loginAs(page);
    await page.goto(PLAYERS_SCREEN);
    await openPlayer(page, playerId);
  });

  /*
   * FV Player 7 looked up the video IDs with a cached jQuery .data('index'),
   * which went stale once playlist_index() renumbered the items. After this
   * sequence it sent the 3rd video without its ID and later the new one too,
   * so both were saved again as new rows, leaving orphans.
   * FV Player 8 updates the cached value in playlist_index().
   */
  test('adding, removing and adding another video keeps the video IDs', async ({ page, request }) => {
    await addPlaylistItem(page, 3);
    await setFieldAndSave(page, field(page, 'src', 3), NEW_SRC_1);
    await backToPlaylist(page);

    const dialog = acceptNextDialog(page);
    await expectSave(page, () => clickPlaylistItemControl(page, 2, '.fvp_item_remove'));
    await dialog;

    // The new item gets data-index 3 again, the index the first new video had.
    await addPlaylistItem(page, 3);
    await setFieldAndSave(page, field(page, 'src', 3), NEW_SRC_2);
    await backToPlaylist(page);

    // One more save after the playlist was renumbered.
    await setFieldAndSave(page, playerField(page, 'player_name'), 'Renamed');

    await expectPlayerVideos(request, playerId, [VIDEOS.fire.src, VIDEOS.paypal.src, NEW_SRC_1, NEW_SRC_2]);
  });

  // TODO: This currently fails, fix the underlying issue.
  test('removing a video while a new video is being saved keeps the video IDs', async ({ page, request }) => {
    test.fail(
      true,
      'After a save the editor gives the returned video IDs to the playlist items by position ' +
      '($(".fv-player-playlist-item").eq(k) in the fv_player_db_save success handler). ' +
      'If a video was removed while the save was running, the next video gets the wrong ID: ' +
      'here the new video is saved over Carly Simon and the new video row is left orphaned.',
    );

    // Hold the first save, the one with the new video, until the removal is done.
    let releaseSave!: () => void;
    const saveHeld = new Promise<void>((resolve) => (releaseSave = resolve));
    let held = false;
    await page.route((url) => isSaveRequest(url.toString()), async (route) => {
      if (!held) {
        held = true;
        await saveHeld;
      }
      await route.continue();
    });

    const removedId = (await getPlayer(request, playerId)).video_objects[1].id;

    await addPlaylistItem(page, 3);
    const saveStarted = page.waitForRequest((r) => isSaveRequest(r.url()));
    await setField(field(page, 'src', 3), NEW_SRC_1);
    await saveStarted;

    await backToPlaylist(page);

    const dialog = acceptNextDialog(page);
    await clickPlaylistItemControl(page, 1, '.fvp_item_remove');
    await dialog;

    // The removal is saved by a follow-up save once the first one finishes.
    const followUpSave = page.waitForResponse(
      (r) => isSaveRequest(r.url()) && r.request().postData()?.includes(`"deleted_videos":"${removedId}"`) === true,
    );
    releaseSave();
    await followUpSave;
    await waitForSaveQueue(page);

    await expectPlayerVideos(request, playerId, [VIDEOS.fire.src, VIDEOS.carly.src, NEW_SRC_1]);
  });
});
