import { test, expect, type Locator, type Page } from '@playwright/test';
import {
  acceptNextDialog,
  configureVideo,
  editorTab,
  expectSave,
  getPlayer,
  importPlayer,
  loginAs,
  metaValue,
  openPlayer,
  PLAYERS_SCREEN,
  resetPlayers,
  setField,
  showTab,
  VIDEOS,
} from './helpers';

const SUBTITLES_EN = metaValue(VIDEOS.paypal.meta, 'subtitles_en')!;
const SUBTITLES_DE = SUBTITLES_EN.replace('.vtt', '-de.vtt');

/** One row (language + URL) per subtitle language of playlist item `index`. */
function subtitleRows(page: Page, index: number): Locator {
  return editorTab(page, 'subtitles')
    .locator(`[data-playlist-item][data-index="${index}"] .fv-player-editor-field-wrap-subtitles`)
    .locator('.components-base-control__field:has(.field-with-language)');
}

/**
 * Sets the subtitle language and URL of a row. The language is a <select>
 * once the item is shown, but a plain text input in the markup of items
 * that were not shown yet, so handle both.
 */
async function setSubtitle(row: Locator, lang: string, url: string) {
  const langField = row.locator('[name=fv_wp_flowplayer_field_subtitles_lang]');
  if ((await langField.evaluate((e) => e.tagName)) === 'SELECT') {
    await langField.selectOption(lang);
  } else {
    await setField(langField, lang);
  }
  await setField(row.locator('[name=fv_wp_flowplayer_field_subtitles]'), url);
}

test.describe.serial('FV Player Editor — subtitles', () => {
  let playerId: number;

  async function openFirstVideoSubtitles(page: Page) {
    await loginAs(page);
    await page.goto(PLAYERS_SCREEN);
    await openPlayer(page, playerId);
    await configureVideo(page, 0);
    await showTab(page, 'subtitles');
  }

  async function subtitleMeta(request) {
    const meta = (await getPlayer(request, playerId)).video_objects[0].meta;
    return meta.filter((m) => m.meta_key.startsWith('subtitles_'));
  }

  test.beforeAll(async ({ request }) => {
    await resetPlayers(request);
    playerId = await importPlayer(request);
  });

  test('adding English subtitles to a video saves them as video meta', async ({ page, request }) => {
    await openFirstVideoSubtitles(page);

    const rows = subtitleRows(page, 0);
    await expect(rows).toHaveCount(1);
    await expectSave(page, () => setSubtitle(rows.first(), 'en', SUBTITLES_EN));

    expect(await subtitleMeta(request)).toEqual([{ meta_key: 'subtitles_en', meta_value: SUBTITLES_EN }]);
  });

  test('"Add Another Language" adds a second subtitle language', async ({ page, request }) => {
    await openFirstVideoSubtitles(page);

    const rows = subtitleRows(page, 0);
    await expect(rows).toHaveCount(1);
    // Saved URLs are shown shortened, the real input is hidden.
    await expect(rows.first().locator('.fv_player_editor_url_shortened')).toHaveAttribute('title', SUBTITLES_EN);

    await editorTab(page, 'subtitles').locator('[data-playlist-item][data-index="0"] .add_language').click();
    await expect(rows).toHaveCount(2);
    await expectSave(page, () => setSubtitle(rows.nth(1), 'de', SUBTITLES_DE));

    expect(await subtitleMeta(request)).toEqual([
      { meta_key: 'subtitles_en', meta_value: SUBTITLES_EN },
      { meta_key: 'subtitles_de', meta_value: SUBTITLES_DE },
    ]);
  });

  test('removing a subtitle language asks for confirmation and deletes it', async ({ page, request }) => {
    await openFirstVideoSubtitles(page);

    const rows = subtitleRows(page, 0);
    await expect(rows).toHaveCount(2);

    const dialog = acceptNextDialog(page);
    // The remove icon only shows while the row is hovered.
    await expectSave(page, async () => {
      await rows.nth(1).hover();
      await rows.nth(1).locator('.remove_language').click();
    });
    expect(await dialog).toBe('Would you like to remove this Subtitle?');

    await expect(rows).toHaveCount(1);
    expect(await subtitleMeta(request)).toEqual([{ meta_key: 'subtitles_en', meta_value: SUBTITLES_EN }]);
  });
});
