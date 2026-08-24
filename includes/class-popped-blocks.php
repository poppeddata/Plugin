<?php
/**
 * Dynamic block, variation and pattern registration.
 *
 * @package Popped
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Popped_Blocks {
	private static function style_label( $slug, $style ) {
		if ( 'featured-collection' === $slug && 'inherit' === $style ) {
			return __( 'Use Collection Style', 'popped' );
		}

		$labels = array(
			'default'  => __( 'Default', 'popped' ),
			'minimal'  => __( 'Minimal', 'popped' ),
			'filmstrip' => __( 'Filmstrip', 'popped' ),
			'feature'  => __( 'Feature', 'popped' ),
			'breaking' => __( 'Breaking', 'popped' ),
			'inherit'  => __( 'Inherit', 'popped' ),
		);

		return isset( $labels[ $style ] ) ? $labels[ $style ] : sanitize_text_field( $style );
	}

	public static function register() {
		wp_register_style( 'popped', POPPED_URL . 'assets/css/popped.css', array(), POPPED_VERSION );
		wp_add_inline_style( 'popped', Popped_Plugin::design_token_css() );
		wp_register_script( 'popped', POPPED_URL . 'assets/js/popped.js', array(), POPPED_VERSION, true );
		wp_script_add_data( 'popped', 'strategy', 'defer' );

		wp_register_script(
			'popped-blocks',
			POPPED_URL . 'assets/js/blocks.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n', 'wp-data', 'wp-server-side-render', 'wp-html-entities' ),
			POPPED_VERSION,
			true
		);
		wp_set_script_translations( 'popped-blocks', 'popped' );
		wp_register_style( 'popped-editor', POPPED_URL . 'assets/css/editor.css', array( 'wp-edit-blocks', 'popped' ), POPPED_VERSION );

		$definitions = Popped_Block_Config::definitions();
		$metadata    = array();
		foreach ( $definitions as $slug => $definition ) {
			$metadata_file = POPPED_DIR . 'blocks/' . $slug . '/block.json';
			if ( ! file_exists( $metadata_file ) ) {
				continue;
			}

			$block_metadata = json_decode( (string) file_get_contents( $metadata_file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ( ! is_array( $block_metadata ) ) {
				continue;
			}
			$metadata[ $slug ] = $block_metadata;

			register_block_type_from_metadata(
				dirname( $metadata_file ),
				array(
					'render_callback' => static function ( $attributes ) use ( $slug ) {
						return self::render_block( $slug, $attributes );
					},
				)
			);
		}

		$collections = array();
		foreach ( Popped_Settings::get( 'collections', array() ) as $id => $collection ) {
			$collections[] = array( 'value' => $id, 'label' => $collection['name'], 'description' => $collection['description'] );
		}
		$editor_defaults    = array();
		$insertion_defaults = array();
		foreach ( array_keys( $definitions ) as $slug ) {
			$editor_defaults[ $slug ]     = Popped_Block_Config::defaults( $slug );
			$insertion_defaults[ $slug ] = Popped_Block_Config::insertion_defaults( $slug );
		}
		wp_localize_script(
			'popped-blocks',
			'poppedBlocks',
			array(
				'definitions'       => $definitions,
				'metadata'          => $metadata,
				'defaults'          => $editor_defaults,
				'insertionDefaults' => $insertion_defaults,
				'collections'       => $collections,
				'timelineUrl'       => self::page_url( 'timeline_page_id' ),
				'archiveUrl'        => self::page_url( 'archive_page_id' ),
				'currentMonth'      => absint( wp_date( 'n' ) ),
				'currentDay'        => absint( wp_date( 'j' ) ),
			)
		);
	}

	/** @return string */
	public static function render_block( $slug, $attributes ) {
		$resolved = Popped_Block_Config::resolve( $slug, $attributes );
		$classes  = array(
			'popped-block',
			'popped-block--' . sanitize_html_class( $slug ),
			'popped-type-' . sanitize_html_class( Popped_Settings::get( 'typography', 'inherit' ) ),
			'popped-density-' . sanitize_html_class( Popped_Settings::get( 'density', 'standard' ) ),
			'popped-shape-' . sanitize_html_class( Popped_Settings::get( 'shape', 'soft' ) ),
			'popped-motion-' . sanitize_html_class( Popped_Settings::get( 'motion', 'standard' ) ),
		);
		if ( in_array( $slug, array( 'homepage', 'timeline', 'archive-explorer', 'search' ), true ) ) {
			$classes[] = 'popped-page-block';
		}
		$class_name = ! empty( $attributes['className'] ) ? (string) $attributes['className'] : '';
		$has_inherit_style = false !== strpos( $class_name, 'is-style-inherit' );
		$has_explicit_style = false !== strpos( $class_name, 'is-style-' ) && ! $has_inherit_style;
		if ( 'featured-collection' === $slug && ! $has_explicit_style && ! empty( $resolved['collection'] ) ) {
			$collections = Popped_Settings::get( 'collections', array() );
			if ( ! empty( $collections[ $resolved['collection'] ]['style'] ) ) {
				$classes[] = 'is-style-' . sanitize_html_class( $collections[ $resolved['collection'] ]['style'] );
			}
		}
		$class_map = array(
			'density' => 'popped-local-density-', 'columns' => 'popped-columns-', 'mobileColumns' => 'popped-mobile-columns-', 'displayLayout' => 'popped-display-',
			'layout' => 'popped-layout-', 'imageRatio' => 'popped-image-ratio-', 'imageFit' => 'popped-image-fit-', 'imagePosition' => 'popped-image-position-', 'featureSize' => 'popped-feature-size-',
			'radius' => 'popped-radius-', 'headingSize' => 'popped-heading-', 'headingWeight' => 'popped-heading-weight-', 'headingLineHeight' => 'popped-heading-line-',
			'excerptSize' => 'popped-excerpt-size-', 'cardSurface' => 'popped-card-surface-', 'cardBorder' => 'popped-card-border-', 'cardRadius' => 'popped-card-radius-',
			'metadataSize' => 'popped-meta-size-', 'metadataTone' => 'popped-meta-tone-', 'metadataCase' => 'popped-meta-case-',
			'metadataWeight' => 'popped-meta-weight-', 'metadataSeparator' => 'popped-meta-separator-', 'cardWidth' => 'popped-card-width-',
		);
		foreach ( $class_map as $key => $prefix ) {
			if ( isset( $resolved[ $key ] ) && '' !== $resolved[ $key ] && 'inherit' !== $resolved[ $key ] ) { $classes[] = $prefix . sanitize_html_class( (string) $resolved[ $key ] ); }
		}
		$style_rules = array();
		if ( ! empty( $resolved['itemGap'] ) ) {
			$style_rules[] = '--popped-local-gap:' . max( 4, min( 96, absint( $resolved['itemGap'] ) ) ) . 'px';
		}
		if ( isset( $resolved['cardGap'] ) ) {
			$style_rules[] = '--popped-card-media-gap:' . max( 0, min( 64, absint( $resolved['cardGap'] ) ) ) . 'px';
		}
		if ( isset( $resolved['contentGap'] ) ) {
			$style_rules[] = '--popped-card-content-gap:' . max( 4, min( 40, absint( $resolved['contentGap'] ) ) ) . 'px';
		}
		if ( ! empty( $resolved['cardPadding'] ) ) {
			$classes[] = 'popped-card-has-padding';
			$style_rules[] = '--popped-card-padding:' . max( 0, min( 48, absint( $resolved['cardPadding'] ) ) ) . 'px';
		}
		// Exact visual overrides. These remain optional: presets still provide the default design.
		$align_values = array( 'left', 'center', 'right' );
		if ( ! empty( $resolved['contentAlign'] ) && in_array( $resolved['contentAlign'], $align_values, true ) ) {
			$classes[] = 'popped-content-align-' . sanitize_html_class( $resolved['contentAlign'] );
			$style_rules[] = '--popped-content-align:' . $resolved['contentAlign'];
		}
		if ( ! empty( $resolved['sectionTitleAlign'] ) && in_array( $resolved['sectionTitleAlign'], $align_values, true ) ) {
			$classes[] = 'popped-section-align-' . sanitize_html_class( $resolved['sectionTitleAlign'] );
			$style_rules[] = '--popped-section-align:' . $resolved['sectionTitleAlign'];
		}
		if ( ! empty( $resolved['utilityAlign'] ) && in_array( $resolved['utilityAlign'], $align_values, true ) ) {
			$classes[] = 'popped-utility-align-' . sanitize_html_class( $resolved['utilityAlign'] );
			$style_rules[] = '--popped-utility-align:' . $resolved['utilityAlign'];
		}
		if ( isset( $resolved['utilityGap'] ) && is_numeric( $resolved['utilityGap'] ) ) {
			$style_rules[] = '--popped-utility-gap:' . max( 0, min( 48, (float) $resolved['utilityGap'] ) ) . 'px';
		}

		$size_overrides = array(
			'headingFontSize'           => array( '--popped-heading-font-size', 12, 96 ),
			'excerptFontSizeExact'      => array( '--popped-excerpt-font-size', 10, 48 ),
			'metadataFontSizeExact'     => array( '--popped-meta-font-size', 9, 32 ),
			'sectionTitleFontSize'      => array( '--popped-section-title-font-size', 14, 96 ),
			'mobileHeadingFontSize'     => array( '--popped-mobile-heading-font-size', 12, 72 ),
			'mobileExcerptFontSize'     => array( '--popped-mobile-excerpt-font-size', 10, 40 ),
			'mobileMetadataFontSize'    => array( '--popped-mobile-meta-font-size', 9, 28 ),
			'mobileSectionTitleFontSize'=> array( '--popped-mobile-section-title-font-size', 14, 72 ),
			'utilityFontSize'           => array( '--popped-utility-font-size', 10, 72 ),
			'utilitySecondaryFontSize'  => array( '--popped-utility-secondary-font-size', 9, 36 ),
			'mobileUtilityFontSize'     => array( '--popped-mobile-utility-font-size', 10, 56 ),
			'mobileUtilitySecondaryFontSize'=> array( '--popped-mobile-utility-secondary-font-size', 9, 30 ),
		);
		foreach ( $size_overrides as $key => $definition ) {
			if ( isset( $resolved[ $key ] ) && is_numeric( $resolved[ $key ] ) && (float) $resolved[ $key ] > 0 ) {
				$value = max( $definition[1], min( $definition[2], (float) $resolved[ $key ] ) );
				$style_rules[] = $definition[0] . ':' . $value . 'px';
			}
		}

		$color_overrides = array(
			'headingColor'      => '--popped-heading-color',
			'excerptColor'      => '--popped-excerpt-color',
			'metadataColor'     => '--popped-meta-color',
			'sectionTitleColor' => '--popped-section-title-color',
			'utilityColor'       => '--popped-utility-color',
			'utilitySecondaryColor' => '--popped-utility-secondary-color',
			'utilityAccentColor' => '--popped-utility-accent-color',
		);
		foreach ( $color_overrides as $key => $variable ) {
			if ( ! empty( $resolved[ $key ] ) ) {
				$color = sanitize_hex_color( $resolved[ $key ] );
				if ( $color ) { $style_rules[] = $variable . ':' . $color; }
			}
		}

		$font_overrides = array(
			'headingFontFamily'      => '--popped-heading-font-family',
			'excerptFontFamily'      => '--popped-excerpt-font-family',
			'metadataFontFamily'     => '--popped-meta-font-family',
			'sectionTitleFontFamily' => '--popped-section-title-font-family',
		);
		foreach ( $font_overrides as $key => $variable ) {
			if ( ! empty( $resolved[ $key ] ) ) {
				$font = sanitize_text_field( $resolved[ $key ] );
				$font = preg_replace( '/[;{}<>]/', '', $font );
				if ( $font ) { $style_rules[] = $variable . ':' . $font; }
			}
		}

		$wrapper = get_block_wrapper_attributes(
			array(
				'class'              => implode( ' ', $classes ),
				'style'              => implode( ';', $style_rules ),
				'data-popped-motion' => Popped_Settings::get( 'motion', 'standard' ),
			)
		);
		return '<div ' . $wrapper . '>' . Popped_Components::render( $slug, $resolved ) . '</div>';
	}

	/** @param array<int,array<string,mixed>> $categories Categories. @return array<int,array<string,mixed>> */
	public static function block_category( $categories ) {
		array_unshift( $categories, array( 'slug' => 'popped', 'title' => __( 'Popped', 'popped' ), 'icon' => 'archive' ) );
		return $categories;
	}

	public static function editor_assets() {
		wp_enqueue_script( 'popped-blocks' );
		wp_enqueue_style( 'popped' );
		wp_enqueue_style( 'popped-editor' );
	}

	public static function register_pattern_category() {
		if ( ! function_exists( 'register_block_pattern_category' ) ) {
			return;
		}

		$categories = array(
			'popped'          => array( 'label' => __( 'Popped', 'popped' ), 'description' => __( 'Curated editorial layouts and functional sections.', 'popped' ) ),
			'popped-pages'    => array( 'label' => __( 'Popped — Pages', 'popped' ), 'description' => __( 'Complete page starting points.', 'popped' ) ),
			'popped-sections' => array( 'label' => __( 'Popped — Sections', 'popped' ), 'description' => __( 'Functional sections for Gutenberg pages.', 'popped' ) ),
			'popped-article'  => array( 'label' => __( 'Popped — Article', 'popped' ), 'description' => __( 'Story discovery and article endcaps.', 'popped' ) ),
		);
		foreach ( $categories as $slug => $args ) {
			register_block_pattern_category( $slug, $args );
		}
	}

	public static function register_patterns() {
		if ( ! function_exists( 'register_block_pattern' ) ) {
			return;
		}

		$pattern_files = glob( POPPED_DIR . 'patterns/*.php' );
		if ( false === $pattern_files ) {
			return;
		}

		// Keep the inserter focused on reusable editorial sections, not page templates.
		$curated = array(
			'on-this-day-feature', 'latest-stories-section', 'editorial-story-rail',
			'compact-story-list', 'history-rail', 'featured-collection-showcase',
			'breaking-news-header', 'article-discovery', 'historical-article-endcap',
			'related-stories-grid', 'related-stories-rail', 'timeline-explorer', 'year-explorer',
		);
		$page_patterns = array();
		$article_patterns = array( 'article-discovery', 'historical-article-endcap', 'related-stories-grid', 'related-stories-rail' );

		sort( $pattern_files, SORT_STRING );
		foreach ( $pattern_files as $pattern_file ) {
			$pattern = include $pattern_file;
			if ( ! is_array( $pattern ) || empty( $pattern['name'] ) || empty( $pattern['title'] ) || empty( $pattern['content'] ) ) {
				continue;
			}

			$name = sanitize_title( $pattern['name'] );
			if ( ! in_array( $name, $curated, true ) ) {
				continue;
			}
			unset( $pattern['name'] );
			$primary_category = in_array( $name, $page_patterns, true ) ? 'popped-pages' : ( in_array( $name, $article_patterns, true ) ? 'popped-article' : 'popped-sections' );
			$pattern['categories'] = array_values( array_unique( array_merge( array( 'popped', $primary_category ), isset( $pattern['categories'] ) && is_array( $pattern['categories'] ) ? $pattern['categories'] : array() ) ) );
			$pattern['source']   = 'plugin';
			$pattern['inserter'] = true;

			register_block_pattern( 'popped/' . $name, $pattern );
		}
	}

	/** @return string */
	private static function page_url( $key ) {
		$page_id = absint( Popped_Settings::get( $key, 0 ) );
		return $page_id ? get_permalink( $page_id ) : '';
	}
}
