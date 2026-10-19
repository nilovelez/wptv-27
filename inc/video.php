<?php
/**
 * VideoPress helpers.
 *
 * Ported from the wptv2 WordPressTV_Theme class. VideoPress data functions
 * (video_get_info_by_guid() and friends) only exist on WordPress.com, so every
 * call is guarded and degrades to "no data" elsewhere (docs/wpcom-constraints.md).
 *
 * @package WordPressTV
 */

namespace WordPressTV\Video;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the VideoPress GUID of a post's [wpvideo] shortcode.
 *
 * When there are several shortcodes, the last one wins (same as wptv2).
 *
 * @param int|\WP_Post|null $post Post. Defaults to the current post.
 * @return string|false
 */
function get_guid( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return false;
	}

	$guid = false;
	if ( preg_match_all( '/\[wpvideo +([a-zA-Z0-9,\#,\&,\/,;,",=, ]*?)\]/i', $post->post_content, $matches ) ) {
		foreach ( $matches[1] as $code ) {
			if ( preg_match( '/([0-9A-Za-z]+)/i', $code, $m ) ) {
				$guid = $m[1];
			}
		}
	}

	return $guid;
}

/**
 * Returns the VideoPress details of a post's video.
 *
 * @param int|\WP_Post|null $post Post. Defaults to the current post.
 * @return object|false
 */
function get_details( $post = null ) {
	$guid = get_guid( $post );
	if ( ! $guid || ! function_exists( 'video_get_info_by_guid' ) ) {
		return false;
	}
	return video_get_info_by_guid( $guid );
}

/**
 * Returns the URL of a post's video thumbnail.
 *
 * @param int|\WP_Post|null $post Post. Defaults to the current post.
 * @return string|false
 */
function get_image_url( $post = null ) {
	$guid = get_guid( $post );
	if ( ! $guid || ! function_exists( 'video_get_highest_resolution_image_url' ) ) {
		return false;
	}
	return video_get_highest_resolution_image_url( $guid );
}

/**
 * Supplies VideoPress thumbnails to the core Featured Image block.
 *
 * Keeps core's image/link wrapper, accessibility attributes and aspect ratio.
 * Existing featured images remain the fallback for non-video posts.
 *
 * @param string       $html          Existing thumbnail HTML.
 * @param int          $post_id       Post ID.
 * @param int          $thumbnail_id  Attachment ID (unused).
 * @param string|int[] $size          Requested size (unused).
 * @param string|array $attr          Image attributes from core.
 * @return string
 */
function thumbnail_html( $html, $post_id, $thumbnail_id, $size, $attr ) {
	if ( 'post' !== get_post_type( $post_id ) ) {
		return $html;
	}
	$url = get_image_url( $post_id );
	if ( ! $url && $html ) {
		return $html;
	}
	$url = $url ?: get_theme_file_uri( 'assets/images/placeholder.png' );
	$attributes = wp_parse_args( $attr, array(
		'alt'      => '',
		'loading'  => 'lazy',
		'decoding' => 'async',
	) );
	$image = new \WP_HTML_Tag_Processor( '<img>' );
	$image->next_tag();
	$image->set_attribute( 'src', esc_url_raw( $url ) );
	foreach ( array( 'alt', 'class', 'width', 'height', 'loading', 'decoding', 'style' ) as $name ) {
		if ( isset( $attributes[ $name ] ) ) {
			$value = 'style' === $name ? safecss_filter_attr( $attributes[ $name ] ) : $attributes[ $name ];
			$image->set_attribute( $name, $value );
		}
	}
	return $image->get_updated_html();
}
add_filter( 'post_thumbnail_html', __NAMESPACE__ . '\thumbnail_html', 10, 5 );

/**
 * Returns the URLs of a post's transcoded video files, best quality first.
 *
 * @param int|\WP_Post|null $post Post. Defaults to the current post.
 * @return string[]|null Null when the video has no details (same as wptv2).
 */
function get_file_urls( $post = null ) {
	$details = get_details( $post );
	if ( ! $details ) {
		return null;
	}

	$urls = array();
	if ( function_exists( 'video_url_by_format' ) ) {
		foreach ( array( 'fmt_hd', 'fmt_dvd', 'fmt_std', 'fmt1_ogg' ) as $format ) {
			$url = video_url_by_format( $details, $format );
			if ( $url ) {
				$urls[] = $url;
			}
		}
	}

	return $urls;
}

/**
 * Returns the original uploaded video attachment of a post.
 *
 * @param int|\WP_Post|null $post Post. Defaults to the current post.
 * @return \WP_Post|false
 */
function get_attachment( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return false;
	}

	$videos = get_children(
		array(
			'post_parent'    => $post->ID,
			'post_mime_type' => 'video/%',
		)
	);

	return $videos ? reset( $videos ) : false;
}

/**
 * Returns the URL of the original uploaded video file of a post.
 *
 * @param int|\WP_Post|null $post Post. Defaults to the current post.
 * @return string|false
 */
function get_attachment_url( $post = null ) {
	$attachment = get_attachment( $post );
	return $attachment ? wp_get_attachment_url( $attachment->ID ) : false;
}

/**
 * Converts a `language` term slug to a WordPress locale.
 *
 * Mapping copied from wptv2 (see https://meta.trac.wordpress.org/ticket/1156).
 * It is known to be incomplete.
 *
 * @param string $slug Language term slug.
 * @return string|false
 */
function language_to_locale( $slug ) {
	static $locales = array(
		'bengali'                 => 'bn_BD',
		'bulgarianбългарски-език' => 'bg_BG',
		'catalan'                 => 'ca',
		'croatianhrvatski'        => 'hr',
		'czechcestina'            => 'cs_CZ',
		'danishdansk'             => 'da_DK',
		'dutchnederlands'         => 'nl_NL',
		'english'                 => 'en',
		'english-swahili'         => 'sw',
		'english-and-dutch'       => 'nl_NL',
		'finnishsuomi'            => 'fi',
		'frenchfrancais'          => 'fr_FR',
		'germandeutsch'           => 'de_DE',
		'greek-ελληνικά'          => 'el',
		'gujarati'                => 'gu',
		'hebrewעברית'             => 'he_IL',
		'hindi'                   => 'hi_IN',
		'indonesian'              => 'id_ID',
		'italianitaliano'         => 'it_IT',
		'ελληνικά'                => 'el',
		'japanese'                => 'ja',
		'japanese-and-english'    => 'ja',
		'japanese日本語'          => 'ja',
		'kananda'                 => 'kn',
		'lithuanian'              => 'lt_LT',
		'malay'                   => 'ms_MY',
		'malay-bahasa-melayu'     => 'ms_MY',
		'malayalam'               => 'ms_MY',
		'marathi'                 => 'mr',
		'maori'                   => 'mri',
		'nepaliनेपाली'            => 'ne_NP',
		'norwegiannorsk'          => 'nb_NO',
		'persian-farsi'           => 'fa_IR',
		'polishpolski'            => 'pl_PL',
		'portugueseportugues'     => 'pt_BR',
		'romanianromana'          => 'ro_RO',
		'russianрусский'          => 'ru_RU',
		'serbianсрпски'           => 'sr_RS',
		'slovakslovencina'        => 'sk_SK',
		'spanishespanol'          => 'es_ES',
		'swedishsvenska'          => 'sv_SE',
		'thai'                    => 'th',
		'traditional-chinese'     => 'zh_CN',
		'ukrainianукраїнська'     => 'uk',
		'urdu'                    => 'ur',
		'urdu-and-english'        => 'ur',
		'vietnamese'              => 'vi',
	);

	return $locales[ $slug ] ?? false;
}
