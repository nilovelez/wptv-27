<?php
/**
 * Isolated checks: run with PHP, never with a bootstrapped WordPress instance.
 * Usage: php check-environment-guard.php route|loader local|staging|production|wpcom
 * For the loader check, pass -d sys_temp_dir=<non-Studio temporary directory>.
 *
 * @package WordPressTV
 */

if ( defined( 'ABSPATH' ) ) {
	exit( "Run this script directly with PHP, outside WordPress.\n" );
}

$target = $argv[1] ?? 'route';
$environment = $argv[2] ?? 'production';
if ( 'wpcom' === $environment ) {
	define( 'IS_WPCOM', true );
}

function wp_get_environment_type() {
	return $GLOBALS['environment'];
}

function wp_normalize_path( $path ) {
	return str_replace( '\\', '/', $path );
}

function trailingslashit( $path ) {
	return rtrim( $path, '/\\' ) . '/';
}

$logging = array( ini_get( 'display_errors' ), ini_get( 'log_errors' ), ini_get( 'error_log' ) );
$root = dirname( __DIR__, 4 );
$file = 'loader' === $target
	? $root . '/mu-plugins/99-studio-loader.php'
	: __DIR__ . '/mu-plugin/wptv-route-test.php';
require $file;

$unchanged = $logging === array( ini_get( 'display_errors' ), ini_get( 'log_errors' ), ini_get( 'error_log' ) );
$inactive = ! function_exists( 'video_get_info_by_guid' )
	&& ! function_exists( 'video_get_highest_resolution_image_url' )
	&& ! defined( 'WP_ENVIRONMENT_TYPE' )
	&& ! defined( 'DB_NAME' );
if ( ! $unchanged || ! $inactive ) {
	fwrite( STDERR, "FAIL: {$target} changed the non-Studio environment.\n" );
	exit( 1 );
}
echo "PASS: {$target} is inactive outside Studio ({$environment}).\n";
