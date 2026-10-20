<?php
/**
 * Read-only checks for the homepage category-card exclusions.
 *
 * @package WordPressTV
 */

if ( 'wptv-27' !== get_stylesheet() ) {
	WP_CLI::error( 'Activate wptv-27 before running these checks.' );
}

$checks = 0;
$assert = static function ( $condition, $message ) use ( &$checks ) {
	if ( ! $condition ) {
		WP_CLI::error( $message );
	}
	++$checks;
	WP_CLI::log( 'PASS ' . $message );
};
$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'wptv/front-page' );
$find = static function ( $blocks ) use ( &$find ) {
	foreach ( $blocks as $block ) {
		if ( 'core/terms-query' === $block['blockName']
			&& 'category' === ( $block['attrs']['termQuery']['taxonomy'] ?? '' ) ) {
			return $block;
		}
		$found = $find( $block['innerBlocks'] );
		if ( $found ) {
			return $found;
		}
	}
	return null;
};
$parsed = $find( parse_blocks( $pattern['content'] ) );
$assert( null !== $parsed, 'The homepage has its native category Terms Query.' );
$block = $parsed['innerBlocks'][0];
$excluded = array();
foreach ( array( 'year', 'location' ) as $slug ) {
	$term = get_term_by( 'slug', $slug, 'category' );
	$assert( $term instanceof WP_Term, 'The ' . $slug . ' fixture still exists.' );
	$excluded[] = $term->term_id;
}
$args = array( 'taxonomy' => 'category', 'parent' => 0, 'hide_empty' => true, 'orderby' => 'count', 'order' => 'desc', 'fields' => 'ids' );
$expected = get_terms( array_merge( $args, array( 'exclude' => $excluded ) ) );
$extract = static function ( $html ) {
	$ids = array();
	$tags = new WP_HTML_Tag_Processor( $html );
	while ( $tags->next_tag( 'li' ) ) {
		if ( preg_match( '/\bterm-(\d+)\b/', $tags->get_attribute( 'class' ) ?? '', $match ) ) {
			$ids[] = (int) $match[1];
		}
	}
	return $ids;
};
$original = get_terms( $args );
$rendered = ( new WP_Block( $parsed ) )->render();
$actual = $extract( $rendered );
$assert( ! array_intersect( $excluded, $actual ), 'Both structural categories are excluded from the cards.' );
$assert( array_map( 'intval', $expected ) === $actual, 'All other cards retain their native count ordering.' );
$assert( $original === get_terms( $args ) && count( array_intersect( $excluded, $original ) ) === count( $excluded ), 'Ordinary category queries retain both structural categories after rendering.' );
$unmarked = $block;
$unmarked['attrs']['className'] = 'wptv-test-unmarked';
$unmarked_html = ( new WP_Block( $unmarked, array( 'termQuery' => $parsed['attrs']['termQuery'] ) ) )->render();
$assert( array_map( 'intval', get_terms( $args ) ) === $extract( $unmarked_html ), 'An unmarked Term Template remains unchanged.' );

// Force a renderer failure and verify that the temporary filter is still removed.
global $wp_filter;
$before = isset( $wp_filter['get_terms_args'] ) ? $wp_filter['get_terms_args']->callbacks : array();
$wrapped = WordPressTV\BlockQueries\register_category_card_renderer(
	array( 'render_callback' => static function () { throw new RuntimeException( 'Expected test exception.' ); } ),
	'core/term-template'
);
try {
	call_user_func( $wrapped['render_callback'], $block['attrs'], '', new WP_Block( $block, array( 'termQuery' => $parsed['attrs']['termQuery'] ) ) );
	WP_CLI::error( 'The test renderer should throw.' );
} catch ( RuntimeException $exception ) {
	$after = isset( $wp_filter['get_terms_args'] ) ? $wp_filter['get_terms_args']->callbacks : array();
	$assert( $before === $after, 'The term filter is removed even when rendering throws.' );
}
WP_CLI::success( $checks . ' category-card checks passed; no data was written.' );
