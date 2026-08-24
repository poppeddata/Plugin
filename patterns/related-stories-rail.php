<?php
/**
 * Popped Related Stories Rail pattern.
 *
 * @package Popped
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
	'name'          => 'related-stories-rail',
	'title'         => __( 'Popped Related Story Rail', 'popped' ),
	'description'   => __( 'A compact swipeable related-reading endcap that preserves article flow.', 'popped' ),
	'categories'    => array( 'popped/patterns' ),
	'keywords'      => array( 'related', 'stories', 'rail', 'article' ),
	'viewportWidth' => 1440,
	'content'       => <<<'HTML'
<!-- wp:popped/related-stories {"selectionMode":"automatic","relevance":"both","count":5,"displayLayout":"rail","showExcerpt":false,"className":"popped-pattern-embedded"} /-->
HTML,
);
