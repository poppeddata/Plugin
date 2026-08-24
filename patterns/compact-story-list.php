<?php
/**
 * Popped Compact Story List pattern.
 *
 * @package Popped
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
	'name'          => 'compact-story-list',
	'title'         => __( 'Popped Compact Story List', 'popped' ),
	'description'   => __( 'A scan-friendly latest-stories list for side sections and dense editorial pages.', 'popped' ),
	'categories'    => array( 'popped/patterns' ),
	'keywords'      => array( 'stories', 'list', 'compact', 'latest' ),
	'viewportWidth' => 1440,
	'content'       => <<<'HTML'
<!-- wp:popped/latest-stories {"title":"Latest","count":6,"order":"newest","displayLayout":"list","showCategory":true,"showExcerpt":false,"density":"compact","align":"wide","className":"popped-pattern-embedded"} /-->
HTML,
);
