<?php
/**
 * Settings and sanitisation.
 *
 * @package Popped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Popped_Settings {
	const OPTION = 'popped_options';

	/**
	 * Recommended defaults. Options are intentionally compact: advanced values are
	 * stored only when the administrator elects to change them.
	 *
	 * @return array<string,mixed>
	 */
	public static function defaults() {
		return array(
			'version'             => POPPED_VERSION,
			'setup_complete'      => false,
			'template_mode'       => false,
			'sticky_header'       => true,
			'motion'              => 'standard',
			'density'             => 'standard',
			'typography'          => 'inherit',
			'shape'               => 'soft',
			'colour_preset'       => 'paper',
			'logo_id'             => 0,
			'custom_font_id'      => 0,
			'custom_font_name'    => '',
			'custom_font_fallback'=> '-apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
			'custom_font_role'    => 'none',
			'menu_id'             => 0,
			'menu_svg'            => self::default_menu_svg(),
			'timeline_tag'        => 'timeline',
			'timeline_layout'     => 'vertical',
			'timeline_per_page'   => 10,
			'year_range_mode'     => 'auto',
			'year_start'          => 0,
			'year_end'            => 0,
			'on_this_day_source'  => 'all',
			'on_this_day_count'   => 4,
			'on_this_day_override'=> 0,
			'latest_count'        => 5,
			'latest_source'       => 'all',
			'latest_order'        => 'newest',
			'related_count'       => 3,
			'related_layout'      => 'cards',
			'ticker_enabled'      => false,
			'ticker_placement'    => 'below-header',
			'ticker_source'       => 'latest',
			'ticker_post_ids'     => array(),
			'append_discovery'    => false,
			'taxonomy_search'     => false,
			'homepage_id'         => 0,
			'homepage_composition'=> 'editorial',
			'timeline_page_id'    => 0,
			'archive_page_id'     => 0,
			'search_page_id'      => 0,
			'homepage_sections'   => array(
				'latest-stories'      => array( 'enabled' => true,  'label' => 'Latest Stories' ),
				'on-this-day'         => array( 'enabled' => true,  'label' => 'On This Day' ),
				'featured-collections'=> array( 'enabled' => true,  'label' => 'Featured Collections' ),
				'mini-timeline'       => array( 'enabled' => true,  'label' => 'Timeline' ),
				'year-navigator'      => array( 'enabled' => true,  'label' => 'Explore by Year' ),
				'news-ticker'        => array( 'enabled' => false, 'label' => 'News Ticker' ),
			),
			'collections'         => array(),
			'colours'             => array(
				'background' => '#f7f7f4',
				'surface'    => '#ffffff',
				'text'       => '#171714',
				'muted'      => '#6d6d65',
				'accent'     => '#c83d27',
				'border'     => '#deded8',
			),
		);
	}


	/**
	 * Translate built-in homepage section labels while preserving custom labels.
	 *
	 * @param string $slug Section slug.
	 * @param string $stored_label Stored label, if any.
	 * @return string
	 */
	public static function homepage_section_label( $slug, $stored_label = '' ) {
		$stored_label = sanitize_text_field( $stored_label );
		$canonical    = array(
			'latest-stories'       => 'Latest Stories',
			'on-this-day'          => 'On This Day',
			'featured-collections' => 'Featured Collections',
			'mini-timeline'        => 'Timeline',
			'year-navigator'       => 'Explore by Year',
			'news-ticker'          => 'News Ticker',
		);

		if ( ! isset( $canonical[ $slug ] ) ) {
			return $stored_label;
		}
		if ( '' !== $stored_label && $stored_label !== $canonical[ $slug ] ) {
			return $stored_label;
		}

		switch ( $slug ) {
			case 'latest-stories':
				return __( 'Latest Stories', 'popped' );
			case 'on-this-day':
				return __( 'On This Day', 'popped' );
			case 'featured-collections':
				return __( 'Featured Collections', 'popped' );
			case 'mini-timeline':
				return __( 'Timeline', 'popped' );
			case 'year-navigator':
				return __( 'Explore by Year', 'popped' );
			case 'news-ticker':
				return __( 'News Ticker', 'popped' );
			default:
				return $stored_label;
		}
	}

	/**
	 * Get every option merged with the current defaults.
	 *
	 * @return array<string,mixed>
	 */
	public static function all() {
		$stored = get_option( self::OPTION, array() );
		$stored = is_array( $stored ) ? $stored : array();
		$options = self::merge_recursive( self::defaults(), $stored );
		if ( isset( $stored['homepage_sections'] ) && is_array( $stored['homepage_sections'] ) ) {
			$options['homepage_sections'] = self::merge_homepage_sections( $stored['homepage_sections'] );
		}

		if ( ! array_key_exists( 'typography', $stored ) && ! empty( $stored ) ) {
			// Pre-1.6.1 installs inherited the old Editorial default. Preserve that
			// appearance; only genuinely new installs start in Theme / Global Styles.
			$options['typography'] = 'editorial';
		}

		if ( ! array_key_exists( 'year_range_mode', $stored ) && ! empty( $stored ) ) {
			$options['year_range_mode'] = 'manual';
		}

		if ( ! array_key_exists( 'homepage_composition', $stored ) && ! empty( $stored ) ) {
			$options['homepage_composition'] = 'sections';
			if ( ! isset( $stored['homepage_sections'] ) || ! is_array( $stored['homepage_sections'] ) ) {
				$options['homepage_sections'] = self::legacy_homepage_sections();
			}
		}

		$options['motion'] = array( 'moderate' => 'standard', 'subtle' => 'reduced' )[ $options['motion'] ] ?? $options['motion'];
		$options['typography'] = array( 'modern' => 'clean', 'system' => 'clean', 'classic' => 'magazine' )[ $options['typography'] ] ?? $options['typography'];
		if ( ! in_array( $options['ticker_placement'], array( 'below-header', 'above-footer' ), true ) ) {
			$options['ticker_placement'] = 'below-header';
		}
		unset( $options['timeline_preset'] );
		return $options;
	}

	/**
	 * Get one setting.
	 *
	 * @param string $key Setting key.
	 * @param mixed  $fallback Fallback.
	 * @return mixed
	 */
	public static function get( $key, $fallback = null ) {
		$options = self::all();
		return array_key_exists( $key, $options ) ? $options[ $key ] : $fallback;
	}

	/**
	 * Update one setting without discarding the rest.
	 *
	 * @param string $key Setting key.
	 * @param mixed  $value Value.
	 */
	public static function set( $key, $value ) {
		$options         = self::all();
		$options[ $key ] = $value;
		self::store( $options );
	}

	/**
	 * Persist a complete, trusted settings record generated by Popped itself.
	 * Form submissions continue through sanitize(); this avoids running the
	 * partial-form sanitizer a second time for setup and internal updates.
	 *
	 * @param array<string,mixed> $options Complete Popped settings.
	 * @return bool
	 */
	public static function store( $options ) {
		$options = is_array( $options ) ? $options : self::defaults();
		$options['version'] = POPPED_VERSION;
		$hook = 'sanitize_option_' . self::OPTION;
		$removed = remove_filter( $hook, array( __CLASS__, 'sanitize' ) );
		$saved = update_option( self::OPTION, $options, false );
		if ( $removed ) { add_filter( $hook, array( __CLASS__, 'sanitize' ) ); }
		return $saved;
	}

	/**
	 * Register the single settings record.
	 */
	public static function register() {
		register_setting(
			'popped_settings',
			self::OPTION,
			array(
				'type'              => 'object',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);
	}

	/**
	 * Sanitize partial settings forms while retaining other pages' values.
	 *
	 * @param mixed $input Submitted value.
	 * @return array<string,mixed>
	 */
	public static function sanitize( $input ) {
		$current = self::all();
		$input   = is_array( $input ) ? $input : array();
		$section = isset( $input['_section'] ) ? sanitize_key( $input['_section'] ) : '';

		if ( in_array( $section, array( 'homepage', 'components' ), true ) ) {
			$current['homepage_composition'] = self::one_of( $input, 'homepage_composition', array( 'editorial', 'sections' ), $current['homepage_composition'] );
			$known     = self::defaults()['homepage_sections'];
			$submitted = isset( $input['homepage_sections'] ) && is_array( $input['homepage_sections'] ) ? $input['homepage_sections'] : array();
			$order     = isset( $input['homepage_order'] ) && is_array( $input['homepage_order'] ) ? array_map( 'sanitize_key', $input['homepage_order'] ) : array_keys( $current['homepage_sections'] );
			$sections  = array();
			foreach ( array_unique( array_merge( $order, array_keys( $known ) ) ) as $slug ) {
				if ( ! isset( $known[ $slug ] ) ) {
					continue;
				}
				$existing_label = isset( $current['homepage_sections'][ $slug ]['label'] ) ? sanitize_text_field( $current['homepage_sections'][ $slug ]['label'] ) : '';
				$sections[ $slug ] = array(
					'enabled' => ! empty( $submitted[ $slug ]['enabled'] ),
					'label'   => '' !== $existing_label ? $existing_label : $known[ $slug ]['label'],
				);
			}
			$current['homepage_sections'] = $sections;
		}

		if ( in_array( $section, array( 'timeline', 'components' ), true ) ) {
			$current['timeline_tag']      = isset( $input['timeline_tag'] ) ? sanitize_title( $input['timeline_tag'] ) : 'timeline';
			$current['timeline_layout']   = self::one_of( $input, 'timeline_layout', array( 'vertical', 'horizontal' ), 'vertical' );
			$current['timeline_per_page'] = self::bounded_int( $input, 'timeline_per_page', 6, 36, 10 );
		}

		if ( in_array( $section, array( 'on-this-day', 'components' ), true ) ) {
			$current['on_this_day_source']   = self::one_of( $input, 'on_this_day_source', array( 'all', 'timeline' ), 'all' );
			$current['on_this_day_count']    = self::bounded_int( $input, 'on_this_day_count', 1, 12, 4 );
			$current['on_this_day_override'] = isset( $input['on_this_day_override'] ) ? absint( $input['on_this_day_override'] ) : 0;
		}

		if ( in_array( $section, array( 'ticker', 'components' ), true ) ) {
			$current['ticker_enabled']   = ! empty( $input['ticker_enabled'] );
			$current['ticker_placement'] = self::one_of( $input, 'ticker_placement', array( 'below-header', 'above-footer' ), 'below-header' );
			$current['ticker_source']    = self::one_of( $input, 'ticker_source', array( 'latest', 'manual', 'mixed' ), 'latest' );
			$current['ticker_post_ids']  = self::id_list( isset( $input['ticker_post_ids'] ) ? $input['ticker_post_ids'] : '' );
		}

		if ( 'design' === $section ) {
			$current['typography'] = self::one_of( $input, 'typography', array( 'inherit', 'clean', 'editorial', 'magazine', 'custom' ), 'inherit' );
			$current['density']    = self::one_of( $input, 'density', array( 'compact', 'standard', 'spacious' ), 'standard' );
			$current['shape']      = self::one_of( $input, 'shape', array( 'square', 'soft', 'rounded' ), 'soft' );
			$current['motion']     = self::one_of( $input, 'motion', array( 'none', 'reduced', 'standard' ), 'standard' );
			$current['colour_preset'] = self::one_of( $input, 'colour_preset', array( 'paper', 'crisp', 'warm', 'custom' ), 'paper' );
			$current['sticky_header'] = ! empty( $input['sticky_header'] );
			$current['logo_id']       = isset( $input['logo_id'] ) ? absint( $input['logo_id'] ) : 0;
			$current['custom_font_id']       = isset( $input['custom_font_id'] ) ? absint( $input['custom_font_id'] ) : 0;
			$current['custom_font_name']     = isset( $input['custom_font_name'] ) ? sanitize_text_field( $input['custom_font_name'] ) : '';
			$current['custom_font_fallback'] = isset( $input['custom_font_fallback'] ) ? sanitize_text_field( $input['custom_font_fallback'] ) : '-apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
			$current['custom_font_role']     = self::one_of( $input, 'custom_font_role', array( 'none', 'display', 'heading', 'body', 'navigation', 'meta', 'buttons' ), 'none' );
			$current['menu_svg']      = isset( $input['menu_svg'] ) ? self::sanitize_svg( wp_unslash( $input['menu_svg'] ) ) : $current['menu_svg'];
			$presets = self::colour_presets();
			$colours = isset( $presets[ $current['colour_preset'] ] ) ? $presets[ $current['colour_preset'] ] : self::defaults()['colours'];
			if ( 'custom' === $current['colour_preset'] ) {
				$colours = self::defaults()['colours'];
				foreach ( array_keys( $colours ) as $role ) {
					$value = isset( $input['colours'][ $role ] ) ? sanitize_hex_color( $input['colours'][ $role ] ) : '';
					$colours[ $role ] = $value ? $value : $colours[ $role ];
				}
			}
			$current['colours'] = $colours;
		}

		if ( in_array( $section, array( 'archives', 'advanced' ), true ) ) {
			$current['year_range_mode'] = self::one_of( $input, 'year_range_mode', array( 'auto', 'manual' ), 'auto' );
			if ( isset( $input['year_start'] ) ) {
				$current['year_start'] = self::bounded_int( $input, 'year_start', 1000, 3000, (int) wp_date( 'Y' ) - 9 );
			}
			if ( isset( $input['year_end'] ) ) {
				$current['year_end'] = self::bounded_int( $input, 'year_end', 1000, 3000, (int) wp_date( 'Y' ) );
			}
			if ( $current['year_start'] && $current['year_end'] && $current['year_end'] < $current['year_start'] ) {
				$current['year_end'] = $current['year_start'];
			}
		}

		if ( in_array( $section, array( 'related', 'components' ), true ) ) {
			$current['related_count']    = self::bounded_int( $input, 'related_count', 2, 8, 3 );
			$current['related_layout']   = self::one_of( $input, 'related_layout', array( 'cards', 'list' ), 'cards' );
			$current['append_discovery'] = ! empty( $input['append_discovery'] );
		}

		if ( in_array( $section, array( 'search', 'components' ), true ) ) {
			$current['latest_count'] = self::bounded_int( $input, 'latest_count', 3, 12, 5 );
			$current['latest_source'] = self::one_of( $input, 'latest_source', array( 'all', 'timeline' ), 'all' );
			$current['latest_order'] = self::one_of( $input, 'latest_order', array( 'newest', 'chronological' ), 'newest' );
		}

		if ( 'templates' === $section ) {
			$current['template_mode'] = ! empty( $input['template_mode'] );
		}
		if ( 'advanced' === $section ) {
			$current['taxonomy_search'] = ! empty( $input['taxonomy_search'] );
			$current['menu_svg'] = isset( $input['menu_svg'] ) ? self::sanitize_svg( wp_unslash( $input['menu_svg'] ) ) : $current['menu_svg'];
		}

		if ( 'collections' === $section ) {
			$current['collections'] = self::sanitize_collections( isset( $input['collections'] ) ? $input['collections'] : array() );
		}

		$current['version'] = POPPED_VERSION;
		unset( $current['_section'], $current['homepage_order'] );
		return $current;
	}

	/** @return string */
	private static function one_of( $input, $key, $allowed, $fallback ) {
		$value = isset( $input[ $key ] ) ? sanitize_key( $input[ $key ] ) : $fallback;
		return in_array( $value, $allowed, true ) ? $value : $fallback;
	}

	/** @return int */
	private static function bounded_int( $input, $key, $min, $max, $fallback ) {
		$value = isset( $input[ $key ] ) ? absint( $input[ $key ] ) : $fallback;
		return max( $min, min( $max, $value ) );
	}

	/** @return int[] */
	public static function id_list( $value ) {
		if ( is_array( $value ) ) {
			$parts = $value;
		} else {
			$parts = preg_split( '/[\s,]+/', (string) $value );
		}
		return array_values( array_unique( array_filter( array_map( 'absint', $parts ) ) ) );
	}

	/** @return array<string,array<string,string>> */
	public static function colour_presets() {
		return array(
			'paper' => array( 'background' => '#f7f7f4', 'surface' => '#ffffff', 'text' => '#171714', 'muted' => '#6d6d65', 'accent' => '#c83d27', 'border' => '#deded8' ),
			'crisp' => array( 'background' => '#ffffff', 'surface' => '#f4f6f8', 'text' => '#101418', 'muted' => '#5f6872', 'accent' => '#2457e6', 'border' => '#dce1e6' ),
			'warm'  => array( 'background' => '#f5f0e7', 'surface' => '#fffaf2', 'text' => '#241c18', 'muted' => '#776a60', 'accent' => '#a93f2d', 'border' => '#ded2c3' ),
		);
	}

	/**
	 * Resolve the effective archive year range.
	 *
	 * Automatic mode follows published post dates. Manual mode preserves the
	 * administrator's explicit range.
	 *
	 * @return array{start:int,end:int}
	 */
	public static function year_range() {
		$options = self::all();
		if ( 'manual' === $options['year_range_mode'] ) {
			$start = absint( $options['year_start'] );
			$end   = absint( $options['year_end'] );
			if ( $start >= 1000 && $end >= $start ) {
				return array( 'start' => $start, 'end' => $end );
			}
		}
		return self::published_year_range();
	}

	/**
	 * Detect the oldest and newest published post years.
	 *
	 * @return array{start:int,end:int}
	 */
	public static function published_year_range() {
		static $range = null;
		if ( null !== $range ) {
			return $range;
		}

		$current_year = (int) wp_date( 'Y' );
		$fallback     = array( 'start' => max( 1000, $current_year - 9 ), 'end' => $current_year );
		$query_args   = array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'orderby'                => 'date',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);

		$oldest = get_posts( array_merge( $query_args, array( 'order' => 'ASC' ) ) );
		$newest = get_posts( array_merge( $query_args, array( 'order' => 'DESC' ) ) );
		if ( empty( $oldest ) || empty( $newest ) ) {
			$range = $fallback;
			return $range;
		}

		$oldest_date = get_post_field( 'post_date', $oldest[0] );
		$newest_date = get_post_field( 'post_date', $newest[0] );
		$start       = $oldest_date ? absint( mysql2date( 'Y', $oldest_date ) ) : 0;
		$end         = $newest_date ? absint( mysql2date( 'Y', $newest_date ) ) : 0;

		if ( $start < 1000 || $end < $start ) {
			$range = $fallback;
			return $range;
		}

		$range = array( 'start' => $start, 'end' => $end );
		return $range;
	}

	/**
	 * Preserve an administrator's homepage section order while adding new
	 * sections from future versions at the end.
	 *
	 * @param array<string,mixed> $stored_sections Stored section configuration.
	 * @return array<string,array<string,mixed>>
	 */
	private static function merge_homepage_sections( $stored_sections ) {
		$defaults = self::defaults()['homepage_sections'];
		$merged   = array();

		foreach ( $stored_sections as $slug => $section ) {
			if ( ! isset( $defaults[ $slug ] ) || ! is_array( $section ) ) {
				continue;
			}
			$merged[ $slug ] = array(
				'enabled' => array_key_exists( 'enabled', $section ) ? (bool) $section['enabled'] : (bool) $defaults[ $slug ]['enabled'],
				'label'   => ! empty( $section['label'] ) ? sanitize_text_field( $section['label'] ) : $defaults[ $slug ]['label'],
			);
		}

		foreach ( $defaults as $slug => $section ) {
			if ( ! isset( $merged[ $slug ] ) ) {
				$merged[ $slug ] = $section;
			}
		}

		return $merged;
	}

	/**
	 * Original pre-1.4.0 homepage order used only to protect sparse legacy option records.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private static function legacy_homepage_sections() {
		return array(
			'on-this-day'          => array( 'enabled' => true,  'label' => 'On This Day' ),
			'latest-stories'       => array( 'enabled' => true,  'label' => 'Latest Stories' ),
			'mini-timeline'        => array( 'enabled' => true,  'label' => 'Timeline' ),
			'featured-collections' => array( 'enabled' => true,  'label' => 'Featured Collections' ),
			'year-navigator'       => array( 'enabled' => true,  'label' => 'Explore by Year' ),
			'news-ticker'         => array( 'enabled' => false, 'label' => 'News Ticker' ),
		);
	}

	/** @return array<string,array<string,mixed>> */
	private static function sanitize_collections( $collections ) {
		$out = array();
		if ( ! is_array( $collections ) ) { return $out; }
		foreach ( array_slice( $collections, 0, 50 ) as $index => $collection ) {
			if ( ! is_array( $collection ) ) { continue; }
			$name = isset( $collection['name'] ) ? sanitize_text_field( $collection['name'] ) : '';
			if ( '' === $name ) { continue; }
			$id = isset( $collection['id'] ) ? sanitize_key( $collection['id'] ) : '';
			if ( '' === $id ) { $id = sanitize_title( $name ) . '-' . absint( $index + 1 ); }
			$source = isset( $collection['source'] ) ? sanitize_key( $collection['source'] ) : 'tag';
			if ( ! in_array( $source, array( 'category', 'tag', 'categories-tags', 'manual' ), true ) ) { $source = 'tag'; }
			$out[ $id ] = array(
				'id' => $id,
				'name' => $name,
				'description' => isset( $collection['description'] ) ? sanitize_textarea_field( $collection['description'] ) : '',
				'source' => $source,
				'category' => isset( $collection['category'] ) ? sanitize_title( $collection['category'] ) : '',
				'tag' => isset( $collection['tag'] ) ? sanitize_title( $collection['tag'] ) : '',
				'posts' => self::id_list( isset( $collection['posts'] ) ? $collection['posts'] : array() ),
				'featured_image' => isset( $collection['featured_image'] ) ? absint( $collection['featured_image'] ) : 0,
				'order' => self::one_of( $collection, 'order', array( 'newest', 'chronological', 'manual' ), 'newest' ),
				'count' => self::bounded_int( $collection, 'count', 1, 12, 5 ),
				'style' => self::one_of( $collection, 'style', array( 'editorial', 'cards', 'feature', 'minimal' ), 'editorial' ),
			);
		}
		return $out;
	}

	/**
	 * A deliberately small SVG allowlist for the menu icon.
	 *
	 * @param string $svg SVG markup.
	 * @return string
	 */
	public static function sanitize_svg( $svg ) {
		$allowed = array(
			'svg' => array(
				'xmlns' => true, 'width' => true, 'height' => true, 'viewbox' => true,
				'viewBox' => true, 'fill' => true, 'stroke' => true, 'aria-hidden' => true,
				'role' => true, 'focusable' => true, 'class' => true,
			),
			'path' => array( 'd' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true ),
			'line' => array( 'x1' => true, 'x2' => true, 'y1' => true, 'y2' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true ),
			'circle' => array( 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ),
			'g' => array( 'fill' => true, 'stroke' => true, 'transform' => true ),
		);
		$clean = wp_kses( $svg, $allowed );
		return false !== strpos( $clean, '<svg' ) ? $clean : self::default_menu_svg();
	}

	/** @return string */
	public static function default_menu_svg() {
		return '<svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M4 7h16M4 12h16M4 17h16" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/></svg>';
	}

	/** @return array<string,mixed> */
	private static function merge_recursive( $defaults, $stored ) {
		foreach ( $stored as $key => $value ) {
			if ( isset( $defaults[ $key ] ) && is_array( $defaults[ $key ] ) && is_array( $value ) ) {
				$defaults[ $key ] = self::merge_recursive( $defaults[ $key ], $value );
			} else {
				$defaults[ $key ] = $value;
			}
		}
		return $defaults;
	}
}
