#!/usr/bin/env bash
# Installs WordPress inside the running docker-compose stack using wp-cli.
# Idempotent: safe to re-run.
set -euo pipefail

SCRIPTS_DIR="$(cd "$(dirname "$0")" && pwd)"
WP_PORT="${WP_PORT:-8891}"
WP_URL="http://localhost:${WP_PORT}"

compose() {
  "$SCRIPTS_DIR/compose.sh" "$@"
}

echo "==> Waiting for WordPress container to serve HTTP at ${WP_URL}..."
for i in $(seq 1 60); do
  if curl -sf -o /dev/null "${WP_URL}/wp-login.php"; then
    break
  fi
  sleep 2
done

echo "==> Running wp core install (if needed)..."
if compose exec -T wp-cli wp core is-installed >/dev/null 2>&1; then
  echo "    WordPress already installed."
else
  compose exec -T wp-cli wp core install \
    --url="${WP_URL}" \
    --title="FV Player Test Site" \
    --admin_user="admin" \
    --admin_password="password" \
    --admin_email="admin@example.com" \
    --skip-email
fi

echo "==> Activating fv-player plugin..."
compose exec -T wp-cli wp plugin activate fv-player

# FV Player shows admin pointers (upgrade notes, video checker service
# opt-in) that overlap the Players screen buttons and intercept clicks.
# The video checker service is the frontend encoding checker; the video
# meta check run on save (models/db-video.php) is not affected.
echo "==> Dismissing FV Player admin pointers..."
compose exec -T wp-cli wp eval '
  global $fv_fp, $fv_wp_flowplayer_ver;
  $fv_fp->_set_option( "nag_fv_player_upgrade", $fv_wp_flowplayer_ver );
  $fv_fp->_set_option( "nag_fv_player_8", 1 );
  $fv_fp->_set_option( "disable_videochecker", "true" );
'

# Second user for the edit lock tests: the lock message names the user who
# holds the lock.
echo "==> Creating editor user (if needed)..."
compose exec -T wp-cli wp user get editor >/dev/null 2>&1 \
  || compose exec -T wp-cli wp user create editor editor@example.com \
    --role=editor --user_pass=password --display_name="Test Editor"

# Enable pretty permalinks so /wp-json/... routes resolve; with plain
# permalinks they go to the homepage and the test helper endpoints break.
echo "==> Enabling pretty permalinks..."
compose exec -T wp-cli wp rewrite structure '/%postname%/' --hard
compose exec -T wp-cli wp rewrite flush --hard

echo "==> Done. WordPress is at ${WP_URL} (admin / password)."
