<?php
/**
 * Simulated production image API; not proof of production integration.
 * Run with WP-CLI eval-file --skip-wordpress. Never run on production.
 *
 * @package WordPressTV
 */

if ( function_exists( 'get_post' ) ) {
	WP_CLI::error( 'Run this file with eval-file --skip-wordpress.' );
}
$GLOBALS['wptv_test_image'] = 'https://example.org/multisite.jpg';
$GLOBALS['wptv_test_image_guids'] = array();
function video_get_highest_resolution_image_url( $guid ) {
	$GLOBALS['wptv_test_image_guids'][] = $guid;
	return $GLOBALS['wptv_test_image'];
}
$root = WP_CLI::get_runner()->config['path'] ?: getcwd();
require rtrim( $root, '/\\' ) . '/wp-load.php';
if ( 'wptv-27' !== get_stylesheet() ) {
	WP_CLI::error( 'Activate wptv-27 first.' );
}
$checks = 0;
$assert = static function ( $condition, $message ) use ( &$checks ) {
	if ( ! $condition ) {
		WP_CLI::error( $message );
	}
	++$checks;
	WP_CLI::log( 'PASS ' . $message );
};
$post = new WP_Post( (object) array(
	'ID' => PHP_INT_MAX, 'filter' => 'raw', 'post_type' => 'post', 'post_content' => '[wpvideo Ab12Cd34]',
) );
$requests = 0;
$block_http = static function () use ( &$requests ) {
	++$requests;
	return new WP_Error( 'wptv_test_network_blocked' );
};
add_filter( 'pre_http_request', $block_http );
$assert( 'https://example.org/multisite.jpg' === WordPressTV\Video\get_image_url( $post ), 'Production image API remains the first choice.' );
$assert( array( 'Ab12Cd34' ) === $GLOBALS['wptv_test_image_guids'], 'Legacy GUID reaches the production API unchanged.' );
$post->post_content = serialize_block( array(
	'blockName' => 'videopress/video', 'attrs' => array( 'guid' => 'Ab12Cd34' ),
	'innerHTML' => '', 'innerContent' => array(), 'innerBlocks' => array(),
) );
$assert( 'https://example.org/multisite.jpg' === WordPressTV\Video\get_image_url( $post ), 'Block GUID also reaches the production API.' );
$assert( 0 === $requests, 'A successful production lookup does not call the standalone remote API.' );
$details = static function () {
	return (object) array( 'poster' => 'https://example.org/plugin.jpg', 'is_private' => false );
};
add_filter( 'pre_transient_jetpack_videopress_Ab12Cd34', $details );
foreach ( array( false, '', new WP_Error( 'unavailable' ) ) as $missing ) {
	$GLOBALS['wptv_test_image'] = $missing;
	$assert( 'https://example.org/plugin.jpg' === WordPressTV\Video\get_image_url( $post ), 'Unavailable or malformed production response safely falls through to the plugin.' );
}
remove_filter( 'pre_transient_jetpack_videopress_Ab12Cd34', $details );
$assert( 0 === $requests, 'Simulated multisite checks make no external HTTP requests.' );
remove_filter( 'pre_http_request', $block_http );
WP_CLI::success( $checks . ' simulated multisite image checks passed.' );
