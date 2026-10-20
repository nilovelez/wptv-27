<?php
/**
 * Title: Category and event spotlight
 * Slug: wptv/taxonomy-spotlight
 * Inserter: no
 *
 * @package WordPressTV
 */

?>
<!-- wp:group {"align":"full","className":"wptv-taxonomy__spotlight","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull wptv-taxonomy__spotlight"><!-- wp:columns {"align":"wide","className":"wptv-taxonomy__columns"} -->
<div class="wp-block-columns alignwide wptv-taxonomy__columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:query {"query":{"inherit":true}} -->
<div class="wp-block-query"><!-- wp:post-template {"className":"wptv-taxonomy__lead"} -->
<!-- wp:group {"className":"is-style-video-frame","backgroundColor":"coal-2","textColor":"white","layout":{"type":"default"}} -->
<div class="wp-block-group is-style-video-frame has-white-color has-coal-2-background-color has-text-color has-background"><!-- wp:post-content {"layout":{"type":"default"}} /-->
<!-- wp:group {"className":"wptv-home__lead-caption","layout":{"type":"default"}} -->
<div class="wp-block-group wptv-home__lead-caption"><!-- wp:post-title {"level":2,"isLink":true,"fontSize":"medium"} /-->
<!-- wp:post-terms {"term":"event","className":"wptv-taxonomy__category-events"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
<!-- /wp:post-template --></div>
<!-- /wp:query --></div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column"><!-- wp:query {"query":{"inherit":true}} -->
<div class="wp-block-query"><!-- wp:post-template {"className":"wptv-taxonomy__secondary"} -->
<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9","className":"is-style-video-frame"} /-->
<!-- wp:group {"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:post-title {"level":2,"isLink":true,"fontSize":"small"} /-->
<!-- wp:post-terms {"term":"event","className":"wptv-taxonomy__category-events"} /--></div>
<!-- /wp:group -->
<!-- /wp:post-template --></div>
<!-- /wp:query --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
