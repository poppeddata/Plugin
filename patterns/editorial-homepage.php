<?php
/**
 * Popped Homepage pattern.
 *
 * @package Popped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'name'          => 'editorial-homepage',
	'title'         => __( 'Popped Homepage', 'popped' ),
	'description'   => __( 'A polished dynamic homepage using your configured Popped sections.', 'popped' ),
	'categories'    => array( 'popped/patterns' ),
	'keywords'      => array( 'homepage', 'front page', 'editorial' ),
	'viewportWidth' => 1440,
	'content'       => <<<'HTML'
<!-- wp:group {"align":"full","className":"popped-quality-pattern popped-pattern popped-pattern--homepage","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull popped-quality-pattern popped-pattern popped-pattern--homepage">
<!-- wp:popped/on-this-day {"title":"On This Day","count":4,"useToday":true,"showExcerpt":true,"showCategory":true,"align":"full","className":"popped-pattern-embedded"} /-->
<!-- wp:popped/latest-stories {"title":"Latest Stories","count":5,"order":"newest","displayLayout":"cards","columns":3,"showCategory":true,"align":"full","className":"popped-pattern-embedded"} /-->
<!-- wp:popped/horizontal-timeline {"title":"From the Timeline","source":"timeline","count":5,"order":"chronological","showNavigation":true,"showCategory":true,"align":"full","className":"popped-pattern-embedded"} /-->
<!-- wp:popped/featured-collection {"collection":"all","count":4,"showCollectionImage":true,"align":"full","className":"popped-pattern-embedded"} /-->
<!-- wp:popped/year-navigator {"title":"Explore by Year","maxYears":10,"showCounts":true,"align":"full","className":"popped-pattern-embedded"} /-->
</div>
<!-- /wp:group -->
HTML,
);
