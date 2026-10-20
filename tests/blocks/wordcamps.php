<?php
/**
 * Focused WordCampTV checks; writes only the two disposable theme transients.
 * Synthetic recorded metadata is injected into one CLI-only SELECT, never into
 * posts or postmeta. Its transient write is suppressed; real caches are rebuilt
 * in finally. Optional prepare-browser mode exports two disposable HTML/JSON
 * fixtures under wp-content; no content, terms, menus or route baselines change.
 *
 * @package WordPressTV
 */

use WordPressTV\WordCamps as WC;

if ( 'wptv-27' !== get_stylesheet() ) {
	WP_CLI::error( 'Activate wptv-27 first.' );
}
$checks = 0;
$assert = static function ( $condition, $message ) use ( &$checks ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
	++$checks;
	WP_CLI::log( 'PASS ' . $message );
};
foreach ( array( 'WordCamp Madrid 2026' => 2026, 'Wordcamp Madrid 2026 for editors' => 2026, 'WordCamp 2000' => 2000, 'WordCamp 2099' => 2099, 'Meetup 2026' => 0, 'WordCamp 1999' => 0, 'WordCamp 2100' => 0, 'WordCamp 20260' => 0, 'WordCamp 2025–2026' => 0, 'WordCamp Madrid' => 0, 'WordCamp 2026 edition 2026' => 2026 ) as $name => $year ) {
	$assert( $year === WC\event_year( $name ), 'Name/year rule: ' . $name );
}
foreach ( array( '2024-02-29' => '2024-02-29', '2025-02-29' => '', '2026-13-01' => '', '2026-01-32' => '', '2026-1-01' => '', '2026-01-01 12:00:00' => '', '' => '' ) as $input => $expected ) {
	$assert( $expected === WC\recorded_date( array( 'recorded' => $input ) ), 'Strict recorded date: ' . ( $input ?: '(empty)' ) );
}
$assert( '' === WC\recorded_date( array() ) && '' === WC\recorded_date( array( 'recorded' => array() ) ), 'Missing or non-string recorded dates have no publication-date fallback.' );
$window = WC\recording_window( new DateTimeImmutable( '2026-10-20 23:59:59', wp_timezone() ) );
$assert( array( '2025-10-20', '2026-10-20' ) === $window, 'Window is twelve calendar months in the site timezone.' );
$groups = WC\group_directory( array( (object) array( 'term_id' => 2, 'name' => 'WordCamp Valencia 2026' ), (object) array( 'term_id' => 1, 'name' => 'WordCamp Madrid 2026' ), (object) array( 'term_id' => 3, 'name' => 'WordCamp US 2025' ), (object) array( 'term_id' => 4, 'name' => 'Meetup 2026' ) ) );
$assert( array( 2026 => array( 1, 2 ), 2025 => array( 3 ) ) === $groups, 'Years descend, names sort alphabetically and non-WordCamps are omitted.' );

$directory = WC\directory();
$ids = array_merge( array(), ...array_values( $directory ) );
$assert( count( $ids ) === count( array_unique( $ids ) ), 'Real directory has no duplicate events across years.' );
$assert( ! empty( $ids ), 'The local fixture has real qualifying WordCamp events.' );
$event = get_term( $ids[0], 'event' );
$event_args = WordPressTV\BlockQueries\resolve_terms( array( 'post_type' => 'post', 'post_status' => 'publish' ), new WP_Block( array( 'blockName' => 'core/post-template', 'attrs' => array() ), array( 'query' => array( 'wptvQuery' => 'wptv/wordcamp-event', 'wptvEvent' => $event->term_id ) ) ) );
$posts = get_posts( $event_args );
$assert( count( $posts ) > 0 && count( $posts ) <= 4, 'Event query returns at most four published category videos.' );
foreach ( $posts as $video ) {
	$assert( has_term( $event->term_id, 'event', $video ) && has_term( 'wordcamptv', 'category', $video ), 'Event card belongs to both required taxonomies.' );
}
$missing = WordPressTV\BlockQueries\resolve_terms( array(), new WP_Block( array( 'blockName' => 'core/post-template', 'attrs' => array() ), array( 'query' => array( 'wptvQuery' => 'wptv/wordcamp-event' ) ) ) );
$assert( array( 0 ) === $missing['post__in'], 'Missing event ID cannot broaden the independent query.' );

// Exercise the cold recent query without publishing a fictitious recording.
global $wpdb;
$fixture_id = $posts[0]->ID;
$today = current_datetime()->format( 'Y-m-d' );
$recording = $today;
$injected = false;
$inject = static function ( $sql ) use ( &$injected, $wpdb, $fixture_id, &$recording ) {
	if ( false !== strpos( $sql, 'SELECT m.meta_id, p.ID, m.meta_value' ) ) {
		$injected = true;
		return $wpdb->prepare( 'SELECT 1 AS meta_id, %d AS ID, %s AS meta_value', $fixture_id, serialize( array( 'recorded' => $recording ) ) );
	}
	return $sql;
};
$suppress_write = static function () { return false; };
try {
	WC\invalidate();
	add_filter( 'query', $inject );
	add_filter( 'pre_set_transient_' . WC\RECENT_KEY, $suppress_write );
	$recent = WC\recent();
	$assert( $injected && ( $recent[ $event->term_id ] ?? '' ) === $today, 'Cold metadata query produces the qualifying event and recorded date.' );
	// Without an external object cache, WordPress stores boolean false as ''.
	$assert( in_array( get_transient( WC\RECENT_KEY ), array( false, '' ), true ), 'Synthetic recordings are never written to the cache.' );
	list( $cutoff ) = WC\recording_window();
	$outside = ( new DateTimeImmutable( $cutoff, wp_timezone() ) )->modify( '-1 day' )->format( 'Y-m-d' );
	foreach ( array( $cutoff => true, $outside => false, current_datetime()->modify( '+1 day' )->format( 'Y-m-d' ) => false, '2026-02-30' => false ) as $recording => $included ) {
		$result = WC\recent();
		$assert( isset( $result[ $event->term_id ] ) === $included, 'Recent query enforces the exact date boundary: ' . $recording );
	}
	remove_filter( 'query', $inject );
	remove_filter( 'pre_set_transient_' . WC\RECENT_KEY, $suppress_write );
	set_transient( WC\RECENT_KEY, array(), HOUR_IN_SECONDS );
	set_transient( WC\DIRECTORY_KEY, $directory, DAY_IN_SECONDS );
	WC\metadata_changed( 0, $fixture_id, '_wptv_submitted_video' );
	$assert( false === get_transient( WC\RECENT_KEY ) && $directory === get_transient( WC\DIRECTORY_KEY ), 'Recorded metadata invalidates recent only, not the name-based directory.' );
	set_transient( WC\RECENT_KEY, array(), HOUR_IN_SECONDS );
	WC\relationships_changed( $fixture_id, array(), 'category' );
	$assert( false === get_transient( WC\RECENT_KEY ) && false === get_transient( WC\DIRECTORY_KEY ), 'Category departures invalidate both indexes without requiring current membership.' );
	set_transient( WC\RECENT_KEY, array(), HOUR_IN_SECONDS );
	set_transient( WC\DIRECTORY_KEY, $directory, DAY_IN_SECONDS );
	WC\term_changed( $event->term_id, $event->term_taxonomy_id, 'event' );
	$assert( false === get_transient( WC\RECENT_KEY ) && false === get_transient( WC\DIRECTORY_KEY ), 'Event rename/year/deletion invalidates both indexes.' );
	set_transient( WC\RECENT_KEY, array(), HOUR_IN_SECONDS );
	set_transient( WC\DIRECTORY_KEY, $directory, DAY_IN_SECONDS );
	WC\status_changed( 'draft', 'publish', new WP_Post( (object) array( 'post_type' => 'post' ) ) );
	$assert( false === get_transient( WC\RECENT_KEY ) && false === get_transient( WC\DIRECTORY_KEY ), 'Unpublishing clears both indexes even after the new status is draft.' );
} finally {
	remove_filter( 'query', $inject );
	remove_filter( 'pre_set_transient_' . WC\RECENT_KEY, $suppress_write );
	WC\invalidate();
	WC\recent();
	WC\directory();
}
$assert( has_action( 'deleted_term_relationships', 'WordPressTV\\WordCamps\\relationships_changed' ) !== false, 'Direct relationship removals have an invalidation hook.' );
$assert( has_action( 'deleted_post_meta', 'WordPressTV\\WordCamps\\metadata_changed' ) !== false, 'Direct recorded metadata deletion has an invalidation hook.' );
$original_query = $GLOBALS['wp_query'];
$original_post = $GLOBALS['post'] ?? null;
$markup = WC\recent_markup( array( $event->term_id => $today ) ) . WC\directory_markup( $directory );
$html = do_blocks( $markup );
$assert( $original_query === $GLOBALS['wp_query'] && $original_post === ( $GLOBALS['post'] ?? null ), 'Independent core blocks restore main-query and post globals.' );
$assert( false === strpos( $html, 'WPTV_EVENT_NAME' ) && false === strpos( $html, 'WPTV_YEAR' ) && false === strpos( $html, '#wptv-event' ), 'Prototype placeholders never leak into rendered output.' );
if ( in_array( 'prepare-browser', $args ?? array(), true ) ) {
	$expected = array( 'groups' => $directory, 'event_url' => get_term_link( $event ) );
	foreach ( array( 1, 2 ) as $page ) {
		$q = new WP_Query( array( 'category_name' => 'wordcamptv', 'post_status' => 'publish', 'posts_per_page' => 22, 'paged' => $page ) );
		$expected['pages'][ $page ] = wp_list_pluck( $q->posts, 'ID' );
	}
	file_put_contents( ABSPATH . 'wp-content/wptv-wordcamps-expected.json', wp_json_encode( $expected ) );
	file_put_contents( ABSPATH . 'wp-content/wptv-wordcamp-preview.html', do_blocks( WC\recent_markup( array( $event->term_id => $today ) ) ) );
	WP_CLI::log( 'Prepared two disposable browser fixtures under wp-content; remove them after the browser checks.' );
}
WP_CLI::success( $checks . ' WordCampTV checks passed; no content or terms changed.' );
