<?php
/**
 * Front-end component library.
 *
 * @package Popped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Popped_Components {
	const MAX_RENDERED_YEARS = 100;
	const MAX_FITTED_ROW_YEARS = 12;
	/**
	 * Render a named dynamic block.
	 *
	 * @param string              $component Component key.
	 * @param array<string,mixed> $attributes Block attributes.
	 * @return string
	 */
	public static function render( $component, $attributes = array() ) {
		$attributes = Popped_Block_Config::resolve( $component, $attributes );
		switch ( $component ) {
			case 'homepage': return self::homepage( $attributes );
			case 'on-this-day': return self::on_this_day( $attributes );
			case 'timeline': return self::timeline( $attributes );
			case 'mini-timeline': return self::mini_timeline( $attributes );
			case 'horizontal-timeline': $attributes['layout'] = 'horizontal'; $attributes['paginate'] = false; $attributes['_poppedHorizontalBlock'] = true; return self::timeline( $attributes );
			case 'latest-stories': return self::latest_stories( $attributes );
			case 'year-navigator': return self::year_navigator( $attributes );
			case 'featured-collection': return self::featured_collection( $attributes );
			case 'news-ticker': return self::news_ticker( $attributes );
			case 'archive-explorer': return self::archive_explorer( $attributes );
			case 'related-stories': return self::related_stories( $attributes );
			case 'continue-story': return self::continue_story( $attributes );
			case 'timeline-navigation': return self::timeline_navigation( $attributes );
			case 'also-on-this-day': return self::also_on_this_day( $attributes );
			case 'search': return self::search( $attributes );
		}
		return '';
	}

	/** @return string */
	public static function homepage( $attributes = array() ) {
		$sections    = Popped_Settings::get( 'homepage_sections', Popped_Settings::defaults()['homepage_sections'] );
		$composition = isset( $attributes['composition'] ) ? sanitize_key( $attributes['composition'] ) : Popped_Settings::get( 'homepage_composition', 'editorial' );

		if ( 'editorial' !== $composition ) {
			return self::homepage_section_stack( $sections );
		}

		$lead    = self::homepage_lead();
		$exclude = $lead['id'] ? array( $lead['id'] ) : array();
		$out     = '<div class="popped-homepage popped-homepage--editorial">';

		if ( $lead['html'] ) {
			$out .= $lead['html'];
		}

		foreach ( $sections as $slug => $section ) {
			if ( empty( $section['enabled'] ) ) {
				continue;
			}

			switch ( $slug ) {
				case 'latest-stories':
					$content = self::render(
						'latest-stories',
						array(
							'excludePosts' => $exclude,
							'showExcerpt'  => true,
						)
					);
					$out .= self::homepage_section_wrap( 'latest', $content );
					break;
				case 'on-this-day':
					$content = self::render(
						'on-this-day',
						array(
							'excludePosts'  => $exclude,
							'hideWhenEmpty' => true,
						)
					);
					$out .= self::homepage_section_wrap( 'on-this-day', $content );
					break;
				case 'featured-collections':
					$content = self::render( 'featured-collection', array( 'collection' => 'all', 'count' => 4 ) );
					$out .= self::homepage_section_wrap( 'collections', $content );
					break;
				case 'mini-timeline':
					$content = self::render(
						'mini-timeline',
						array(
							'excludePosts'  => $exclude,
							'hideWhenEmpty' => true,
							'count'         => 4,
						)
					);
					$out .= self::homepage_section_wrap( 'timeline', $content );
					break;
				case 'year-navigator':
					$content = self::render( 'year-navigator', array( 'showCounts' => true, 'maxYears' => 10 ) );
					$out .= self::homepage_section_wrap( 'years', $content );
					break;
				case 'news-ticker':
					$out .= self::render( 'news-ticker', array( 'standalone' => true ) );
					break;
			}
		}

		return $out . '</div>';
	}

	/**
	 * Preserve the original homepage renderer for upgraded sites until an editor
	 * explicitly selects the new editorial hierarchy.
	 *
	 * @param array<string,array<string,mixed>> $sections Ordered homepage sections.
	 * @return string
	 */
	private static function homepage_section_stack( $sections ) {
		$out = '<div class="popped-homepage popped-homepage--sections">';
		foreach ( $sections as $slug => $section ) {
			if ( empty( $section['enabled'] ) ) {
				continue;
			}
			switch ( $slug ) {
				case 'on-this-day':
					$out .= self::render( 'on-this-day' );
					break;
				case 'mini-timeline':
					$out .= self::render( 'mini-timeline' );
					break;
				case 'latest-stories':
					$out .= self::render( 'latest-stories' );
					break;
				case 'year-navigator':
					$out .= self::render( 'year-navigator' );
					break;
				case 'featured-collections':
					$out .= self::render( 'featured-collection', array( 'collection' => 'all' ) );
					break;
				case 'news-ticker':
					$out .= self::render( 'news-ticker', array( 'standalone' => true ) );
					break;
			}
		}
		return $out . '</div>';
	}

	/**
	 * Render the current lead story used by the recommended homepage hierarchy.
	 *
	 * @return array{id:int,html:string}
	 */
	private static function homepage_lead() {
		$query = Popped_Query::get(
			array(
				'source' => Popped_Settings::get( 'latest_source', 'all' ),
				'count'  => 1,
				'order'  => 'newest',
			)
		);
		if ( ! $query->have_posts() ) {
			return array( 'id' => 0, 'html' => '' );
		}

		$post       = $query->posts[0];
		$image      = get_the_post_thumbnail( $post, 'full', array( 'class' => 'popped-home-lead__image', 'loading' => 'eager', 'fetchpriority' => 'high' ) );
		$excerpt    = self::story_excerpt( $post, 36 );
		$permalink  = get_permalink( $post );
		$heading_id = self::unique_id( 'popped-home-lead-title' );
		$heading    = is_front_page() && ! self::is_editor_preview() ? 'h1' : 'h2';
		$media_class = $image ? '' : ' popped-home-lead--no-image';

		ob_start();
		?>
		<section class="popped-home-lead<?php echo esc_attr( $media_class ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
			<div class="popped-wrap">
				<div class="popped-home-lead__eyebrow">
					<p class="popped-kicker"><?php esc_html_e( 'Lead story', 'popped' ); ?></p>
					<p><?php echo esc_html( wp_date( (string) get_option( 'date_format', 'F j, Y' ) ) ); ?></p>
				</div>
				<article class="popped-home-lead__article">
					<?php if ( $image ) : ?>
						<a class="popped-home-lead__media" href="<?php echo esc_url( $permalink ); ?>" tabindex="-1" aria-hidden="true"><?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
					<?php endif; ?>
					<div class="popped-home-lead__copy">
						<?php echo self::metadata( $post, array( 'showDate' => true, 'showCategory' => true ), 'j M Y' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php if ( 'h1' === $heading ) : ?>
							<h1 id="<?php echo esc_attr( $heading_id ); ?>"><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h1>
						<?php else : ?>
							<h2 id="<?php echo esc_attr( $heading_id ); ?>"><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h2>
						<?php endif; ?>
						<?php if ( $excerpt ) : ?><p class="popped-home-lead__deck"><?php echo esc_html( $excerpt ); ?></p><?php endif; ?>
						<a class="popped-action" href="<?php echo esc_url( $permalink ); ?>"><?php esc_html_e( 'Read the lead story', 'popped' ); ?> <span aria-hidden="true">→</span></a>
					</div>
				</article>
			</div>
		</section>
		<?php
		return array( 'id' => (int) $post->ID, 'html' => ob_get_clean() );
	}

	/**
	 * Add a semantic wrapper only when a homepage section has visible content.
	 *
	 * @param string $slug Section slug.
	 * @param string $content Rendered component.
	 * @return string
	 */
	private static function homepage_section_wrap( $slug, $content ) {
		if ( '' === trim( (string) $content ) ) {
			return '';
		}
		return '<div class="popped-homepage__section popped-homepage__' . esc_attr( sanitize_html_class( $slug ) ) . '">' . $content . '</div>';
	}

	/** @return string */
	public static function on_this_day( $attributes = array() ) {
		$use_today = ! isset( $attributes['useToday'] ) || ! empty( $attributes['useToday'] );
		$month = ! $use_today && ! empty( $attributes['month'] ) ? absint( $attributes['month'] ) : absint( wp_date( 'n' ) );
		$day   = ! $use_today && ! empty( $attributes['day'] ) ? absint( $attributes['day'] ) : absint( wp_date( 'j' ) );
		$count = ! empty( $attributes['count'] ) ? absint( $attributes['count'] ) : 4;
		$source_config = self::source_config( array_merge( $attributes, array( 'count' => $count ) ) );
		if ( empty( $attributes['order'] ) ) {
			$source_config['order'] = 'manual' === $source_config['source'] ? 'manual' : 'chronological';
		}

		$result = Popped_Query::on_this_day_results( $month, $day, $source_config, $count );
		$posts  = $result['posts'];
		$total  = $result['total'];

		$preferred = Popped_Query::on_this_day_preferred( $month, $day, $source_config );
		if ( $preferred ) {
			$posts = array_values(
				array_filter(
					$posts,
					static function ( $post ) use ( $preferred ) {
						return (int) $post->ID !== (int) $preferred->ID;
					}
				)
			);
			array_unshift( $posts, $preferred );
			$posts = array_slice( $posts, 0, $count );
		}

		$override = absint( Popped_Settings::get( 'on_this_day_override', 0 ) );
		if ( $override ) {
			$override_post = Popped_Query::on_this_day_override( $override, $month, $day, $source_config );
			if ( $override_post ) {
				$posts = array_values(
					array_filter(
						$posts,
						static function ( $post ) use ( $override ) {
							return (int) $post->ID !== $override;
						}
					)
				);
				array_unshift( $posts, $override_post );
				$posts = array_slice( $posts, 0, $count );
			}
		}

		$label      = wp_date( 'j F', mktime( 12, 0, 0, $month, $day, 2000 ) );
		$title      = isset( $attributes['title'] ) ? (string) $attributes['title'] : __( 'On This Day', 'popped' );
		$fallback   = ! empty( $attributes['fallbackText'] ) ? $attributes['fallbackText'] : __( 'No stories have been added for this date yet.', 'popped' );
		$heading_id  = self::unique_id( 'popped-otd-title' );
		$section_level = isset( $attributes['sectionTitleLevel'] ) ? absint( $attributes['sectionTitleLevel'] ) : 2;
		$section_tag   = self::heading_tag( $section_level, 2 );
		$group_tag     = self::heading_tag( min( 6, $section_level + 1 ), 3 );
		$story_tag   = self::heading_tag( isset( $attributes['headingLevel'] ) ? $attributes['headingLevel'] : 3, 3 );
		if ( ! $posts ) {
			if ( ! empty( $attributes['hideWhenEmpty'] ) && ! self::is_editor_preview() ) {
				return '';
			}
			$section_label = '' !== $title ? ' aria-labelledby="' . esc_attr( $heading_id ) . '"' : ' aria-label="' . esc_attr__( 'On This Day', 'popped' ) . '"';
			return '<section class="popped-section popped-otd popped-otd--empty"' . $section_label . '><div class="popped-wrap"><p class="popped-kicker">' . esc_html( strtoupper( $label ) ) . '</p>' . ( '' !== $title ? '<' . $section_tag . ' id="' . esc_attr( $heading_id ) . '">' . esc_html( $title ) . '</' . $section_tag . '>' : '' ) . '<p>' . esc_html( $fallback ) . '</p>' . self::editor_hint( __( 'Change the source in the Content panel.', 'popped' ) ) . '</div></section>';
		}

		$hero        = $posts[0];
		$more        = max( 0, $total - 1 );
		$archive_url = self::archive_url( array( '_popped_month' => $month, '_popped_day' => $day ) );
		$more_label  = '';
		if ( $more > 0 ) {
			/* translators: %d: Number of additional events for the selected day. */
			$more_label = sprintf( _n( '%d more event on this day', '%d more events on this day', $more, 'popped' ), $more );
		}
		$image       = '';
		if ( ! empty( $attributes['showImage'] ) ) {
			$image = get_the_post_thumbnail( $hero, 'full', array( 'class' => 'popped-otd__image', 'loading' => 'eager', 'fetchpriority' => 'high' ) );
			if ( ! $image ) { $image = '<span class="popped-image-placeholder" aria-hidden="true"></span>'; }
		}
		$excerpt = self::story_excerpt( $hero, 26 );

		$section_label = '' !== $title ? 'aria-labelledby="' . esc_attr( $heading_id ) . '"' : 'aria-label="' . esc_attr__( 'On This Day', 'popped' ) . '"';
		ob_start();
		?>
		<section class="popped-section popped-otd" <?php echo $section_label; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<div class="popped-wrap">
				<header class="popped-section-head popped-otd__head">
					<div><p class="popped-kicker"><?php echo esc_html( strtoupper( $label ) ); ?></p><?php if ( '' !== $title ) : ?><<?php echo esc_attr( $section_tag ); ?> id="<?php echo esc_attr( $heading_id ); ?>"><?php echo esc_html( $title ); ?></<?php echo esc_attr( $section_tag ); ?>><?php endif; ?></div>
					<?php if ( $more > 0 ) : ?><a class="popped-text-link" href="<?php echo esc_url( $archive_url ); ?>"><?php echo esc_html( $more_label ); ?> <span aria-hidden="true">→</span></a><?php endif; ?>
				</header>
				<article class="popped-otd__feature">
					<?php if ( $image ) : ?><a class="popped-otd__media" href="<?php echo esc_url( get_permalink( $hero ) ); ?>" tabindex="-1" aria-hidden="true"><?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a><?php endif; ?>
					<div class="popped-otd__copy">
						<?php echo self::metadata( $hero, $attributes, 'Y' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<<?php echo esc_attr( $story_tag ); ?>><a href="<?php echo esc_url( get_permalink( $hero ) ); ?>"><?php echo esc_html( get_the_title( $hero ) ); ?></a></<?php echo esc_attr( $story_tag ); ?>>
						<?php if ( ! empty( $attributes['showExcerpt'] ) && $excerpt ) : ?><p class="popped-standfirst"><?php echo esc_html( $excerpt ); ?></p><?php endif; ?>
						<a class="popped-action" href="<?php echo esc_url( get_permalink( $hero ) ); ?>"><?php esc_html_e( 'Read the story', 'popped' ); ?> <span aria-hidden="true">→</span></a>
					</div>
				</article>
			</div>
		</section>
		<?php
		return ob_get_clean();
	}

	/** @return string */
	public static function mini_timeline( $attributes = array() ) {
		$query = Popped_Query::get( self::source_config( $attributes ) );
		if ( ! $query->have_posts() ) {
			if ( ! empty( $attributes['hideWhenEmpty'] ) && ! self::is_editor_preview() ) {
				return '';
			}
			return self::empty_section( $attributes['title'], __( 'No stories are available for this selection.', 'popped' ), __( 'Add the Timeline tag to posts or change the source in the Content panel.', 'popped' ) );
		}
		$link_url = ! empty( $attributes['linkUrl'] ) ? $attributes['linkUrl'] : self::timeline_url();
		$link_text = ! empty( $attributes['linkText'] ) ? $attributes['linkText'] : __( 'View full timeline', 'popped' );
		$out = '<section class="popped-section popped-mini-timeline"><div class="popped-wrap">' . self::section_heading( $attributes['title'], ! empty( $attributes['showViewLink'] ) ? $link_url : '', $link_text, $attributes );
		$out .= '<div class="popped-rail" role="region" aria-label="' . esc_attr( $attributes['title'] ) . '" tabindex="0">';
		foreach ( $query->posts as $post ) {
			$out .= self::post_card( $post, 'rail', $attributes );
		}
		return $out . '</div></div></section>';
	}

	/** @return string */
	public static function timeline( $attributes = array() ) {
		$is_horizontal_block = ! empty( $attributes['_poppedHorizontalBlock'] );
		$layout = $is_horizontal_block ? 'horizontal' : ( isset( $_GET['_popped_view'] ) ? sanitize_key( wp_unslash( $_GET['_popped_view'] ) ) : ( isset( $attributes['layout'] ) ? sanitize_key( $attributes['layout'] ) : Popped_Settings::get( 'timeline_layout', 'vertical' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$layout = in_array( $layout, array( 'vertical', 'horizontal' ), true ) ? $layout : 'vertical';
		$count  = isset( $attributes['count'] ) ? absint( $attributes['count'] ) : absint( Popped_Settings::get( 'timeline_per_page', 10 ) );
		$paginate = ! isset( $attributes['paginate'] ) || ! empty( $attributes['paginate'] );
		$paged  = $paginate && isset( $_GET['_popped_page'] ) ? max( 1, absint( $_GET['_popped_page'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$year   = isset( $_GET['_popped_year'] ) ? absint( $_GET['_popped_year'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$month  = isset( $_GET['_popped_month'] ) ? absint( $_GET['_popped_month'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$day    = isset( $_GET['_popped_day'] ) ? absint( $_GET['_popped_day'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$search = isset( $_GET['_popped_q'] ) ? sanitize_text_field( wp_unslash( $_GET['_popped_q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$cat    = isset( $_GET['_popped_cat'] ) ? sanitize_title( wp_unslash( $_GET['_popped_cat'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tag    = isset( $_GET['_popped_tag'] ) ? sanitize_title( wp_unslash( $_GET['_popped_tag'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$default_sort = isset( $attributes['order'] ) ? sanitize_key( $attributes['order'] ) : 'chronological';
		if ( 'oldest' === $default_sort ) { $default_sort = 'chronological'; }
		if ( 'random' === $default_sort ) { $default_sort = 'newest'; }
		if ( ! in_array( $default_sort, array( 'chronological', 'newest', 'manual' ), true ) ) { $default_sort = 'chronological'; }
		$sort = isset( $_GET['_popped_sort'] ) ? sanitize_key( wp_unslash( $_GET['_popped_sort'] ) ) : $default_sort; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 'random' === $sort ) { $sort = 'newest'; }
		if ( ! in_array( $sort, array( 'chronological', 'newest', 'manual' ), true ) ) { $sort = $default_sort; }
		if ( $is_horizontal_block ) {
			$year = 0; $month = 0; $day = 0; $search = ''; $cat = ''; $tag = ''; $sort = $default_sort;
		}
		$date_query = array();
		if ( $year || $month || $day ) {
			$date_query[] = array_filter( array( 'year' => $year, 'month' => $month, 'day' => $day ) );
		}
		$config = self::source_config( wp_parse_args( $attributes, array( 'source' => 'timeline', 'count' => $count ) ) );
		$config['order'] = $sort;
		$context = array_filter( array( 's' => $search, 'year' => $year, 'monthnum' => $month, 'date_query' => $date_query, 'category_name' => $cat, 'tag' => $tag ) );
		if ( $paginate ) { $context['paged'] = $paged; }
		$query = Popped_Query::get( $config, $context );
		$title = isset( $attributes['title'] ) ? sanitize_text_field( $attributes['title'] ) : __( 'Timeline', 'popped' );
		$heading_id  = self::unique_id( 'popped-timeline-title' );
		$section_tag = self::heading_tag( isset( $attributes['sectionTitleLevel'] ) ? $attributes['sectionTitleLevel'] : 2, 2 );
		$result_count = $query->found_posts ? (int) $query->found_posts : (int) $query->post_count;
		/* translators: %s: Number of matching timeline events. */
		$result_count_label = sprintf( _n( '%s event', '%s events', $result_count, 'popped' ), number_format_i18n( $result_count ) );

		ob_start();
		?>
		<section class="popped-section popped-full-timeline" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
			<div class="popped-wrap">
				<div class="popped-section-head">
					<div><p class="popped-kicker"><?php esc_html_e( 'Explore the archive', 'popped' ); ?></p><<?php echo esc_attr( $section_tag ); ?> id="<?php echo esc_attr( $heading_id ); ?>"><?php echo esc_html( $title ); ?></<?php echo esc_attr( $section_tag ); ?>></div>
					<?php
					$show_result_count = ! $is_horizontal_block || ! isset( $attributes['showResultCount'] ) || ! empty( $attributes['showResultCount'] );
					$show_view_link = $is_horizontal_block && ! empty( $attributes['showViewLink'] );
					if ( $show_result_count || $show_view_link ) :
						$link_url = ! empty( $attributes['linkUrl'] ) ? $attributes['linkUrl'] : self::timeline_url();
						$link_text = ! empty( $attributes['linkText'] ) ? $attributes['linkText'] : __( 'See full timeline', 'popped' );
					?>
						<div class="popped-section-head__actions">
							<?php if ( $show_result_count ) : ?><p class="popped-result-count"><?php echo esc_html( $result_count_label ); ?></p><?php endif; ?>
							<?php if ( $show_view_link ) : ?><a class="popped-text-link" href="<?php echo esc_url( $link_url ); ?>"><?php echo esc_html( $link_text ); ?><span aria-hidden="true">→</span></a><?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php if ( ! $is_horizontal_block ) { echo self::timeline_filters( compact( 'search', 'year', 'month', 'cat', 'tag', 'sort', 'layout' ) ); } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php if ( 'horizontal' === $layout && ! empty( $attributes['showNavigation'] ) ) : ?><div class="popped-rail-controls"><button type="button" data-popped-rail-prev aria-label="<?php esc_attr_e( 'Previous timeline stories', 'popped' ); ?>"><span class="popped-rail-control__icon" aria-hidden="true">←</span></button><button type="button" data-popped-rail-next aria-label="<?php esc_attr_e( 'Next timeline stories', 'popped' ); ?>"><span class="popped-rail-control__icon" aria-hidden="true">→</span></button></div><?php endif; ?>
			<div class="popped-timeline popped-timeline--<?php echo esc_attr( $layout ); ?>" data-popped-timeline data-view="<?php echo esc_attr( $layout ); ?>" <?php echo 'horizontal' === $layout ? 'tabindex="0" role="region" aria-label="' . esc_attr__( 'Scrollable timeline stories', 'popped' ) . '"' : ''; ?>>
				<?php if ( $query->have_posts() ) : $last_year = ''; foreach ( $query->posts as $post ) : $post_year = get_the_date( 'Y', $post ); if ( ! empty( $attributes['groupByYear'] ) && $post_year !== $last_year ) { echo '<' . esc_attr( $group_tag ) . ' class="popped-timeline-year">' . esc_html( $post_year ) . '</' . esc_attr( $group_tag ) . '>'; $last_year = $post_year; } echo self::timeline_entry( $post, $attributes ); endforeach; else : echo self::empty_state( __( 'No stories match this selection.', 'popped' ), self::is_editor_preview() ? __( 'Change the source in the Content panel or clear a visitor filter.', 'popped' ) : __( 'Try clearing one or more filters.', 'popped' ) ); endif; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<?php if ( $paginate ) { echo self::pagination( $query, $paged ); } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</section>
		<?php
		return ob_get_clean();
	}

	/** @return string */
	public static function latest_stories( $attributes = array() ) {
		$count = ! empty( $attributes['count'] ) ? absint( $attributes['count'] ) : 5;
		$query_attributes = $attributes;
		$query_attributes['count'] = $count + 1;
		$query = Popped_Query::get( self::source_config( $query_attributes ) );
		if ( ! $query->have_posts() ) { return self::empty_section( $attributes['title'], __( 'Published stories will appear here.', 'popped' ) ); }
		$posts = array_slice( $query->posts, 0, $count );
		$view_all_url = count( $query->posts ) > $count ? self::archive_url() : '';
		$display = isset( $attributes['displayLayout'] ) ? sanitize_key( $attributes['displayLayout'] ) : 'cards';
		$out = '<section class="popped-section popped-latest"><div class="popped-wrap">' . self::section_heading( $attributes['title'], $view_all_url, __( 'View all stories', 'popped' ), $attributes );
		$out .= self::story_collection( $posts, $display, $attributes );
		return $out . '</div></section>';
	}

	/** @return string */
	public static function year_navigator( $attributes = array() ) {
		$range = Popped_Settings::year_range();
		$start = isset( $attributes['startYear'] ) ? absint( $attributes['startYear'] ) : $range['start'];
		$end   = isset( $attributes['endYear'] ) ? absint( $attributes['endYear'] ) : $range['end'];
		$title = isset( $attributes['title'] ) ? sanitize_text_field( $attributes['title'] ) : __( 'Explore by Year', 'popped' );
		$max_years      = isset( $attributes['maxYears'] ) ? absint( $attributes['maxYears'] ) : 0;
		$display_layout = isset( $attributes['displayLayout'] ) ? sanitize_key( $attributes['displayLayout'] ) : 'grid';
		$limit          = $max_years > 0 ? min( $max_years, self::MAX_RENDERED_YEARS ) : self::MAX_RENDERED_YEARS;
		if ( 'scroll' === $display_layout ) {
			$limit = min( $limit, self::MAX_FITTED_ROW_YEARS );
		}
		$counts     = self::year_counts( $start, $end );
		$years      = self::limited_years( $start, $end, 0, $limit );
		$year_order = isset( $attributes['yearOrder'] ) ? sanitize_key( $attributes['yearOrder'] ) : 'oldest';
		if ( 'newest' === $year_order ) {
			$years = array_reverse( $years );
		}
		$total     = $counts ? count( $counts ) : max( 0, $end - $start + 1 );
		$trimmed   = $total > count( $years );

		$out = '<section class="popped-section popped-years"><div class="popped-wrap">' . self::section_heading( $title, $trimmed ? self::archive_url() : '', __( 'View full archive', 'popped' ), $attributes ) . '<nav class="popped-year-list" aria-label="' . esc_attr__( 'Browse posts by year', 'popped' ) . '">';
		foreach ( $years as $year ) {
			$count = isset( $counts[ $year ] ) ? (int) $counts[ $year ] : 0;
			$base = ! empty( $attributes['destination'] ) ? $attributes['destination'] : self::archive_url();
			/* translators: %s: Number of stories published in the year. */
			$count_label = sprintf( _n( '%s story', '%s stories', $count, 'popped' ), number_format_i18n( $count ) );
			$out .= '<a href="' . esc_url( add_query_arg( '_popped_year', $year, $base ) ) . '"><span>' . esc_html( $year ) . '</span>' . ( ! empty( $attributes['showCounts'] ) ? '<small>' . esc_html( $count_label ) . '</small>' : '' ) . '</a>';
		}
		return $out . '</nav></div></section>';
	}

	/** @return string */
	public static function featured_collection( $attributes = array() ) {
		$collections = Popped_Settings::get( 'collections', array() );
		$selected = isset( $attributes['collection'] ) ? sanitize_key( $attributes['collection'] ) : '';
		if ( 'all' === $selected ) {
			if ( ! $collections ) { return self::is_editor_preview() ? self::empty_section( __( 'Featured Collections', 'popped' ), __( 'No collections have been created yet.', 'popped' ), __( 'Create a named collection in Popped → Collections.', 'popped' ) ) : ''; }
			$collection_limit = ! empty( $attributes['count'] ) ? max( 1, absint( $attributes['count'] ) ) : 5;
			$visible_collections = array_slice( $collections, 0, $collection_limit, true );
			$out = '<section class="popped-section popped-collections"><div class="popped-wrap">' . self::section_heading( __( 'Featured Collections', 'popped' ) ) . '<div class="popped-collection-list">';
			foreach ( $visible_collections as $collection ) {
				$url = self::collection_url( $collection );
				$image = ! empty( $collection['featured_image'] ) ? wp_get_attachment_image( $collection['featured_image'], 'medium_large', false, array( 'loading' => 'lazy' ) ) : '';
				$link_class = 'popped-collection-link' . ( $image ? ' popped-collection-link--with-image' : '' );
				$out .= '<a class="' . esc_attr( $link_class ) . '" href="' . esc_url( $url ) . '">' . ( $image ? '<span class="popped-collection-thumb">' . $image . '</span>' : '' ) . '<span class="popped-collection-copy"><strong>' . esc_html( $collection['name'] ) . '</strong>' . ( $collection['description'] ? '<small>' . esc_html( $collection['description'] ) . '</small>' : '' ) . '</span><b class="popped-collection-arrow" aria-hidden="true">→</b></a>';
			}
			return $out . '</div></div></section>';
		}
		if ( ! $selected || empty( $collections[ $selected ] ) ) { return self::is_editor_preview() ? self::empty_section( __( 'Featured Collection', 'popped' ), __( 'No collection is selected.', 'popped' ), __( 'Choose a collection in the Content panel.', 'popped' ) ) : ''; }
		$collection = $collections[ $selected ];
		$query_config = array(
			'source' => $collection['source'], 'categories' => $collection['category'] ? array( $collection['category'] ) : array(),
			'tags' => $collection['tag'] ? array( $collection['tag'] ) : array(), 'posts' => $collection['posts'],
			'count' => $collection['count'], 'order' => $collection['order'],
		);
		$query = Popped_Query::get( $query_config );
		$display = ! empty( $attributes['displayLayout'] ) ? $attributes['displayLayout'] : 'cards';
		$out = '<section class="popped-section popped-collection"><div class="popped-wrap">' . self::section_heading( $collection['name'], self::collection_url( $collection ), __( 'View collection', 'popped' ), $attributes );
		if ( ! empty( $attributes['showCollectionImage'] ) && ! empty( $collection['featured_image'] ) ) {
			$out .= '<figure class="popped-collection-featured">' . wp_get_attachment_image( $collection['featured_image'], 'full', false, array( 'loading' => 'eager' ) ) . '</figure>';
		}
		if ( $collection['description'] ) { $out .= '<p class="popped-collection-description">' . esc_html( $collection['description'] ) . '</p>'; }
		if ( $query->have_posts() ) {
			$out .= self::story_collection( $query->posts, $display, $attributes );
		} else {
			$out .= self::empty_state( __( 'No stories match this collection.', 'popped' ), self::is_editor_preview() ? __( 'Edit the collection source in Popped → Collections.', 'popped' ) : '' );
		}
		return $out . '</div></section>';
	}

	/** @return string */
	public static function news_ticker( $attributes = array() ) {
		$source = isset( $attributes['source'] ) ? sanitize_key( $attributes['source'] ) : Popped_Settings::get( 'ticker_source', 'latest' );
		$manual = isset( $attributes['posts'] ) ? Popped_Settings::id_list( $attributes['posts'] ) : Popped_Settings::get( 'ticker_post_ids', array() );
		$count = ! empty( $attributes['count'] ) ? absint( $attributes['count'] ) : 5;
		$config = array(
			'source' => 'latest' === $source ? 'all' : 'manual',
			'posts'  => $manual,
			'count'  => $count,
			'order'  => in_array( $source, array( 'manual', 'mixed' ), true ) ? 'manual' : 'newest',
		);
		$query  = Popped_Query::get( $config );
		$posts  = $query->posts;
		if ( 'mixed' === $source ) {
			$latest = Popped_Query::get( array( 'source' => 'all', 'count' => $count, 'order' => 'newest', 'excludePosts' => wp_list_pluck( $posts, 'ID' ) ) );
			$posts = array_slice( array_merge( $posts, $latest->posts ), 0, $count );
		}
		if ( ! $posts ) { return ''; }
		$label     = ! empty( $attributes['tickerLabel'] ) ? $attributes['tickerLabel'] : __( 'Latest', 'popped' );
		$speed     = ! empty( $attributes['tickerSpeed'] ) ? sanitize_key( $attributes['tickerSpeed'] ) : 'static';
		if ( ! in_array( $speed, array( 'static', 'slow', 'standard' ), true ) ) {
			$speed = 'static';
		}
		$direction = ! empty( $attributes['tickerDirection'] ) && 'right' === sanitize_key( $attributes['tickerDirection'] ) ? 'right' : 'left';
		$separator = ! empty( $attributes['tickerSeparator'] ) ? sanitize_key( $attributes['tickerSeparator'] ) : 'dot';
		if ( ! in_array( $separator, array( 'dot', 'bullet', 'slash', 'none' ), true ) ) {
			$separator = 'dot';
		}
		$items = '';
		foreach ( $posts as $post ) {
			$items .= '<a href="' . esc_url( get_permalink( $post ) ) . '">' . esc_html( get_the_title( $post ) ) . ( ! empty( $attributes['showDate'] ) ? ' <time datetime="' . esc_attr( get_the_date( 'c', $post ) ) . '">' . esc_html( get_the_date( (string) get_option( 'date_format', 'F j, Y' ), $post ) ) . '</time>' : '' ) . '</a>';
		}

		$pause_label  = __( 'Pause ticker', 'popped' );
		$resume_label = __( 'Resume ticker', 'popped' );
		$toggle_hidden = 'static' === $speed ? ' hidden' : '';

		return '<aside class="popped-ticker popped-ticker--' . esc_attr( $speed ) . ' popped-ticker--direction-' . esc_attr( $direction ) . ' popped-ticker--separator-' . esc_attr( $separator ) . '" data-popped-ticker data-pause="' . ( ! empty( $attributes['tickerPause'] ) ? 'true' : 'false' ) . '" aria-label="' . esc_attr( $label ) . '"><div class="popped-ticker__inner"><strong>' . esc_html( $label ) . '</strong><div class="popped-ticker__viewport"><div class="popped-ticker__track"><div class="popped-ticker__group" data-popped-ticker-group="source">' . $items . '</div></div></div><button type="button" class="popped-ticker__toggle" data-popped-ticker-toggle aria-pressed="false" data-pause-label="' . esc_attr( $pause_label ) . '" data-resume-label="' . esc_attr( $resume_label ) . '"' . $toggle_hidden . '><span class="popped-ticker__toggle-text">' . esc_html( $pause_label ) . '</span></button></div></aside>';
	}

	/** @return string */
	public static function archive_explorer( $attributes = array() ) {
		$year  = isset( $_GET['_popped_year'] ) ? absint( $_GET['_popped_year'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$month = isset( $_GET['_popped_month'] ) ? absint( $_GET['_popped_month'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$day   = isset( $_GET['_popped_day'] ) ? absint( $_GET['_popped_day'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$paged = isset( $_GET['_popped_page'] ) ? max( 1, absint( $_GET['_popped_page'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$default_view = ! empty( $attributes['displayLayout'] ) && 'cards' !== $attributes['displayLayout'] ? $attributes['displayLayout'] : 'grid';
		$view  = isset( $_GET['_popped_archive_view'] ) ? sanitize_key( wp_unslash( $_GET['_popped_archive_view'] ) ) : $default_view; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$search = isset( $_GET['_popped_archive_q'] ) ? sanitize_text_field( wp_unslash( $_GET['_popped_archive_q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$category = isset( $_GET['_popped_archive_cat'] ) ? sanitize_title( wp_unslash( $_GET['_popped_archive_cat'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tag = isset( $_GET['_popped_archive_tag'] ) ? sanitize_title( wp_unslash( $_GET['_popped_archive_tag'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! $category && is_category() ) { $object = get_queried_object(); $category = $object && isset( $object->slug ) ? $object->slug : ''; }
		if ( ! $tag && is_tag() ) { $object = get_queried_object(); $tag = $object && isset( $object->slug ) ? $object->slug : ''; }
		if ( ! $year && is_year() ) { $year = absint( get_query_var( 'year' ) ); }
		if ( ! $month && is_month() ) { $month = absint( get_query_var( 'monthnum' ) ); }
		if ( ! in_array( $view, array( 'timeline', 'grid', 'list', 'magazine' ), true ) ) { $view = 'grid'; }
		$title = isset( $attributes['title'] ) ? sanitize_text_field( $attributes['title'] ) : __( 'Explore the Archive', 'popped' );
		if ( ! isset( $attributes['title'] ) && ( is_archive() || is_home() ) ) {
			$title = is_home() ? __( 'Stories', 'popped' ) : wp_strip_all_tags( get_the_archive_title() );
		}
		$context = array_filter( array( 'year' => $year, 'monthnum' => $month, 'paged' => $paged, 's' => $search, 'category_name' => $category, 'tag' => $tag, 'date_query' => $day ? array( array( 'month' => $month, 'day' => $day ) ) : array() ) );
		$query = Popped_Query::get( self::source_config( $attributes ), $context );
		$out = '<section class="popped-section popped-archive"><div class="popped-wrap">' . self::section_heading( $title, '', '', $attributes );
		$out .= self::archive_filter_form( $attributes, compact( 'year', 'month', 'search', 'category', 'tag', 'view' ) ) . self::archive_controls( $attributes, compact( 'year', 'month', 'search', 'category', 'tag' ), $view ) . '<div class="popped-archive-results popped-archive-results--' . esc_attr( $view ) . '">';
		if ( $query->have_posts() ) {
			foreach ( $query->posts as $post ) {
				$out .= 'timeline' === $view ? self::timeline_entry( $post, $attributes ) : self::post_card( $post, 'list' === $view ? 'list' : 'grid', $attributes );
			}
		} else { $out .= self::empty_state( __( 'No stories match these filters.', 'popped' ), self::is_editor_preview() ? __( 'Clear a filter or choose a broader source in the Content panel.', 'popped' ) : __( 'Try clearing one or more filters.', 'popped' ) ); }
		return $out . '</div>' . self::pagination( $query, $paged ) . '</div></section>';
	}

	/** @return string */
	public static function related_stories( $attributes = array() ) {
		$post_id = ! empty( $attributes['postId'] ) ? absint( $attributes['postId'] ) : get_the_ID();
		$manual_mode = isset( $attributes['selectionMode'] ) && 'manual' === $attributes['selectionMode'];
		if ( ! $post_id && ! $manual_mode ) { return ''; }
		$count = ! empty( $attributes['count'] ) ? absint( $attributes['count'] ) : absint( Popped_Settings::get( 'related_count', 3 ) );
		$pinned_ids = $manual_mode ? Popped_Settings::id_list( isset( $attributes['posts'] ) ? $attributes['posts'] : array() ) : Popped_Settings::id_list( get_post_meta( $post_id, '_popped_related_posts', true ) );
		$exclude    = Popped_Settings::id_list( get_post_meta( $post_id, '_popped_related_exclude', true ) );
		$posts      = array_values( array_filter( array_map( 'get_post', $pinned_ids ), static function ( $post ) use ( $post_id, $exclude ) { return $post && 'publish' === $post->post_status && empty( $post->post_password ) && (int) $post->ID !== (int) $post_id && ! in_array( (int) $post->ID, $exclude, true ); } ) );
		if ( ! $manual_mode && count( $posts ) < $count ) {
			$automatic = Popped_Query::related( $post_id, $count + count( $exclude ) + count( $posts ), isset( $attributes['relevance'] ) ? $attributes['relevance'] : 'both' );
			$used = array_merge( array( $post_id ), $exclude, wp_list_pluck( $posts, 'ID' ) );
			foreach ( $automatic as $candidate ) {
				if ( ! in_array( (int) $candidate->ID, array_map( 'intval', $used ), true ) ) { $posts[] = $candidate; $used[] = $candidate->ID; }
				if ( count( $posts ) >= $count ) { break; }
			}
		}
		$posts = array_slice( $posts, 0, $count );
		if ( ! $posts ) { return ''; }
		$display = ! empty( $attributes['displayLayout'] ) ? $attributes['displayLayout'] : 'cards';
		$heading_id = self::unique_id( 'popped-related-title' );
		$heading_tag = self::heading_tag( isset( $attributes['sectionTitleLevel'] ) ? $attributes['sectionTitleLevel'] : 2, 2 );
		$out = '<section class="popped-discovery popped-related" aria-labelledby="' . esc_attr( $heading_id ) . '"><div class="popped-section-head"><' . $heading_tag . ' id="' . esc_attr( $heading_id ) . '">' . esc_html__( 'Related Stories', 'popped' ) . '</' . $heading_tag . '></div>';
		$out .= self::story_collection( $posts, $display, $attributes );
		return $out . '</section>';
	}

	/** @return string */
	public static function continue_story( $attributes = array() ) {
		$post_id = ! empty( $attributes['postId'] ) ? absint( $attributes['postId'] ) : get_the_ID();
		$manual = isset( $attributes['selectionMode'] ) && 'manual' === $attributes['selectionMode'] ? Popped_Settings::id_list( isset( $attributes['posts'] ) ? $attributes['posts'] : array() ) : array();
		if ( ! $post_id && ! $manual ) { return ''; }
		$override = $manual ? absint( $manual[0] ) : absint( get_post_meta( $post_id, '_popped_continue_post', true ) );
		$override_post = $override ? get_post( $override ) : null;
		if ( $override_post && ( 'publish' !== $override_post->post_status || ! empty( $override_post->post_password ) ) ) { $override_post = null; }
		$count = ! empty( $attributes['count'] ) ? max( 1, absint( $attributes['count'] ) ) : 1;
		if ( $manual ) { $posts = array_values( array_filter( array_map( 'get_post', array_slice( $manual, 0, $count ) ), static function ( $post ) { return $post && 'publish' === $post->post_status && empty( $post->post_password ); } ) ); }
		else { $posts = $override_post && 'publish' === $override_post->post_status ? array( $override_post ) : Popped_Query::related( $post_id, $count ); }
		if ( ! $posts ) { return ''; }
		$terms = get_the_terms( $post_id, 'post_tag' );
		$context = $terms && ! is_wp_error( $terms ) ? $terms[0]->name : __( 'the archive', 'popped' );
		/* translators: %s: Archive section, such as a tag name. */
		$context_label = sprintf( __( 'More from %s', 'popped' ), $context );
		$out = '<section class="popped-discovery popped-continue"><p class="popped-kicker">' . esc_html__( 'Continue the Story', 'popped' ) . '</p><div class="popped-continue-list">';
		foreach ( $posts as $post ) { if ( ! $post || 'publish' !== $post->post_status || ! empty( $post->post_password ) ) { continue; } $out .= '<a href="' . esc_url( get_permalink( $post ) ) . '"><span>' . esc_html( $context_label ) . '</span><strong>' . esc_html( get_the_title( $post ) ) . '</strong><b aria-hidden="true">→</b></a>'; }
		return $out . '</div></section>';
	}

	/** @return string */
	public static function timeline_navigation( $attributes = array() ) {
		$post_id = ! empty( $attributes['postId'] ) ? absint( $attributes['postId'] ) : get_the_ID();
		if ( ! $post_id ) { return ''; }
		$post = get_post( $post_id );
		if ( ! $post ) { return ''; }
		$tag = sanitize_title( Popped_Settings::get( 'timeline_tag', 'timeline' ) );
		$base = array(
			'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 1,
			'ignore_sticky_posts' => true, 'has_password' => false, 'tag' => $tag,
		);
		$previous = get_posts( array_merge( $base, array( 'date_query' => array( array( 'before' => $post->post_date, 'inclusive' => false ) ), 'orderby' => 'date', 'order' => 'DESC' ) ) );
		$next     = get_posts( array_merge( $base, array( 'date_query' => array( array( 'after' => $post->post_date, 'inclusive' => false ) ), 'orderby' => 'date', 'order' => 'ASC' ) ) );
		if ( ! $previous && ! $next ) { return ''; }
		$heading_tag = self::heading_tag( isset( $attributes['sectionTitleLevel'] ) ? $attributes['sectionTitleLevel'] : 2, 2 );
		$out = '<nav class="popped-discovery popped-chronology" aria-label="' . esc_attr__( 'Timeline chronology', 'popped' ) . '"><' . esc_attr( $heading_tag ) . '>' . esc_html__( 'Previous / Next in Timeline', 'popped' ) . '</' . esc_attr( $heading_tag ) . '><div>';
		if ( $previous ) { $out .= '<a rel="prev" href="' . esc_url( get_permalink( $previous[0] ) ) . '"><small>← ' . esc_html__( 'Previous', 'popped' ) . '</small><span>' . esc_html( get_the_title( $previous[0] ) ) . '</span></a>'; }
		if ( $next ) { $out .= '<a rel="next" href="' . esc_url( get_permalink( $next[0] ) ) . '"><small>' . esc_html__( 'Next', 'popped' ) . ' →</small><span>' . esc_html( get_the_title( $next[0] ) ) . '</span></a>'; }
		return $out . '</div></nav>';
	}

	/** @return string */
	public static function also_on_this_day( $attributes = array() ) {
		$post_id = ! empty( $attributes['postId'] ) ? absint( $attributes['postId'] ) : get_the_ID();
		$post = get_post( $post_id );
		if ( ! $post ) { return ''; }
		$month = (int) get_the_date( 'n', $post );
		$day   = (int) get_the_date( 'j', $post );
		$count = ! empty( $attributes['count'] ) ? absint( $attributes['count'] ) : 4;
		$config = self::source_config( $attributes );
		if ( empty( $attributes['order'] ) ) { $config['order'] = 'manual' === $config['source'] ? 'manual' : 'chronological'; }
		$exclude = array_merge( array( $post_id ), Popped_Settings::id_list( isset( $attributes['excludePosts'] ) ? $attributes['excludePosts'] : array() ) );
		$posts = Popped_Query::on_this_day( $month, $day, $config, $count, array_values( array_unique( $exclude ) ) );
		$title = isset( $attributes['title'] ) ? (string) $attributes['title'] : __( 'Also On This Day', 'popped' );
		if ( ! $posts ) { return self::empty_section( $title, __( 'No other stories match this date and source.', 'popped' ) ); }
		$rail_label = '' !== $title ? $title : __( 'Also On This Day', 'popped' );
		$out = '<section class="popped-discovery popped-also-otd">' . self::section_heading( $title, '', '', $attributes ) . '<div class="popped-rail" tabindex="0" role="region" aria-label="' . esc_attr( $rail_label ) . '">';
		foreach ( $posts as $item ) { $out .= self::post_card( $item, 'rail', $attributes ); }
		return $out . '</div></section>';
	}

	/** @return string */
	public static function search( $attributes = array() ) {
		$query_text = get_search_query();
		$category = isset( $_GET['_popped_search_cat'] ) ? sanitize_title( wp_unslash( $_GET['_popped_search_cat'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tag = isset( $_GET['_popped_search_tag'] ) ? sanitize_title( wp_unslash( $_GET['_popped_search_tag'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$display = ! empty( $attributes['displayLayout'] ) ? sanitize_key( $attributes['displayLayout'] ) : 'list';
		$categories = ! empty( $attributes['filterCategory'] ) ? get_terms( array( 'taxonomy' => 'category', 'hide_empty' => true, 'orderby' => 'name', 'number' => 200 ) ) : array();
		$tags = ! empty( $attributes['filterTag'] ) ? get_terms( array( 'taxonomy' => 'post_tag', 'hide_empty' => true, 'orderby' => 'name', 'number' => 200 ) ) : array();
		$search_id = self::unique_id( 'popped-search-input' );
		$category_id = self::unique_id( 'popped-search-category' );
		$tag_id = self::unique_id( 'popped-search-tag' );
		if ( $query_text ) {
			/* translators: %s: The visitor's search query. */
			$search_heading = sprintf( __( 'Results for “%s”', 'popped' ), $query_text );
		} else {
			$search_heading = __( 'What are you looking for?', 'popped' );
		}
		$search_result_count_label = '';
		if ( is_search() ) {
			global $wp_query;
			/* translators: %s: Number of search results. */
			$search_result_count_label = sprintf( _n( '%s result', '%s results', $wp_query->found_posts, 'popped' ), number_format_i18n( $wp_query->found_posts ) );
		}
		ob_start();
		?>
		<section class="popped-section popped-search-page"><div class="popped-wrap">
			<p class="popped-kicker"><?php esc_html_e( 'Search the archive', 'popped' ); ?></p>
			<h1><?php echo esc_html( $search_heading ); ?></h1>
			<form class="popped-search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<input type="hidden" name="_popped_search" value="1">
				<label class="screen-reader-text" for="<?php echo esc_attr( $search_id ); ?>"><?php esc_html_e( 'Search stories', 'popped' ); ?></label>
				<input id="<?php echo esc_attr( $search_id ); ?>" type="search" name="s" value="<?php echo esc_attr( $query_text ); ?>" placeholder="<?php esc_attr_e( 'Search stories, artists and places…', 'popped' ); ?>">
				<?php if ( $categories && ! is_wp_error( $categories ) ) : ?><label class="screen-reader-text" for="<?php echo esc_attr( $category_id ); ?>"><?php esc_html_e( 'Category', 'popped' ); ?></label><select id="<?php echo esc_attr( $category_id ); ?>" name="_popped_search_cat"><option value=""><?php esc_html_e( 'All categories', 'popped' ); ?></option><?php foreach ( $categories as $term ) : ?><option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $category, $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option><?php endforeach; ?></select><?php endif; ?>
				<?php if ( $tags && ! is_wp_error( $tags ) ) : ?><label class="screen-reader-text" for="<?php echo esc_attr( $tag_id ); ?>"><?php esc_html_e( 'Tag', 'popped' ); ?></label><select id="<?php echo esc_attr( $tag_id ); ?>" name="_popped_search_tag"><option value=""><?php esc_html_e( 'All tags', 'popped' ); ?></option><?php foreach ( $tags as $term ) : ?><option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $tag, $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option><?php endforeach; ?></select><?php endif; ?>
				<button type="submit"><?php esc_html_e( 'Search', 'popped' ); ?></button>
			</form>
			<?php if ( is_search() ) : ?>
				<?php if ( ! empty( $attributes['showResultCount'] ) ) : ?><p class="popped-result-count"><?php echo esc_html( $search_result_count_label ); ?></p><?php endif; ?>
				<div class="popped-search-results <?php echo 'list' === $display ? 'popped-story-list' : 'popped-story-grid'; ?>">
				<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); echo self::post_card( get_post(), 'list' === $display ? 'list' : 'grid', $attributes ); endwhile; else : echo self::empty_state( __( 'No stories found.', 'popped' ), __( 'Try a band, person, place or year.', 'popped' ) ); endif; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<?php if ( $wp_query->max_num_pages > 1 ) { echo wp_kses_post( get_the_posts_pagination( array( 'aria_label' => __( 'Search results pages', 'popped' ) ) ) ); } ?>
			<?php endif; ?>
		</div></section>
		<?php
		return ob_get_clean();
	}

	/**
	 * Add the recommended discovery sequence to ordinary post content.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public static function append_article_discovery( $content ) {
		if ( is_admin() || ! Popped_Settings::get( 'append_discovery', false ) || ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}
		if ( has_block( 'popped/related-stories', $content ) || has_block( 'popped/continue-story', $content ) ) {
			return $content;
		}
		return $content . '<div class="popped-article-discovery">' . self::continue_story() . self::timeline_navigation() . self::also_on_this_day() . self::related_stories() . '</div>';
	}

	/**
	 * Return the site identity used by the Popped shell.
	 *
	 * @param string $image_class CSS classes for an uploaded logo.
	 * @return string
	 */
	private static function site_brand_markup( $image_class = 'popped-brand__image' ) {
		$logo_id = absint( Popped_Settings::get( 'logo_id', 0 ) );
		if ( ! $logo_id ) {
			return '<span>' . esc_html( get_bloginfo( 'name' ) ) . '</span>';
		}

		/* translators: %s: Site name. */
		$home_alt = sprintf( __( '%s home', 'popped' ), get_bloginfo( 'name' ) );
		$logo = wp_get_attachment_image(
			$logo_id,
			'medium',
			false,
			array(
				'alt'      => $home_alt,
				'class'    => $image_class,
				'decoding' => 'async',
				'loading'  => 'eager',
			)
		);

		return $logo ? $logo : '<span>' . esc_html( get_bloginfo( 'name' ) ) . '</span>';
	}

	/** @return string */
	private static function search_icon() {
		return '<svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><circle cx="11" cy="11" r="6.5" fill="none" stroke="currentColor" stroke-width="1.75"/><path d="m16 16 4 4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/></svg>';
	}

	/** @return string */
	private static function close_icon() {
		return '<svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/></svg>';
	}

	/**
	 * Classic-theme fallback header. Block themes keep their native template part.
	 */
	public static function render_global_header() {
		if ( ! Popped_Templates::is_managed_request() || Popped_Templates::uses_native_shell() ) { return; }
		$admin = is_admin_bar_showing() ? ' popped-site-header--admin' : '';
		$brand = self::site_brand_markup();
		?>
		<a class="screen-reader-text popped-skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'popped' ); ?></a>
		<header class="popped-site-header<?php echo esc_attr( $admin ); ?>" data-popped-header>
			<div class="popped-site-header__inner">
				<a class="popped-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo $brand; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				<div class="popped-header-actions">
					<a class="popped-header-search" href="<?php echo esc_url( add_query_arg( 's', '', home_url( '/' ) ) ); ?>">
						<span class="popped-header-search__label"><?php esc_html_e( 'Search', 'popped' ); ?></span>
						<span class="popped-header-search__icon"><?php echo self::search_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</a>
					<button class="popped-menu-trigger" type="button" aria-expanded="false" aria-controls="popped-navigation">
						<span class="screen-reader-text"><?php esc_html_e( 'Open navigation', 'popped' ); ?></span>
						<?php echo Popped_Settings::sanitize_svg( Popped_Settings::get( 'menu_svg', Popped_Settings::default_menu_svg() ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</button>
				</div>
			</div>
		</header>
		<?php
		if ( Popped_Settings::get( 'ticker_enabled', false ) && 'below-header' === Popped_Settings::get( 'ticker_placement', 'below-header' ) ) {
			echo self::news_ticker(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	public static function render_navigation_overlay() {
		if ( ! Popped_Templates::is_managed_request() || Popped_Templates::uses_native_shell() ) { return; }
		$menu_id = absint( Popped_Settings::get( 'menu_id', 0 ) );
		$brand   = self::site_brand_markup( 'popped-brand__image popped-navigation__brand-image' );
		?>
		<div id="popped-navigation" class="popped-navigation" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Site navigation', 'popped' ); ?>" aria-hidden="true" hidden>
			<div class="popped-navigation__panel">
				<div class="popped-navigation__top">
					<a class="popped-navigation__brand popped-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo $brand; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
					<button type="button" class="popped-menu-close">
						<span class="screen-reader-text"><?php esc_html_e( 'Close navigation', 'popped' ); ?></span>
						<?php echo self::close_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</button>
				</div>
				<nav class="popped-navigation__menu" aria-label="<?php esc_attr_e( 'Main navigation', 'popped' ); ?>">
				<?php
				if ( $menu_id ) {
					wp_nav_menu( array( 'menu' => $menu_id, 'container' => false, 'fallback_cb' => false, 'depth' => 2 ) );
				} else {
					wp_page_menu( array( 'show_home' => true, 'menu_class' => 'menu-popped-fallback' ) );
				}
				?>
				</nav>
				<a class="popped-navigation__search" href="<?php echo esc_url( add_query_arg( 's', '', home_url( '/' ) ) ); ?>">
					<span><?php esc_html_e( 'Search stories', 'popped' ); ?></span>
					<span class="popped-navigation__search-icon"><?php echo self::search_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				</a>
			</div>
		</div>
		<?php
	}

	public static function render_site_footer() {
		if ( ! Popped_Templates::is_managed_request() || Popped_Templates::uses_native_shell() ) { return; }
		$menu_id = absint( Popped_Settings::get( 'menu_id', 0 ) );
		if ( Popped_Settings::get( 'ticker_enabled', false ) && 'above-footer' === Popped_Settings::get( 'ticker_placement', 'below-header' ) ) {
			echo self::news_ticker(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>
		<footer class="popped-site-footer"><div class="popped-wrap"><div><a class="popped-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a><?php if ( get_bloginfo( 'description' ) ) : ?><p><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p><?php endif; ?></div><?php if ( $menu_id ) { wp_nav_menu( array( 'menu' => $menu_id, 'container' => 'nav', 'container_aria_label' => __( 'Footer navigation', 'popped' ), 'depth' => 1, 'fallback_cb' => false ) ); } ?><small>© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?></small></div></footer>
		<?php
	}

	/** @return string */
	private static function timeline_filters( $values ) {
		$categories = get_categories( array( 'hide_empty' => true, 'number' => 200 ) );
		$tags       = get_tags( array( 'hide_empty' => true, 'number' => 200 ) );
		$range      = Popped_Settings::year_range();
		$years      = self::limited_years( $range['start'], $range['end'], ! empty( $values['year'] ) ? absint( $values['year'] ) : 0 );
		ob_start();
		?>
		<form class="popped-filters" method="get" data-popped-filters>
			<label class="popped-filter-search"><span><?php esc_html_e( 'Search', 'popped' ); ?></span><input type="search" name="_popped_q" value="<?php echo esc_attr( $values['search'] ); ?>" placeholder="<?php esc_attr_e( 'Title or keyword', 'popped' ); ?>"></label>
			<label><span><?php esc_html_e( 'Year', 'popped' ); ?></span><select name="_popped_year"><option value=""><?php esc_html_e( 'All years', 'popped' ); ?></option><?php foreach ( $years as $year ) : ?><option value="<?php echo esc_attr( $year ); ?>" <?php selected( $values['year'], $year ); ?>><?php echo esc_html( $year ); ?></option><?php endforeach; ?></select></label>
			<label><span><?php esc_html_e( 'Category', 'popped' ); ?></span><select name="_popped_cat"><option value=""><?php esc_html_e( 'All categories', 'popped' ); ?></option><?php foreach ( $categories as $category ) : ?><option value="<?php echo esc_attr( $category->slug ); ?>" <?php selected( $values['cat'], $category->slug ); ?>><?php echo esc_html( $category->name ); ?></option><?php endforeach; ?></select></label>
			<label><span><?php esc_html_e( 'Artist / Band', 'popped' ); ?></span><select name="_popped_tag"><option value=""><?php esc_html_e( 'All tags', 'popped' ); ?></option><?php foreach ( $tags as $tag ) : ?><option value="<?php echo esc_attr( $tag->slug ); ?>" <?php selected( $values['tag'], $tag->slug ); ?>><?php echo esc_html( $tag->name ); ?></option><?php endforeach; ?></select></label>
			<fieldset class="popped-view-switch"><legend><?php esc_html_e( 'View', 'popped' ); ?></legend><label><input type="radio" name="_popped_view" value="vertical" <?php checked( $values['layout'], 'vertical' ); ?>><span><?php esc_html_e( 'Vertical', 'popped' ); ?></span></label><label><input type="radio" name="_popped_view" value="horizontal" <?php checked( $values['layout'], 'horizontal' ); ?>><span><?php esc_html_e( 'Horizontal', 'popped' ); ?></span></label></fieldset>
			<details class="popped-more-filters"><summary><?php esc_html_e( 'More filters', 'popped' ); ?></summary><div><label><span><?php esc_html_e( 'Month', 'popped' ); ?></span><select name="_popped_month"><option value=""><?php esc_html_e( 'All months', 'popped' ); ?></option><?php for ( $month = 1; $month <= 12; $month++ ) : ?><option value="<?php echo esc_attr( $month ); ?>" <?php selected( $values['month'], $month ); ?>><?php echo esc_html( wp_date( 'F', mktime( 12, 0, 0, $month, 1, 2000 ) ) ); ?></option><?php endfor; ?></select></label><label><span><?php esc_html_e( 'Order', 'popped' ); ?></span><select name="_popped_sort"><option value="chronological" <?php selected( $values['sort'], 'chronological' ); ?>><?php esc_html_e( 'Oldest first', 'popped' ); ?></option><option value="newest" <?php selected( $values['sort'], 'newest' ); ?>><?php esc_html_e( 'Newest first', 'popped' ); ?></option><?php if ( 'manual' === $values['sort'] ) : ?><option value="manual" selected><?php esc_html_e( 'Chosen order', 'popped' ); ?></option><?php endif; ?></select></label></div></details>
			<div class="popped-filter-actions"><button type="submit"><?php esc_html_e( 'Apply filters', 'popped' ); ?></button><a href="<?php echo esc_url( strtok( self::current_url(), '?' ) ); ?>"><?php esc_html_e( 'Clear', 'popped' ); ?></a></div>
		</form>
		<?php
		return ob_get_clean();
	}

	/** @return string */
	private static function archive_filter_form( $attributes, $values ) {
		if ( empty( $attributes['filterSearch'] ) && empty( $attributes['filterYear'] ) && empty( $attributes['filterCategory'] ) && empty( $attributes['filterTag'] ) ) { return ''; }
		$categories = ! empty( $attributes['filterCategory'] ) ? get_categories( array( 'hide_empty' => true, 'number' => 200 ) ) : array();
		$tags = ! empty( $attributes['filterTag'] ) ? get_tags( array( 'hide_empty' => true, 'number' => 200 ) ) : array();
		$range = Popped_Settings::year_range();
		$years = self::limited_years( $range['start'], $range['end'], ! empty( $values['year'] ) ? absint( $values['year'] ) : 0 );
		ob_start();
		?>
		<form class="popped-archive-filterbar" method="get" aria-label="<?php esc_attr_e( 'Filter stories', 'popped' ); ?>">
			<?php if ( ! empty( $values['view'] ) ) : ?><input type="hidden" name="_popped_archive_view" value="<?php echo esc_attr( $values['view'] ); ?>"><?php endif; ?>
			<?php if ( ! empty( $values['month'] ) ) : ?><input type="hidden" name="_popped_month" value="<?php echo esc_attr( $values['month'] ); ?>"><?php endif; ?>
			<?php if ( empty( $attributes['filterYear'] ) && ! empty( $values['year'] ) ) : ?><input type="hidden" name="_popped_year" value="<?php echo esc_attr( $values['year'] ); ?>"><?php endif; ?>
			<?php if ( empty( $attributes['filterSearch'] ) && ! empty( $values['search'] ) ) : ?><input type="hidden" name="_popped_archive_q" value="<?php echo esc_attr( $values['search'] ); ?>"><?php endif; ?>
			<?php if ( empty( $attributes['filterCategory'] ) && ! empty( $values['category'] ) ) : ?><input type="hidden" name="_popped_archive_cat" value="<?php echo esc_attr( $values['category'] ); ?>"><?php endif; ?>
			<?php if ( empty( $attributes['filterTag'] ) && ! empty( $values['tag'] ) ) : ?><input type="hidden" name="_popped_archive_tag" value="<?php echo esc_attr( $values['tag'] ); ?>"><?php endif; ?>
			<?php if ( ! empty( $attributes['filterSearch'] ) ) : ?><label class="popped-archive-filterbar__search"><span class="screen-reader-text"><?php esc_html_e( 'Search stories', 'popped' ); ?></span><input type="search" name="_popped_archive_q" value="<?php echo esc_attr( $values['search'] ); ?>" placeholder="<?php esc_attr_e( 'Search the archive…', 'popped' ); ?>"></label><?php endif; ?>
			<?php if ( ! empty( $attributes['filterYear'] ) ) : ?><label><span class="screen-reader-text"><?php esc_html_e( 'Year', 'popped' ); ?></span><select name="_popped_year"><option value=""><?php esc_html_e( 'Any year', 'popped' ); ?></option><?php foreach ( $years as $year ) : ?><option value="<?php echo esc_attr( $year ); ?>" <?php selected( $values['year'], $year ); ?>><?php echo esc_html( $year ); ?></option><?php endforeach; ?></select></label><?php endif; ?>
			<?php if ( ! empty( $attributes['filterCategory'] ) ) : ?><label><span class="screen-reader-text"><?php esc_html_e( 'Category', 'popped' ); ?></span><select name="_popped_archive_cat"><option value=""><?php esc_html_e( 'Any category', 'popped' ); ?></option><?php foreach ( $categories as $category ) : ?><option value="<?php echo esc_attr( $category->slug ); ?>" <?php selected( $values['category'], $category->slug ); ?>><?php echo esc_html( $category->name ); ?></option><?php endforeach; ?></select></label><?php endif; ?>
			<?php if ( ! empty( $attributes['filterTag'] ) ) : ?><label><span class="screen-reader-text"><?php esc_html_e( 'Tag', 'popped' ); ?></span><select name="_popped_archive_tag"><option value=""><?php esc_html_e( 'Any tag', 'popped' ); ?></option><?php foreach ( $tags as $tag ) : ?><option value="<?php echo esc_attr( $tag->slug ); ?>" <?php selected( $values['tag'], $tag->slug ); ?>><?php echo esc_html( $tag->name ); ?></option><?php endforeach; ?></select></label><?php endif; ?>
			<button type="submit"><?php esc_html_e( 'Show stories', 'popped' ); ?></button><a href="<?php echo esc_url( self::archive_url() ); ?>"><?php esc_html_e( 'Clear', 'popped' ); ?></a>
		</form>
		<?php
		return ob_get_clean();
	}

	/** @return string */
	private static function archive_controls( $attributes, $values, $selected_view ) {
		$range = Popped_Settings::year_range();
		$selected_year  = ! empty( $values['year'] ) ? absint( $values['year'] ) : 0;
		$years = self::limited_years( $range['start'], $range['end'], $selected_year );
		$selected_month = ! empty( $values['month'] ) ? absint( $values['month'] ) : 0;
		$preserve = array_filter(
			array(
				'_popped_archive_q'   => isset( $values['search'] ) ? $values['search'] : '',
				'_popped_archive_cat' => isset( $values['category'] ) ? $values['category'] : '',
				'_popped_archive_tag' => isset( $values['tag'] ) ? $values['tag'] : '',
				'_popped_archive_view'=> $selected_view,
			)
		);
		$out = '<nav class="popped-archive-controls" aria-label="' . esc_attr__( 'Archive controls', 'popped' ) . '">';
		if ( ! empty( $attributes['filterYear'] ) ) {
			$out .= '<div class="popped-archive-years">';
			foreach ( $years as $year ) {
				$args = array_merge( $preserve, array( '_popped_year' => $year ) );
				if ( $selected_month ) { $args['_popped_month'] = $selected_month; }
				$out .= '<a ' . ( $selected_year === $year ? 'aria-current="page" ' : '' ) . 'href="' . esc_url( self::archive_url( $args ) ) . '">' . esc_html( $year ) . '</a>';
			}
			$out .= '</div><div class="popped-archive-months">';
			for ( $month = 1; $month <= 12; $month++ ) {
				$args = array_merge( $preserve, array( '_popped_month' => $month ) );
				if ( $selected_year ) { $args['_popped_year'] = $selected_year; }
				$out .= '<a ' . ( $selected_month === $month ? 'aria-current="page" ' : '' ) . 'href="' . esc_url( self::archive_url( $args ) ) . '">' . esc_html( strtoupper( wp_date( 'M', mktime( 12, 0, 0, $month, 1, 2000 ) ) ) ) . '</a>';
			}
			$out .= '</div>';
		}
		$out .= '<div class="popped-archive-views" aria-label="' . esc_attr__( 'Archive view', 'popped' ) . '">';
		foreach ( array( 'timeline' => __( 'Timeline', 'popped' ), 'grid' => __( 'Grid', 'popped' ), 'list' => __( 'List', 'popped' ), 'magazine' => __( 'Magazine', 'popped' ) ) as $view => $label ) {
			$args = array_merge( $preserve, array( '_popped_archive_view' => $view ) );
			if ( $selected_year ) { $args['_popped_year'] = $selected_year; }
			if ( $selected_month ) { $args['_popped_month'] = $selected_month; }
			$out .= '<a ' . ( $selected_view === $view ? 'aria-current="page" ' : '' ) . 'href="' . esc_url( self::archive_url( $args ) ) . '">' . esc_html( $label ) . '</a>';
		}
		return $out . '</div></nav>';
	}

	/** @return string */
	public static function timeline_entry( $post, $options = array() ) {
		$image = '';
		if ( ! isset( $options['showImage'] ) || ! empty( $options['showImage'] ) ) {
			$image = get_the_post_thumbnail( $post, 'large', array( 'loading' => 'lazy' ) );
			if ( ! $image ) { $image = '<span class="popped-image-placeholder" aria-hidden="true"></span>'; }
		}
		$length = ! empty( $options['excerptLength'] ) ? absint( $options['excerptLength'] ) : 24;
		$excerpt = self::story_excerpt( $post, $length );
		return '<article class="popped-timeline-entry"><div class="popped-timeline-entry__marker" aria-hidden="true"></div>' . ( $image ? '<a class="popped-timeline-entry__image" href="' . esc_url( get_permalink( $post ) ) . '" tabindex="-1" aria-hidden="true">' . $image . '</a>' : '' ) . '<div class="popped-timeline-entry__copy">' . self::metadata( $post, $options, 'j F Y' ) . '<' . self::heading_tag( isset( $options['headingLevel'] ) ? $options['headingLevel'] : 3, 3 ) . '><a href="' . esc_url( get_permalink( $post ) ) . '">' . esc_html( get_the_title( $post ) ) . '</a></' . self::heading_tag( isset( $options['headingLevel'] ) ? $options['headingLevel'] : 3, 3 ) . '>' . ( ( ! isset( $options['showExcerpt'] ) || ! empty( $options['showExcerpt'] ) ) && $excerpt ? '<p class="popped-story-excerpt">' . esc_html( $excerpt ) . '</p>' : '' ) . '</div></article>';
	}

	/** @return string */
	/**
	 * Render a reusable editorial story composition.
	 *
	 * @param array  $posts Story posts.
	 * @param string $display cards, list, lead or rail.
	 * @param array  $attributes Block presentation options.
	 * @return string
	 */
	private static function story_collection( $posts, $display = 'cards', $attributes = array() ) {
		$posts = array_values( array_filter( (array) $posts ) );
		if ( ! $posts ) { return ''; }
		$display = in_array( $display, array( 'cards', 'list', 'lead', 'rail' ), true ) ? $display : 'cards';

		if ( 'list' === $display ) {
			$out = '<div class="popped-story-list">';
			foreach ( $posts as $post ) { $out .= self::post_card( $post, 'list', $attributes ); }
			return $out . '</div>';
		}

		if ( 'rail' === $display ) {
			$out = '<div class="popped-rail popped-story-rail" tabindex="0" role="region" aria-label="' . esc_attr__( 'Scrollable stories', 'popped' ) . '">';
			foreach ( $posts as $post ) { $out .= self::post_card( $post, 'rail', $attributes ); }
			return $out . '</div>';
		}

		if ( 'lead' === $display && count( $posts ) > 1 ) {
			$lead = array_shift( $posts );
			$out = '<div class="popped-story-lead-composition">';
			$out .= '<div class="popped-story-lead">' . self::post_card( $lead, 'feature', $attributes ) . '</div>';
			$out .= '<div class="popped-story-supporting">';
			foreach ( $posts as $post ) { $out .= self::post_card( $post, 'grid', $attributes ); }
			return $out . '</div></div>';
		}

		$out = '<div class="popped-story-grid">';
		foreach ( $posts as $post ) { $out .= self::post_card( $post, 'grid', $attributes ); }
		return $out . '</div>';
	}

	public static function post_card( $post, $variant = 'grid', $options = array() ) {
		$image = '';
		if ( ! isset( $options['showImage'] ) || ! empty( $options['showImage'] ) ) {
			$image = get_the_post_thumbnail( $post, 'large', array( 'loading' => 'lazy' ) );
			if ( ! $image ) { $image = '<span class="popped-image-placeholder" aria-hidden="true"></span>'; }
		}
		$length = ! empty( $options['excerptLength'] ) ? absint( $options['excerptLength'] ) : 24;
		$excerpt = self::story_excerpt( $post, $length );
		return '<article class="popped-story popped-story--' . esc_attr( $variant ) . '">' . ( $image ? '<a class="popped-story__image" href="' . esc_url( get_permalink( $post ) ) . '" tabindex="-1" aria-hidden="true">' . $image . '</a>' : '' ) . '<div class="popped-story__copy">' . self::metadata( $post, $options, 'j M Y' ) . '<' . self::heading_tag( isset( $options['headingLevel'] ) ? $options['headingLevel'] : 3, 3 ) . '><a href="' . esc_url( get_permalink( $post ) ) . '">' . esc_html( get_the_title( $post ) ) . '</a></' . self::heading_tag( isset( $options['headingLevel'] ) ? $options['headingLevel'] : 3, 3 ) . '>' . ( ( ! isset( $options['showExcerpt'] ) || ! empty( $options['showExcerpt'] ) ) && $excerpt ? '<p class="popped-story-excerpt">' . esc_html( $excerpt ) . '</p>' : '' ) . '</div></article>';
	}

	/** Build independent metadata elements so every visibility control is exact. */
	private static function metadata( $post, $options, $date_format ) {
		$parts = array();
		$date_format = (string) get_option( 'date_format', $date_format );
		if ( ! isset( $options['showDate'] ) || ! empty( $options['showDate'] ) ) {
			$parts[] = '<time class="popped-date" datetime="' . esc_attr( get_the_date( 'c', $post ) ) . '">' . esc_html( get_the_date( $date_format, $post ) ) . '</time>';
		}
		if ( ! empty( $options['showCategory'] ) ) {
			$categories = get_the_category( $post->ID );
			if ( $categories ) { $parts[] = '<span class="popped-meta-category">' . esc_html( $categories[0]->name ) . '</span>'; }
		}
		if ( ! empty( $options['showTags'] ) ) {
			$tags = get_the_tags( $post->ID );
			if ( $tags ) { $parts[] = '<span class="popped-meta-tags">' . esc_html( implode( ', ', wp_list_pluck( array_slice( $tags, 0, 3 ), 'name' ) ) ) . '</span>'; }
		}
		if ( ! empty( $options['showAuthor'] ) ) {
			$parts[] = '<span class="popped-meta-author">' . esc_html( get_the_author_meta( 'display_name', $post->post_author ) ) . '</span>';
		}
		return $parts ? '<div class="popped-meta">' . implode( '', $parts ) . '</div>' : '';
	}

	/**
	 * Return a safe semantic heading tag.
	 *
	 * @param mixed $level Requested level.
	 * @param int   $fallback Fallback level.
	 * @return string
	 */
	private static function heading_tag( $level, $fallback = 2 ) {
		$level = absint( $level );
		if ( $level < 1 || $level > 6 ) {
			$level = max( 1, min( 6, absint( $fallback ) ) );
		}
		return 'h' . $level;
	}

	/** @return string */
	private static function section_heading( $title, $url = '', $link_text = '', $attributes = array() ) {
		$title = (string) $title;
		if ( '' === $title && ( ! $url || is_wp_error( $url ) ) ) { return ''; }
		$tag = self::heading_tag( isset( $attributes['sectionTitleLevel'] ) ? $attributes['sectionTitleLevel'] : 2, 2 );
		$out = '<header class="popped-section-head">' . ( '' !== $title ? '<' . $tag . '>' . esc_html( $title ) . '</' . $tag . '>' : '' );
		if ( $url && ! is_wp_error( $url ) ) { $out .= '<a class="popped-text-link" href="' . esc_url( $url ) . '">' . esc_html( $link_text ) . ' <span aria-hidden="true">→</span></a>'; }
		return $out . '</header>';
	}

	/** @return string */
	private static function empty_section( $title, $message, $editor_message = '' ) {
		return '<section class="popped-section"><div class="popped-wrap">' . self::section_heading( $title ) . self::empty_state( $message, self::is_editor_preview() ? $editor_message : '' ) . '</div></section>';
	}

	/** @return string */
	private static function empty_state( $title, $message ) {
		return '<div class="popped-empty"><h3>' . esc_html( $title ) . '</h3>' . ( $message ? '<p>' . esc_html( $message ) . '</p>' : '' ) . '</div>';
	}

	/** @return bool */
	private static function is_editor_preview() {
		return defined( 'REST_REQUEST' ) && REST_REQUEST;
	}

	/** @return string */
	private static function editor_hint( $message ) {
		return self::is_editor_preview() && $message ? '<small class="popped-editor-hint">' . esc_html( $message ) . '</small>' : '';
	}

	/** @return string */
	private static function pagination( $query, $paged ) {
		if ( $query->max_num_pages < 2 ) { return ''; }
		$links = paginate_links( array( 'base' => add_query_arg( '_popped_page', '%#%' ), 'format' => '', 'current' => $paged, 'total' => $query->max_num_pages, 'type' => 'list', 'prev_text' => __( 'Previous', 'popped' ), 'next_text' => __( 'Next', 'popped' ) ) );
		return $links ? '<nav class="popped-pagination" aria-label="' . esc_attr__( 'Results pages', 'popped' ) . '">' . $links . '</nav>' : '';
	}

	/** @return array<string,mixed> */
	private static function source_config( $attributes ) {
		return array(
			'source' => isset( $attributes['source'] ) ? sanitize_key( $attributes['source'] ) : 'all',
			'categories' => isset( $attributes['categories'] ) ? $attributes['categories'] : array(),
			'tags' => isset( $attributes['tags'] ) ? $attributes['tags'] : array(),
			'posts' => isset( $attributes['posts'] ) ? $attributes['posts'] : array(),
			'excludeCategories' => isset( $attributes['excludeCategories'] ) ? $attributes['excludeCategories'] : array(),
			'excludeTags' => isset( $attributes['excludeTags'] ) ? $attributes['excludeTags'] : array(),
			'excludePosts' => isset( $attributes['excludePosts'] ) ? $attributes['excludePosts'] : array(),
			'order' => isset( $attributes['order'] ) ? sanitize_key( $attributes['order'] ) : 'newest',
			'count' => isset( $attributes['count'] ) ? absint( $attributes['count'] ) : 5,
		);
	}

	/**
	 * Return a bounded list of useful years without creating thousands of controls.
	 *
	 * Populated years are preferred. Empty archives fall back to a bounded
	 * consecutive range so new sites still have usable navigation.
	 *
	 * @param int $start First allowed year.
	 * @param int $end Last allowed year.
	 * @param int $selected Selected year to preserve in the window.
	 * @param int $limit Maximum number of controls.
	 * @return int[]
	 */
	private static function limited_years( $start, $end, $selected = 0, $limit = self::MAX_RENDERED_YEARS ) {
		$start    = absint( $start );
		$end      = absint( $end );
		$selected = absint( $selected );
		$limit    = max( 1, min( absint( $limit ), self::MAX_RENDERED_YEARS ) );

		if ( ! $start || ! $end || $end < $start ) {
			return array();
		}

		$counts = self::year_counts( $start, $end );
		$years  = array_map( 'absint', array_keys( $counts ) );

		if ( ! $years ) {
			$first = max( $start, $end - $limit + 1 );
			$years = range( $first, $end );
		}

		if ( $selected >= $start && $selected <= $end && ! in_array( $selected, $years, true ) ) {
			$years[] = $selected;
		}

		sort( $years, SORT_NUMERIC );
		if ( count( $years ) <= $limit ) {
			return array_values( $years );
		}

		if ( $selected && in_array( $selected, $years, true ) ) {
			$index  = array_search( $selected, $years, true );
			$offset = max( 0, (int) $index - (int) floor( ( $limit - 1 ) / 2 ) );
			$offset = min( $offset, count( $years ) - $limit );
			return array_values( array_slice( $years, $offset, $limit ) );
		}

		return array_values( array_slice( $years, -$limit ) );
	}


	/**
	 * Count visible stories for a year range in one query.
	 *
	 * @param int $start Start year.
	 * @param int $end End year.
	 * @return array<int,int>
	 */
	private static function year_counts( $start, $end ) {
		$start = absint( $start );
		$end   = absint( $end );
		if ( ! $start || ! $end || $end < $start ) {
			return array();
		}

		$key = 'year_counts_' . absint( get_option( 'popped_content_cache_version', 1 ) ) . '_' . $start . '_' . $end;
		$cached = wp_cache_get( $key, 'popped' );
		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}

		global $wpdb;
		// A single cached aggregate is materially cheaper than loading every post just to count years.
		$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->prepare(
				"SELECT YEAR(post_date) AS story_year, COUNT(ID) AS story_count
				FROM {$wpdb->posts}
				WHERE post_type = %s
					AND post_status = %s
					AND post_password = ''
					AND post_date >= %s
					AND post_date < %s
				GROUP BY YEAR(post_date)",
				'post',
				'publish',
				sprintf( '%04d-01-01 00:00:00', $start ),
				sprintf( '%04d-01-01 00:00:00', $end + 1 )
			),
			ARRAY_A
		);

		$counts = array();
		foreach ( $rows as $row ) {
			$counts[ absint( $row['story_year'] ) ] = absint( $row['story_count'] );
		}
		wp_cache_set( $key, $counts, 'popped', HOUR_IN_SECONDS );
		return $counts;
	}

	/**
	 * Build a safe excerpt without exposing password-protected post content.
	 *
	 * @param WP_Post $post Story post.
	 * @param int     $length Maximum words.
	 * @return string
	 */
	private static function story_excerpt( $post, $length ) {
		if ( ! $post || ! empty( $post->post_password ) ) {
			return '';
		}
		return has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( $post->post_content ), max( 1, absint( $length ) ) );
	}

	/**
	 * Return a request-unique DOM id.
	 *
	 * @param string $base Base identifier.
	 * @return string
	 */
	private static function unique_id( $base ) {
		static $counts = array();
		$base = sanitize_html_class( $base );
		$counts[ $base ] = isset( $counts[ $base ] ) ? $counts[ $base ] + 1 : 1;
		return 1 === $counts[ $base ] ? $base : $base . '-' . $counts[ $base ];
	}

	/** @return string */
	private static function timeline_url() {
		$page_id = absint( Popped_Settings::get( 'timeline_page_id', 0 ) );
		if ( $page_id && 'publish' === get_post_status( $page_id ) ) {
			return get_permalink( $page_id );
		}
		if ( is_singular( 'page' ) ) {
			$current = get_permalink( get_queried_object_id() );
			if ( $current ) {
				return $current;
			}
		}
		return home_url( '/' );
	}

	/** @return string */
	private static function archive_url( $args = array() ) {
		$page_id = absint( Popped_Settings::get( 'archive_page_id', 0 ) );
		if ( $page_id && 'publish' === get_post_status( $page_id ) ) {
			$url = get_permalink( $page_id );
		} elseif ( is_singular( 'page' ) && get_permalink( get_queried_object_id() ) ) {
			$url = get_permalink( get_queried_object_id() );
		} else {
			$url = home_url( '/' );
		}
		return $args ? add_query_arg( $args, $url ) : $url;
	}

	/** @return string */
	private static function collection_url( $collection ) {
		$args = array();
		if ( in_array( $collection['source'], array( 'category', 'categories-tags' ), true ) && $collection['category'] ) { $args['_popped_archive_cat'] = $collection['category']; }
		if ( in_array( $collection['source'], array( 'tag', 'categories-tags' ), true ) && $collection['tag'] ) { $args['_popped_archive_tag'] = $collection['tag']; }
		return self::archive_url( $args );
	}

	/** @return string */
	private static function current_url() {
		$home_parts = wp_parse_url( home_url( '/' ) );
		if ( empty( $home_parts['scheme'] ) || empty( $home_parts['host'] ) ) {
			return home_url( '/' );
		}

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
		$request_path = wp_parse_url( $request_uri, PHP_URL_PATH );
		$request_query = wp_parse_url( $request_uri, PHP_URL_QUERY );

		$origin = $home_parts['scheme'] . '://' . $home_parts['host'];
		if ( ! empty( $home_parts['port'] ) ) {
			$origin .= ':' . absint( $home_parts['port'] );
		}

		$url = $origin . '/' . ltrim( is_string( $request_path ) ? $request_path : '/', '/' );
		if ( is_string( $request_query ) && '' !== $request_query ) {
			$url .= '?' . $request_query;
		}

		return esc_url_raw( $url );
	}
}
