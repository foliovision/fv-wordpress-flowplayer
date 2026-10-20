import * as fs from 'fs';
import * as path from 'path';
import { expect, type APIRequestContext, type Locator, type Page } from '@playwright/test';

export const USERS = {
  admin: {
    login: process.env.WP_ADMIN_USER ?? 'admin',
    password: process.env.WP_ADMIN_PASSWORD ?? 'password',
  },
  // Created by scripts/install-wp.sh, used for the edit lock tests.
  editor: { login: 'editor', password: 'password', displayName: 'Test Editor' },
};

const TEST_KEY = { 'X-FV-Player-Test-Key': 'fv-player-test' };

/**
 * The player fixture shared with the PHPUnit integration tests. Its videos
 * are real files on foliovision.com, so the server-side video checker
 * (models/checker.php) runs against them on save.
 */
export const PLAYER_FIXTURE_PATH = path.resolve(
  __dirname,
  '../../test/integration/backend/player-data.json',
);

export function playerFixture(): PlayerJson {
  return JSON.parse(fs.readFileSync(PLAYER_FIXTURE_PATH, 'utf8'));
}

const fixtureVideos = playerFixture().videos;

export const VIDEOS = {
  fire: fixtureVideos[0],
  paypal: fixtureVideos[1],
  carly: fixtureVideos[2],
};

/**
 * Set FV_PLAYER_TESTS_OFFLINE=1 when the WordPress container has no
 * outbound network access. Assertions that depend on the video checker
 * fetching the remote file (duration, dimensions) are then skipped.
 */
export const OFFLINE = !!process.env.FV_PLAYER_TESTS_OFFLINE;

export type PlayerJson = {
  player_name?: string;
  videos: Array<{ title: string; src: string; splash: string; meta: Array<{ meta_key: string; meta_value: string }> } & Record<string, unknown>>;
} & Record<string, unknown>;

export type Meta = { meta_key: string; meta_value: string };

export type DbVideo = Record<string, string> & { id: string; src: string; title: string; meta: Meta[] };

export type DbPlayer = Record<string, string | unknown> & {
  id: string;
  player_name: string;
  status: string;
  videos: string;
  meta: Meta[];
  video_objects: DbVideo[];
};

/**
 * Logs in via wp-login.php and lands on wp-admin.
 *
 * Clears cookies first and retries once on the `reauth=1` bounce that
 * happens when a stale auth cookie survives `npm run wp:reset`.
 */
export async function loginAs(
  page: Page,
  user: { login: string; password: string } = USERS.admin,
): Promise<void> {
  await page.context().clearCookies();

  for (let attempt = 1; attempt <= 2; attempt++) {
    await page.goto('/wp-login.php', { waitUntil: 'domcontentloaded' });
    await page.locator('#user_login').waitFor({ state: 'visible', timeout: 15_000 });
    await page.fill('#user_login', user.login);
    await page.fill('#user_pass', user.password);
    await page.click('#wp-submit');

    try {
      await page.waitForURL(/\/wp-admin\//, { timeout: 15_000 });
      return;
    } catch (err) {
      if (attempt < 2 && /reauth=1/.test(page.url())) {
        await page.context().clearCookies();
        continue;
      }
      throw err;
    }
  }
}

async function api<T>(
  request: APIRequestContext,
  method: 'get' | 'post',
  route: string,
  data?: unknown,
): Promise<T> {
  const response = await request[method](`/wp-json/fv-player-test/v1${route}`, {
    headers: TEST_KEY,
    data,
  });
  if (!response.ok()) {
    throw new Error(`${method.toUpperCase()} ${route}: ${response.status()} ${await response.text()}`);
  }
  return (await response.json()) as T;
}

/** Empties the four FV Player tables. Call from `test.beforeAll()`. */
export async function resetPlayers(request: APIRequestContext): Promise<void> {
  await api(request, 'post', '/reset');
}

/**
 * Imports a player through FV_Player_Db::import_player_data(), the same code
 * the editor's Import button uses. The remote video file check is skipped
 * as it takes a few seconds per video, so the videos have no duration.
 */
export async function importPlayer(
  request: APIRequestContext,
  data: PlayerJson = playerFixture(),
): Promise<number> {
  return (await api<{ id: number }>(request, 'post', '/player', data)).id;
}

/** Raw DB rows of the player, its videos (in playlist order) and meta. */
export async function getPlayer(request: APIRequestContext, id: number): Promise<DbPlayer> {
  return api<DbPlayer>(request, 'get', `/player/${id}`);
}

/** All players, plus the total number of video rows. */
export async function getPlayers(
  request: APIRequestContext,
): Promise<{ players: Array<{ id: string; player_name: string; status: string; videos: string }>; video_count: number }> {
  return api(request, 'get', '/players');
}

/**
 * Writes an edit lock as if `userLogin` opened the player `age` seconds
 * ago. Returns that user's ID.
 */
export async function setEditLock(
  request: APIRequestContext,
  playerId: number,
  userLogin: string,
  age: number,
): Promise<number> {
  return (await api<{ user_id: number }>(request, 'post', '/lock', { player_id: playerId, user: userLogin, age })).user_id;
}

/** Whether WordPress itself can reach the URL (used by global-setup). */
export async function wordpressCanReach(
  request: APIRequestContext,
  url: string,
): Promise<{ ok: boolean; error?: string }> {
  return api(request, 'get', `/network?url=${encodeURIComponent(url)}`);
}

export function metaValue(meta: Meta[], key: string): string | undefined {
  return meta.find((m) => m.meta_key === key)?.meta_value;
}

/*
 * FV Player Editor helpers
 *
 * The editor lives in js/shortcode-editor.js. Things to know:
 *
 * - Edits are ignored until the editor has finished loading (its internal
 *   `ajax_saving` flag starts as true), so always wait for openEditor().
 * - It saves on `change`, or 500ms after `keyup`. Playwright's fill() fires
 *   only `input`, so setField() blurs the field to fire `change`.
 * - Saves are queued and sent by a 1.5s setInterval, so wait for the
 *   fv_player_db_save response, then for is_busy_saving() to be false.
 * - Every playlist item repeats the same field IDs, so fields are always
 *   looked up within one `[data-playlist-item][data-index=N]` block.
 */

export const PLAYERS_SCREEN = '/wp-admin/admin.php?page=fv_player';

export type EditorTab = 'video-files' | 'subtitles' | 'options' | 'actions' | 'playlist' | 'embeds';

export function isSaveRequest(url: string): boolean {
  return url.includes('admin-ajax.php') && url.includes('action=fv_player_db_save');
}

/**
 * Clicks `trigger` (Add New, a row's Edit link, ...) and waits until the
 * editor has fired `fv_player_editor_finished`, i.e. the player is loaded
 * and edits will be saved.
 */
export async function openEditor(page: Page, trigger: Locator): Promise<void> {
  await page.evaluate(() => {
    (window as any).__fvEditorFinished = false;
    (window as any).jQuery(document).one('fv_player_editor_finished', () => {
      (window as any).__fvEditorFinished = true;
    });
  });
  await trigger.click();
  await expect(page.locator('body')).toHaveClass(/is-fv-player-editor-modal-open/);
  await page.waitForFunction(() => (window as any).__fvEditorFinished === true);
}

export async function openNewPlayer(page: Page): Promise<void> {
  await openEditor(page, page.locator('.page-title-action.fv-player-edit[data-add_new]'));
}

export async function openPlayer(page: Page, playerId: number): Promise<void> {
  // The player name link; the row's "Edit" action is offscreen until hover.
  await openEditor(page, playerRow(page, playerId).locator('a.fv-player-edit').first());
}

/** Row of the Players screen list table. */
export function playerRow(page: Page, playerId: number): Locator {
  return page.locator('#the-list tr').filter({
    has: page.locator(`.fv_player_id_value[data-player_id="${playerId}"]`),
  });
}

export function editorTab(page: Page, tab: EditorTab): Locator {
  return page.locator(`#fv-player-shortcode-editor .fv-player-tab-${tab}`);
}

/** Field `fv_wp_flowplayer_field_{name}` of playlist item `index` in `tab`. */
export function field(page: Page, name: string, index = 0, tab: EditorTab = 'video-files'): Locator {
  return editorTab(page, tab)
    .locator(`[data-playlist-item][data-index="${index}"]`)
    .locator(`[name="fv_wp_flowplayer_field_${name}"]`);
}

/** Player-level field (playlist name, options, actions). */
export function playerField(page: Page, name: string): Locator {
  return page.locator(`#fv-player-shortcode-editor [name="fv_wp_flowplayer_field_${name}"]`).first();
}

/** Switches the right-hand editor tab (Video, Subtitles, Actions, ...). */
export async function showTab(page: Page, tab: EditorTab): Promise<void> {
  const link = page.locator(`#fv-player-shortcode-editor .fv-player-tabs-header a[data-tab="fv-player-tab-${tab}"]`);
  await link.click();
  await expect(link).toHaveClass(/nav-tab-active/);
}

/**
 * Runs `action` and waits for the autosave it causes: the
 * fv_player_db_save response, then for the editor's save queue to drain.
 * Returns the parsed response of the first save.
 */
export async function expectSave(page: Page, action: () => Promise<void>): Promise<any> {
  const responsePromise = page.waitForResponse((r) => isSaveRequest(r.url()), { timeout: 60_000 });
  await action();
  const response = await responsePromise;
  expect(response.ok()).toBe(true);
  const json = await response.json();
  expect(json.error, `fv_player_db_save error: ${json.error}`).toBeFalsy();
  await waitForSaveQueue(page);
  return json;
}

export async function waitForSaveQueue(page: Page): Promise<void> {
  await page.waitForFunction(() => !(window as any).fv_player_editor.is_busy_saving(), null, {
    timeout: 60_000,
  });
}

/** Fills a field and blurs it so the editor gets the `change` event. */
export async function setField(locator: Locator, value: string): Promise<void> {
  await locator.fill(value);
  await locator.blur();
}

/** Fills a field and waits for the autosave. */
export async function setFieldAndSave(page: Page, locator: Locator, value: string): Promise<any> {
  return expectSave(page, () => setField(locator, value));
}

export function currentPlayerId(page: Page): Promise<number> {
  return page.evaluate(() => Number((window as any).fv_player_editor.get_current_player_db_id()));
}

/**
 * Clicks the editor's Save button. On the Players screen that publishes the
 * player, closes the editor and refreshes the player's list table row.
 */
export async function clickSave(page: Page): Promise<void> {
  // The button does nothing while an autosave is in progress.
  await waitForSaveQueue(page);
  const saved = page.waitForResponse(
    (r) => isSaveRequest(r.url()) && r.request().postData()?.includes('"status":"published"') === true,
  );
  await page.locator('#fv-player-shortcode-editor .fv_player_field_insert-button').click();
  await saved;
  await expectEditorClosed(page);
}

export async function closeEditor(page: Page): Promise<void> {
  await page.locator('#fv-player-editor-modal-close').click();
  await expectEditorClosed(page);
}

export async function expectEditorClosed(page: Page): Promise<void> {
  await expect(page.locator('body')).not.toHaveClass(/is-fv-player-editor-modal-open/);
}

/**
 * Accepts the next native dialog (confirm/alert) and returns its message.
 * The editor uses confirm() when removing videos or subtitles.
 */
export function acceptNextDialog(page: Page): Promise<string> {
  return new Promise((resolve) => {
    page.once('dialog', async (dialog) => {
      const message = dialog.message();
      await dialog.accept();
      resolve(message);
    });
  });
}

/** Row of the editor's Playlist tab. */
export function playlistItem(page: Page, index: number): Locator {
  return page.locator(`#fv-player-editor-playlist .fv-player-editor-playlist-item[data-index="${index}"]`);
}

/**
 * Clicks a playlist row control (configure, remove, move up/down). They are
 * only visible while the row is hovered.
 */
export async function clickPlaylistItemControl(page: Page, index: number, selector: string): Promise<void> {
  const item = playlistItem(page, index);
  await item.hover();
  await item.locator(selector).click();
}

/** Opens playlist item `index` for editing (its Video tab). */
export async function configureVideo(page: Page, index: number): Promise<void> {
  await clickPlaylistItemControl(page, index, '.configure-video');
  await expect(editorTab(page, 'video-files').locator(`[data-playlist-item][data-index="${index}"]`)).toBeVisible();
}

/** Back from a video's tabs to the Playlist tab. */
export async function backToPlaylist(page: Page): Promise<void> {
  await page.locator('#fv-player-shortcode-editor .playlist_edit').click();
  await expect(editorTab(page, 'playlist')).toBeVisible();
}

/** Video titles in the Playlist tab, in order. */
export function playlistTitles(page: Page): Promise<string[]> {
  return page.locator('#fv-player-editor-playlist .fvp_item_video-filename').allInnerTexts();
}
