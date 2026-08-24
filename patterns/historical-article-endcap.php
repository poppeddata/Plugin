<?php
/**
 * Popped Historical Endcap pattern.
 *
 * @package Popped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'name'          => 'historical-article-endcap',
	'title'         => __( 'Popped Historical Endcap', 'popped' ),
	'description'   => __( 'Same-date context followed by previous and next timeline navigation.', 'popped' ),
	'categories'    => array( 'popped/patterns' ),
	'keywords'      => array( 'article', 'history', 'timeline' ),
	'viewportWidth' => 1440,
	'content'       => <<<'HTML'
<!-- wp:group {"align":"wide","className":"popped-quality-pattern popped-pattern popped-pattern--historical-endcap","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide popped-quality-pattern popped-pattern popped-pattern--historical-endcap">
<!-- wp:popped/also-on-this-day {"title":"Also On This Day","count":4,"order":"chronological","className":"popped-pattern-embedded"} /-->
<!-- wp:popped/timeline-navigation {"className":"popped-pattern-embedded"} /-->
</div>
<!-- /wp:group -->
HTML,
);
