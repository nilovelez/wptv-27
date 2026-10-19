<?php
/**
 * Slug-based taxonomy constraints for theme-owned Query blocks.
 *
 * @package WordPressTV
 */

namespace WordPressTV\BlockQueries;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves the theme's named queries without persisting environment-specific IDs.
 *
 * The inherited main query is deliberately excluded. Missing terms must return
 * no posts rather than silently broadening a featured/category query.
 *
 * @param array     $query Query arguments.
 * @param \WP_Block $block Post Template block, including its Query context.
 * @return array
 */
function resolve_terms( $query, $block ) {
	$context = $block->context['query'] ?? array();
	if ( ! empty( $context['inherit'] ) ) {
		return $query;
	}

	$constraints = array(
		'wptv/featured' => array( 'post_tag' => 'featured' ),
		'wptv/wordcamp' => array( 'category' => 'wordcamptv' ),
	);
	// Core provides the query object as context, but not the namespace attribute.
	$namespace = $context['wptvQuery'] ?? '';
	if ( ! isset( $constraints[ $namespace ] ) ) {
		return $query;
	}

	$tax_query = array( 'relation' => 'AND' );
	foreach ( $constraints[ $namespace ] as $taxonomy => $slug ) {
		$term = get_term_by( 'slug', $slug, $taxonomy );
		if ( ! $term instanceof \WP_Term ) {
			$query['post__in'] = array( 0 );
			return $query;
		}
		$tax_query[] = array(
			'taxonomy' => $taxonomy,
			'field'    => 'term_id',
			'terms'    => array( $term->term_id ),
		);
	}
	$query['tax_query'] = ! empty( $query['tax_query'] )
		? array( 'relation' => 'AND', $query['tax_query'], $tax_query )
		: $tax_query;
	return $query;
}
add_filter( 'query_loop_block_query_vars', __NAMESPACE__ . '\resolve_terms', 10, 2 );
