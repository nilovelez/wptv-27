<?php
/**
 * Read-only GUID and standalone poster checks; run with WP-CLI eval-file.
 * Uses in-memory posts and intercepted plugin transients, with HTTP blocked.
 *
 * @package WordPressTV
 */

if ( 'wptv-27' !== get_stylesheet() || function_exists( 'video_get_highest_resolution_image_url' ) ) {
	WP_CLI::error( 'Run on local wptv-27 without route-test request headers.' );
}
if ( ! is_callable( array( '\\Automattic\\Jetpack\\VideoPress\\Inline_Player', 'get_poster_url' ) ) ) {
	WP_CLI::error( 'The standalone VideoPress poster resolver is required for these checks.' );
}

$checks = 0;
$assert = static function ( $condition, $message ) use ( &$checks ) {
	if ( ! $condition ) {
		WP_CLI::error( $message );
	}
	++$checks;
	WP_CLI::log( 'PASS ' . $message );
};
$make_post = static function ( $content ) {
	return new WP_Post( (object) array( 'ID' => PHP_INT_MAX, 'filter' => 'raw', 'post_type' => 'post', 'post_content' => $content ) );
};
$make_block = static function ( $guid, $attributes = array(), $name = 'videopress/video' ) {
	return serialize_block( array(
		'blockName' => $name, 'attrs' => array_merge( array( 'guid' => $guid ), $attributes ),
		'innerBlocks' => array(), 'innerContent' => array(), 'innerHTML' => '',
	) );
};
$get_guid = 'WordPressTV\\Video\\get_guid';
$get_image = 'WordPressTV\\Video\\get_image_url';
$a = 'Ab12Cd34';
$b = 'Ef56Gh78';
$shortcode = $make_post( '[wpvideo ' . $a . ']' );
$block = $make_post( $make_block( $a ) );
$assert( $a === $get_guid( $shortcode ), 'Legacy shortcode GUID is unchanged.' );
$assert( $b === $get_guid( $make_post( '[wpvideo ' . $a . '] [wpvideo ' . $b . ' hd=true]' ) ), 'Last legacy shortcode still wins.' );
$assert( $a === $get_guid( $block ), 'Current VideoPress block GUID is recognized.' );
$assert( $a === $get_guid( $make_post( $make_block( $a, array(), 'jetpack/videopress' ) ) ), 'Earlier Jetpack VideoPress block name is recognized.' );
$nested = serialize_block( array(
	'blockName' => 'core/group', 'attrs' => array(), 'innerHTML' => '',
	'innerBlocks' => parse_blocks( $make_block( $b ) ), 'innerContent' => array( null ),
) );
$assert( $b === $get_guid( $make_post( $make_block( $a ) . $nested ) ), 'Last nested video follows document order.' );
$assert( $a === $get_guid( $make_post( '[wpvideo ' . $a . ']' . $make_block( $b ) ) ), 'Shortcode priority is preserved in mixed content.' );
foreach ( array( '', 'bad', 'bad/guid', array( $a ) ) as $invalid ) {
	$assert( false === $get_guid( $make_post( $make_block( $invalid ) ) ), 'Malformed block GUID is ignored: ' . wp_json_encode( $invalid ) );
}
$assert( false === $get_guid( $make_post( $make_block( $a, array(), 'core/video' ) ) ), 'Unrelated blocks do not supply a VideoPress GUID.' );
$assert( false === $get_guid( $make_post( $make_block( '', array( 'cacheHtml' => '<iframe src="https://videopress.com/embed/' . $a . '"></iframe>' ) ) ) ), 'Cached iframe HTML is not scraped as a GUID source.' );

$requests = 0;
$block_http = static function () use ( &$requests ) {
	++$requests;
	return new WP_Error( 'wptv_test_network_blocked', 'No HTTP in deterministic checks.' );
};
$details = static function () {
	return (object) array( 'poster' => 'https://example.org/public-poster.jpg', 'is_private' => false );
};
add_filter( 'pre_http_request', $block_http );
add_filter( 'pre_transient_jetpack_videopress_' . $a, $details );
$assert( 'https://example.org/public-poster.jpg' === $get_image( $shortcode ), 'Shortcodes use the standalone plugin poster API.' );
$assert( 'https://example.org/public-poster.jpg' === $get_image( $block ), 'Blocks use the same cached standalone API.' );
$custom = $make_post( $make_block( $a, array( 'poster' => 'https://example.org/custom-poster.jpg', 'privacySetting' => 0 ) ) );
$assert( 'https://example.org/custom-poster.jpg' === $get_image( $custom ), 'Explicit block poster is handled by the plugin.' );
$private = $make_post( $make_block( $a, array( 'isPrivate' => true, 'privacySetting' => 1 ) ) );
$assert( false === $get_image( $private ), 'Private block does not expose an automatically fetched frame.' );
$mixed = $make_post( '[wpvideo ' . $a . ']' . $make_block( $b, array( 'poster' => 'https://example.org/wrong-video.jpg' ) ) );
$assert( 'https://example.org/public-poster.jpg' === $get_image( $mixed ), 'Another block cannot supply the selected shortcode video poster.' );
remove_filter( 'pre_transient_jetpack_videopress_' . $a, $details );
foreach ( array( null, new WP_Error( 'unavailable' ), (object) array(), (object) array( 'poster' => array( 'bad' ) ), (object) array( 'poster' => 'https://example.org/private.jpg', 'is_private' => true ) ) as $unavailable ) {
	$empty = static function () use ( $unavailable ) { return $unavailable; };
	add_filter( 'pre_transient_jetpack_videopress_' . $a, $empty );
	$assert( false === $get_image( $block ), 'Missing, malformed or private remote data returns no image.' );
	remove_filter( 'pre_transient_jetpack_videopress_' . $a, $empty );
}
add_filter( 'wptv_pre_video_image_url', '__return_false' );
$assert( false === $get_image( $block ), 'Route-test override short-circuits real image lookup.' );
remove_filter( 'wptv_pre_video_image_url', '__return_false' );
$assert( false === $get_image( $make_post( 'No video.' ) ), 'Non-video content has no inferred poster.' );
$assert( 0 === $requests, 'Deterministic image checks make no external HTTP requests.' );
remove_filter( 'pre_http_request', $block_http );

// Fixture post data is virtual; no posts or attachment metadata are written.
$real_post = get_posts( array( 'numberposts' => 1, 'fields' => 'ids', 'post_type' => 'post' ) )[0];
add_filter( 'wptv_pre_video_image_url', '__return_false' );
$featured = '<img src="https://example.org/featured.jpg" alt="Existing">';
$assert( $featured === WordPressTV\Video\thumbnail_html( $featured, $real_post, 0, 'large', array() ), 'Existing featured image is retained when video imagery is unavailable.' );
$fallback = WordPressTV\Video\thumbnail_html( '', $real_post, 0, 'large', array( 'alt' => 'Video & title', 'class' => 'test-image' ) );
$assert( str_contains( $fallback, '/assets/images/placeholder.png' ) && str_contains( $fallback, 'Video &amp; title' ), 'Missing imagery uses the bundled fallback and escapes attributes.' );
remove_filter( 'wptv_pre_video_image_url', '__return_false' );
WP_CLI::success( $checks . ' GUID and image checks passed.' );
