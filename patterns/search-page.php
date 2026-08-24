<?php
/**
 * Popped Search Page pattern.
 *
 * @package Popped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'name'          => 'search-page',
	'title'         => __( 'Popped Search Page', 'popped' ),
	'description'   => __( 'A dedicated archive search with useful results and filters.', 'popped' ),
	'categories'    => array( 'popped/patterns' ),
	'keywords'      => array( 'search', 'archive', 'page' ),
	'viewportWidth' => 1440,
	'content'       => <<<'HTML'
<!-- wp:popped/search {"showResultCount":true,"filterCategory":true,"filterTag":true,"displayLayout":"list","showExcerpt":true,"align":"full","className":"popped-pattern-embedded"} /-->
HTML,
);
