<?php
/**
 * Read-only integration simulations; eval-file --skip-wordpress.
 * Fake production APIs are confined to this process. No posts are written.
 *
 * @package WordPressTV
 */

if ( function_exists( 'get_post' ) ) {
	WP_CLI::error( 'Run with eval-file --skip-wordpress.' );
}

function find_all_videopress_shortcodes( $content ) {
	preg_match_all( '/\[wpvideo ([A-Za-z0-9]+)/', $content, $matches );
	return array_fill_keys( $matches[1], array() );
}
function video_get_info_by_guid( $guid ) {
	if ( 'Missing1' === $guid ) {
		return false;
	}
	return (object) array(
		'guid' => $guid, 'post_id' => PHP_INT_MAX, 'width' => 1280,
		'height' => 720, 'duration' => 60, 'rating' => 'G',
	);
}
function video_get_single_response( $video ) {
	return array(
		'original' => 'https://example.org/' . $video->guid . '.mp4',
		'files' => array( 'dvd' => array( 'hls' => true ) ),
		'subtitles' => array( 'en' => 'https://example.org/captions.ttml' ),
	);
}
function video_url_by_format( $video, $format ) {
	return 'https://example.org/' . $video->guid . '/' . $format . '.mp4';
}

$root = WP_CLI::get_runner()->config['path'] ?: getcwd();
require rtrim( $root, '/\\' ) . '/wp-load.php';
if ( 'wptv-27' !== get_stylesheet() ) {
	WP_CLI::error( 'Activate wptv-27 first.' );
}
add_filter( 'wptv_pre_video_image_url', '__return_false' );
$checks = 0;
$assert = static function ( $condition, $message ) use ( &$checks ) {
	if ( ! $condition ) {
		WP_CLI::error( $message );
	}
	++$checks;
	WP_CLI::log( 'PASS ' . $message );
};
$make_post = static function ( $content ) {
	return new WP_Post( (object) array(
		'ID' => PHP_INT_MAX, 'filter' => 'raw', 'post_type' => 'post',
		'post_status' => 'publish', 'post_content' => $content,
	) );
};
$block = static function ( $guid, $name = 'videopress/video' ) {
	return serialize_block( array(
		'blockName' => $name, 'attrs' => array( 'guid' => $guid ),
		'innerBlocks' => array(), 'innerContent' => array(), 'innerHTML' => '',
	) );
};
$a = 'Ab12Cd34';
$b = 'Ef56Gh78';
$shortcode = $make_post( '[wpvideo ' . $a . ']' );
$video = $make_post( $block( $a ) );
$resolve = 'WordPressTV\\Video\\get_integration_guid';
$assert( $a === $resolve( $shortcode ), 'Integration shortcode GUID is unchanged.' );
$assert( $a === $resolve( $make_post( '[wpvideo ' . $a . '] [wpvideo ' . $b . ']' ) ), 'Integrations preserve first-shortcode semantics.' );
$assert( $a === $resolve( $video ), 'Block-only integration GUID is supported.' );
$assert( $a === $resolve( $make_post( $block( $a, 'jetpack/videopress' ) ) ), 'Earlier Jetpack block is supported.' );
$assert( $a === $resolve( $make_post( '[wpvideo ' . $a . ']' . $block( $b ) ) ), 'Mixed content retains shortcode priority.' );
$assert( false === $resolve( $make_post( '[wpvideo ?' . $block( $a ) ) ), 'Malformed shortcode markers do not fall through to blocks.' );
$assert( false === $resolve( $make_post( $block( 'invalid' ) ) ), 'Invalid block GUID is ignored.' );
$nested = serialize_block( array(
	'blockName' => 'core/group', 'attrs' => array(), 'innerHTML' => '',
	'innerBlocks' => parse_blocks( $block( $b ) ), 'innerContent' => array( null ),
) );
$assert( $b === $resolve( $make_post( $block( $a ) . $nested ) ), 'Last valid nested block is the fallback.' );
$base = array( 'video' => array( 'original' => false, 'mp4' => array(), 'ogg' => array() ), 'subtitles' => array() );
$files = WordPressTV\Endpoints\Api\add_videopress_files( $base, $video );
$assert( $files === WordPressTV\Endpoints\Api\add_videopress_files( $base, $shortcode ), 'API file and subtitle output matches equivalent shortcode content.' );
$assert( ! isset( $files['video']['mp4']['med'] ) && isset( $files['video']['mp4']['high'] ), 'API still excludes HLS-only MP4 formats.' );
$assert( isset( $files['subtitles']['en'] ), 'API includes block video subtitles.' );
$assert( $base === WordPressTV\Endpoints\Api\add_videopress_files( $base, $make_post( $block( 'Missing1' ) ) ), 'Missing video details leave the API item unchanged.' );

// Inject an in-memory post into the cache; never insert or update database rows.
wp_cache_add( PHP_INT_MAX, $video, 'posts' );
$GLOBALS['post'] = $video;
$tags = WordPressTV\OpenGraph\customize_tags( array() );
$assert( 'https://videopress.com/v/' . $a . '?autoplay=0' === $tags['twitter:player'], 'Open Graph resolves the block player GUID.' );
$assert( $a === WordPressTV\VideoBlocks\download_video( PHP_INT_MAX )->guid, 'Download sidebar resolves the block GUID.' );
$assert( $a === WordPressTV\Video\get_details( $video )->guid, 'JSON-LD details resolve the block GUID.' );
$assert( 4 === count( WordPressTV\Video\get_file_urls( $video ) ), 'JSON-LD file lookup uses block video details.' );
wp_cache_delete( PHP_INT_MAX, 'posts' );
$GLOBALS['post'] = $make_post( $block( 'Missing1' ) );
$assert( array( 'existing' => 'value' ) === WordPressTV\OpenGraph\customize_tags( array( 'existing' => 'value' ) ), 'Missing details preserve existing social tags.' );

// Query interception tests the fallback without writing fixture posts.
$queries = array();
$intercept = static function ( $posts, $query ) use ( &$queries, $make_post, $block, $a, $b ) {
	$queries[] = $query->query_vars;
	if ( str_starts_with( $query->get( 's' ), '[wpvideo' ) ) {
		return array();
	}
	return array( $make_post( 'Plain mention: ' . $a ), $make_post( $block( $b ) ), $make_post( $block( $a ) ) );
};
add_filter( 'posts_pre_query', $intercept, 10, 2 );
$found = WordPressTV\Endpoints\Unisubs\get_post_by_guid( $a );
$assert( $found && $a === WordPressTV\Video\get_guid( $found ), 'GUID fallback verifies parsed blocks rather than textual mentions.' );
$assert( '[wpvideo ' . $a . ']' === $queries[0]['s'] && 'publish' === $queries[1]['post_status'], 'Legacy search runs first and fallback only queries published posts.' );
$queries = array();
$assert( false === WordPressTV\Endpoints\Unisubs\get_post_by_guid( 'bad/guid' ) && 1 === count( $queries ), 'Invalid GUID does not enter the block search.' );
remove_filter( 'posts_pre_query', $intercept, 10 );
$legacy = static function () use ( $shortcode ) { return array( $shortcode ); };
add_filter( 'posts_pre_query', $legacy );
$assert( $shortcode === WordPressTV\Endpoints\Unisubs\get_post_by_guid( $a ), 'An existing legacy search result retains precedence.' );
remove_filter( 'posts_pre_query', $legacy );
$pages = array();
$paged = static function ( $posts, $query ) use ( &$pages, $make_post, $block, $a ) {
	if ( str_starts_with( $query->get( 's' ), '[wpvideo' ) ) {
		return array();
	}
	$pages[] = $query->get( 'paged' );
	return 1 === $query->get( 'paged' )
		? array_fill( 0, 100, $make_post( 'Plain mention: ' . $a ) )
		: array( $make_post( $block( $a ) ) );
};
add_filter( 'posts_pre_query', $paged, 10, 2 );
$found = WordPressTV\Endpoints\Unisubs\get_post_by_guid( $a );
$assert( $found && array( 1, 2 ) === $pages, 'Block lookup continues past a full batch of textual false positives.' );
remove_filter( 'posts_pre_query', $paged, 10 );
WP_CLI::success( $checks . ' integration simulations passed; no content was written.' );
