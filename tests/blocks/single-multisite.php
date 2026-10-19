<?php
/**
 * Isolated simulations of unavailable multisite APIs. Never deploy this file.
 * Run with eval-file --skip-wordpress so fixtures precede the local MU stubs.
 *
 * @package WordPressTV
 */

if ( function_exists( 'get_post' ) ) {
	WP_CLI::error( 'Run this file with eval-file --skip-wordpress.' );
}

$GLOBALS['wptv_test_files'] = 'full';

function video_get_single_response( $video ) {
	return array(
		'files'    => array( 'dvd' => array( 'hls' => 'hls' === $GLOBALS['wptv_test_files'] ) ),
		'original' => 'empty' === $GLOBALS['wptv_test_files'] ? '' : 'https://example.org/original.mp4',
	);
}

function video_url_by_format( $video, $format ) {
	return 'empty' === $GLOBALS['wptv_test_files'] ? false : 'https://example.org/' . $format . '.mp4';
}

function video_highest_resolution_ogg( $video ) {
	return 'empty' === $GLOBALS['wptv_test_files'] ? false : 'https://example.org/video.ogg';
}

class VideoPress_Subtitles {
	public static function get_languages() {
		return array( 'en' => array( 'localized_label' => 'English & captions' ) );
	}
}

class VideoPress_Subtitles_Track {
	private $language;
	public function __construct( $track ) {
		$this->language = $track['language'];
	}
	public function url() {
		return 'https://example.org/' . rawurlencode( $this->language ) . '.ttml';
	}
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
$video = (object) array( 'post_id' => PHP_INT_MAX, 'guid' => 'ExampleGuid' );
$files = WordPressTV\VideoBlocks\file_links( $video, PHP_INT_MAX );
$assert( false !== strpos( $files, 'fmt_std.mp4' ) && false !== strpos( $files, '>Low</a>' ), 'Standard MP4 has the Low label.' );
$assert( false !== strpos( $files, 'fmt_dvd.mp4' ) && false !== strpos( $files, '>Med</a>' ), 'DVD MP4 has the Med label.' );
$assert( false !== strpos( $files, 'fmt_hd.mp4' ) && false !== strpos( $files, '>High</a>' ), 'HD MP4 has the High label.' );
$assert( false !== strpos( $files, 'https://example.org/original.mp4' ) && false !== strpos( $files, '>Original</a>' ), 'The original API file is used when no local attachment exists.' );
$assert( false !== strpos( $files, 'OGG:' ) && false !== strpos( $files, 'video.ogg' ), 'OGG keeps the legacy separate format line.' );
$GLOBALS['wptv_test_files'] = 'hls';
$files = WordPressTV\VideoBlocks\file_links( $video, PHP_INT_MAX );
$assert( false === strpos( $files, 'fmt_dvd.mp4' ) && false !== strpos( $files, 'fmt_hd.mp4' ), 'HLS-only MP4 transcodes are omitted without losing other formats.' );
$GLOBALS['wptv_test_files'] = 'empty';
$assert( '' === WordPressTV\VideoBlocks\file_links( $video, PHP_INT_MAX ), 'No files produce no empty Download heading.' );

$metadata = static function ( $value, $id, $key ) {
	if ( PHP_INT_MAX === $id && '_videopress_subtitles' === $key ) {
		return array( array(
			array( 'language' => 'en', 'subtitles_post_id' => 1 ),
			array( 'language' => 'unknown', 'subtitles_post_id' => 2 ),
			array( 'language' => 'en' ),
			'not-a-track',
		) );
	}
	return $value;
};
add_filter( 'get_post_metadata', $metadata, 10, 3 );
$subtitles = WordPressTV\VideoBlocks\subtitle_links( $video );
remove_filter( 'get_post_metadata', $metadata, 10 );
$assert( false !== strpos( $subtitles, 'https://example.org/en.ttml' ) && false !== strpos( $subtitles, 'English &amp; captions' ), 'TTML tracks use escaped localized labels.' );
$assert( false !== strpos( $subtitles, '>unknown</a>' ), 'Unknown language labels safely fall back to the stored code.' );
$assert( 2 === substr_count( $subtitles, '.ttml' ), 'Incomplete and malformed subtitle tracks are skipped.' );
$assert( false !== strpos( $subtitles, '/subtitle/?video=' . PHP_INT_MAX ), 'Subtitle contribution keeps the legacy attachment-ID query argument.' );
$assert( '' === WordPressTV\VideoBlocks\subtitle_links( (object) array( 'guid' => 'ExampleGuid' ) ), 'Absent attachment IDs omit subtitles.' );
WP_CLI::success( $checks . ' simulated multisite checks passed; no data was written.' );
