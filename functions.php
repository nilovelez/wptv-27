<?php
/**
 * WordPress.tv 27 theme bootstrap.
 *
 * This file only loads the files in inc/. Keep logic out of it.
 *
 * @package WordPressTV
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPTV_THEME_DIR', __DIR__ );

// Loaded on every site that uses the theme (WordPress.tv and blog.WordPress.tv).
require_once WPTV_THEME_DIR . '/inc/setup.php';
require_once WPTV_THEME_DIR . '/inc/video.php';
require_once WPTV_THEME_DIR . '/inc/head.php';

/*
 * WordPress.tv-only features. Same switch as wptv2: a site can disable them with
 * add_filter( 'wptv_setup_theme', '__return_false' ) before the theme loads
 * (for example, blog.WordPress.tv from an mu-plugin).
 */
if ( apply_filters( 'wptv_setup_theme', true ) ) {
	require_once WPTV_THEME_DIR . '/inc/taxonomies.php';
	require_once WPTV_THEME_DIR . '/inc/query.php';
	require_once WPTV_THEME_DIR . '/inc/content.php';
	require_once WPTV_THEME_DIR . '/inc/open-graph.php';
	require_once WPTV_THEME_DIR . '/inc/redirects.php';
	require_once WPTV_THEME_DIR . '/inc/maintenance.php';
	require_once WPTV_THEME_DIR . '/inc/endpoints/api.php';
	require_once WPTV_THEME_DIR . '/inc/endpoints/oembed.php';
	require_once WPTV_THEME_DIR . '/inc/endpoints/unisubs.php';
}
