<?php
/**
 * Safe, repairable starter-site setup.
 *
 * @package Popped
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Popped_Setup {
	public static function hooks() {
		add_action( 'admin_post_popped_quick_setup', array( __CLASS__, 'run' ) );
		add_action( 'admin_notices', array( __CLASS__, 'activation_notice' ) );
	}

	public static function activation_notice() {
		if ( ! get_transient( 'popped_activation_notice' ) || ! current_user_can( 'manage_options' ) ) { return; }
		delete_transient( 'popped_activation_notice' );
		echo '<div class="notice notice-info is-dismissible"><p><strong>' . esc_html__( 'Popped is ready.', 'popped' ) . '</strong> ' . esc_html__( 'Run Setup to create or safely repair the editorial structure.', 'popped' ) . ' <a href="' . esc_url( admin_url( 'admin.php?page=popped-setup' ) ) . '">' . esc_html__( 'Open Setup', 'popped' ) . '</a></p></div>';
	}

	public static function run() {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'You are not allowed to configure Popped.', 'popped' ) ); }
		check_admin_referer( 'popped_setup' );
		$mode = isset( $_POST['setup_mode'] ) ? sanitize_key( wp_unslash( $_POST['setup_mode'] ) ) : 'quick';
		$result = self::execute( $mode, wp_unslash( $_POST ) );
		set_transient( 'popped_setup_result_' . get_current_user_id(), $result, 5 * MINUTE_IN_SECONDS );
		wp_safe_redirect( admin_url( 'admin.php?page=popped-setup' ) );
		exit;
	}

	/**
	 * Run setup and return a human-readable repair report. Public for deterministic
	 * integration tests; the request-facing wrapper enforces capability and nonce.
	 *
	 * @param string              $mode quick|custom.
	 * @param array<string,mixed> $input Sanitized by this method.
	 * @return array<string,string>
	 */
	public static function execute( $mode = 'quick', $input = array() ) {
		$options        = Popped_Settings::all();
		$result         = array();
		$setup_complete = true;

		if ( 'custom' === $mode ) {
			$options['typography'] = self::choice( $input, 'typography', array( 'inherit', 'clean', 'editorial', 'magazine', 'custom' ), 'inherit' );
			$options['density'] = self::choice( $input, 'density', array( 'compact', 'standard', 'spacious' ), 'standard' );
			$options['timeline_layout'] = self::choice( $input, 'timeline_layout', array( 'vertical', 'horizontal' ), 'vertical' );
			$options['timeline_tag'] = ! empty( $input['timeline_tag'] ) ? sanitize_title( $input['timeline_tag'] ) : 'timeline';
		}

		$term = term_exists( $options['timeline_tag'], 'post_tag' );
		if ( ! $term ) {
			$created = wp_insert_term(
				ucwords( str_replace( '-', ' ', $options['timeline_tag'] ) ),
				'post_tag',
				array( 'slug' => $options['timeline_tag'] )
			);
			if ( is_wp_error( $created ) ) {
				$result['Timeline tag'] = __( 'Could not be created', 'popped' );
				$setup_complete = false;
			} else {
				$result['Timeline tag'] = __( 'Created', 'popped' );
			}
		} else {
			$result['Timeline tag'] = __( 'Already configured', 'popped' );
		}

		$pages = array(
			'timeline' => array( 'option' => 'timeline_page_id', 'slug' => 'timeline', 'fallback' => 'popped-timeline', 'title' => __( 'Timeline', 'popped' ), 'block' => 'popped/timeline', 'content' => '<!-- wp:popped/timeline /-->' ),
			'archive' => array( 'option' => 'archive_page_id', 'slug' => 'archive', 'fallback' => 'popped-archive', 'title' => __( 'Archive', 'popped' ), 'block' => 'popped/archive-explorer', 'content' => '<!-- wp:popped/archive-explorer /-->' ),
			'search' => array( 'option' => 'search_page_id', 'slug' => 'search-archive', 'fallback' => 'popped-search', 'title' => __( 'Search', 'popped' ), 'block' => 'popped/search', 'content' => '<!-- wp:popped/search /-->' ),
		);
		foreach ( $pages as $role => $definition ) {
			$page = self::ensure_page(
				$role,
				$definition,
				isset( $options[ $definition['option'] ] ) ? absint( $options[ $definition['option'] ] ) : 0
			);
			$options[ $definition['option'] ] = $page['id'];
			$result[ ucfirst( $role ) ] = $page['status'];
			if ( ! $page['ok'] ) {
				$setup_complete = false;
			}
		}


		$options['setup_complete'] = $setup_complete;
		$options['version'] = POPPED_VERSION;
		$result['Setup'] = $setup_complete ? __( 'Complete', 'popped' ) : __( 'Needs attention', 'popped' );
		Popped_Settings::store( $options );
		flush_rewrite_rules();

		return $result;
	}

	/** @return array{id:int,status:string,ok:bool} */
	private static function ensure_page( $role, $definition, $stored_id ) {
		$page = $stored_id ? get_post( $stored_id ) : null;

		if ( $page && ( 'page' !== $page->post_type || 'trash' === $page->post_status ) ) {
			$page = null;
		}

		if ( ! $page ) {
			foreach ( array( $definition['slug'], $definition['fallback'] ) as $managed_slug ) {
				$slug_page = get_page_by_path( $managed_slug, OBJECT, 'page' );
				if ( ! $slug_page || 'trash' === $slug_page->post_status ) {
					continue;
				}
				$managed_role = get_post_meta( $slug_page->ID, '_popped_page_role', true );
				if ( $role === $managed_role || has_block( $definition['block'], $slug_page->post_content ) ) {
					$page = $slug_page;
					break;
				}
			}
		}

		if ( $page ) {
			if ( 'publish' !== $page->post_status ) {
				return array(
					'id'     => (int) $page->ID,
					'status' => __( 'Needs publishing', 'popped' ),
					'ok'     => false,
				);
			}

			$status = __( 'Already configured', 'popped' );
			if ( ! has_block( $definition['block'], $page->post_content ) ) {
				$content = trim( $page->post_content ) . "\n\n" . $definition['content'];
				$updated = wp_update_post(
					array(
						'ID'           => $page->ID,
						'post_content' => trim( $content ),
					),
					true
				);
				if ( is_wp_error( $updated ) || ! $updated ) {
					return array(
						'id'     => (int) $page->ID,
						'status' => __( 'Could not be repaired', 'popped' ),
						'ok'     => false,
					);
				}
				$status = __( 'Repaired', 'popped' );
			}

			update_post_meta( $page->ID, '_popped_page_role', $role );
			return array(
				'id'     => (int) $page->ID,
				'status' => $status,
				'ok'     => true,
			);
		}

		$preferred = get_page_by_path( $definition['slug'] );
		$slug      = $preferred ? $definition['fallback'] : $definition['slug'];
		$id        = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_name'    => $slug,
				'post_title'   => $definition['title'],
				'post_content' => $definition['content'],
				'meta_input'   => array( '_popped_page_role' => $role ),
			),
			true
		);

		if ( is_wp_error( $id ) || ! $id ) {
			return array(
				'id'     => 0,
				'status' => __( 'Could not be created', 'popped' ),
				'ok'     => false,
			);
		}

		return array(
			'id'     => (int) $id,
			'status' => __( 'Created', 'popped' ),
			'ok'     => true,
		);
	}

	/** @return string */
	private static function choice( $input, $key, $allowed, $fallback ) {
		$value = isset( $input[ $key ] ) ? sanitize_key( $input[ $key ] ) : $fallback;
		return in_array( $value, $allowed, true ) ? $value : $fallback;
	}
}
