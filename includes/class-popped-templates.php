<?php
/**
 * Theme-coexistence and optional legacy template helpers.
 *
 * Popped 2.1+ treats the active theme as the owner of site templates. The plugin
 * supplies blocks and optional archive pages; it never creates database-backed
 * single/archive/search/front-page templates.
 *
 * @package Popped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Popped_Templates {
	/**
	 * Retire database templates created by Popped 2.0.x and release page-template
	 * assignments made by the old Setup flow.
	 *
	 * Only records carrying Popped's ownership marker are touched.
	 *
	 * @return int Number of records released.
	 */
	public static function release_legacy_templates() {
		$release_key = 'popped_template_release_2_1';
		if ( get_option( $release_key ) ) {
			return 0;
		}

		$released = 0;
		if ( post_type_exists( 'wp_template' ) ) {
			$templates = get_posts(
				array(
					'post_type'      => 'wp_template',
					'post_status'    => array( 'publish', 'draft', 'private', 'trash' ),
					'posts_per_page' => -1,
					'meta_key'       => '_popped_template',
					'meta_value'     => '1',
				)
			); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key

			foreach ( $templates as $template ) {
				if ( ! in_array( $template->post_name, array( 'front-page', 'single', 'archive', 'search', '404' ), true ) ) {
					continue;
				}
				if ( 'trash' !== $template->post_status && wp_trash_post( $template->ID ) ) {
					$released++;
				}
			}
		}

		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => array( 'publish', 'draft', 'private', 'future' ),
				'posts_per_page' => -1,
				'meta_key'       => '_popped_page_role',
			)
		); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		foreach ( $pages as $page ) {
			$template = get_post_meta( $page->ID, '_wp_page_template', true );
			if ( in_array( $template, array( 'popped-timeline', 'popped-archive-page', 'popped-search-page' ), true ) ) {
				update_post_meta( $page->ID, '_wp_page_template', 'default' );
				$released++;
			}
		}

		update_option( $release_key, POPPED_VERSION, false );
		return $released;
	}

	/**
	 * Backwards-compatible alias retained for upgrades from 2.0.5–2.0.8.
	 *
	 * @return int
	 */
	public static function release_legacy_front_page_template() {
		return self::release_legacy_templates();
	}

	/**
	 * Popped 2.1 no longer registers site-level plugin templates.
	 *
	 * @return void
	 */
	public static function register_block_templates() {}

	/**
	 * Optional classic-theme shell. This is off by default and never runs for
	 * block themes, where the theme/Site Editor remains authoritative.
	 *
	 * @param string $template Theme template path.
	 * @return string
	 */
	public static function template_include( $template ) {
		if ( is_admin() || ! Popped_Settings::get( 'template_mode', false ) || is_front_page() ) {
			return $template;
		}
		if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
			return $template;
		}

		$file = '';
		if ( is_singular( 'post' ) ) {
			$file = 'single.php';
		} elseif ( is_search() ) {
			$file = 'search.php';
		} elseif ( is_404() ) {
			$file = '404.php';
		} elseif ( is_archive() || is_home() ) {
			$file = 'archive.php';
		} elseif ( is_page() && self::is_managed_request() ) {
			$file = 'canvas.php';
		}

		$path = $file ? POPPED_DIR . 'templates/' . $file : '';
		return $path && file_exists( $path ) ? $path : $template;
	}

	/** @return bool */
	public static function is_managed_request() {
		if ( ! Popped_Settings::get( 'template_mode', false ) || is_front_page() ) {
			return false;
		}
		if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
			return false;
		}
		if ( is_singular( 'post' ) || is_search() || is_archive() || is_home() || is_404() ) {
			return true;
		}
		if ( ! is_page() ) {
			return false;
		}

		$managed = array_map(
			'absint',
			array(
				Popped_Settings::get( 'timeline_page_id', 0 ),
				Popped_Settings::get( 'archive_page_id', 0 ),
				Popped_Settings::get( 'search_page_id', 0 ),
			)
		);
		if ( in_array( get_queried_object_id(), array_filter( $managed ), true ) ) {
			return true;
		}

		return self::page_contains_managed_block();
	}

	/** @return bool */
	public static function contains_popped_content() {
		if ( ! is_singular() ) {
			return false;
		}
		$post = get_post( get_queried_object_id() );
		return $post && has_blocks( $post ) && false !== strpos( (string) $post->post_content, '<!-- wp:popped/' );
	}

	/** @return bool */
	private static function page_contains_managed_block() {
		$post = get_post( get_queried_object_id() );
		if ( ! $post ) {
			return false;
		}
		foreach ( array( 'popped/homepage', 'popped/timeline', 'popped/archive-explorer', 'popped/search' ) as $block_name ) {
			if ( has_block( $block_name, $post ) ) {
				return true;
			}
		}
		return false;
	}

	/** @return bool */
	public static function uses_native_shell() {
		return false;
	}

	/**
	 * Kept as a no-op for API compatibility.
	 *
	 * @param string              $block_content Rendered block content.
	 * @param array<string,mixed> $block Parsed block.
	 * @return string
	 */
	public static function filter_native_template_part( $block_content, $block ) {
		return $block_content;
	}

	/**
	 * Whether the current native search request was initiated by a Popped Search
	 * block or the administrator explicitly opted into site-wide enrichment.
	 *
	 * @param WP_Query $query Query.
	 * @return bool
	 */
	private static function is_popped_search_request( $query ) {
		if ( Popped_Settings::get( 'taxonomy_search', false ) ) {
			return true;
		}
		if ( ! $query->is_search() ) {
			return false;
		}
		$marker = isset( $_GET['_popped_search'] ) ? sanitize_key( wp_unslash( $_GET['_popped_search'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return '1' === $marker;
	}

	/**
	 * Extend Popped-initiated search to category and tag names without changing
	 * normal theme/WordPress search globally.
	 *
	 * @param string   $search Native search SQL.
	 * @param WP_Query $query Query.
	 * @return string
	 */
	public static function taxonomy_search( $search, $query ) {
		if ( is_admin() || ! $query->is_main_query() || ! self::is_popped_search_request( $query ) ) {
			return $search;
		}

		$needle = trim( (string) $query->get( 's' ) );
		if ( '' === $needle || $query->get( 'sentence' ) || $query->get( 'exact' ) || preg_match( '/(?:^|\s)-\S+/', $needle ) ) {
			return $search;
		}

		global $wpdb;
		$terms = preg_split( '/\s+/', $needle );
		$parts = array();
		foreach ( array_slice( array_filter( $terms ), 0, 8 ) as $term ) {
			$like = '%' . $wpdb->esc_like( $term ) . '%';
			$parts[] = $wpdb->prepare(
				"({$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.post_excerpt LIKE %s OR {$wpdb->posts}.post_content LIKE %s OR EXISTS (SELECT 1 FROM {$wpdb->term_relationships} popped_tr INNER JOIN {$wpdb->term_taxonomy} popped_tt ON popped_tt.term_taxonomy_id = popped_tr.term_taxonomy_id INNER JOIN {$wpdb->terms} popped_t ON popped_t.term_id = popped_tt.term_id WHERE popped_tr.object_id = {$wpdb->posts}.ID AND popped_tt.taxonomy IN ('category','post_tag') AND popped_t.name LIKE %s))",
				$like,
				$like,
				$like,
				$like
			);
		}
		if ( ! $parts ) {
			return $search;
		}
		$visibility = is_user_logged_in() ? '' : $wpdb->prepare( " AND ({$wpdb->posts}.post_password = %s)", '' );
		return ' AND (' . implode( ' AND ', $parts ) . ')' . $visibility;
	}

	/**
	 * Apply visitor-facing category and tag filters only to Popped search requests.
	 *
	 * @param WP_Query $query Query.
	 * @return void
	 */
	public static function filter_search_results( $query ) {
		if ( is_admin() || ! $query->is_main_query() || ! self::is_popped_search_request( $query ) ) {
			return;
		}
		$category = isset( $_GET['_popped_search_cat'] ) ? sanitize_title( wp_unslash( $_GET['_popped_search_cat'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tag      = isset( $_GET['_popped_search_tag'] ) ? sanitize_title( wp_unslash( $_GET['_popped_search_tag'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $category ) {
			$query->set( 'category_name', $category );
		}
		if ( $tag ) {
			$query->set( 'tag', $tag );
		}
	}
}
