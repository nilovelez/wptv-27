<?php
/**
 * Theme setup: supports and assets.
 *
 * @package WordPressTV
 */

namespace WordPressTV\Setup;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers theme supports.
 *
 * Block themes get title-tag, HTML5 markup, feed links and block styles from core.
 */
function setup() {
	add_editor_style( 'style.css' );
}
add_action( 'after_setup_theme', __NAMESPACE__ . '\setup' );

/**
 * Enqueues the theme stylesheet on the front end.
 */
function enqueue_assets() {
	wp_enqueue_style(
		'wptv-style',
		get_stylesheet_uri(),
		array(),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_assets' );
