<?php
/**
 * Popped Article Discovery pattern.
 *
 * @package Popped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'name'          => 'article-discovery',
	'title'         => __( 'Popped Article Discovery', 'popped' ),
	'description'   => __( 'A purposeful article endcap with one next story followed by related reading.', 'popped' ),
	'categories'    => array( 'popped/patterns' ),
	'keywords'      => array( 'article', 'continue', 'related' ),
	'viewportWidth' => 1440,
	'content'       => <<<'HTML'
<!-- wp:group {"align":"wide","className":"popped-quality-pattern popped-pattern popped-pattern--article-discovery","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide popped-quality-pattern popped-pattern popped-pattern--article-discovery">
<!-- wp:popped/continue-story {"selectionMode":"automatic","className":"popped-pattern-embedded"} /-->
<!-- wp:popped/related-stories {"selectionMode":"automatic","relevance":"both","count":3,"displayLayout":"cards","columns":3,"className":"popped-pattern-embedded"} /-->
</div>
<!-- /wp:group -->
HTML,
);
