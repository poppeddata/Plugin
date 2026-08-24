<?php
/**
 * Popped Related Stories pattern.
 *
 * @package Popped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'name'          => 'related-stories-grid',
	'title'         => __( 'Popped Related Stories', 'popped' ),
	'description'   => __( 'A clean three-story recommendation grid for the end of an article.', 'popped' ),
	'categories'    => array( 'popped/patterns' ),
	'keywords'      => array( 'related', 'stories', 'article' ),
	'viewportWidth' => 1440,
	'content'       => <<<'HTML'
<!-- wp:popped/related-stories {"selectionMode":"automatic","relevance":"both","count":3,"displayLayout":"cards","columns":3,"className":"popped-pattern-embedded"} /-->
HTML,
);
