<?php
/**
 * Central component definitions, defaults and native block styles.
 *
 * @package Popped
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Popped_Block_Config {
	/**
	 * Public block catalogue. Defaults are resolved at render time so blocks inherit
	 * the current Popped settings until an editor deliberately adds an override.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function definitions() {
		return array(
			'homepage' => self::definition( __( 'Popped Homepage', 'popped' ), __( 'Insert an optional homepage composition using the section defaults configured in Popped.', 'popped' ), 'layout', array(), array( 'styles' => array() ) ),
			'timeline' => self::definition( __( 'Timeline', 'popped' ), __( 'Show a filterable vertical sequence of Timeline stories.', 'popped' ), 'editor-ol', array( 'content', 'layout', 'images', 'metadata' ), array( 'styles' => array( 'default', 'minimal' ) ) ),
			'horizontal-timeline' => self::definition( __( 'Horizontal Timeline', 'popped' ), __( 'Show a chronological, swipeable rail of selected stories.', 'popped' ), 'slides', array( 'content', 'layout', 'images', 'metadata' ), array( 'styles' => array( 'default', 'filmstrip', 'minimal' ) ) ),
			'mini-timeline' => self::definition( __( 'Mini Timeline', 'popped' ), __( 'Show a short chronological sequence of selected stories.', 'popped' ), 'excerpt-view', array( 'content', 'layout', 'images', 'metadata' ), array( 'styles' => array( 'default' ) ) ),
			'on-this-day' => self::definition( __( 'On This Day', 'popped' ), __( 'Highlight stories published on this date in previous years.', 'popped' ), 'calendar-alt', array( 'content', 'images', 'metadata' ), array( 'styles' => array( 'default', 'minimal', 'feature' ) ) ),
			'also-on-this-day' => self::definition( __( 'Also On This Day', 'popped' ), __( 'Show a smaller rail of other stories sharing the article date.', 'popped' ), 'calendar', array( 'content', 'images', 'metadata' ), array( 'styles' => array( 'default' ) ) ),
			'continue-story' => self::definition( __( 'Continue the Story', 'popped' ), __( 'Guide readers to the next related story automatically or manually.', 'popped' ), 'controls-forward', array( 'content', 'utility' ), array( 'styles' => array( 'default' ) ) ),
			'timeline-navigation' => self::definition( __( 'Timeline Previous / Next', 'popped' ), __( 'Navigate chronologically through posts carrying the Timeline tag.', 'popped' ), 'leftright', array( 'utility' ), array( 'styles' => array( 'default' ) ) ),
			'related-stories' => self::definition( __( 'Related Stories', 'popped' ), __( 'Show automatically related stories or a deliberate manual selection.', 'popped' ), 'networking', array( 'content', 'layout', 'images', 'metadata' ), array( 'styles' => array( 'default' ) ) ),
			'news-ticker' => self::definition( __( 'News Ticker', 'popped' ), __( 'Add a compact latest, manual or mixed headline strip.', 'popped' ), 'megaphone', array( 'content', 'layout', 'utility' ), array( 'styles' => array( 'default', 'breaking' ) ) ),
			'latest-stories' => self::definition( __( 'Latest Stories', 'popped' ), __( 'Show recent stories as a balanced grid, lead composition, list or swipeable rail.', 'popped' ), 'grid-view', array( 'content', 'layout', 'images', 'metadata' ), array( 'styles' => array( 'default' ) ) ),
			'archive-explorer' => self::definition( __( 'Archive Explorer', 'popped' ), __( 'Add a searchable, filterable story archive.', 'popped' ), 'archive', array( 'content', 'layout', 'images', 'metadata' ), array( 'styles' => array( 'default' ) ) ),
			'year-navigator' => self::definition( __( 'Year Navigator', 'popped' ), __( 'Help readers browse a chosen historical year range.', 'popped' ), 'schedule', array( 'content', 'layout', 'utility' ), array( 'styles' => array( 'default' ) ) ),
			'featured-collection' => self::definition( __( 'Featured Collection', 'popped' ), __( 'Display a named collection as a balanced grid, lead composition, list or swipeable rail.', 'popped' ), 'images-alt2', array( 'content', 'layout', 'images', 'metadata' ), array( 'styles' => array( 'inherit' ) ) ),
			'search' => self::definition( __( 'Search', 'popped' ), __( 'Add a clear archive search with useful results and empty states.', 'popped' ), 'search', array( 'content', 'layout', 'images', 'metadata' ), array( 'styles' => array( 'default' ) ) ),
		);
	}

	/** @return array<string,mixed> */
	private static function definition( $title, $description, $icon, $panels, $extra = array() ) {
		return array_merge( array( 'title' => $title, 'description' => $description, 'icon' => $icon, 'panels' => $panels, 'styles' => array( 'default' ) ), $extra );
	}

	/**
	 * Component-specific global defaults.
	 *
	 * @param string $slug Component slug.
	 * @return array<string,mixed>
	 */
	public static function defaults( $slug ) {
		$common = array(
			'density' => 'inherit', 'showImage' => true, 'imageRatio' => 'landscape', 'imageFit' => 'cover', 'radius' => 'inherit',
			'showDate' => true, 'showCategory' => false, 'showTags' => false, 'showExcerpt' => false, 'showAuthor' => false,
			'headingSize' => 'medium', 'headingWeight' => 'medium', 'headingLineHeight' => 'snug', 'headingLevel' => 3, 'sectionTitleLevel' => 2, 'excerptSize' => 'medium', 'excerptLength' => 24,
			'imagePosition' => 'center', 'cardSurface' => 'transparent', 'cardBorder' => 'none', 'cardRadius' => 'soft',
			'cardGap' => 12, 'contentGap' => 8, 'cardPadding' => 0,
			'metadataSize' => 'small', 'metadataTone' => 'muted', 'metadataCase' => 'normal', 'metadataWeight' => 'semibold', 'metadataSeparator' => 'dot',
		);
		switch ( $slug ) {
			case 'homepage':
				return array( 'composition' => Popped_Settings::get( 'homepage_composition', 'editorial' ) );
			case 'timeline':
				return array_merge( $common, array( 'source' => 'timeline', 'count' => absint( Popped_Settings::get( 'timeline_per_page', 10 ) ), 'order' => 'chronological', 'layout' => Popped_Settings::get( 'timeline_layout', 'vertical' ), 'paginate' => true, 'groupByYear' => false, 'showCategory' => true ) );
			case 'horizontal-timeline':
				return array_merge( $common, array( 'source' => 'timeline', 'count' => 6, 'order' => 'chronological', 'layout' => 'horizontal', 'paginate' => false, 'groupByYear' => false, 'cardWidth' => 'medium', 'showNavigation' => true, 'showResultCount' => true, 'showViewLink' => false, 'linkText' => __( 'See full timeline', 'popped' ) ) );
			case 'mini-timeline':
				$sections = Popped_Settings::get( 'homepage_sections', Popped_Settings::defaults()['homepage_sections'] );
				$title = Popped_Settings::homepage_section_label( 'mini-timeline', isset( $sections['mini-timeline']['label'] ) ? $sections['mini-timeline']['label'] : '' );
				return array_merge( $common, array( 'title' => $title, 'source' => 'timeline', 'count' => 4, 'order' => 'chronological', 'showViewLink' => true, 'linkText' => __( 'View full timeline', 'popped' ) ) );
			case 'on-this-day':
				return array_merge( $common, array( 'title' => __( 'On This Day', 'popped' ), 'source' => Popped_Settings::get( 'on_this_day_source', 'all' ), 'count' => absint( Popped_Settings::get( 'on_this_day_count', 4 ) ), 'useToday' => true, 'featureSize' => 'compact', 'showExcerpt' => true, 'fallbackText' => __( 'No stories have been added for this date yet.', 'popped' ) ) );
			case 'also-on-this-day':
				return array_merge( $common, array( 'title' => __( 'Also On This Day', 'popped' ), 'source' => 'all', 'count' => 4, 'order' => 'chronological' ) );
			case 'continue-story':
				return array( 'selectionMode' => 'automatic', 'count' => 1, 'headingLevel' => 3 );
			case 'timeline-navigation':
				return array( 'utilityAlign' => 'left', 'utilityGap' => 8, 'sectionTitleLevel' => 2 );
			case 'related-stories':
				return array_merge( $common, array( 'selectionMode' => 'automatic', 'relevance' => 'both', 'count' => absint( Popped_Settings::get( 'related_count', 3 ) ), 'displayLayout' => Popped_Settings::get( 'related_layout', 'cards' ), 'columns' => 3 ) );
			case 'news-ticker':
				return array( 'source' => Popped_Settings::get( 'ticker_source', 'latest' ), 'count' => 5, 'order' => 'newest', 'tickerLabel' => __( 'Latest', 'popped' ), 'tickerSpeed' => 'standard', 'tickerDirection' => 'left', 'tickerSeparator' => 'dot', 'tickerPause' => true, 'showDate' => true );
			case 'latest-stories':
				return array_merge( $common, array( 'title' => __( 'Latest Stories', 'popped' ), 'source' => Popped_Settings::get( 'latest_source', 'all' ), 'count' => absint( Popped_Settings::get( 'latest_count', 5 ) ), 'order' => Popped_Settings::get( 'latest_order', 'newest' ), 'displayLayout' => 'cards', 'columns' => 3, 'showCategory' => true ) );
			case 'archive-explorer':
				return array_merge( $common, array( 'title' => __( 'Explore the Archive', 'popped' ), 'source' => 'all', 'count' => 12, 'order' => 'chronological', 'displayLayout' => 'grid', 'columns' => 3, 'filterYear' => true, 'filterCategory' => true, 'filterTag' => true, 'filterSearch' => true, 'showExcerpt' => true ) );
			case 'year-navigator':
				$range = Popped_Settings::year_range();
				return array( 'title' => __( 'Explore by Year', 'popped' ), 'startYear' => $range['start'], 'endYear' => $range['end'], 'maxYears' => 0, 'showCounts' => true, 'displayLayout' => 'grid', 'columns' => 5, 'yearOrder' => 'oldest', 'sectionTitleLevel' => 2 );
			case 'featured-collection':
				return array_merge( $common, array( 'collection' => '', 'displayLayout' => '', 'columns' => 3, 'showCategory' => true, 'showCollectionImage' => true ) );
			case 'search':
				return array_merge( $common, array( 'filterCategory' => false, 'filterTag' => false, 'showResultCount' => true, 'displayLayout' => 'list', 'showExcerpt' => true ) );
			default:
				return array();
		}
	}


	/**
	 * Recommended attributes for newly inserted blocks.
	 *
	 * These are intentionally separate from defaults() so existing blocks that
	 * inherit render-time defaults are not restyled during an upgrade.
	 *
	 * @param string $slug Component slug.
	 * @return array<string,mixed>
	 */
	public static function insertion_defaults( $slug ) {
		$story_cards = array(
			'density'           => 'standard',
			'showImage'         => true,
			'imageRatio'        => 'classic',
			'imageFit'          => 'cover',
			'imagePosition'     => 'center',
			'radius'            => 'inherit',
			'headingSize'       => 'medium',
			'headingWeight'     => 'semibold',
			'headingLineHeight' => 'balanced',
			'excerptSize'       => 'medium',
			'excerptLength'     => 28,
			'cardSurface'       => 'transparent',
			'cardBorder'        => 'none',
			'cardRadius'        => 'soft',
			'itemGap'           => 14,
			'cardGap'           => 10,
			'contentGap'        => 7,
			'cardPadding'       => 0,
			'metadataSize'      => 'small',
			'metadataTone'      => 'muted',
			'metadataCase'      => 'normal',
			'metadataWeight'    => 'semibold',
			'metadataSeparator' => 'dot',
		);

		switch ( $slug ) {
			case 'timeline':
				return array_merge( $story_cards, array( 'count' => 8, 'imageRatio' => 'classic' ) );
			case 'horizontal-timeline':
				return array_merge(
					$story_cards,
					array(
						'count'       => 5,
						'imageRatio'  => 'wide',
						'headingSize' => 'small',
						'itemGap'     => 12,
						'cardGap'     => 10,
						'contentGap'  => 6,
						'cardWidth'   => 'medium',
						'showResultCount' => true,
						'showViewLink'    => false,
						'linkText'        => __( 'See full timeline', 'popped' ),
					)
				);
			case 'mini-timeline':
				return array_merge(
					$story_cards,
					array(
						'title'       => __( 'Timeline', 'popped' ),
						'count'       => 5,
						'headingSize' => 'small',
						'itemGap'     => 12,
						'contentGap'  => 6,
					)
				);
			case 'on-this-day':
				return array_merge(
					$story_cards,
					array(
						'count'         => 4,
						'featureSize'   => 'compact',
						'imageRatio'    => 'wide',
						'headingSize'   => 'medium',
						'headingWeight' => 'medium',
						'cardGap'       => 12,
						'contentGap'    => 8,
					)
				);
			case 'also-on-this-day':
				return array_merge(
					$story_cards,
					array(
						'count'       => 4,
						'headingSize' => 'small',
						'itemGap'     => 12,
						'contentGap'  => 6,
					)
				);
			case 'related-stories':
				return array_merge( $story_cards, array( 'count' => 3, 'columns' => 3 ) );
			case 'latest-stories':
				return array_merge( $story_cards, array( 'count' => 5, 'columns' => 3 ) );
			case 'archive-explorer':
				return array_merge(
					$story_cards,
					array(
						'count'       => 12,
						'order'       => 'newest',
						'contentGap'  => 8,
					)
				);
			case 'featured-collection':
				return array_merge(
					$story_cards,
					array(
						'count'      => 5,
						'imageRatio' => 'classic',
					)
				);
			case 'search':
				return array_merge(
					$story_cards,
					array(
						'headingSize' => 'small',
						'contentGap'  => 6,
					)
				);
			case 'news-ticker':
				return array(
					'count'       => 5,
					'utilityAlign' => 'left',
					'utilityGap'   => 8,
					'tickerSpeed'     => 'static',
					'tickerDirection' => 'left',
					'tickerSeparator' => 'dot',
					'tickerPause' => true,
					'showDate'    => false,
				);
			case 'continue-story':
				return array( 'selectionMode' => 'automatic', 'utilityAlign' => 'left', 'utilityGap' => 8 );
			case 'year-navigator':
				return array(
					'maxYears'      => 12,
					'utilityAlign'  => 'left',
					'utilityGap'    => 6,
					'displayLayout' => 'grid',
					'columns'       => 5,
					'yearOrder'     => 'newest',
					'showCounts'    => true,
				);
			default:
				return array();
		}
	}

	/** @return array<string,mixed> */
	public static function resolve( $slug, $attributes ) {
		$resolved = self::defaults( $slug );
		foreach ( (array) $attributes as $key => $value ) {
			if ( null !== $value ) { $resolved[ $key ] = $value; }
		}
		return $resolved;
	}
}
