<?php
/**
 * Plugin bootstrap.
 *
 * @package Popped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Popped_Plugin {
	/** @var self|null */
	private static $instance = null;

	/** @return self */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'load_textdomain' ), 0 );
		add_action( 'init', array( 'Popped_Settings', 'register' ) );
		add_action( 'init', array( 'Popped_Blocks', 'register_pattern_category' ), 9 );
		add_action( 'init', array( 'Popped_Blocks', 'register' ) );
		add_action( 'init', array( 'Popped_Blocks', 'register_patterns' ), 20 );
		add_action( 'init', array( 'Popped_Templates', 'release_legacy_templates' ), 24 );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( 'Popped_Blocks', 'editor_assets' ) );
		add_filter( 'block_categories_all', array( 'Popped_Blocks', 'block_category' ), 10, 2 );
		add_filter( 'template_include', array( 'Popped_Templates', 'template_include' ), 99 );
		add_filter( 'render_block_core/template-part', array( 'Popped_Templates', 'filter_native_template_part' ), 10, 2 );
		add_filter( 'posts_search', array( 'Popped_Templates', 'taxonomy_search' ), 20, 2 );
		add_action( 'pre_get_posts', array( 'Popped_Templates', 'filter_search_results' ) );
		add_filter( 'the_content', array( 'Popped_Components', 'append_article_discovery' ), 20 );
		add_filter( 'body_class', array( $this, 'body_classes' ) );
		add_filter( 'upload_mimes', array( $this, 'font_mimes' ) );
		add_action( 'save_post_post', array( $this, 'invalidate_content_cache' ), 10, 3 );
		add_action( 'deleted_post', array( $this, 'invalidate_content_cache' ) );
		add_action( 'set_object_terms', array( $this, 'invalidate_content_cache' ), 10, 6 );
		add_action( 'wp_body_open', array( 'Popped_Components', 'render_global_header' ), 5 );
		add_action( 'wp_footer', array( 'Popped_Components', 'render_site_footer' ), 1 );
		add_action( 'wp_footer', array( 'Popped_Components', 'render_navigation_overlay' ), 5 );

		if ( is_admin() ) {
			Popped_Admin::hooks();
			Popped_Setup::hooks();
		}
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'popped', false, dirname( plugin_basename( POPPED_FILE ) ) . '/languages' );
	}

	public function frontend_assets() {
		$needs_shell_assets = Popped_Templates::is_managed_request();
		$needs_discovery_assets = is_singular( 'post' ) && Popped_Settings::get( 'append_discovery', false );

		if ( ! $needs_shell_assets && ! $needs_discovery_assets ) {
			return;
		}

		wp_enqueue_style( 'popped' );
		wp_enqueue_script( 'popped' );
	}

	/** @param string[] $classes Body classes. @return string[] */
	public function body_classes( $classes ) {
		$managed   = Popped_Templates::is_managed_request();
		$discovery = is_singular( 'post' ) && Popped_Settings::get( 'append_discovery', false );
		if ( ! $managed && ! $discovery ) {
			return $classes;
		}

		$classes[] = 'popped-density-' . sanitize_html_class( Popped_Settings::get( 'density', 'standard' ) );
		$classes[] = 'popped-type-' . sanitize_html_class( Popped_Settings::get( 'typography', 'inherit' ) );
		$classes[] = 'popped-shape-' . sanitize_html_class( Popped_Settings::get( 'shape', 'soft' ) );
		$classes[] = 'popped-motion-' . sanitize_html_class( Popped_Settings::get( 'motion', 'standard' ) );
		if ( $managed && Popped_Settings::get( 'sticky_header', true ) ) {
			$classes[] = 'popped-sticky-header';
			$classes[] = 'popped-managed-request';
		}
		return $classes;
	}


	/** @return string Safe CSS containing semantic tokens and the selected font face. */
	public static function design_token_css() {
		$colours = Popped_Settings::get( 'colours', Popped_Settings::defaults()['colours'] );
		$tokens  = array();
		foreach ( Popped_Settings::defaults()['colours'] as $role => $fallback ) {
			$value = isset( $colours[ $role ] ) ? sanitize_hex_color( $colours[ $role ] ) : $fallback;
			$tokens[] = '--popped-' . sanitize_key( $role ) . ':' . ( $value ? $value : $fallback );
		}
		$font_css = '';
		$font_id  = absint( Popped_Settings::get( 'custom_font_id', 0 ) );
		$font_role = sanitize_key( Popped_Settings::get( 'custom_font_role', 'none' ) );
		if ( $font_id && 'none' !== $font_role ) {
			$font_url = wp_get_attachment_url( $font_id );
			$name     = preg_replace( '/[^a-zA-Z0-9 _-]/', '', sanitize_text_field( Popped_Settings::get( 'custom_font_name', 'Popped Custom' ) ) );
			$fallback = preg_replace( '/[^a-zA-Z0-9 ,"\'-]/', '', sanitize_text_field( Popped_Settings::get( 'custom_font_fallback', '-apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif' ) ) );
			$extension = strtolower( pathinfo( (string) wp_parse_url( $font_url, PHP_URL_PATH ), PATHINFO_EXTENSION ) );
			$formats = array( 'woff2' => 'woff2', 'woff' => 'woff', 'ttf' => 'truetype', 'otf' => 'opentype' );
			if ( $font_url && isset( $formats[ $extension ] ) ) {
				$font_css = '@font-face{font-family:"' . $name . '";src:url("' . esc_url( $font_url ) . '") format("' . $formats[ $extension ] . '");font-display:swap}:root{--popped-font-' . sanitize_key( $font_role ) . ':"' . $name . '",' . $fallback . '}';
			}
		}
		return ':root{' . implode( ';', $tokens ) . '}' . $font_css;
	}

	/** @param array<string,string> $mimes Allowed MIME types. @return array<string,string> */
	public function font_mimes( $mimes ) {
		if ( current_user_can( 'manage_options' ) ) {
			$mimes['woff']  = 'font/woff';
			$mimes['woff2'] = 'font/woff2';
			$mimes['ttf']   = 'font/ttf';
			$mimes['otf']   = 'font/otf';
		}
		return $mimes;
	}

	/**
	 * Invalidate persistent Popped query caches when editorial content changes.
	 *
	 * @return void
	 */
	public function invalidate_content_cache() {
		$version = max( 1, absint( get_option( 'popped_content_cache_version', 1 ) ) );
		update_option( 'popped_content_cache_version', $version + 1, false );
	}

	public static function activate() {
		if ( ! get_option( Popped_Settings::OPTION ) ) {
			add_option( Popped_Settings::OPTION, Popped_Settings::defaults(), '', false );
		}
		set_transient( 'popped_activation_notice', 1, MINUTE_IN_SECONDS );
		flush_rewrite_rules();
	}

	public static function deactivate() {
		flush_rewrite_rules();
	}
}
