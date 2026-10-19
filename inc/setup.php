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
 * Registers the recurring visual treatments used by theme patterns.
 */
function register_block_styles() {
	foreach ( array( 'core/group', 'core/post-featured-image' ) as $block ) {
		register_block_style( $block, array( 'name' => 'video-frame', 'label' => __( 'Video frame', 'wptv' ) ) );
	}
	register_block_style( 'core/group', array( 'name' => 'event-tile', 'label' => __( 'Event tile', 'wptv' ) ) );
}
add_action( 'init', __NAMESPACE__ . '\register_block_styles' );

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
