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
	foreach ( array( 'core/group', 'core/post-template', 'core/term-template' ) as $block ) {
		register_block_style( $block, array( 'name' => 'cards-grid', 'label' => __( 'Cards grid', 'wptv' ) ) );
	}
	foreach ( array( 'core/group', 'core/comments' ) as $block ) {
		register_block_style( $block, array( 'name' => 'two-column-display', 'label' => __( 'Two-column display', 'wptv' ) ) );
	}
	register_block_style( 'core/heading', array( 'name' => 'with-arrow', 'label' => __( 'With arrow', 'wptv' ) ) );
	register_block_style( 'core/search', array( 'name' => 'light', 'label' => __( 'Light', 'wptv' ) ) );
	foreach ( array(
		'fill-on-dark'    => __( 'Fill on dark', 'wptv' ),
		'outline-on-dark' => __( 'Outline on dark', 'wptv' ),
		'text'            => __( 'Text', 'wptv' ),
		'toggle'          => __( 'Toggle', 'wptv' ),
	) as $name => $label ) {
		register_block_style( 'core/button', array( 'name' => $name, 'label' => $label ) );
	}
}
add_action( 'init', __NAMESPACE__ . '\register_block_styles' );

/**
 * Marks unlinked thumbnails as decorative beside descriptive linked titles.
 *
 * Only theme-marked card images are decorative; editorial image alt text stays intact.
 *
 * @param string $content Rendered featured-image block.
 * @param array  $block   Parsed block.
 * @return string
 */
function render_card_thumbnail( $content, $block ) {
	$classes = preg_split( '/\s+/', $block['attrs']['className'] ?? '' );
	if ( ! in_array( 'wptv-card__thumbnail', $classes, true ) ) {
		return $content;
	}

	$html = new \WP_HTML_Tag_Processor( $content );
	if ( $html->next_tag( 'img' ) ) {
		$html->set_attribute( 'alt', '' );
	}
	return $html->get_updated_html();
}
add_filter( 'render_block_core/post-featured-image', __NAMESPACE__ . '\render_card_thumbnail', 20, 2 );

/**
 * Removes term links only from the theme's marked card speaker blocks.
 *
 * Core Post Terms has no unlinked-display option. Keep its escaped names,
 * comma separators, suffix and wrapper; leave all other term blocks alone.
 *
 * @param string $content Rendered Post Terms block.
 * @param array  $block   Parsed block.
 * @return string
 */
function render_card_speakers( $content, $block ) {
	$classes = preg_split( '/\s+/', $block['attrs']['className'] ?? '' );
	if ( 'speakers' !== ( $block['attrs']['term'] ?? '' ) || ! in_array( 'wptv-card__speakers', $classes, true ) ) {
		return $content;
	}
	return strip_tags( $content, '<div><span>' );
}
add_filter( 'render_block_core/post-terms', __NAMESPACE__ . '\render_card_speakers', 20, 2 );

/**
 * Enqueues frontend styles, navigation state and progressive card activation.
 */
function enqueue_assets() {
	wp_enqueue_style(
		'wptv-style',
		get_stylesheet_uri(),
		array(),
		wp_get_theme()->get( 'Version' )
	);
	wp_enqueue_script(
		'wptv-navigation',
		get_theme_file_uri( 'assets/js/navigation.js' ),
		array(),
		wp_get_theme()->get( 'Version' ),
		array( 'in_footer' => true, 'strategy' => 'defer' )
	);
	wp_enqueue_script(
		'wptv-cards',
		get_theme_file_uri( 'assets/js/cards.js' ),
		array(),
		wp_get_theme()->get( 'Version' ),
		array( 'in_footer' => true, 'strategy' => 'defer' )
	);
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_assets' );

/**
 * Keep the homepage player's speaker/title caption inside one native video link.
 * Only the marked Post Title changes; other speaker terms and titles are intact.
 *
 * @param string    $content Rendered title.
 * @param array     $parsed  Parsed block.
 * @param \WP_Block $block   Block with its video context.
 * @return string
 */
function render_home_lead_title( $content, $parsed, $block ) {
	$classes = preg_split( '/\s+/', $parsed['attrs']['className'] ?? '' );
	if ( ! in_array( 'wptv-home__lead-title', $classes, true ) || empty( $block->context['postId'] ) ) {
		return $content;
	}
	$speakers = get_the_terms( $block->context['postId'], 'speakers' );
	if ( ! $speakers || is_wp_error( $speakers ) ) {
		return $content;
	}
	$prefix = esc_html( implode( ', ', wp_list_pluck( $speakers, 'name' ) ) . ': ' );
	return preg_replace_callback( '/<a\b[^>]*>/', static function ( $match ) use ( $prefix ) {
		return $match[0] . $prefix;
	}, $content, 1 );
}
add_filter( 'render_block_core/post-title', __NAMESPACE__ . '\render_home_lead_title', 20, 3 );
