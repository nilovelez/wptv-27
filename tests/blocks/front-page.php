<?php
/**
 * Read-only front-page checks. Run with WP-CLI eval-file while wptv-27 is active.
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
$resolve = 'WordPressTV\\BlockQueries\\resolve_terms';
$make_block = static function ( $query ) {
	return new WP_Block( array( 'blockName' => 'core/post-template', 'attrs' => array() ), array( 'query' => $query ) );
};
$base = array( 'post_type' => 'post', 'posts_per_page' => 4, 'tax_query' => array() );
$featured_context = array( 'wptvQuery' => 'wptv/featured', 'inherit' => false );
$args = $resolve( $base, $make_block( $featured_context ) );
$term = get_term_by( 'slug', 'featured', 'post_tag' );
$assert( $term instanceof WP_Term, 'The featured fixture exists.' );
$assert( array( $term->term_id ) === $args['tax_query'][0]['terms'], 'Featured resolves its slug to the current site term ID.' );
$query = new WP_Query( $args );
$expected = new WP_Query( array( 'post_type' => 'post', 'tag' => 'featured', 'posts_per_page' => 4 ) );
$assert( wp_list_pluck( $expected->posts, 'ID' ) === wp_list_pluck( $query->posts, 'ID' ), 'Featured matches a native slug-based query, including an initially empty tax_query.' );
$assert( $base === $resolve( $base, $make_block( array() ) ), 'Unmarked queries remain untouched.' );
$assert( $base === $resolve( $base, $make_block( array_merge( $featured_context, array( 'inherit' => true ) ) ) ), 'Inherited queries remain untouched.' );
$category_args = $resolve( $base, $make_block( array( 'wptvQuery' => 'wptv/wordcamp' ) ) );
$category = get_term_by( 'slug', 'wordcamptv', 'category' );
$assert( $category instanceof WP_Term && array( $category->term_id ) === $category_args['tax_query'][0]['terms'], 'Category constraints also resolve by slug.' );
$hide_featured = static function ( $terms, $term_query ) {
	$query_args = $term_query->query_vars;
	return in_array( 'post_tag', (array) $query_args['taxonomy'], true ) && array( 'featured' ) === (array) ( $query_args['slug'] ?? array() ) ? array() : $terms;
};
add_filter( 'terms_pre_query', $hide_featured, 10, 2 );
$missing = $resolve( $base, $make_block( $featured_context ) );
remove_filter( 'terms_pre_query', $hide_featured, 10 );
$assert( array( 0 ) === ( $missing['post__in'] ?? null ), 'A missing term fails closed instead of showing unrelated posts.' );
$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'wptv/front-page' );
$assert( ! empty( $pattern['content'] ), 'The front-page pattern is registered.' );
$queries = array();
$walk = static function ( $blocks ) use ( &$walk, &$queries ) {
	foreach ( $blocks as $block ) {
		if ( 'core/query' === $block['blockName'] ) {
			$queries[] = $block['attrs']['query'];
		}
		$walk( $block['innerBlocks'] );
	}
};
$walk( parse_blocks( $pattern['content'] ) );
$assert( 3 === count( $queries ), 'The pattern has exactly three post queries.' );
$assert( 1 === $queries[0]['perPage'] && 4 === $queries[1]['perPage'], 'The independent queries request one latest video and four featured videos.' );
$assert( array( 'inherit' => true ) === $queries[2], 'Recent videos inherit without overriding page size, offset or ordering.' );
WP_CLI::success( $checks . ' front-page checks passed.' );
