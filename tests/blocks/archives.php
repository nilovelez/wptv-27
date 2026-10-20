<?php
/**
 * Read-only category/event partition checks. Run with WP-CLI eval-file.
 *
 * @package WordPressTV
 */

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
$registry  = WP_Block_Patterns_Registry::get_instance();
$spotlight = $registry->get_registered( 'wptv/taxonomy-spotlight' );
$grid      = $registry->get_registered( 'wptv/video-grid' );
$assert( $spotlight && ! $spotlight['inserter'], 'The spotlight pattern is registered and hidden.' );
$queries = array();
$walk = static function ( $blocks ) use ( &$walk, &$queries ) {
	foreach ( $blocks as $block ) {
		if ( 'core/query' === $block['blockName'] ) {
			$queries[] = $block['attrs']['query'];
		}
		$walk( $block['innerBlocks'] );
	}
};
$walk( parse_blocks( $spotlight['content'] . $grid['content'] ) );
$assert( 3 === count( $queries ) && array( array( 'inherit' => true ) ) === array_values( array_unique( $queries, SORT_REGULAR ) ), 'All three queries inherit without page-size, offset or ordering overrides.' );
foreach ( array( 'category', 'taxonomy-event' ) as $name ) {
	$template = file_get_contents( get_theme_file_path( 'templates/' . $name . '.html' ) );
	$assert( false !== strpos( $template, '"slug":"header"' ) && false !== strpos( $template, '"slug":"footer"' ), $name . ' keeps the shared header and footer.' );
	$assert( 1 === substr_count( $template, 'wp:query-title ' ) && false !== strpos( $template, '"showPrefix":false' ), $name . ' has one unprefixed archive title.' );
	$assert( ( 'category' === $name ) === ( false !== strpos( $template, 'wp:term-description ' ) ), $name . ' preserves the legacy term-description rule.' );
}

// Avoid network/player rendering; exercise real core loops and post contexts.
$skip_player = static function ( $pre_render, $parsed ) {
	return 'core/post-content' === $parsed['blockName'] ? '' : $pre_render;
};
add_filter( 'pre_render_block', $skip_player, 5, 2 );
add_filter( 'wptv_pre_video_image_url', '__return_false' );
$original_query = $GLOBALS['wp_query'];
$original_main  = $GLOBALS['wp_the_query'];
$original_post  = $GLOBALS['post'] ?? null;
$original_posts = $GLOBALS['posts'] ?? null;
try {
	foreach ( array( 'category_name' => 'how-to', 'event' => 'wordcamp-europe-2023' ) as $key => $slug ) {
		$query = new WP_Query( array( $key => $slug, 'posts_per_page' => 22 ) );
		$assert( 22 === count( $query->posts ), $slug . ' has a full fixture page.' );
		// Match the real HTTP bootstrap, including the by-reference posts global.
		$query->get_queried_object();
		$GLOBALS['posts']        = &$query->posts;
		$GLOBALS['wp_query']     = $query;
		$GLOBALS['wp_the_query'] = $query;
		$GLOBALS['post']         = $query->posts[0];
		$ids                    = wp_list_pluck( $query->posts, 'ID' );
		$original_results       = $query->posts;
		$before                 = serialize( $query );
		$post_before            = $GLOBALS['post'];
		$html                   = do_blocks( $spotlight['content'] . $grid['content'] );
		preg_match_all( '/<li\b[^>]*\bpost-(\d+)\b/', $html, $matches );
		$actual = array_map( 'intval', $matches[1] );
		$assert( $ids === $actual, $slug . ' renders each main-query video once and in the original order.' );
		$assert( $before === serialize( $query ) && $GLOBALS['wp_query'] === $query, $slug . ' leaves the original query, loop state and totals unchanged.' );
		$assert( $post_before === $GLOBALS['post'], $slug . ' restores the global post.' );
		foreach ( array( 'lead' => 1, 'secondary' => 5, 'remaining' => 16 ) as $section => $count ) {
			preg_match( '/<ul\b[^>]*wptv-taxonomy__' . $section . '[^>]*>(.*?)<\/ul>/s', $html, $list );
			$assert( isset( $list[1] ) && $count === substr_count( $list[1], '<li ' ), $slug . ' renders ' . $count . ' videos in ' . $section . '.' );
		}
		$assert( ( 'category_name' === $key ) === ( false !== strpos( $html, 'wptv-taxonomy__category-events' ) ), $slug . ' retains event captions only for categories.' );

		foreach ( array( 0, 1, 6, 7 ) as $total ) {
			$query->found_posts = $total;
			$assert( ( $total > 6 ) === WordPressTV\Archives\has_spotlight( $query ), $slug . ' applies the strict threshold for ' . $total . ' results.' );
			$query->posts      = array_slice( $original_results, 0, $total );
			$query->post_count = $total;
			$query->rewind_posts();
			$html = do_blocks( $spotlight['content'] . $grid['content'] );
			preg_match_all( '/<li\b[^>]*\bpost-(\d+)\b/', $html, $matches );
			$assert( array_slice( $ids, 0, $total ) === array_map( 'intval', $matches[1] ), $slug . ' renders all ' . $total . ' results once at the boundary.' );
		}
		$query->posts       = $original_results;
		$query->post_count  = 22;
		$query->found_posts = 22;
		$query->rewind_posts();
		$query->set( 'paged', 2 );
		$html = do_blocks( $spotlight['content'] . $grid['content'] );
		preg_match_all( '/<li\b[^>]*\bpost-(\d+)\b/', $html, $matches );
		$assert( false === strpos( $html, 'wptv-taxonomy__spotlight' ) && $ids === array_map( 'intval', $matches[1] ), $slug . ' page two omits the spotlight and keeps every queried video.' );
		$query->set( 'paged', 0 );
		$query->posts       = array_slice( $query->posts, 0, 6 );
		$query->post_count  = 6;
		$query->found_posts = 6;
		$query->rewind_posts();
		$html = do_blocks( $spotlight['content'] . $grid['content'] );
		preg_match_all( '/<li\b[^>]*\bpost-(\d+)\b/', $html, $matches );
		$assert( false === strpos( $html, 'wptv-taxonomy__spotlight' ) && array_slice( $ids, 0, 6 ) === array_map( 'intval', $matches[1] ), $slug . ' with six results has no spotlight or omitted video.' );
		$query->is_feed = true;
		$query->found_posts = 22;
		$assert( ! WordPressTV\Archives\has_spotlight( $query ), $slug . ' never partitions a feed.' );
	}
	$query->is_feed = false;
	$parent = new WP_Block( array( 'blockName' => 'core/query', 'attrs' => array( 'query' => array( 'inherit' => false ) ) ) );
	$marked = array( 'blockName' => 'core/post-template', 'attrs' => array( 'className' => 'wptv-taxonomy__lead' ) );
	$assert( null === WordPressTV\Archives\render_partition( null, $marked, $parent ), 'An independent Query block is never partitioned.' );
	$assert( 'prior output' === WordPressTV\Archives\render_partition( 'prior output', $marked, $parent ), 'Previously short-circuited output is preserved.' );
} finally {
	$GLOBALS['wp_query']     = $original_query;
	$GLOBALS['wp_the_query'] = $original_main;
	$GLOBALS['post']         = $original_post;
	unset( $GLOBALS['posts'] );
	$GLOBALS['posts']        = $original_posts;
	remove_filter( 'pre_render_block', $skip_player, 5 );
	remove_filter( 'wptv_pre_video_image_url', '__return_false' );
}
WP_CLI::success( $checks . ' archive checks passed; no data was written.' );
