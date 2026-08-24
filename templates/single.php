<?php
/** @package Popped */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?><!doctype html>
<html <?php language_attributes(); ?>>
<head><meta charset="<?php bloginfo( 'charset' ); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?></head>
<body <?php body_class( 'popped-single-template' ); ?>>
<?php wp_body_open(); ?>
<main id="main" class="popped-main">
<?php while ( have_posts() ) : the_post(); ?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'popped-article' ); ?>>
		<header class="popped-article__header popped-wrap">
			<p class="popped-kicker"><?php $cats = get_the_category(); if ( $cats ) { echo esc_html( $cats[0]->name ) . ' · '; } ?><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( (string) get_option( 'date_format', 'F j, Y' ) ) ); ?></time></p>
			<h1><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?><p class="popped-article__standfirst"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
			<?php if ( has_post_thumbnail() ) : ?><figure class="popped-article__hero"><?php the_post_thumbnail( 'full', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?></figure><?php endif; ?>
		</header>
		<div class="popped-entry-content"><?php the_content(); wp_link_pages(); ?></div>
	</article>
<?php endwhile; ?>
</main>
<?php wp_footer(); ?>
</body></html>
