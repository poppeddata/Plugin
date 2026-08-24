<?php
/**
 * Popped Archive Hub pattern.
 *
 * @package Popped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'name'          => 'archive-hub',
	'title'         => __( 'Popped Archive Hub', 'popped' ),
	'description'   => __( 'A year navigator followed by a searchable, filterable visual archive.', 'popped' ),
	'categories'    => array( 'popped/patterns' ),
	'keywords'      => array( 'archive', 'years', 'filters' ),
	'viewportWidth' => 1440,
	'content'       => <<<'HTML'
<!-- wp:group {"align":"full","className":"popped-quality-pattern popped-pattern popped-pattern--archive-hub","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull popped-quality-pattern popped-pattern popped-pattern--archive-hub">
<!-- wp:popped/year-navigator {"title":"Explore by Year","maxYears":12,"showCounts":true,"align":"full","className":"popped-pattern-embedded"} /-->
<!-- wp:popped/archive-explorer {"title":"Explore the Archive","source":"all","count":12,"order":"newest","displayLayout":"grid","columns":3,"filterYear":true,"filterCategory":true,"filterTag":true,"filterSearch":true,"showExcerpt":true,"showCategory":true,"align":"full","className":"popped-pattern-embedded"} /-->
</div>
<!-- /wp:group -->
HTML,
);
