<?php
/**
 * Open Graph and Twitter Card tags for videos (via Jetpack).
 *
 * Ported from wptv2's wordpresstv-open-graph plugin.
 *
 * @package WordPressTV
 */

namespace WordPressTV\OpenGraph;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds player and MP4 tags to posts that embed a VideoPress video.
 *
 * Runs after wpcom_twitter_cards_tags (priority 11).
 *
 * @param array $tags Open Graph tags.
 * @return array
 */
function customize_tags( $tags ) {
	$post = get_post();
	if ( ! $post instanceof \WP_Post || ! function_exists( 'find_all_videopress_shortcodes' ) || ! function_exists( 'video_get_info_by_guid' ) ) {
		return $tags;
	}

	$guids = array_keys( find_all_videopress_shortcodes( $post->post_content ) );
	if ( ! $guids ) {
		return $tags;
	}

	$info                   = video_get_info_by_guid( $guids[0] );
	list( $width, $height ) = wp_expand_dimensions( $info->width ?? null, $info->height ?? null, 560, 315 );
	$mp4_url                = set_url_scheme( wp_get_attachment_url( $info->post_id ?? null ), 'https' );

	// Twitter player card.
	$tags['twitter:card']                       = 'player';
	$tags['twitter:player']                     = sprintf( 'https://videopress.com/v/%s?autoplay=0', $guids[0] );
	$tags['twitter:player:width']               = $width;
	$tags['twitter:player:height']              = $height;
	$tags['twitter:player:stream']              = $mp4_url;
	$tags['twitter:player:stream:content_type'] = 'video/mp4';

	// MP4 instead of the Flash player on Facebook. Can go once https://github.com/Automattic/jetpack/issues/2950 is fixed.
	$tags['og:video:type']       = 'video/mp4';
	$tags['og:video']            = $mp4_url;
	$tags['og:video:secure_url'] = $mp4_url;

	return $tags;
}
add_filter( 'jetpack_open_graph_tags', __NAMESPACE__ . '\customize_tags', 11 );
