<?php
/** @package Popped */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?><!doctype html><html <?php language_attributes(); ?>><head><meta charset="<?php bloginfo( 'charset' ); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?></head><body <?php body_class( 'popped-search-template' ); ?>><?php wp_body_open(); ?><main id="main" class="popped-main"><?php echo Popped_Components::search(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></main><?php wp_footer(); ?></body></html>
