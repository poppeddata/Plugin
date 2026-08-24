<?php
/**
 * Popped Editorial Story Rail pattern.
 *
 * @package Popped
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
	'name'          => 'editorial-story-rail',
	'title'         => __( 'Popped Story Rail', 'popped' ),
	'description'   => __( 'A swipeable row of recent stories for compact discovery without a dense grid.', 'popped' ),
	'categories'    => array( 'popped/patterns' ),
	'keywords'      => array( 'stories', 'rail', 'carousel', 'latest' ),
	'viewportWidth' => 1440,
	'content'       => <<<'HTML'
<!-- wp:popped/latest-stories {"title":"More Stories","count":6,"order":"newest","displayLayout":"rail","showCategory":true,"showExcerpt":false,"align":"full","className":"popped-pattern-embedded"} /-->
HTML,
);
