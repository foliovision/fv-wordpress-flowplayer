# Browser tests

Playwright end-to-end tests for the **FV Player Editor**: the player editor
modal on the wp-admin → FV Player screen. They run against a disposable
WordPress 6.9 site in Docker.

The PHPUnit tests are in `../test/` and are separate from these.

## Prerequisites

- Docker, with either Docker Compose v2 (`docker compose`) or the legacy v1
  `docker-compose` binary. The `wp:*` scripts detect whichever is installed.
- Node.js 18+
- Outbound network access **from the WordPress container** to
  foliovision.com (see [Sample videos](#sample-videos)).

On macOS you have to run `colima start` first.

## One-time setup

```bash
cd tests
npm install
npx playwright install --with-deps chromium
```

## Running the tests

```bash
# Bring up WordPress + MariaDB, install WP, activate FV Player:
npm run wp:up

# Run the Playwright suite:
npm test

# Tear everything down (keeps data volumes):
npm run wp:down

# Remove volumes and start from scratch:
npm run wp:reset
```

The stack listens on <http://localhost:8891> by default with admin
credentials `admin` / `password`. The fv-polls tests use port 8890. Use
`WP_PORT` to pick another port:

```bash
WP_PORT=12345 npm run wp:up
WP_PORT=12345 npm test
```

The plugin source is mounted live from the repo root into
`wp-content/plugins/fv-player`, so PHP/JS changes are picked up on the next
page load. JS is loaded unminified from `js/`, so there is no build step.

## Seeing what the tests did

Every run writes an HTML report and a trace to `playwright-report/`,
with screenshots at every action.

```bash
npm run test:report    # open the HTML report from the last run
npm run test:ui        # Playwright's interactive UI
npm run test:headed    # visible browser window
npm run test:debug     # step through in the Playwright Inspector
npm run test:trace -- path/to/trace.zip
```

## Sample videos

The tests use the real videos from
`../test/integration/backend/player-data.json`, the fixture the PHPUnit
integration tests also use. When a video is saved, the server checks the
file (`models/checker.php`), so the WordPress **container** must be able to
reach foliovision.com. `global-setup.ts` checks this before any test runs.

If the container has no outbound access, each save of a new video waits
for a 10s timeout and the video duration stays empty. To run anyway and
skip only the assertions that need the video check:

```bash
FV_PLAYER_TESTS_OFFLINE=1 npm test
```

On Fedora with firewalld, Docker bridge networks can lose outbound access
even though the host has it. This usually fixes it:

```bash
sudo firewall-cmd --permanent --zone=docker --add-masquerade
sudo firewall-cmd --reload
sudo systemctl restart docker
# Networks created before the change keep failing, so recreate the stack's:
npm run wp:down && npm run wp:up
```

## SELinux

The compose file sets `security_opt: label=disable` on the WordPress
containers rather than mounting with `:z`. Relabelling would change the
SELinux context of the plugin source folder, and a local Apache that
serves the same folder (`/var/www/html/...`) would lose access to it.

## Test structure

- **`smoke.spec.ts`**: the Players screen renders without PHP notices,
  "Add New" opens the editor.
- **`create-player.spec.ts`**: a video link autosaves a draft, the
  server checks the video, Save publishes the player.
- **`edit-player.spec.ts`**: an imported player loads, renaming
  autosaves the same player, the change persists.
- **`playlist.spec.ts`**: move up/down, inline title edit, remove with
  confirm, add a video.
- **`subtitles.spec.ts`**: add a subtitle language, add another one,
  remove one.
- **`end-of-video-actions.spec.ts`**: End of Video Action toggle, Redirect
  URL, switching actions, disabling.
- **`edit-lock.spec.ts`**: lock on open, another user's lock blocks
  opening, a stale lock is taken over, released on heartbeat after close.
- **`list-actions.spec.ts`**: Import (valid and invalid JSON), Export,
  Clone, Delete on the Players screen.
- **`close-editor.spec.ts`**: closing while a save is running, a closed
  new player stays a draft.

Tests that document known plugin bugs use `test.fail()` with the reason.
They pass while the bug exists and fail once it is fixed, which is the cue
to remove the `test.fail()` line.

### Test helpers

`wp-mu-plugins/fv-player-test-helpers.php` is mounted as an MU-plugin and
adds REST routes under `/wp-json/fv-player-test/v1/`. They require the
header `X-FV-Player-Test-Key: fv-player-test`.

| Route | What it does |
|---|---|
| `POST /reset` | Creates and empties the four `fv_player_*` tables |
| `POST /player` | Imports a player from the JSON body (`import_player_data()`). Skips the remote video file check (~10s per video) unless `?check_videos=1` |
| `GET /player/:id` | Raw DB rows of the player, its videos and all meta |
| `GET /players` | All players and the total number of video rows |
| `POST /lock` | Writes an `edit_lock_*` meta for a user, `age` seconds old |
| `GET /network?url=` | Whether WordPress itself can reach a URL |

The MU-plugin also blocks HTTP requests to wordpress.org (update checks),
which would otherwise slow every admin page when there is no network.

`e2e/helpers.ts` wraps these routes and adds the editor helpers.

## Writing editor tests

Watch out for these when you write new tests:

- **Wait for the editor to load.** Edits made before
  `fv_player_editor_finished` are silently dropped. `openEditor()` and
  `openNewPlayer()` / `openPlayer()` wait for it.
- **`fill()` does not save.** The editor saves on `change`, or 500ms
  after `keyup`. Use `setField()`, which fills and then blurs. Some inputs
  only react to keyup (the inline playlist title), so type into those
  with `pressSequentially()`.
- **Wait for the save.** Saves are queued and sent by a 1.5s interval.
  Wrap the action in `expectSave()`, which waits for the
  `fv_player_db_save` response and for `is_busy_saving()` to turn false.
  Don't wait for the "Saved!" notice, which fades after 2.5s.
- **Field IDs repeat.** Every playlist item has the same input IDs, so
  look fields up with `field(page, name, index, tab)`.
- **Hover-only controls.** Playlist row controls, the subtitle remove icon
  and the list table row actions only show on hover. Hover first, as
  `clickPlaylistItemControl()` does.
- **Hidden by default.** Title, Splash Text, Synopsis and the Options tab
  are hidden unless enabled under Settings → Post Interface Options.
- **Native dialogs.** Removing a video or a subtitle uses `confirm()`.
  Register `acceptNextDialog()` before the click.
- **Check the DB.** Assert on `getPlayer()` and not only on the DOM; the
  editor UI can look right while the save went wrong.
