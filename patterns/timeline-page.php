<?php
/**
 * Popped Timeline Page pattern.
 *
 * @package Popped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'name'          => 'timeline-page',
	'title'         => __( 'Popped Timeline Page', 'popped' ),
	'description'   => __( 'A complete chronological timeline with filters, pagination and responsive story cards.', 'popped' ),
	'categories'    => array( 'popped/patterns' ),
	'keywords'      => array( 'timeline', 'chronology', 'page' ),
	'viewportWidth' => 1440,
	'content'       => <<<'HTML'
<!-- wp:popped/timeline {"title":"Timeline","source":"timeline","count":8,"order":"chronological","layout":"vertical","paginate":true,"groupByYear":true,"showCategory":true,"showExcerpt":true,"align":"full","className":"popped-pattern-embedded"} /-->
HTML,
);
