<?php
/**
 * Archive fallback template.
 *
 * @package Popped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title = is_home() ? __( 'Stories', 'popped' ) : wp_strip_all_tags( get_the_archive_title() );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'popped-archive-template' ); ?>>
<?php wp_body_open(); ?>
<main id="main" class="popped-main">
	<?php echo Popped_Components::render( 'archive-explorer', array( 'title' => $title ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</main>
<?php wp_footer(); ?>
</body>
</html>
