<?php
/**
 * Plugin Name: WPTV Route Test Helper (local development only)
 * Description: Stubs WordPress.com-only VideoPress functions and exposes main query data in a response header, so route regression tests can run locally with any theme. NEVER deploy this file.
 *
 * Install: copy this file to wp-content/mu-plugins/. See tests/routes/README.md.
 *
 * @package WordPressTV
 */

// Fail closed outside Studio, before changing logging or defining test doubles.
if ( ( defined( 'IS_WPCOM' ) && IS_WPCOM ) || 'local' !== wp_get_environment_type() ) {
	return;
}

// Require an actually loaded Studio runtime, not a synced loader or localhost URL.
// Inspect loaded files so this still works when Studio regenerates its loader.
$wptv_studio_runtime_loaded = false;
$wptv_studio_runtime_prefix = trailingslashit( wp_normalize_path( sys_get_temp_dir() ) ) . 'studio-mu-plugins-';
foreach ( get_included_files() as $wptv_loaded_file ) {
	$wptv_loaded_file = wp_normalize_path( $wptv_loaded_file );
	if (
		str_starts_with( $wptv_loaded_file, $wptv_studio_runtime_prefix ) &&
		'0-studio-admin-api.php' === basename( $wptv_loaded_file )
	) {
		$wptv_studio_runtime_loaded = true;
		break;
	}
}
unset( $wptv_studio_runtime_prefix, $wptv_loaded_file );
if ( ! $wptv_studio_runtime_loaded ) {
	return;
}
unset( $wptv_studio_runtime_loaded );

/*
 * Behave like production: never print PHP notices into responses.
 *
 * A notice printed before the headers changes the status code and Content-Type,
 * which production (display_errors off) never does. Notices are logged instead,
 * one line per notice, to wp-content/wptv-route-test.log. run.sh clears the log
 * before a run and copies it into the results folder afterwards, so every new
 * notice can still be reviewed (and treated as a bug in wptv-27).
 */
// phpcs:disable WordPress.PHP.IniSet -- Local development helper only.
ini_set( 'display_errors', '0' );
ini_set( 'log_errors', '1' );
ini_set( 'error_log', WP_CONTENT_DIR . '/wptv-route-test.log' );
// phpcs:enable

/*
 * Stubs for WordPress.com-only VideoPress helpers.
 *
 * Video details are fake but deterministic (derived from the GUID only), so the
 * full code paths of endpoints, JSON-LD and Open Graph run, and every run
 * produces identical output. File URLs, thumbnails and subtitles return "no data".
 * Each stub is only defined when the real function or class is missing.
 */

if ( ! function_exists( 'find_all_videopress_shortcodes' ) ) {
	/**
	 * Finds VideoPress shortcodes in a string.
	 *
	 * @param string $content Post content.
	 * @return array Map of VideoPress GUID => shortcode attributes.
	 */
	function find_all_videopress_shortcodes( $content ) {
		$found = array();
		if ( preg_match_all( '/\[wpvideo +([a-zA-Z0-9]+)([^\]]*)\]/i', (string) $content, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$found[ $match[1] ] = shortcode_parse_atts( trim( $match[2] ) );
			}
		}
		return $found;
	}
}

if ( ! function_exists( 'video_get_info_by_guid' ) ) {
	/**
	 * Stub: fake, deterministic video details.
	 *
	 * @param string $guid VideoPress GUID.
	 * @return object|false
	 */
	function video_get_info_by_guid( $guid ) {
		if ( empty( $guid ) ) {
			return false;
		}
		return (object) array(
			'guid'        => (string) $guid,
			'post_id'     => 0,
			'title'       => 'Stub video ' . $guid,
			'description' => 'Stub description for ' . $guid,
			'width'       => 1280,
			'height'      => 720,
			'duration'    => 600,
			'rating'      => 'G',
		);
	}
}

if ( ! function_exists( 'videopress_2015_player_get_html' ) ) {
	/**
	 * Stub: deterministic player markup.
	 *
	 * @param array $args Player arguments (guid, width, height).
	 * @return string
	 */
	function videopress_2015_player_get_html( $args ) {
		return sprintf(
			'<iframe class="wptv-stub-player" data-guid="%s" width="%d" height="%d"></iframe>',
			esc_attr( $args['guid'] ?? '' ),
			(int) ( $args['width'] ?? 0 ),
			(int) ( $args['height'] ?? 0 )
		);
	}
}

if ( ! class_exists( 'VideoPress_Subtitles' ) ) {
	/**
	 * Stub: no subtitle languages are available locally.
	 */
	class VideoPress_Subtitles {
		/**
		 * Returns the supported subtitle languages.
		 *
		 * @return array
		 */
		public static function get_languages() {
			return array();
		}
	}
}

if ( ! function_exists( 'video_get_highest_resolution_image_url' ) ) {
	/**
	 * Stub: no thumbnails are available locally.
	 *
	 * @param string $guid VideoPress GUID.
	 * @return false
	 */
	function video_get_highest_resolution_image_url( $guid ) {
		return false;
	}
}

if ( ! function_exists( 'video_url_by_format' ) ) {
	/**
	 * Stub: no video files are available locally.
	 *
	 * @param object $info   Video details.
	 * @param string $format Format key.
	 * @return false
	 */
	function video_url_by_format( $info, $format ) {
		return false;
	}
}

if ( ! function_exists( 'video_highest_resolution_ogg' ) ) {
	/**
	 * Stub: no OGG files are available locally.
	 *
	 * @param object $info Video details.
	 * @return false
	 */
	function video_highest_resolution_ogg( $info ) {
		return false;
	}
}

if ( ! function_exists( 'video_get_single_response' ) ) {
	/**
	 * Stub: API data with the real structure, but no files or subtitles.
	 *
	 * @param object $info Video details.
	 * @return array
	 */
	function video_get_single_response( $info ) {
		return array(
			'guid'      => $info->guid ?? '',
			'original'  => '',
			'files'     => array(),
			'subtitles' => array(),
		);
	}
}

/**
 * Exposes main query data in the `X-WPTV-Route` response header.
 *
 * This lets the route test compare themes without parsing theme-specific markup.
 * Runs on `wp`, after the main query and 404 handling.
 *
 * @param WP $wp Current WordPress environment instance.
 */
function wptv_route_test_header( $wp ) {
	global $wp_query;

	if ( headers_sent() || ! $wp_query instanceof WP_Query ) {
		return;
	}

	$object = get_queried_object();
	if ( $object instanceof WP_Term ) {
		$queried = 'term:' . $object->taxonomy . ':' . $object->slug;
	} elseif ( $object instanceof WP_Post ) {
		$queried = 'post:' . $object->post_type . ':' . $object->post_name;
	} elseif ( $object instanceof WP_User ) {
		$queried = 'user:' . $object->user_nicename;
	} else {
		$queried = '-';
	}

	$fields = array(
		'404'      => (int) $wp_query->is_404(),
		'found'    => (int) $wp_query->found_posts,
		'count'    => (int) $wp_query->post_count,
		'pages'    => (int) $wp_query->max_num_pages,
		'per_page' => (int) $wp_query->get( 'posts_per_page' ),
		'paged'    => (int) max( 1, $wp_query->get( 'paged' ) ),
		'queried'  => rawurlencode( $queried ),
	);

	$pairs = array();
	foreach ( $fields as $key => $value ) {
		$pairs[] = $key . '=' . $value;
	}

	header( 'X-WPTV-Route: ' . implode( '; ', $pairs ) );
}
add_action( 'wp', 'wptv_route_test_header', 999 );
