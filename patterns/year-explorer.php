<?php
/**
 * Popped Year Explorer pattern.
 *
 * @package Popped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'name'          => 'year-explorer',
	'title'         => __( 'Popped Year Explorer', 'popped' ),
	'description'   => __( 'A compact year browser with story counts and an archive destination.', 'popped' ),
	'categories'    => array( 'popped/patterns' ),
	'keywords'      => array( 'year', 'archive', 'browse' ),
	'viewportWidth' => 1440,
	'content'       => <<<'HTML'
<!-- wp:popped/year-navigator {"title":"Explore by Year","maxYears":12,"showCounts":true,"displayLayout":"grid","align":"wide","className":"popped-pattern-embedded"} /-->
HTML,
);
