<?php
/**
 * Popped History Rail pattern.
 *
 * @package Popped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'name'          => 'history-rail',
	'title'         => __( 'Popped History Rail', 'popped' ),
	'description'   => __( 'A compact horizontal timeline for homepages, landing pages and story collections.', 'popped' ),
	'categories'    => array( 'popped/patterns' ),
	'keywords'      => array( 'timeline', 'horizontal', 'rail' ),
	'viewportWidth' => 1440,
	'content'       => <<<'HTML'
<!-- wp:popped/horizontal-timeline {"title":"From the Archive","source":"timeline","count":5,"order":"chronological","cardWidth":"medium","showNavigation":true,"showCategory":true,"align":"full","className":"popped-pattern-embedded"} /-->
HTML,
);
