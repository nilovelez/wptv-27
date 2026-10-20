<?php
/**
 * Read-only breadcrumb policy checks. Run with Studio WP-CLI:
 * eval-file wp-content/themes/wptv-27/tests/blocks/breadcrumbs.php
 *
 * Synthetic terms stay in memory; no posts, terms, relationships or caches change.
 * HTTP coverage additionally checks the State of the Word single and taxonomy
 * archives, including existing paginated event URLs.
 *
 * @package WordPressTV
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "Run this file with WP-CLI eval-file.\n" );
}

$cases = array(
	array( array(), false, array() ),
	array( array( 'year', '2023', 'location' ), false, array() ),
	array( array( 'wordcamptv' ), false, array( 'wordcamptv' ) ),
	array( array( 'education' ), false, array( 'education' ) ),
	array( array( 'how-to' ), false, array( 'how-to' ) ),
	array( array( 'how-to', 'education' ), false, array( 'education' ) ),
	array( array( 'wordcamptv', 'education' ), false, array( 'education' ) ),
	array( array( 'wordcamptv', 'how-to' ), false, array( 'how-to' ) ),
	array( array( 'how-to', 'wordcamptv', 'education' ), false, array( 'education' ) ),
	array( array(), true, array( 'test-event' ) ),
	array( array( 'year', '2023' ), true, array( 'test-event' ) ),
	array( array( 'education' ), true, array( 'test-event' ) ),
	array( array( 'how-to' ), true, array( 'test-event' ) ),
	array( array( 'how-to', 'education' ), true, array( 'test-event' ) ),
	array( array( 'wordcamptv' ), true, array( 'wordcamptv', 'test-event' ) ),
	array( array( 'education', 'wordcamptv' ), true, array( 'wordcamptv', 'test-event' ) ),
	array( array( 'education', 'wordcamptv', 'how-to' ), true, array( 'wordcamptv', 'test-event' ) ),
);
$make_term = static function ( $slug, $taxonomy = 'category' ) {
	return new WP_Term( (object) array( 'slug' => $slug, 'name' => $slug, 'taxonomy' => $taxonomy ) );
};
foreach ( $cases as $index => $case ) {
	$categories = array_map( $make_term, $case[0] );
	$events     = $case[1] ? array( $make_term( 'test-event', 'event' ), $make_term( 'second-event', 'event' ) ) : array();
	$before     = serialize( array( $categories, $events ) );
	$actual     = wp_list_pluck( WordPressTV\Setup\breadcrumb_terms( $categories, $events ), 'slug' );
	if ( $case[2] !== $actual || $before !== serialize( array( $categories, $events ) ) ) {
		throw new RuntimeException( 'Breadcrumb case ' . $index . ' failed: ' . wp_json_encode( $actual ) );
	}
}
$events = array( $make_term( 'second-event', 'event' ), $make_term( 'test-event', 'event' ) );
if ( array( 'second-event' ) !== wp_list_pluck( WordPressTV\Setup\breadcrumb_terms( array(), $events ), 'slug' ) ) {
	throw new RuntimeException( 'Native first-event ordering was not preserved.' );
}
echo "18 breadcrumb selection checks passed; no stored data changed.\n";
