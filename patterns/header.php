<?php
/**
 * Title: Header
 * Slug: wptv/header
 * Categories: header
 * Block Types: core/template-part/header
 * Inserter: no
 *
 * Site header. Navigation links are provisional until the production menus
 * are known (docs/decisions.md).
 *
 * @package WordPressTV
 */

?>
<!-- wp:group {"align":"full","className":"wptv-header","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull wptv-header"><!-- wp:group {"align":"wide","className":"wptv-header__bar","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
<div class="wp-block-group alignwide wptv-header__bar"><!-- wp:site-title {"level":0} /-->

<!-- wp:group {"className":"wptv-header__tools","layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"}} -->
<div class="wp-block-group wptv-header__tools"><!-- wp:navigation {"overlayMenu":"mobile","layout":{"type":"flex","flexWrap":"wrap"}} -->
<!-- wp:navigation-link {"label":"<?php echo esc_attr__( 'WordCamp.tv', 'wptv' ); ?>","url":"/category/wordcamptv/","kind":"custom"} /-->

<!-- wp:navigation-link {"label":"<?php echo esc_attr__( 'How-To', 'wptv' ); ?>","url":"/category/how-to/","kind":"custom"} /-->

<!-- wp:navigation-link {"label":"<?php echo esc_attr__( 'Learn WordPress', 'wptv' ); ?>","url":"/category/learn-wordpress/","kind":"custom"} /-->

<!-- wp:navigation-link {"label":"<?php echo esc_attr__( 'Get Involved', 'wptv' ); ?>","url":"/get-involved/","kind":"custom"} /-->

<!-- wp:navigation-link {"label":"<?php echo esc_attr__( 'About', 'wptv' ); ?>","url":"/about/","kind":"custom"} /-->
<!-- /wp:navigation -->

<!-- wp:search {"label":"<?php echo esc_attr__( 'Search', 'wptv' ); ?>","showLabel":false,"placeholder":"<?php echo esc_attr__( 'Search WordPress.tv', 'wptv' ); ?>","buttonText":"<?php echo esc_attr__( 'Search', 'wptv' ); ?>","buttonPosition":"button-only","buttonUseIcon":true,"isSearchFieldHidden":true} /-->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"wptv-header__submit"} -->
<div class="wp-block-button wptv-header__submit"><a class="wp-block-button__link wp-element-button" href="/submit-video/"><?php esc_html_e( 'Submit a video', 'wptv' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
