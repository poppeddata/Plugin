<?php
/**
 * Shared content-source query system.
 *
 * @package Popped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Popped_Query {
	/**
	 * Build a WP_Query from global defaults and local block overrides.
	 *
	 * @param array<string,mixed> $config Content-source settings.
	 * @param array<string,mixed> $context Additional safe query arguments.
	 * @return WP_Query
	 */
	public static function get( $config = array(), $context = array() ) {
		$defaults = array(
			'source'            => 'all',
			'categories'        => array(),
			'tags'              => array(),
			'posts'             => array(),
			'excludeCategories' => array(),
			'excludeTags'       => array(),
			'excludePosts'      => array(),
			'order'             => 'newest',
			'count'             => 5,
		);
		$config = wp_parse_args( $config, $defaults );

		$args = array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'ignore_sticky_posts'    => true,
			'has_password'           => false,
			'no_found_rows'          => empty( $context['paged'] ),
			'update_post_meta_cache' => true,
			'update_post_term_cache' => true,
			'posts_per_page'         => max( 1, min( 100, absint( $config['count'] ) ) ),
		);

		$order = sanitize_key( $config['order'] );
		switch ( $order ) {
			case 'oldest':
			case 'chronological':
				$args['orderby'] = 'date';
				$args['order']   = 'ASC';
				break;
			case 'random':
				// Random SQL ordering does not scale on large archives. Existing blocks
				// using the legacy value fall back to newest-first deterministically.
				$args['orderby'] = 'date';
				$args['order']   = 'DESC';
				break;
			case 'manual':
				$args['orderby'] = 'post__in';
				break;
			default:
				$args['orderby'] = 'date';
				$args['order']   = 'DESC';
		}

		$tax_query = array();
		$source    = sanitize_key( $config['source'] );
		if ( 'category' === $source ) { $source = 'categories'; }
		if ( 'tag' === $source ) { $source = 'tags'; }
		if ( 'timeline' === $source ) {
			$tax_query[] = array(
				'taxonomy' => 'post_tag',
				'field'    => 'slug',
				'terms'    => array( sanitize_title( Popped_Settings::get( 'timeline_tag', 'timeline' ) ) ),
			);
		}

		$categories = self::term_list( $config['categories'] );
		$tags       = self::term_list( $config['tags'] );
		if ( in_array( $source, array( 'categories', 'categories-tags' ), true ) && $categories ) {
			$tax_query[] = array( 'taxonomy' => 'category', 'field' => self::term_field( $categories ), 'terms' => $categories );
		}
		if ( in_array( $source, array( 'tags', 'categories-tags' ), true ) && $tags ) {
			$tax_query[] = array( 'taxonomy' => 'post_tag', 'field' => self::term_field( $tags ), 'terms' => $tags );
		}

		$manual_posts = Popped_Settings::id_list( $config['posts'] );
		if ( 'manual' === $source ) {
			$args['post__in'] = $manual_posts ? $manual_posts : array( 0 );
			if ( 'manual' === $order ) {
				$args['orderby'] = 'post__in';
			}
		}

		$exclude_categories = self::term_list( $config['excludeCategories'] );
		$exclude_tags       = self::term_list( $config['excludeTags'] );
		if ( $exclude_categories ) {
			$tax_query[] = array( 'taxonomy' => 'category', 'field' => self::term_field( $exclude_categories ), 'terms' => $exclude_categories, 'operator' => 'NOT IN' );
		}
		if ( $exclude_tags ) {
			$tax_query[] = array( 'taxonomy' => 'post_tag', 'field' => self::term_field( $exclude_tags ), 'terms' => $exclude_tags, 'operator' => 'NOT IN' );
		}
		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'AND';
		}
		if ( $tax_query ) {
			$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		}

		$exclude_posts = Popped_Settings::id_list( $config['excludePosts'] );
		if ( $exclude_posts ) {
			$args['post__not_in'] = $exclude_posts;
		}

		$allowed_context = array( 'paged', 's', 'date_query', 'meta_query', 'post__not_in', 'post__in', 'year', 'monthnum', 'category_name', 'tag', 'offset' );
		foreach ( $allowed_context as $key ) {
			if ( isset( $context[ $key ] ) && '' !== $context[ $key ] ) {
				$args[ $key ] = $context[ $key ];
			}
		}

		if ( isset( $context['paged'] ) ) {
			$args['no_found_rows'] = false;
		}

		return new WP_Query( $args );
	}

	/**
	 * Posts sharing a calendar day/month in previous years.
	 *
	 * @param int                 $month Month 1-12.
	 * @param int                 $day Day 1-31.
	 * @param array<string,mixed> $config Source settings.
	 * @param int                 $count Result count.
	 * @param int[]               $exclude Post IDs.
	 * @return WP_Post[]
	 */
	public static function on_this_day( $month, $day, $config = array(), $count = 12, $exclude = array() ) {
		$result = self::on_this_day_results( $month, $day, $config, $count, $exclude );
		return $result['posts'];
	}

	/**
	 * Return On This Day posts plus the total matching previous-year stories.
	 *
	 * @param int                 $month Month 1-12.
	 * @param int                 $day Day 1-31.
	 * @param array<string,mixed> $config Source settings.
	 * @param int                 $count Result count.
	 * @param int[]               $exclude Post IDs.
	 * @return array{posts:WP_Post[],total:int}
	 */
	public static function on_this_day_results( $month, $day, $config = array(), $count = 12, $exclude = array() ) {
		$month    = max( 1, min( 12, absint( $month ) ) );
		$day      = max( 1, min( 31, absint( $day ) ) );
		$count    = max( 1, min( 100, absint( $count ) ) );
		$excluded = array_values(
			array_unique(
				array_merge(
					Popped_Settings::id_list( isset( $config['excludePosts'] ) ? $config['excludePosts'] : array() ),
					Popped_Settings::id_list( $exclude )
				)
			)
		);
		$key = 'otd_' . md5( wp_json_encode( array( $month, $day, $config, $count, $excluded, wp_date( 'Y' ), get_option( 'popped_content_cache_version', 1 ) ) ) );
		$cached = wp_cache_get( $key, 'popped' );
		if ( false !== $cached && is_array( $cached ) && isset( $cached['posts'], $cached['total'] ) ) {
			return $cached;
		}

		$config['count'] = $count;
		$query = self::get(
			$config,
			array(
				'date_query'   => self::on_this_day_date_query( $month, $day ),
				'post__not_in' => $excluded,
				'paged'        => 1,
			)
		);
		$result = array(
			'posts' => $query->posts,
			'total' => (int) $query->found_posts,
		);
		wp_cache_set( $key, $result, 'popped', HOUR_IN_SECONDS );
		return $result;
	}

	/**
	 * Find an eligible story explicitly preferred as the On This Day hero.
	 *
	 * @param int                 $month Month 1-12.
	 * @param int                 $day Day 1-31.
	 * @param array<string,mixed> $config Source settings.
	 * @param int[]               $exclude Post IDs.
	 * @return WP_Post|null
	 */
	public static function on_this_day_preferred( $month, $day, $config = array(), $exclude = array() ) {
		$excluded = array_values(
			array_unique(
				array_merge(
					Popped_Settings::id_list( isset( $config['excludePosts'] ) ? $config['excludePosts'] : array() ),
					Popped_Settings::id_list( $exclude )
				)
			)
		);
		$config['count'] = 1;
		$query = self::get(
			$config,
			array(
				'date_query'   => self::on_this_day_date_query( $month, $day ),
				'meta_query'   => array(
					array(
						'key'     => '_popped_otd_primary',
						'value'   => '1',
						'compare' => '=',
					),
				),
				'post__not_in' => $excluded,
			)
		);
		return $query->have_posts() ? $query->posts[0] : null;
	}

	/**
	 * Resolve a configured On This Day override only when it still matches the block source and date.
	 *
	 * @param int                 $post_id Override post ID.
	 * @param int                 $month Month 1-12.
	 * @param int                 $day Day 1-31.
	 * @param array<string,mixed> $config Source settings.
	 * @param int[]               $exclude Post IDs.
	 * @return WP_Post|null
	 */
	public static function on_this_day_override( $post_id, $month, $day, $config = array(), $exclude = array() ) {
		$post_id = absint( $post_id );
		if ( ! $post_id || in_array( $post_id, Popped_Settings::id_list( $exclude ), true ) ) {
			return null;
		}
		if ( 'manual' === sanitize_key( isset( $config['source'] ) ? $config['source'] : '' ) ) {
			$manual = Popped_Settings::id_list( isset( $config['posts'] ) ? $config['posts'] : array() );
			if ( ! in_array( $post_id, $manual, true ) ) {
				return null;
			}
		}

		$config['count'] = 1;
		$query = self::get(
			$config,
			array(
				'date_query' => self::on_this_day_date_query( $month, $day ),
				'post__in'   => array( $post_id ),
			)
		);
		return $query->have_posts() && (int) $query->posts[0]->ID === $post_id ? $query->posts[0] : null;
	}

	/**
	 * Match the selected calendar date while excluding the current year.
	 *
	 * @param int $month Month 1-12.
	 * @param int $day Day 1-31.
	 * @return array<int|string,mixed>
	 */
	private static function on_this_day_date_query( $month, $day ) {
		return array(
			'relation' => 'AND',
			array(
				'month' => max( 1, min( 12, absint( $month ) ) ),
				'day'   => max( 1, min( 31, absint( $day ) ) ),
			),
			array(
				'before'    => sprintf( '%d-01-01 00:00:00', max( 1, (int) wp_date( 'Y' ) ) ),
				'inclusive' => false,
			),
		);
	}
	/**
	 * Rank related posts by overlapping categories/tags, then by date proximity.
	 *
	 * @param int $post_id Source post ID.
	 * @param int $count Result count.
	 * @param string $relevance Taxonomy relevance: both, category or tag.
	 * @return WP_Post[]
	 */
	public static function related( $post_id, $count = 4, $relevance = 'both' ) {
		$post_id  = absint( $post_id );
		$count    = max( 1, min( 100, absint( $count ) ) );
		$relevance = in_array( $relevance, array( 'both', 'category', 'tag' ), true ) ? $relevance : 'both';
		$source   = get_post( $post_id );
		if ( ! $source ) {
			return array();
		}

		$tags = wp_get_post_tags( $post_id, array( 'fields' => 'ids' ) );
		$cats = wp_get_post_categories( $post_id );
		$tags = is_wp_error( $tags ) ? array() : array_map( 'absint', $tags );
		$cats = is_wp_error( $cats ) ? array() : array_map( 'absint', $cats );

		$conditions = array();
		$args       = array( 'post', 'publish', $post_id );

		if ( $tags && in_array( $relevance, array( 'both', 'tag' ), true ) ) {
			$conditions[] = "(tt.taxonomy = 'post_tag' AND tt.term_id IN (" . implode( ',', array_fill( 0, count( $tags ), '%d' ) ) . '))';
			$args = array_merge( $args, $tags );
		}
		if ( $cats && in_array( $relevance, array( 'both', 'category' ), true ) ) {
			$conditions[] = "(tt.taxonomy = 'category' AND tt.term_id IN (" . implode( ',', array_fill( 0, count( $cats ), '%d' ) ) . '))';
			$args = array_merge( $args, $cats );
		}
		if ( ! $conditions ) {
			return array();
		}

		$source_date = '0000-00-00 00:00:00' !== $source->post_date_gmt ? $source->post_date_gmt : get_gmt_from_date( $source->post_date );
		$args[]      = $source_date;
		$args[]      = $count;

		global $wpdb;
		$sql = "
			SELECT p.ID
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID
			INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
			WHERE p.post_type = %s
				AND p.post_status = %s
				AND p.post_password = ''
				AND p.ID <> %d
				AND (" . implode( ' OR ', $conditions ) . ")
			GROUP BY p.ID
			ORDER BY COUNT(DISTINCT tt.term_taxonomy_id) DESC,
				ABS(TIMESTAMPDIFF(SECOND, p.post_date_gmt, %s)) ASC,
				p.post_date_gmt DESC,
				p.ID DESC
			LIMIT %d
		";
		$ids = array_map( 'absint', $wpdb->get_col( $wpdb->prepare( $sql, $args ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		if ( ! $ids ) {
			return array();
		}

		return get_posts(
			array(
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'posts_per_page'      => count( $ids ),
				'post__in'            => $ids,
				'orderby'             => 'post__in',
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
				'suppress_filters'    => false,
			)
		);
	}

	/** @return array<int|string> */
	private static function term_list( $value ) {
		$parts = is_array( $value ) ? $value : preg_split( '/[\s,]+/', (string) $value );
		$out   = array();
		foreach ( $parts as $part ) {
			if ( is_numeric( $part ) ) {
				$out[] = absint( $part );
			} elseif ( '' !== trim( (string) $part ) ) {
				$out[] = sanitize_title( $part );
			}
		}
		return array_values( array_unique( array_filter( $out ) ) );
	}

	/** @param array<int|string> $terms Terms. @return string */
	private static function term_field( $terms ) {
		return isset( $terms[0] ) && is_int( $terms[0] ) ? 'term_id' : 'slug';
	}
}
