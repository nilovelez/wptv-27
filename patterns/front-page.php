<?php
/**
 * Title: Front page
 * Slug: wptv/front-page
 * Inserter: no
 *
 * @package WordPressTV
 */

?>
<!-- wp:group {"tagName":"main","align":"full","className":"is-flush wptv-home","layout":{"type":"default"}} -->
<main class="wp-block-group alignfull is-flush wptv-home"><!-- wp:group {"align":"full","className":"wptv-home__welcome","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull wptv-home__welcome"><!-- wp:columns {"align":"wide","verticalAlignment":"center"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading"><?php esc_html_e( 'Welcome to WordPress.tv', 'wptv' ); ?></h1>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php esc_html_e( 'WordPress-related videos, curated and moderated by the WordPress community.', 'wptv' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:query {"query":{"perPage":1,"pages":1,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":false,"sticky":"ignore"}} -->
<div class="wp-block-query"><!-- wp:post-template -->
<!-- wp:group {"className":"is-style-video-frame","backgroundColor":"coal-2","textColor":"white","layout":{"type":"default"}} -->
<div class="wp-block-group is-style-video-frame has-white-color has-coal-2-background-color has-text-color has-background"><!-- wp:post-content {"layout":{"type":"default"}} /-->
<!-- wp:group {"className":"wptv-home__lead-caption","layout":{"type":"default"}} -->
<div class="wp-block-group wptv-home__lead-caption"><!-- wp:post-terms {"term":"speakers","suffix":":"} /-->
<!-- wp:post-title {"level":2,"isLink":true,"fontSize":"medium"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
<!-- /wp:post-template --></div>
<!-- /wp:query --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
<!-- wp:group {"align":"full","className":"wptv-home__featured","backgroundColor":"coal-2","textColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull wptv-home__featured has-white-color has-coal-2-background-color has-text-color has-background"><!-- wp:heading {"align":"wide","textColor":"white"} -->
<h2 class="wp-block-heading alignwide has-white-color has-text-color"><?php esc_html_e( 'Featured videos', 'wptv' ); ?></h2>
<!-- /wp:heading -->
<!-- wp:query {"namespace":"wptv/featured","query":{"wptvQuery":"wptv/featured","perPage":4,"pages":1,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":false,"sticky":"ignore"},"align":"wide"} -->
<div class="wp-block-query alignwide"><!-- wp:post-template {"className":"wptv-home__videos","layout":{"type":"grid","columnCount":4}} -->
<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9","className":"is-style-video-frame"} /-->
<!-- wp:post-terms {"term":"speakers","suffix":":"} /-->
<!-- wp:post-title {"level":3,"isLink":true,"fontSize":"small"} /-->
<!-- /wp:post-template -->
<!-- wp:query-no-results -->
<!-- wp:paragraph -->
<p><?php esc_html_e( 'No featured videos were found.', 'wptv' ); ?></p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query --></div>
<!-- /wp:group -->
<!-- wp:group {"align":"full","className":"wptv-home__events","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull wptv-home__events"><!-- wp:heading {"align":"wide"} -->
<h2 class="wp-block-heading alignwide"><?php esc_html_e( 'Recent events', 'wptv' ); ?></h2>
<!-- /wp:heading -->
<!-- wp:terms-query {"termQuery":{"perPage":12,"taxonomy":"event","order":"desc","orderBy":"id","include":[],"hideEmpty":true,"showNested":false,"inherit":false},"align":"wide"} -->
<div class="wp-block-terms-query alignwide"><!-- wp:term-template {"className":"wptv-home__events-grid","layout":{"type":"grid","columnCount":3}} -->
<!-- wp:group {"className":"is-style-event-tile","backgroundColor":"gray","layout":{"type":"default"}} -->
<div class="wp-block-group is-style-event-tile has-gray-background-color has-background"><!-- wp:term-name {"level":3,"isLink":true,"fontSize":"medium"} /--></div>
<!-- /wp:group -->
<!-- /wp:term-template --></div>
<!-- /wp:terms-query --></div>
<!-- /wp:group -->
<!-- wp:group {"align":"full","className":"wptv-home__categories","backgroundColor":"gray","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull wptv-home__categories has-gray-background-color has-background"><!-- wp:heading {"align":"wide"} -->
<h2 class="wp-block-heading alignwide"><?php esc_html_e( 'Video categories', 'wptv' ); ?></h2>
<!-- /wp:heading -->
<!-- wp:terms-query {"termQuery":{"perPage":0,"taxonomy":"category","order":"desc","orderBy":"count","include":[],"hideEmpty":true,"showNested":false,"inherit":false},"align":"wide"} -->
<div class="wp-block-terms-query alignwide"><!-- wp:term-template {"className":"wptv-home__categories-grid","layout":{"type":"grid","columnCount":4}} -->
<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
<div class="wp-block-group"><!-- wp:term-name {"level":3,"isLink":true,"fontSize":"small"} /-->
<!-- wp:term-count /--></div>
<!-- /wp:group -->
<!-- /wp:term-template --></div>
<!-- /wp:terms-query --></div>
<!-- /wp:group -->
<!-- wp:group {"align":"full","className":"wptv-home__recent","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull wptv-home__recent"><!-- wp:heading {"align":"wide"} -->
<h2 class="wp-block-heading alignwide"><?php esc_html_e( 'Recent videos', 'wptv' ); ?></h2>
<!-- /wp:heading -->
<!-- wp:query {"query":{"inherit":true},"align":"wide"} -->
<div class="wp-block-query alignwide"><!-- wp:post-template {"className":"wptv-home__videos","layout":{"type":"grid","columnCount":4}} -->
<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9","className":"is-style-video-frame"} /-->
<!-- wp:post-terms {"term":"speakers","suffix":":"} /-->
<!-- wp:post-title {"level":3,"isLink":true,"fontSize":"small"} /-->
<!-- /wp:post-template -->
<!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"center"}} -->
<!-- wp:query-pagination-previous /-->
<!-- wp:query-pagination-numbers /-->
<!-- wp:query-pagination-next /-->
<!-- /wp:query-pagination -->
<!-- wp:query-no-results -->
<!-- wp:paragraph -->
<p><?php esc_html_e( 'No videos were found.', 'wptv' ); ?></p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query --></div>
<!-- /wp:group --></main>
<!-- /wp:group -->
