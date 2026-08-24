<?php
/**
 * Popped Timeline Explorer pattern.
 *
 * @package Popped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'name'          => 'timeline-explorer',
	'title'         => __( 'Popped Timeline Explorer', 'popped' ),
	'description'   => __( 'A filterable vertical chronology with clear grouping and pagination.', 'popped' ),
	'categories'    => array( 'popped/patterns' ),
	'keywords'      => array( 'timeline', 'chronology', 'archive' ),
	'viewportWidth' => 1440,
	'content'       => <<<'HTML'
<!-- wp:popped/timeline {"title":"Timeline","source":"timeline","count":12,"order":"chronological","layout":"vertical","paginate":true,"groupByYear":true,"showCategory":true,"showDate":true,"showExcerpt":false,"align":"full","className":"popped-pattern-embedded"} /-->
HTML,
);
