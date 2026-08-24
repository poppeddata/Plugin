<?php
/**
 * Live WordPress smoke tests for CI.
 *
 * Run with:
 * wp eval-file wp-content/plugins/popped/tools/wordpress_smoke.php
 *
 * @package Popped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

/**
 * Fail the smoke suite with a useful message.
 *
 * @param bool   $condition Assertion condition.
 * @param string $message Failure message.
 * @return void
 */
function popped_smoke_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
}

/**
 * Run the live smoke suite.
 *
 * @return void
 */
function popped_smoke_run() {
	$plugin_headers = get_file_data( POPPED_DIR . 'popped.php', array( 'Version' => 'Version' ), 'plugin' );
	popped_smoke_assert(
		defined( 'POPPED_VERSION' ) && POPPED_VERSION === $plugin_headers['Version'],
		'POPPED_VERSION does not match the plugin header.'
	);

	$options = Popped_Settings::all();
	popped_smoke_assert( empty( $options['template_mode'] ), 'Legacy template shell must be opt-in.' );
	popped_smoke_assert( empty( $options['append_discovery'] ), 'Global article discovery must be opt-in.' );
	popped_smoke_assert( empty( $options['taxonomy_search'] ), 'Global taxonomy search enrichment must be opt-in.' );

	$registered = WP_Block_Type_Registry::get_instance()->get_all_registered();
	$popped     = array_filter(
		$registered,
		static function ( $block, $name ) {
			return 0 === strpos( $name, 'popped/' );
		},
		ARRAY_FILTER_USE_BOTH
	);
	popped_smoke_assert( 15 === count( $popped ), 'Expected 15 registered Popped blocks.' );

	foreach ( $popped as $name => $block ) {
		popped_smoke_assert( isset( $block->attributes['headingLevel'] ), "{$name} is missing headingLevel metadata." );
		popped_smoke_assert( isset( $block->attributes['sectionTitleLevel'] ), "{$name} is missing sectionTitleLevel metadata." );
	}

	$horizontal = WP_Block_Type_Registry::get_instance()->get_registered( 'popped/horizontal-timeline' );
	$archive    = WP_Block_Type_Registry::get_instance()->get_registered( 'popped/archive-explorer' );
	popped_smoke_assert(
		$horizontal && in_array( 'popped', $horizontal->view_script_handles, true ),
		'Horizontal Timeline must load its interaction script.'
	);
	popped_smoke_assert(
		$archive && ! in_array( 'popped', $archive->view_script_handles, true ),
		'Archive Explorer must not load the interaction script unnecessarily.'
	);

	$inline_styles = wp_styles()->get_data( 'popped', 'after' );
	$inline_css    = is_array( $inline_styles ) ? implode( "\n", $inline_styles ) : (string) $inline_styles;
	popped_smoke_assert(
		false !== strpos( $inline_css, '--popped-background' ),
		'Design tokens are not attached to the Popped block stylesheet.'
	);

	$custom_home_id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => 'Smoke Test Home',
			'post_content' => '<!-- wp:paragraph --><p>Custom homepage content.</p><!-- /wp:paragraph -->',
		),
		true
	);
	popped_smoke_assert( ! is_wp_error( $custom_home_id ) && $custom_home_id, 'Could not create smoke-test homepage.' );
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $custom_home_id );

	$result = Popped_Setup::execute(
		'custom',
		array(
			'typography'      => 'inherit',
			'density'         => 'standard',
			'timeline_layout' => 'vertical',
			'timeline_tag'    => 'timeline',
		)
	);

	$options = Popped_Settings::all();
	popped_smoke_assert( 'page' === get_option( 'show_on_front' ), 'Setup changed show_on_front.' );
	popped_smoke_assert(
		absint( get_option( 'page_on_front' ) ) === absint( $custom_home_id ),
		'Setup changed page_on_front.'
	);
	popped_smoke_assert( 'inherit' === $options['typography'], 'Custom Setup did not preserve Theme / Global Styles typography.' );
	popped_smoke_assert( ! empty( $options['setup_complete'] ), 'Successful setup did not mark setup_complete.' );

	foreach ( array( 'timeline_page_id', 'archive_page_id', 'search_page_id' ) as $key ) {
		$page = ! empty( $options[ $key ] ) ? get_post( absint( $options[ $key ] ) ) : null;
		popped_smoke_assert( $page && 'page' === $page->post_type, "{$key} does not reference a page." );
		popped_smoke_assert( 'publish' === $page->post_status, "{$key} does not reference a published page." );
		popped_smoke_assert(
			'default' === get_post_meta( $page->ID, '_wp_page_template', true ) ||
			'' === get_post_meta( $page->ID, '_wp_page_template', true ),
			"{$key} was assigned a plugin page template."
		);
	}

	$old_timeline_id = absint( $options['timeline_page_id'] );
	wp_trash_post( $old_timeline_id );
	Popped_Setup::execute( 'quick', array() );

	$options         = Popped_Settings::all();
	$new_timeline_id = absint( $options['timeline_page_id'] );
	$new_timeline    = $new_timeline_id ? get_post( $new_timeline_id ) : null;

	popped_smoke_assert(
		$new_timeline_id && $new_timeline_id !== $old_timeline_id,
		'Quick Setup reused a trashed Timeline page.'
	);
	popped_smoke_assert(
		$new_timeline && 'publish' === $new_timeline->post_status,
		'Replacement Timeline page is not published.'
	);
	popped_smoke_assert( ! empty( $options['setup_complete'] ), 'Repair setup did not finish cleanly.' );

	if ( post_type_exists( 'wp_template' ) ) {
		$legacy_template_id = wp_insert_post(
			array(
				'post_type'    => 'wp_template',
				'post_status'  => 'publish',
				'post_name'    => 'single',
				'post_title'   => 'Legacy Popped Single',
				'post_content' => '<!-- wp:popped/continue-story /-->',
				'meta_input'   => array( '_popped_template' => '1' ),
			),
			true
		);
		if ( ! is_wp_error( $legacy_template_id ) && $legacy_template_id ) {
			delete_option( 'popped_template_release_2_1' );
			Popped_Templates::release_legacy_templates();
			popped_smoke_assert(
				'trash' === get_post_status( $legacy_template_id ),
				'Legacy Popped-owned site template was not retired.'
			);
		}
	}

	echo "PASS WordPress smoke: block metadata, selective assets, design tokens, theme ownership, setup, and legacy cleanup.\n";
}

popped_smoke_run();
