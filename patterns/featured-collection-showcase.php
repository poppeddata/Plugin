<?php
/**
 * Popped Collections pattern.
 *
 * @package Popped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'name'          => 'featured-collection-showcase',
	'title'         => __( 'Popped Collections', 'popped' ),
	'description'   => __( 'A chosen collection presented with one lead story and supporting stories.', 'popped' ),
	'categories'    => array( 'popped/patterns' ),
	'keywords'      => array( 'collection', 'featured', 'curated' ),
	'viewportWidth' => 1440,
	'content'       => <<<'HTML'
<!-- wp:popped/featured-collection {"collection":"","displayLayout":"lead","showCollectionImage":true,"showCategory":true,"showExcerpt":true,"align":"full","className":"popped-pattern-embedded"} /-->
HTML,
);
