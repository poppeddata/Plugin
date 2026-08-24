<?php
/**
 * Uninstall cleanup for Popped-owned presentation records.
 *
 * Editorial content, archive pages, taxonomy terms and settings are preserved.
 * Only legacy site-template overrides created by Popped 2.0.x are removed so an
 * uninstall cannot leave WordPress pointing at templates that contain missing
 * plugin blocks.
 *
 * @package Popped
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$legacy_slugs = array( 'front-page', 'single', 'archive', 'search', '404' );

if ( post_type_exists( 'wp_template' ) ) {
	$templates = get_posts(
		array(
			'post_type'      => 'wp_template',
			'post_status'    => array( 'publish', 'draft', 'private', 'trash' ),
			'posts_per_page' => -1,
			'meta_key'       => '_popped_template',
			'meta_value'     => '1',
			'fields'         => 'ids',
		)
	); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key

	foreach ( $templates as $template_id ) {
		$template = get_post( $template_id );
		if ( $template && in_array( $template->post_name, $legacy_slugs, true ) ) {
			wp_delete_post( $template_id, true );
		}
	}
}

$pages = get_posts(
	array(
		'post_type'      => 'page',
		'post_status'    => array( 'publish', 'draft', 'private', 'future', 'trash' ),
		'posts_per_page' => -1,
		'meta_key'       => '_popped_page_role',
		'fields'         => 'ids',
	)
); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key

foreach ( $pages as $page_id ) {
	$template = get_post_meta( $page_id, '_wp_page_template', true );
	if ( in_array( $template, array( 'popped-timeline', 'popped-archive-page', 'popped-search-page' ), true ) ) {
		update_post_meta( $page_id, '_wp_page_template', 'default' );
	}
}
