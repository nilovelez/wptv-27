<?php
/**
 * Title: Footer
 * Slug: wptv/footer
 * Categories: footer
 * Block Types: core/template-part/footer
 * Inserter: no
 *
 * Site footer. Links are provisional until the production menus are known.
 *
 * @package WordPressTV
 */

?>
<!-- wp:group {"align":"full","className":"wptv-footer","backgroundColor":"coal","textColor":"gray","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull wptv-footer has-gray-color has-coal-background-color has-text-color has-background"><!-- wp:columns {"align":"wide","className":"wptv-footer__columns"} -->
<div class="wp-block-columns alignwide wptv-footer__columns"><!-- wp:column {"width":"40%"} -->
<div class="wp-block-column" style="flex-basis:40%"><!-- wp:site-title {"level":0} /-->

<!-- wp:paragraph {"fontSize":"small"} -->
<p class="has-small-font-size"><?php esc_html_e( 'Talks, workshops and tutorials from the WordPress community, from WordCamps and meetups all over the world.', 'wptv' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":2,"className":"wptv-footer__heading"} -->
<h2 class="wp-block-heading wptv-footer__heading"><?php esc_html_e( 'Contribute', 'wptv' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:list {"className":"wptv-footer__links"} -->
<ul class="wp-block-list wptv-footer__links"><!-- wp:list-item -->
<li><a href="/submit-video/"><?php esc_html_e( 'Submit a video', 'wptv' ); ?></a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="/get-involved/"><?php esc_html_e( 'Get involved', 'wptv' ); ?></a></li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":2,"className":"wptv-footer__heading"} -->
<h2 class="wp-block-heading wptv-footer__heading"><?php esc_html_e( 'About', 'wptv' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:list {"className":"wptv-footer__links"} -->
<ul class="wp-block-list wptv-footer__links"><!-- wp:list-item -->
<li><a href="/about/"><?php esc_html_e( 'About WordPress.tv', 'wptv' ); ?></a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="https://wordpress.org/about/privacy/"><?php esc_html_e( 'Privacy', 'wptv' ); ?></a></li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":2,"className":"wptv-footer__heading"} -->
<h2 class="wp-block-heading wptv-footer__heading"><?php esc_html_e( 'WordPress', 'wptv' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:list {"className":"wptv-footer__links"} -->
<ul class="wp-block-list wptv-footer__links"><!-- wp:list-item -->
<li><a href="https://wordpress.org/">WordPress.org</a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="https://make.wordpress.org/">Make WordPress</a></li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:group {"align":"wide","className":"wptv-footer__license","layout":{"type":"flex","flexWrap":"wrap","verticalAlignment":"center"}} -->
<div class="wp-block-group alignwide wptv-footer__license"><!-- wp:image {"width":"88px","sizeSlug":"full","linkDestination":"custom"} -->
<figure class="wp-block-image size-full is-resized"><a href="https://creativecommons.org/licenses/by-sa/4.0/"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/cc-by-sa.png' ) ); ?>" alt="<?php esc_attr_e( 'Creative Commons Attribution-ShareAlike license', 'wptv' ); ?>" style="width:88px"/></a></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"fontSize":"small"} -->
<p class="has-small-font-size"><?php esc_html_e( 'Unless noted otherwise, videos are licensed under Creative Commons Attribution-ShareAlike.', 'wptv' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
