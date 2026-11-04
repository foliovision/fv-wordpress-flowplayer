<?php
/**
 * Plugin Name: FV Player Test Helpers (MU)
 * Description: MU-plugin used by the Playwright suite to reset and inspect
 *              FV Player DB state. Only active inside the test Docker stack
 *              (lives under tests/wp-mu-plugins/, not shipped with the
 *              plugin).
 *
 * @package FV_Player_Tests
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Short-circuit WordPress.org update and API checks. They are irrelevant to
 * the tests, and without outbound network access each one blocks an admin
 * page load until it times out. Requests to other hosts (the
 * foliovision.com sample videos) go through untouched.
 */
add_filter( 'pre_http_request', function ( $response, $args, $url ) {
	$host = wp_parse_url( $url, PHP_URL_HOST );
	if ( in_array( $host, array( 'api.wordpress.org', 'downloads.wordpress.org', 'wordpress.org' ), true ) ) {
		return new WP_Error( 'fv_player_test_blocked', 'Blocked by FV Player test helpers.' );
	}
	return $response;
}, 10, 3 );

/**
 * All routes live under /wp-json/fv-player-test/v1/ and are guarded by the
 * `X-FV-Player-Test-Key: fv-player-test` header — a plain string, not a real
 * secret; this MU-plugin is only mounted inside the disposable test container.
 */
add_action( 'rest_api_init', function () {
	$routes = array(
		'/reset'                => array( 'POST', 'fv_player_test_reset' ),
		'/player'               => array( 'POST', 'fv_player_test_import_player' ),
		'/player/(?P<id>\d+)'   => array( 'GET', 'fv_player_test_get_player' ),
		'/players'              => array( 'GET', 'fv_player_test_get_players' ),
		'/lock'                 => array( 'POST', 'fv_player_test_set_lock' ),
		'/network'              => array( 'GET', 'fv_player_test_network' ),
	);
	foreach ( $routes as $route => $args ) {
		register_rest_route(
			'fv-player-test/v1',
			$route,
			array(
				'methods'             => $args[0],
				'callback'            => $args[1],
				'permission_callback' => 'fv_player_test_check_key',
			)
		);
	}
} );

function fv_player_test_check_key( $request ) {
	return $request->get_header( 'X-FV-Player-Test-Key' ) === 'fv-player-test';
}

function fv_player_test_tables() {
	global $wpdb;
	return array(
		'players'    => $wpdb->prefix . 'fv_player_players',
		'videos'     => $wpdb->prefix . 'fv_player_videos',
		'playermeta' => $wpdb->prefix . 'fv_player_playermeta',
		'videometa'  => $wpdb->prefix . 'fv_player_videometa',
	);
}

/**
 * POST /reset — empties the four FV Player tables.
 *
 * The plugin creates its tables lazily (the meta tables only appear once
 * something is stored there), so create them all first. That also lets
 * GET /player read the meta tables on a fresh install.
 */
function fv_player_test_reset() {
	global $wpdb, $FV_Player_Db;

	$FV_Player_Db->init_tables();

	foreach ( fv_player_test_tables() as $t ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "TRUNCATE TABLE $t" );
	}

	return array( 'ok' => true );
}

/**
 * POST /player — imports a player from the JSON body, using the same code
 * path as the editor's Import button (FV_Player_Db::import_player_data()).
 * The body has the format of test/integration/backend/player-data.json.
 */
function fv_player_test_import_player( $request ) {
	global $FV_Player_Db;

	$data = $request->get_json_params();
	if ( empty( $data ) ) {
		return new WP_Error( 'fv_player_test_no_data', 'No player JSON in request body.', array( 'status' => 400 ) );
	}

	// The import stores the current user as the author. REST requests with
	// the test key are anonymous, so act as the admin.
	wp_set_current_user( 1 );

	// Skip the remote video file check (duration, dimensions) which takes
	// a few seconds per video. FV_Player_Db_Video::save() only does the
	// check if this filter does not return an array. Pass
	// ?check_videos=1 to import with the real check.
	$skip_check = function () {
		return array();
	};
	if ( ! $request['check_videos'] ) {
		add_filter( 'fv_player_meta_data', $skip_check, PHP_INT_MAX );
	}

	$result = $FV_Player_Db->import_player_data( null, false, $data );

	remove_filter( 'fv_player_meta_data', $skip_check, PHP_INT_MAX );
	if ( ! is_numeric( $result ) ) {
		return new WP_Error( 'fv_player_test_import_failed', (string) $result, array( 'status' => 500 ) );
	}

	return array( 'id' => (int) $result );
}

/**
 * GET /player/:id — raw DB rows for the player, its videos (in playlist
 * order) and all meta, read directly with SQL so no plugin cache can hide
 * what was actually saved.
 */
function fv_player_test_get_player( $request ) {
	global $wpdb;
	$t  = fv_player_test_tables();
	$id = absint( $request['id'] );

	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$player = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t['players']} WHERE id = %d", $id ), ARRAY_A );
	if ( ! $player ) {
		return new WP_Error( 'fv_player_test_not_found', 'Player not found.', array( 'status' => 404 ) );
	}

	$player['meta'] = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$t['playermeta']} WHERE id_player = %d ORDER BY id", $id ), ARRAY_A );

	$player['video_objects'] = array();
	foreach ( array_filter( array_map( 'absint', explode( ',', $player['videos'] ) ) ) as $video_id ) {
		$video = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t['videos']} WHERE id = %d", $video_id ), ARRAY_A );
		if ( $video ) {
			$video['meta'] = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$t['videometa']} WHERE id_video = %d ORDER BY id", $video_id ), ARRAY_A );
		}
		$player['video_objects'][] = $video;
	}
	// phpcs:enable

	return $player;
}

/**
 * GET /players — id, name and status of every player, plus the total
 * number of video rows, to catch duplicate players or orphaned videos.
 */
function fv_player_test_get_players() {
	global $wpdb;
	$t = fv_player_test_tables();

	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	return array(
		'players'     => $wpdb->get_results( "SELECT id, player_name, status, videos FROM {$t['players']} ORDER BY id", ARRAY_A ),
		'video_count' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t['videos']}" ),
	);
	// phpcs:enable
}

/**
 * GET /network?url= — whether WordPress itself (not the test runner) can
 * reach the URL. The video checker runs inside the container, and Docker
 * bridge networks can lack outbound access even when the host has it.
 */
function fv_player_test_network( $request ) {
	$response = wp_remote_head( esc_url_raw( $request['url'] ), array( 'timeout' => 10 ) );
	if ( is_wp_error( $response ) ) {
		return array( 'ok' => false, 'error' => $response->get_error_message() );
	}
	return array( 'ok' => true, 'status' => wp_remote_retrieve_response_code( $response ) );
}

/**
 * POST /lock {player_id, user, age} — writes an edit_lock_{user ID} player
 * meta row as if the user with login `user` opened the player `age` seconds
 * ago, so edit lock tests don't have to wait for the WordPress heartbeat.
 */
function fv_player_test_set_lock( $request ) {
	global $wpdb;
	$t = fv_player_test_tables();

	$user = get_user_by( 'login', (string) $request['user'] );
	if ( ! $user ) {
		return new WP_Error( 'fv_player_test_no_user', 'Unknown user.', array( 'status' => 400 ) );
	}

	$player_id = absint( $request['player_id'] );
	$user_id   = $user->ID;
	$age       = absint( $request['age'] );

	$wpdb->delete( $t['playermeta'], array( 'id_player' => $player_id, 'meta_key' => 'edit_lock_' . $user_id ) );
	$wpdb->insert(
		$t['playermeta'],
		array(
			'id_player'  => $player_id,
			'meta_key'   => 'edit_lock_' . $user_id,
			'meta_value' => time() - $age,
		)
	);

	return array( 'ok' => true, 'user_id' => $user_id );
}
