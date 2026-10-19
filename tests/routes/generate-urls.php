<?php
/**
 * Generates the route regression test URL list from real site data.
 *
 * Usage (from the site root, with a theme that registers the WPTV taxonomies active):
 *     wp eval-file wp-content/themes/wptv-27/tests/routes/generate-urls.php
 *
 * Writes urls.txt next to this file: one "label<TAB>path" pair per line.
 * The list is committed, so baseline and comparison runs use the same URLs.
 * Regenerate it only when the site data changes, and then capture a new baseline.
 *
 * @package WordPressTV
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wptv_urls = array();

/**
 * Adds a URL to the list, as a path relative to the home URL.
 *
 * @param string $label Short, unique description.
 * @param string $url   Absolute URL or path.
 */
$add = function ( $label, $url ) use ( &$wptv_urls ) {
	$home = untrailingslashit( home_url() );
	$path = str_starts_with( $url, $home ) ? substr( $url, strlen( $home ) ) : $url;
	$wptv_urls[ $label ] = '' === $path ? '/' : $path;
};

/**
 * Posts per page for a view, mirroring wptv2's posts_per_page() (route contract).
 *
 * @param string $view front|category|event|archive.
 * @return int
 */
$per_page = function ( $view ) {
	$map = array(
		'front'    => 8,
		'category' => 22,
		'event'    => 22,
	);
	return $map[ $view ] ?? 10;
};

/**
 * Adds page 1, page 2, the last page and the page after the last one for an archive.
 *
 * @param string $label Base label.
 * @param string $url   Archive URL (page 1).
 * @param array  $args  WP_Query args that reproduce the archive's main query.
 * @param int    $pp    Posts per page.
 */
$add_paged = function ( $label, $url, $args, $pp ) use ( $add ) {
	$query = new WP_Query( array_merge( $args, array( 'posts_per_page' => $pp, 'fields' => 'ids', 'post_status' => 'publish' ) ) );
	$last  = max( 1, (int) $query->max_num_pages );
	$base  = trailingslashit( $url );

	$add( "$label", $url );
	if ( $last >= 2 ) {
		$add( "$label page 2", $base . 'page/2/' );
	}
	if ( $last > 2 ) {
		$add( "$label last page ($last)", $base . "page/$last/" );
	}
	$add( "$label past last page (" . ( $last + 1 ) . ')', $base . 'page/' . ( $last + 1 ) . '/' );
};

foreach ( array( 'event', 'speakers', 'language', 'producer', 'producer-username', 'flavor' ) as $tax ) {
	if ( ! taxonomy_exists( $tax ) ) {
		WP_CLI::error( "Taxonomy '$tax' is not registered. Activate a theme that registers the WPTV taxonomies (wptv2) and run again." );
	}
}

// Front page.
$add_paged( 'front', home_url( '/' ), array(), $per_page( 'front' ) );

// Single posts.
$latest = get_posts( array( 'numberposts' => 1 ) );
$oldest = get_posts( array( 'numberposts' => 1, 'order' => 'ASC' ) );
$slides = get_posts( array( 'numberposts' => 1, 'meta_key' => '_wptv_slides_url' ) );
$commented = get_posts( array( 'numberposts' => 1, 'orderby' => 'comment_count' ) );
foreach ( array( 'latest' => $latest, 'oldest' => $oldest, 'with slides' => $slides, 'most commented' => $commented ) as $name => $posts ) {
	if ( $posts ) {
		$add( "single: $name", get_permalink( $posts[0] ) );
	}
}
// The most recent post that embeds a VideoPress video (the latest post may be a test without one).
$video_post = null;
foreach ( get_posts( array( 'numberposts' => 50 ) ) as $candidate ) {
	if ( preg_match( '/\[wpvideo +[a-zA-Z0-9]+/', $candidate->post_content ) ) {
		$video_post = $candidate;
		break;
	}
}
if ( $video_post ) {
	$add( 'single: latest with video', get_permalink( $video_post ) );
	$add( 'single: by ?p=ID', '/?p=' . $video_post->ID );
	$add( 'single: wrong date in URL', '/1999/01/01/' . $video_post->post_name . '/' );
	$add( 'single: feed', trailingslashit( get_permalink( $video_post ) ) . 'feed/' );
}

// Pages.
foreach ( get_pages() as $page ) {
	$add( 'page: ' . $page->post_name, get_permalink( $page ) );
}
$add( 'page: subtitle (production only)', '/subtitle/' );
$add( 'page: subtitle with video param', '/subtitle/?video=1' );

// Date archives.
if ( $video_post ) {
	$time = strtotime( $video_post->post_date );
	$y    = (int) gmdate( 'Y', $time );
	$m    = (int) gmdate( 'm', $time );
	$d    = (int) gmdate( 'd', $time );
	$add_paged( 'date: year', get_year_link( $y ), array( 'year' => $y ), $per_page( 'archive' ) );
	$add( 'date: month', get_month_link( $y, $m ) );
	$add( 'date: day', get_day_link( $y, $m, $d ) );
}

// Categories.
$wordcamptv = get_category_by_slug( 'wordcamptv' );
if ( $wordcamptv ) {
	$add_paged( 'category: wordcamptv', get_category_link( $wordcamptv ), array( 'cat' => $wordcamptv->term_id ), $per_page( 'category' ) );
	$add( 'category: wordcamptv feed', trailingslashit( get_category_link( $wordcamptv ) ) . 'feed/' );
}
foreach ( array( 'how-to', 'learn-wordpress-online-workshops' ) as $slug ) {
	$cat = get_category_by_slug( $slug );
	if ( $cat ) {
		$add_paged( "category: $slug", get_category_link( $cat ), array( 'cat' => $cat->term_id ), $per_page( 'category' ) );
	}
}
foreach ( array( 'year', 'location' ) as $parent_slug ) {
	$parent = get_category_by_slug( $parent_slug );
	if ( ! $parent ) {
		continue;
	}
	$add( "category: $parent_slug (parent)", get_category_link( $parent ) );
	$children = get_categories( array( 'parent' => $parent->term_id, 'orderby' => 'count', 'order' => 'DESC', 'number' => 1 ) );
	if ( $children ) {
		$add_paged( "category: $parent_slug child", get_category_link( $children[0] ), array( 'cat' => $children[0]->term_id ), $per_page( 'category' ) );
	}
}

// Tags.
$tags = get_tags( array( 'orderby' => 'count', 'order' => 'DESC', 'number' => 1 ) );
if ( $tags ) {
	$add_paged( 'tag: most used', get_tag_link( $tags[0] ), array( 'tag_id' => $tags[0]->term_id ), $per_page( 'archive' ) );
}
$add( 'tag: featured', '/tag/featured/' );

// Theme taxonomies: the most used term, plus a term right above the page boundary.
foreach ( array( 'event', 'speakers', 'language', 'producer', 'producer-username', 'flavor' ) as $tax ) {
	$pp    = $per_page( 'event' === $tax ? 'event' : 'archive' );
	$terms = get_terms( array( 'taxonomy' => $tax, 'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => true ) );
	if ( is_wp_error( $terms ) || ! $terms ) {
		continue;
	}
	$args = array( 'tax_query' => array( array( 'taxonomy' => $tax, 'field' => 'term_id', 'terms' => $terms[0]->term_id ) ) );
	$add_paged( "$tax: most used", get_term_link( $terms[0] ), $args, $pp );

	foreach ( array_reverse( $terms ) as $term ) {
		if ( $term->count > $pp && $term->term_id !== $terms[0]->term_id ) {
			$args = array( 'tax_query' => array( array( 'taxonomy' => $tax, 'field' => 'term_id', 'terms' => $term->term_id ) ) );
			$add_paged( "$tax: just over boundary ({$term->count})", get_term_link( $term ), $args, $pp );
			break;
		}
	}

	// A slug with non-ASCII characters, if any.
	foreach ( $terms as $term ) {
		if ( str_contains( $term->slug, '%' ) ) {
			$add( "$tax: non-ASCII slug", get_term_link( $term ) );
			break;
		}
	}

	$add( "$tax: feed", trailingslashit( get_term_link( $terms[0] ) ) . 'feed/' );
	$add( "$tax: unknown term", "/$tax/this-term-does-not-exist/" );
}

// Post formats.
$formats = get_terms( array( 'taxonomy' => 'post_format', 'hide_empty' => true, 'number' => 1 ) );
if ( ! is_wp_error( $formats ) && $formats ) {
	$add( 'post format', get_term_link( $formats[0] ) );
}

// Authors.
$authors = get_users( array( 'has_published_posts' => array( 'post' ), 'number' => 1, 'orderby' => 'post_count', 'order' => 'DESC' ) );
if ( $authors ) {
	$add_paged( 'author', get_author_posts_url( $authors[0]->ID ), array( 'author' => $authors[0]->ID ), $per_page( 'archive' ) );
}

// Search.
$add( 'search: results', '/?s=wordcamp' );
$add( 'search: page 2', '/?s=wordcamp&paged=2' );
$add( 'search: pretty URL', '/search/wordcamp/' );
$add( 'search: no results', '/?s=zzqxnotfoundzzqx' );

// Feeds.
$add( 'feed: main', '/feed/' );
$add( 'feed: rss2 query var', '/?feed=rss2' );
$add( 'feed: comments', '/comments/feed/' );

// Redirects (wptv2 redirects()).
$add( 'redirect: /upload', '/upload' );
$add( 'redirect: /upload/', '/upload/' );
$add( 'redirect: social-learning', '/category/social-learning/' );
$add( 'redirect: social-learning page 2', '/category/social-learning/page/2/' );

// Endpoints.
$permalink = $video_post ? get_permalink( $video_post ) : home_url( '/' );
$guid      = '';
if ( $video_post && preg_match( '/\[wpvideo +([a-zA-Z0-9]+)/', $video_post->post_content, $m ) ) {
	$guid = $m[1];
}
$event = get_terms( array( 'taxonomy' => 'event', 'orderby' => 'count', 'order' => 'DESC', 'number' => 1 ) );

$add( 'api: videos.json', '/api/videos.json' );
$add( 'api: videos.array', '/api/videos.array' );
$add( 'api: videos.json posts_per_page', '/api/videos.json?posts_per_page=3' );
if ( ! is_wp_error( $event ) && $event ) {
	$add( 'api: videos.json by event', '/api/videos.json?event=' . $event[0]->slug );
}
$add( 'api: videos.json page 2', '/api/videos.json?paged=2' );
foreach ( array( 'events', 'speakers', 'languages', 'tags', 'categories' ) as $method ) {
	$add( "api: $method.json", "/api/$method.json" );
}
$add( 'api: invalid method', '/api/nothing.json' );
$add( 'api: invalid format', '/api/videos.xml' );

$add( 'oembed: json', '/oembed/?format=json&url=' . rawurlencode( $permalink ) );
$add( 'oembed: xml', '/oembed/?format=xml&url=' . rawurlencode( $permalink ) );
$add( 'oembed: maxwidth', '/oembed/?format=json&maxwidth=400&url=' . rawurlencode( $permalink ) );
$add( 'oembed: missing url', '/oembed/' );
$add( 'oembed: unknown url', '/oembed/?format=json&url=' . rawurlencode( home_url( '/nothing-here/' ) ) );
$add( 'oembed: core endpoint', '/wp-json/oembed/1.0/embed?url=' . rawurlencode( $permalink ) );

$add( 'unisubs: by url', '/unisubs/?url=' . rawurlencode( $permalink ) );
$add( 'unisubs: missing params', '/unisubs/' );
if ( $guid ) {
	$add( 'unisubs: by guid', '/unisubs/?guid=' . $guid );
	$add( 'guid redirect: home', '/?guid=' . $guid );
	$add( 'guid redirect: any URL', '/about/?guid=' . $guid );
}
$add( 'guid redirect: unknown guid', '/?guid=zzqxnotfound' );

$add( 'rest: speakers', '/wp-json/wp/v2/speakers?per_page=2&orderby=count&order=desc' );
$add( 'rest: language', '/wp-json/wp/v2/language?per_page=2&orderby=count&order=desc' );
$add( 'rest: event', '/wp-json/wp/v2/event?per_page=2&orderby=count&order=desc' );
$add( 'rest: posts filtered by event', '/wp-json/wp/v2/posts?per_page=2&_fields=id,link' . ( ( ! is_wp_error( $event ) && $event ) ? '&event=' . $event[0]->term_id : '' ) );

// Maintenance and not found.
$add( 'maintenance: flush_rules (anonymous)', '/?flush_rules=1' );
$add( 'not found: page', '/this-page-does-not-exist/' );
$add( 'not found: deep path', '/2010/01/01/this-post-does-not-exist/' );

// Write the list.
$lines = array( '# Generated by generate-urls.php on ' . gmdate( 'Y-m-d H:i' ) . ' UTC. Format: label<TAB>path' );
foreach ( $wptv_urls as $label => $path ) {
	$lines[] = $label . "\t" . $path;
}
$file = __DIR__ . '/urls.txt';
file_put_contents( $file, implode( "\n", $lines ) . "\n" );

WP_CLI::success( count( $wptv_urls ) . " URLs written to $file" );
