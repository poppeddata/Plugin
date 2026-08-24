<?php
/** @package Popped */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?><!doctype html>
<html <?php language_attributes(); ?>>
<head><meta charset="<?php bloginfo( 'charset' ); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?></head>
<body <?php body_class( 'popped-canvas' ); ?>>
<?php wp_body_open(); ?>
<main id="main" class="popped-main">
<?php while ( have_posts() ) : the_post(); ?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'popped-page' ); ?>><?php the_content(); ?></article>
<?php endwhile; ?>
</main>
<?php wp_footer(); ?>
</body></html>
