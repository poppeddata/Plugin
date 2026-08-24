<?php
/**
 * Popped Latest Stories pattern.
 *
 * @package Popped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'name'          => 'latest-stories-section',
	'title'         => __( 'Popped Latest Stories', 'popped' ),
	'description'   => __( 'A strong lead story followed by supporting stories; ideal for the top of an editorial section.', 'popped' ),
	'categories'    => array( 'popped/patterns' ),
	'keywords'      => array( 'latest', 'stories', 'posts' ),
	'viewportWidth' => 1440,
	'content'       => <<<'HTML'
<!-- wp:popped/latest-stories {"title":"Latest Stories","count":5,"order":"newest","displayLayout":"lead","showCategory":true,"showExcerpt":true,"align":"full","className":"popped-pattern-embedded"} /-->
HTML,
);
