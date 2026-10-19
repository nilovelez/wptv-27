<?php
/**
 * Read-only single-video checks with the active site's registered blocks.
 * Run: wp eval-file wp-content/themes/wptv-27/tests/blocks/single.php
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
$posts = get_posts( array( 'numberposts' => 1, 'post_status' => 'publish' ) );
$assert( ! empty( $posts ), 'A published post fixture exists.' );
$post_id = $posts[0]->ID;
$make_block = static function ( $name, $attrs = array(), $context = array() ) use ( $post_id ) {
	return new WP_Block(
		array( 'blockName' => $name, 'attrs' => $attrs, 'innerBlocks' => array(), 'innerHTML' => '', 'innerContent' => array() ),
		array_merge( array( 'postId' => $post_id, 'postType' => 'post' ), $context )
	);
};
foreach ( array( 'wptv/producer', 'wptv/video-downloads' ) as $name ) {
	$type = WP_Block_Type_Registry::get_instance()->get_registered( $name );
	$assert( $type && ! empty( $type->supports['autoRegister'] ), $name . ' is PHP-only and auto-registered.' );
}
$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'wptv/single' );
$assert( ! empty( $pattern['content'] ), 'The hidden single-video pattern is registered.' );
$counts = array();
$walk = static function ( $blocks ) use ( &$walk, &$counts ) {
	foreach ( $blocks as $block ) {
		$name = $block['blockName'];
		$counts[ $name ] = ( $counts[ $name ] ?? 0 ) + 1;
		$walk( $block['innerBlocks'] );
	}
};
$walk( parse_blocks( $pattern['content'] ) );
$assert( 1 === $counts['core/post-title'] && 1 === $counts['core/post-content'], 'There is one title and one player/content block.' );
$assert( 1 === $counts['jetpack/like'] && 1 === $counts['jetpack/sharing-buttons'], 'Likes and sharing use the existing Jetpack blocks.' );
$assert( 1 === $counts['core/comment-template'] && 1 === $counts['core/post-comments-form'], 'Comments use native blocks and the native reply form.' );
$assert( empty( $counts['core/html'] ) && empty( $counts['core/shortcode'] ), 'The template adds no raw HTML blocks or shortcodes.' );

$term_mode = 'none';
$terms = static function ( $result, $id, $taxonomy ) use ( &$term_mode, $post_id ) {
	if ( $post_id !== $id || ! in_array( $taxonomy, array( 'producer', 'producer-username' ), true ) ) {
		return $result;
	}
	if ( 'error' === $term_mode ) {
		return new WP_Error( 'wptv_test', 'Simulated taxonomy failure.' );
	}
	$include = ( 'producer' === $taxonomy && in_array( $term_mode, array( 'name', 'both' ), true ) ) || ( 'producer-username' === $taxonomy && in_array( $term_mode, array( 'user', 'both' ), true ) );
	return $include ? array( new WP_Term( (object) array( 'term_id' => 0, 'taxonomy' => $taxonomy, 'name' => 'producer' === $taxonomy ? 'Example Producer' : 'example-user', 'slug' => 'example-producer' ) ) ) : false;
};
$link = static function ( $url, $term, $taxonomy ) {
	return 'producer' === $taxonomy ? home_url( '/producer/example-producer/' ) : $url;
};
add_filter( 'get_the_terms', $terms, 10, 3 );
add_filter( 'term_link', $link, 10, 3 );
$assert( '' === $make_block( 'wptv/producer' )->render(), 'No producer terms yield no empty heading.' );
$term_mode = 'name';
$html = $make_block( 'wptv/producer' )->render();
$assert( false !== strpos( $html, '/producer/example-producer/' ) && false !== strpos( $html, 'Example Producer' ), 'A name-only producer links to its existing archive.' );
$term_mode = 'user';
$html = $make_block( 'wptv/producer' )->render();
$assert( false !== strpos( $html, 'https://profiles.wordpress.org/example-user' ), 'A username-only producer links to WordPress.org.' );
$term_mode = 'both';
$html = $make_block( 'wptv/producer' )->render();
$assert( false !== strpos( $html, 'https://profiles.wordpress.org/example-user' ) && false !== strpos( $html, '>Example Producer</a>' ), 'Combined producer terms use the name and the WordPress.org profile.' );
$term_mode = 'error';
$assert( '' === $make_block( 'wptv/producer' )->render(), 'Taxonomy errors omit the producer without warnings.' );
remove_filter( 'get_the_terms', $terms, 10 );
remove_filter( 'term_link', $link, 10 );

$excerpt = static function ( $text, $post ) use ( $post_id ) {
	return $post && $post_id === $post->ID ? str_repeat( 'description-word ', 150 ) . '<a href="https://example.org/slides">Presentation Slides</a>' : $text;
};
add_filter( 'get_the_excerpt', $excerpt, 20, 2 );
$full = $make_block( 'core/post-excerpt', array( 'className' => 'wptv-single__excerpt' ) )->render();
$normal = $make_block( 'core/post-excerpt' )->render();
$assert( 150 === substr_count( $full, 'description-word' ), 'The single description is not truncated at the core excerpt limit.' );
$assert( false !== strpos( $full, 'href="https://example.org/slides"' ), 'The description preserves the presentation slides link.' );
$assert( 55 === substr_count( $normal, 'description-word' ), 'Ordinary excerpt blocks retain their core length.' );
remove_filter( 'get_the_excerpt', $excerpt, 20 );
$assert( false === WordPressTV\VideoBlocks\download_video( 0 ), 'Missing posts do not query video internals.' );
$assert( '' === WordPressTV\VideoBlocks\subtitle_links( (object) array() ), 'Missing multisite subtitle details produce no dead contribution link.' );
WP_CLI::success( $checks . ' single-video checks passed; no posts, terms or options were changed.' );
