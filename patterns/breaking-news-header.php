<?php
/**
 * Popped News Ticker pattern.
 *
 * @package Popped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'name'          => 'breaking-news-header',
	'title'         => __( 'Popped News Ticker', 'popped' ),
	'description'   => __( 'A compact headline ticker that can stay static or move when motion is enabled.', 'popped' ),
	'categories'    => array( 'popped/patterns' ),
	'keywords'      => array( 'news', 'ticker', 'latest' ),
	'viewportWidth' => 1440,
	'content'       => <<<'HTML'
<!-- wp:popped/news-ticker {"source":"latest","count":6,"tickerLabel":"Latest","tickerSpeed":"static","tickerPause":true,"showDate":false,"align":"full","className":"popped-pattern-embedded"} /-->
HTML,
);
