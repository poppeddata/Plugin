<?php
/**
 * Popped On This Day pattern.
 *
 * @package Popped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'name'          => 'on-this-day-feature',
	'title'         => __( 'Popped On This Day', 'popped' ),
	'description'   => __( 'A ready-to-use same-date feature that stays balanced across image ratios and screen sizes.', 'popped' ),
	'categories'    => array( 'popped/patterns' ),
	'keywords'      => array( 'on this day', 'history', 'feature' ),
	'viewportWidth' => 1440,
	'content'       => <<<'HTML'
<!-- wp:popped/on-this-day {"title":"On This Day","count":4,"useToday":true,"featureSize":"compact","showExcerpt":true,"showCategory":true,"align":"full","className":"popped-pattern-embedded"} /-->
HTML,
);
