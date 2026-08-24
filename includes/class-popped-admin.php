<?php
/**
 * Task-focused Popped control centre.
 *
 * @package Popped
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Popped_Admin {
	/** @var string[] */
	private static $pages = array(
		'popped-setup',
		'popped-design',
		'popped-components',
		'popped-collections',
		'popped-templates',
		'popped-advanced',
	);

	/** @return array<string,string> */
	private static function page_labels() {
		return array(
			'popped-setup'       => __( 'Setup', 'popped' ),
			'popped-design'      => __( 'Design', 'popped' ),
			'popped-components'  => __( 'Components', 'popped' ),
			'popped-collections' => __( 'Collections', 'popped' ),
			'popped-templates'   => __( 'Templates / Display', 'popped' ),
			'popped-advanced'    => __( 'Advanced', 'popped' ),
		);
	}

	public static function hooks() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'add_meta_boxes_post', array( __CLASS__, 'discovery_meta_box' ) );
		add_action( 'save_post_post', array( __CLASS__, 'save_discovery_meta' ) );
	}

	public static function menu() {
		add_menu_page( __( 'Popped', 'popped' ), __( 'Popped', 'popped' ), 'manage_options', 'popped', array( __CLASS__, 'render' ), 'dashicons-archive', 3 );
		$labels = self::page_labels();
		foreach ( self::$pages as $slug ) {
			$label = $labels[ $slug ];
			add_submenu_page( 'popped', $label . ' — Popped', $label, 'manage_options', $slug, array( __CLASS__, 'render' ) );
		}
	}

	/** @param string $hook Admin hook. */
	public static function assets( $hook ) {
		$is_popped_page = false !== strpos( $hook, 'popped' );
		$screen         = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		$is_post_editor = in_array( $hook, array( 'post.php', 'post-new.php' ), true ) && $screen && 'post' === $screen->post_type;
		if ( ! $is_popped_page && ! $is_post_editor ) { return; }
		if ( $is_popped_page ) { wp_enqueue_style( 'popped-admin', POPPED_URL . 'assets/css/admin.css', array(), POPPED_VERSION ); }
		wp_enqueue_style( 'popped-admin-search', POPPED_URL . 'assets/css/admin-search.css', $is_popped_page ? array( 'popped-admin' ) : array(), POPPED_VERSION );
		wp_enqueue_script( 'popped-admin', POPPED_URL . 'assets/js/admin.js', array( 'wp-api-fetch', 'wp-html-entities', 'wp-i18n' ), POPPED_VERSION, true );
		wp_set_script_translations( 'popped-admin', 'popped', POPPED_DIR . 'languages' );
		if ( $is_popped_page ) { wp_enqueue_media(); }
		wp_localize_script( 'popped-admin', 'poppedAdmin', array(
			'optionName' => Popped_Settings::OPTION,
		) );
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : 'popped'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$method = 'popped' === $page ? 'dashboard' : str_replace( array( 'popped-', '-' ), array( '', '_' ), $page );
		if ( ! method_exists( __CLASS__, $method ) ) { $method = 'dashboard'; }
		echo '<div class="wrap popped-admin"><div class="popped-admin__mast"><a href="' . esc_url( admin_url( 'admin.php?page=popped' ) ) . '" class="popped-admin__brand">Popped</a><span>' . esc_html__( 'Editorial archive system', 'popped' ) . '</span></div>';
		settings_errors();
		self::$method();
		echo '</div>';
	}

	private static function dashboard() {
		$o = Popped_Settings::all();
		self::page_head( __( 'Build the archive, one clear choice at a time.', 'popped' ), __( 'Setup creates the structure. Design establishes the system. Blocks handle the page-level decisions.', 'popped' ), __( 'Control centre', 'popped' ) );
		echo '<div class="popped-task-grid">';
		$labels = self::page_labels();
		foreach ( self::$pages as $slug ) {
			$label = $labels[ $slug ];
			$descriptions = array( 'popped-setup' => __( 'Create or safely repair the core pages and navigation.', 'popped' ), 'popped-design' => __( 'Choose typography, colour, shape, density and motion.', 'popped' ), 'popped-components' => __( 'Set the defaults inherited by new Popped blocks.', 'popped' ), 'popped-collections' => __( 'Create named, reusable story selections.', 'popped' ), 'popped-templates' => __( 'Choose how Popped integrates with the active theme.', 'popped' ), 'popped-advanced' => __( 'Archive range and less common system controls.', 'popped' ) );
			echo '<a class="popped-task-card" href="' . esc_url( admin_url( 'admin.php?page=' . $slug ) ) . '"><span><strong>' . esc_html( $label ) . '</strong><small>' . esc_html( $descriptions[ $slug ] ) . '</small></span><b aria-hidden="true">→</b></a>';
		}
		echo '</div>';
		$checks = array( __( 'Timeline page', 'popped' ) => ! empty( $o['timeline_page_id'] ) && get_post( $o['timeline_page_id'] ), __( 'Archive page', 'popped' ) => ! empty( $o['archive_page_id'] ) && get_post( $o['archive_page_id'] ), __( 'Timeline tag', 'popped' ) => term_exists( $o['timeline_tag'], 'post_tag' ) );
		echo '<div class="popped-status"><h2>' . esc_html__( 'Structure', 'popped' ) . '</h2>';
		foreach ( $checks as $label => $ready ) { echo '<div><span class="popped-status__mark ' . ( $ready ? 'is-good' : 'is-warning' ) . '" aria-hidden="true">' . ( $ready ? '✓' : '!' ) . '</span><strong>' . esc_html( $label ) . '</strong><span>' . esc_html( $ready ? __( 'Ready', 'popped' ) : __( 'Needs setup', 'popped' ) ) . '</span></div>'; }
		echo '</div>';
	}

	private static function setup() {
		$o = Popped_Settings::all();
		self::page_head(
			__( 'Set up Popped', 'popped' ),
			__( 'Create the optional archive structure safely. Popped never takes ownership of your theme, homepage or navigation.', 'popped' )
		);
		$result = get_transient( 'popped_setup_result_' . get_current_user_id() );
		if ( $result ) {
			delete_transient( 'popped_setup_result_' . get_current_user_id() );
			echo '<div class="popped-setup-result"><h2>' . esc_html__( 'Setup result', 'popped' ) . '</h2>';
			foreach ( $result as $label => $status ) {
				echo '<div><strong>' . esc_html( $label ) . '</strong><span>' . esc_html( $status ) . '</span></div>';
			}
			echo '</div>';
		}
		?>
		<div class="popped-setup-grid">
			<div class="popped-panel popped-setup-card">
				<p class="popped-eyebrow"><?php esc_html_e( 'Recommended', 'popped' ); ?></p>
				<h2><?php esc_html_e( 'Quick Setup', 'popped' ); ?></h2>
				<p><?php esc_html_e( 'Create or repair the Timeline, Archive and Search pages. Templates, navigation and your homepage remain entirely under WordPress and the active theme.', 'popped' ); ?></p>
				<?php self::setup_form_start( 'quick' ); ?>
				<button class="button button-primary button-hero" type="submit"><?php echo $o['setup_complete'] ? esc_html__( 'Check and repair setup', 'popped' ) : esc_html__( 'Create archive pages', 'popped' ); ?></button>
				</form>
			</div>
			<div class="popped-panel popped-setup-card">
				<p class="popped-eyebrow"><?php esc_html_e( 'A few choices', 'popped' ); ?></p>
				<h2><?php esc_html_e( 'Custom Setup', 'popped' ); ?></h2>
				<p><?php esc_html_e( 'Choose only the archive defaults Popped needs. Site-wide presentation remains owned by your theme.', 'popped' ); ?></p>
				<?php self::setup_form_start( 'custom' ); ?>
				<div class="popped-fields">
					<?php
					self::raw_select(
						'typography',
						__( 'Typography', 'popped' ),
						$o['typography'],
						array(
							'inherit'   => __( 'Theme / Global Styles — Recommended', 'popped' ),
							'clean'     => __( 'Clean', 'popped' ),
							'editorial' => __( 'Editorial', 'popped' ),
							'magazine'  => __( 'Magazine', 'popped' ),
							'custom'    => __( 'Custom', 'popped' ),
						)
					);
					self::raw_select(
						'density',
						__( 'Density', 'popped' ),
						$o['density'],
						array(
							'compact'  => __( 'Compact', 'popped' ),
							'standard' => __( 'Standard — Recommended', 'popped' ),
							'spacious' => __( 'Spacious', 'popped' ),
						)
					);
					?>
					<label>
						<span><?php esc_html_e( 'Timeline tag', 'popped' ); ?></span>
						<input type="text" name="timeline_tag" value="<?php echo esc_attr( $o['timeline_tag'] ); ?>">
					</label>
					<?php
					self::raw_select(
						'timeline_layout',
						__( 'Timeline view', 'popped' ),
						$o['timeline_layout'],
						array(
							'vertical'   => __( 'Vertical — Recommended', 'popped' ),
							'horizontal' => __( 'Horizontal', 'popped' ),
						)
					);
					?>
				</div>
				<button class="button button-primary" type="submit"><?php esc_html_e( 'Create or repair archive pages', 'popped' ); ?></button>
				</form>
			</div>
		</div>
		<?php
	}

	private static function design() {
		$o = Popped_Settings::all();
		self::page_head( __( 'Design the system', 'popped' ), __( 'Choose a coherent direction. Individual blocks can make deliberate local changes later.', 'popped' ) );
		self::form_open( 'design' );
		self::preset_group( 'typography', __( 'Typography', 'popped' ), $o['typography'], array( 'inherit' => array( __( 'Theme / Global Styles', 'popped' ), __( 'Follow the active block theme typography', 'popped' ), true ), 'clean' => array( __( 'Clean', 'popped' ), __( 'Crisp sans-serif hierarchy', 'popped' ) ), 'editorial' => array( __( 'Editorial', 'popped' ), __( 'Serif-led and restrained', 'popped' ) ), 'magazine' => array( __( 'Magazine', 'popped' ), __( 'Expressive display typography', 'popped' ) ), 'custom' => array( __( 'Custom', 'popped' ), __( 'Use an uploaded font below', 'popped' ) ) ) );
		self::preset_group( 'density', __( 'Density', 'popped' ), $o['density'], array( 'compact' => array( __( 'Compact', 'popped' ), __( 'More stories in less space', 'popped' ) ), 'standard' => array( __( 'Standard', 'popped' ), __( 'Balanced editorial rhythm', 'popped' ), true ), 'spacious' => array( __( 'Spacious', 'popped' ), __( 'Generous, gallery-like pacing', 'popped' ) ) ) );
		self::preset_group( 'shape', __( 'Shape', 'popped' ), $o['shape'], array( 'square' => array( __( 'Square', 'popped' ), __( 'Sharp, precise edges', 'popped' ) ), 'soft' => array( __( 'Soft', 'popped' ), __( 'Subtle corner treatment', 'popped' ), true ), 'rounded' => array( __( 'Rounded', 'popped' ), __( 'Friendlier image corners', 'popped' ) ) ) );
		self::preset_group( 'motion', __( 'Motion', 'popped' ), $o['motion'], array( 'standard' => array( __( 'Standard', 'popped' ), __( 'Quiet transitions; system reduced-motion preferences are always respected', 'popped' ), true ), 'reduced' => array( __( 'Reduced', 'popped' ), __( 'Immediate movement, minimal transitions', 'popped' ) ), 'none' => array( __( 'None', 'popped' ), __( 'No Popped-created animation', 'popped' ) ) ) );
		self::preset_group( 'colour_preset', __( 'Colour', 'popped' ), $o['colour_preset'], array( 'paper' => array( __( 'Paper', 'popped' ), __( 'Warm neutral with an accessible burnt-red accent', 'popped' ), true ), 'crisp' => array( __( 'Crisp', 'popped' ), __( 'White, graphite and blue', 'popped' ) ), 'warm' => array( __( 'Warm', 'popped' ), __( 'Cream, umber and brick', 'popped' ) ), 'custom' => array( __( 'Custom', 'popped' ), __( 'Set semantic colours below', 'popped' ) ) ) );
		if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
			echo '<div class="popped-panel"><h2>' . esc_html__( 'Site identity', 'popped' ) . '</h2><p>' . esc_html__( 'Your block theme owns the header, footer, logo and global typography. Change those in the Site Editor; Popped only styles its own editorial components.', 'popped' ) . '</p><p><a class="button" href="' . esc_url( admin_url( 'site-editor.php' ) ) . '">' . esc_html__( 'Open Site Editor', 'popped' ) . '</a></p></div>';
		} else {
			echo '<div class="popped-panel"><h2>' . esc_html__( 'Identity', 'popped' ) . '</h2><div class="popped-fields">';
			self::media( 'logo_id', __( 'Logo', 'popped' ), $o['logo_id'], 'image' );
			self::checkbox( 'sticky_header', __( 'Keep the header visible', 'popped' ), $o['sticky_header'], __( 'Recommended for long archives. The header remains compact and respects the WordPress admin bar.', 'popped' ) );
			echo '</div><p class="description">' . esc_html__( 'Popped constrains logos by both height and width to protect the header proportions. Use tightly cropped artwork without large transparent margins for the cleanest result.', 'popped' ) . '</p></div>';
		}
		?>
		<details class="popped-advanced"><summary><?php esc_html_e( 'Advanced colours and custom font', 'popped' ); ?></summary><div class="popped-advanced__body"><div class="popped-fields popped-colours"><?php foreach ( Popped_Settings::defaults()['colours'] as $role => $fallback ) : ?><label><span><?php echo esc_html( self::colour_label( $role ) ); ?></span><input type="color" name="<?php echo esc_attr( Popped_Settings::OPTION ); ?>[colours][<?php echo esc_attr( $role ); ?>]" value="<?php echo esc_attr( $o['colours'][ $role ] ); ?>"></label><?php endforeach; ?></div><hr><div class="popped-fields"><?php self::media( 'custom_font_id', __( 'Custom font file', 'popped' ), $o['custom_font_id'], 'font' ); self::text( 'custom_font_name', __( 'Font family name', 'popped' ), $o['custom_font_name'] ); self::text( 'custom_font_fallback', __( 'Fallback stack', 'popped' ), $o['custom_font_fallback'], __( 'Use a resilient system stack so text remains readable while the custom font loads.', 'popped' ) ); self::select( 'custom_font_role', __( 'Use for', 'popped' ), $o['custom_font_role'], array( 'none' => __( 'Not assigned', 'popped' ), 'display' => __( 'Display', 'popped' ), 'heading' => __( 'Headings', 'popped' ), 'body' => __( 'Body', 'popped' ), 'navigation' => __( 'Navigation', 'popped' ), 'meta' => __( 'Metadata', 'popped' ), 'buttons' => __( 'Buttons', 'popped' ) ) ); ?></div></div></details>
		<?php self::form_close();
	}

	private static function components() {
		$o = Popped_Settings::all();
		self::page_head( __( 'Component defaults', 'popped' ), __( 'New blocks inherit these choices. A block changes only when an editor deliberately overrides it.', 'popped' ) );
		self::form_open( 'components' );
		?>
		<div class="popped-panel"><h2><?php esc_html_e( 'Homepage block defaults', 'popped' ); ?></h2><p><?php esc_html_e( 'These defaults apply only when you insert the optional Popped Homepage block. Your WordPress homepage and template remain theme-owned.', 'popped' ); ?></p><div class="popped-fields"><?php self::select( 'homepage_composition', __( 'Homepage block hierarchy', 'popped' ), $o['homepage_composition'], array( 'editorial' => __( 'Editorial lead + sections — Recommended', 'popped' ), 'sections' => __( 'Section stack — Legacy', 'popped' ) ), __( 'Editorial adds a current lead story, suppresses immediate duplicates, and treats the ordered sections as supporting depth. Section stack preserves the original behaviour.', 'popped' ) ); ?></div><ul class="popped-sortable" data-popped-sortable><?php foreach ( $o['homepage_sections'] as $slug => $section ) : ?><li draggable="true"><span class="popped-drag" aria-hidden="true">⋮⋮</span><input type="hidden" name="<?php echo esc_attr( Popped_Settings::OPTION ); ?>[homepage_order][]" value="<?php echo esc_attr( $slug ); ?>"><strong><?php echo esc_html( Popped_Settings::homepage_section_label( $slug, $section['label'] ) ); ?></strong><label class="popped-switch"><input type="checkbox" name="<?php echo esc_attr( Popped_Settings::OPTION ); ?>[homepage_sections][<?php echo esc_attr( $slug ); ?>][enabled]" value="1" <?php checked( $section['enabled'] ); ?>><span aria-hidden="true"></span><em><?php esc_html_e( 'On', 'popped' ); ?></em></label></li><?php endforeach; ?></ul></div>
		<div class="popped-component-grid">
				<section class="popped-panel"><p class="popped-eyebrow">01</p><h2><?php esc_html_e( 'Timeline', 'popped' ); ?></h2><div class="popped-fields"><?php self::text( 'timeline_tag', __( 'Timeline tag', 'popped' ), $o['timeline_tag'], __( 'Posts carrying this tag enter the primary timeline.', 'popped' ) ); self::number( 'timeline_per_page', __( 'Stories per page', 'popped' ), $o['timeline_per_page'], 6, 36, __( 'Recommended: 10. Pagination keeps long timelines manageable.', 'popped' ) ); self::select( 'timeline_layout', __( 'Default direction', 'popped' ), $o['timeline_layout'], array( 'vertical' => __( 'Vertical', 'popped' ), 'horizontal' => __( 'Horizontal', 'popped' ) ) ); ?></div></section>
			<section class="popped-panel"><p class="popped-eyebrow">02</p><h2><?php esc_html_e( 'On This Day', 'popped' ); ?></h2><div class="popped-fields"><?php self::select( 'on_this_day_source', __( 'Eligible posts', 'popped' ), $o['on_this_day_source'], array( 'all' => __( 'All posts', 'popped' ), 'timeline' => __( 'Timeline posts only', 'popped' ) ) ); self::number( 'on_this_day_count', __( 'Maximum stories', 'popped' ), $o['on_this_day_count'], 1, 12, __( 'Recommended: 4. One story is featured; the rest feed the archive link.', 'popped' ) ); self::number( 'on_this_day_override', __( 'Optional hero post', 'popped' ), $o['on_this_day_override'], 0, PHP_INT_MAX, __( 'Post-level “Prefer as hero” is usually easier.', 'popped' ) ); ?></div></section>
			<section class="popped-panel"><p class="popped-eyebrow">03</p><h2><?php esc_html_e( 'Latest Stories', 'popped' ); ?></h2><div class="popped-fields"><?php self::select( 'latest_source', __( 'Default source', 'popped' ), $o['latest_source'], array( 'all' => __( 'All posts', 'popped' ), 'timeline' => __( 'Timeline posts', 'popped' ) ) ); self::number( 'latest_count', __( 'Number of stories', 'popped' ), $o['latest_count'], 3, 12, __( 'Recommended: 5. One lead plus four supporting stories avoids an unnecessary extra row.', 'popped' ) ); self::select( 'latest_order', __( 'Order', 'popped' ), $o['latest_order'], array( 'newest' => __( 'Newest first', 'popped' ), 'chronological' => __( 'Oldest first', 'popped' ) ) ); ?></div></section>
			<section class="popped-panel"><p class="popped-eyebrow">04</p><h2><?php esc_html_e( 'Related Content', 'popped' ); ?></h2><div class="popped-fields"><?php self::number( 'related_count', __( 'Related stories', 'popped' ), $o['related_count'], 2, 8, __( 'Recommended: 3. Keeps the article endcap to one clean row.', 'popped' ) ); self::select( 'related_layout', __( 'Presentation', 'popped' ), $o['related_layout'], array( 'cards' => __( 'Cards', 'popped' ), 'list' => __( 'List', 'popped' ) ) ); self::checkbox( 'append_discovery', __( 'Add discovery after posts', 'popped' ), $o['append_discovery'], __( 'Continue the Story, chronology, same-date stories and related stories.', 'popped' ) ); ?></div></section>
			<section class="popped-panel">
				<p class="popped-eyebrow">05</p>
				<h2><?php esc_html_e( 'News Ticker', 'popped' ); ?></h2>
				<p><?php esc_html_e( 'These defaults are used by News Ticker blocks and the optional News Ticker section in a Popped Homepage block. Global injection is available only for the opt-in legacy Popped shell.', 'popped' ); ?></p>
				<div class="popped-fields">
					<?php
					self::checkbox( 'ticker_enabled', __( 'Inject ticker into legacy Popped shell', 'popped' ), $o['ticker_enabled'], __( 'Only applies when Templates / Display → Enable legacy Popped template shell is turned on.', 'popped' ) );
					self::select( 'ticker_source', __( 'Default headlines', 'popped' ), $o['ticker_source'], array( 'latest' => __( 'Latest posts', 'popped' ), 'manual' => __( 'Chosen stories', 'popped' ), 'mixed' => __( 'Chosen, then latest', 'popped' ) ) );
					self::select( 'ticker_placement', __( 'Legacy shell placement', 'popped' ), $o['ticker_placement'], array( 'below-header' => __( 'Below header', 'popped' ), 'above-footer' => __( 'Above footer', 'popped' ) ) );
					self::post_picker(
						Popped_Settings::OPTION . '[ticker_post_ids][]',
						__( 'Chosen ticker stories', 'popped' ),
						$o['ticker_post_ids'],
						__( 'Used by Manual and Mixed sources. Drag to set headline order.', 'popped' ),
						0,
						true
					);
					?>
				</div>
			</section>
		</div>
		<?php self::form_close();
	}

	private static function collections() {
		$o = Popped_Settings::all();
		self::page_head( __( 'Collections', 'popped' ), __( 'Create named query definitions that can be reused by any Featured Collection block.', 'popped' ) );
		self::form_open( 'collections' );
		echo '<div data-popped-collections>';
		$index = 0;
		foreach ( $o['collections'] as $collection ) { self::collection_card( $collection, $index++ ); }
		echo '</div><button type="button" class="button button-secondary" data-popped-add-collection>' . esc_html__( 'Add collection', 'popped' ) . '</button>';
		echo '<template id="popped-collection-template">'; self::collection_card( array( 'id' => '', 'name' => '', 'description' => '', 'source' => 'tag', 'category' => '', 'tag' => '', 'posts' => array(), 'featured_image' => 0, 'order' => 'newest', 'count' => 5, 'style' => 'editorial' ), '__INDEX__' ); echo '</template>';
		self::form_close();
	}

	private static function templates() {
		$o = Popped_Settings::all();
		self::page_head( __( 'Theme ownership', 'popped' ), __( 'Popped supplies editorial blocks. Your active theme owns site templates, navigation, header, footer and homepage.', 'popped' ) );
		self::form_open( 'templates' );
		echo '<div class="popped-panel"><h2>' . esc_html__( 'Classic-theme compatibility', 'popped' ) . '</h2><p>' . esc_html__( 'Most sites should leave this off. Enable it only when a classic theme cannot provide a usable shell for Popped archive pages.', 'popped' ) . '</p><div class="popped-fields">';
		self::checkbox( 'template_mode', __( 'Enable legacy Popped template shell', 'popped' ), $o['template_mode'], __( 'Opt-in only. Never affects block themes or the homepage.', 'popped' ) );
		echo '</div></div>';
		self::form_close();
		echo '<div class="popped-panel"><h2>' . esc_html__( 'Templates stay yours', 'popped' ) . '</h2><p>' . esc_html__( 'Popped 2.1 no longer creates or repairs Single, Archive, Search, 404 or Front Page templates. Existing Popped-owned template records from 2.0.x are retired automatically during upgrade.', 'popped' ) . '</p>';
		if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
			echo '<a class="button" href="' . esc_url( admin_url( 'site-editor.php?path=/templates' ) ) . '">' . esc_html__( 'Open Site Editor templates', 'popped' ) . '</a>';
		}
		echo '</div>';
	}

	private static function advanced() {
		$o = Popped_Settings::all();
		$detected = Popped_Settings::published_year_range();
		self::page_head( __( 'Advanced', 'popped' ), __( 'Less common system choices. Most sites should leave these at their recommended values.', 'popped' ) );
		self::form_open( 'advanced' );
		echo '<div class="popped-panel"><h2>' . esc_html__( 'Historical range', 'popped' ) . '</h2><div class="popped-fields">';
		self::select( 'year_range_mode', __( 'Year range', 'popped' ), $o['year_range_mode'], array( 'auto' => __( 'Automatic — Recommended', 'popped' ), 'manual' => __( 'Manual', 'popped' ) ), sprintf( __( 'Automatic follows your published posts. Current detected range: %1$d–%2$d.', 'popped' ), $detected['start'], $detected['end'] ) );
		echo '</div><div class="popped-fields popped-fields--two">';
		self::number( 'year_start', __( 'Manual first year', 'popped' ), $o['year_start'] ? $o['year_start'] : $detected['start'], 1000, 3000, __( 'Used only when Year range is Manual.', 'popped' ) );
		self::number( 'year_end', __( 'Manual last year', 'popped' ), $o['year_end'] ? $o['year_end'] : $detected['end'], 1000, 3000, __( 'Used only when Year range is Manual.', 'popped' ) );
		echo '</div></div><div class="popped-panel"><h2>' . esc_html__( 'Search integration', 'popped' ) . '</h2><div class="popped-fields">'; self::checkbox( 'taxonomy_search', __( 'Enrich all WordPress searches with taxonomy names', 'popped' ), $o['taxonomy_search'], __( 'Off by default. Popped Search blocks already get taxonomy-aware search without changing your theme’s normal search.', 'popped' ) ); echo '</div></div><details class="popped-advanced"><summary>' . esc_html__( 'Custom navigation SVG', 'popped' ) . '</summary><div class="popped-advanced__body popped-fields"><label><span>' . esc_html__( 'Menu icon SVG', 'popped' ) . '</span><textarea name="' . esc_attr( Popped_Settings::OPTION ) . '[menu_svg]" rows="6">' . esc_textarea( $o['menu_svg'] ) . '</textarea><small>' . esc_html__( 'Popped applies a strict SVG allowlist when this is saved.', 'popped' ) . '</small></label></div></details>';
		self::form_close();
	}

	public static function discovery_meta_box() { add_meta_box( 'popped-discovery', __( 'Popped Discovery', 'popped' ), array( __CLASS__, 'discovery_meta_box_content' ), 'post', 'side', 'default' ); }

	/** @param WP_Post $post Current post. */
	public static function discovery_meta_box_content( $post ) {
		wp_nonce_field( 'popped_discovery_' . $post->ID, 'popped_discovery_nonce' );
		$continue = absint( get_post_meta( $post->ID, '_popped_continue_post', true ) );
		$related = Popped_Settings::id_list( get_post_meta( $post->ID, '_popped_related_posts', true ) );
		$exclude = Popped_Settings::id_list( get_post_meta( $post->ID, '_popped_related_exclude', true ) );
		$primary = (bool) get_post_meta( $post->ID, '_popped_otd_primary', true );
		?>
		<?php
		self::post_picker( 'popped_continue_post', __( 'Continue the Story', 'popped' ), $continue ? array( $continue ) : array(), __( 'Leave empty to choose automatically.', 'popped' ), 1, false );
		self::post_picker( 'popped_related_posts[]', __( 'Pinned related stories', 'popped' ), $related, __( 'Selected stories appear before automatic results.', 'popped' ), 0, true );
		self::post_picker( 'popped_related_exclude[]', __( 'Exclude from related stories', 'popped' ), $exclude, '', 0, false );
		?>
		<p><label><input type="checkbox" name="popped_otd_primary" value="1" <?php checked( $primary ); ?>> <?php esc_html_e( 'Prefer as On This Day hero', 'popped' ); ?></label></p>
		<?php
	}

	/** @param int $post_id Saved post ID. */
	public static function save_discovery_meta( $post_id ) {
		if ( ! isset( $_POST['popped_discovery_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['popped_discovery_nonce'] ) ), 'popped_discovery_' . $post_id ) ) { return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
		$continue_ids = self::readable_post_ids(
			isset( $_POST['popped_continue_post'] )
				? array( absint( wp_unslash( $_POST['popped_continue_post'] ) ) )
				: array()
		);
		$related_ids = self::readable_post_ids(
			isset( $_POST['popped_related_posts'] )
				? array_map( 'absint', (array) wp_unslash( $_POST['popped_related_posts'] ) )
				: array()
		);
		$exclude_ids = self::readable_post_ids(
			isset( $_POST['popped_related_exclude'] )
				? array_map( 'absint', (array) wp_unslash( $_POST['popped_related_exclude'] ) )
				: array()
		);
		update_post_meta( $post_id, '_popped_continue_post', $continue_ids ? $continue_ids[0] : 0 );
		update_post_meta( $post_id, '_popped_related_posts', $related_ids );
		update_post_meta( $post_id, '_popped_related_exclude', $exclude_ids );
		update_post_meta( $post_id, '_popped_otd_primary', ! empty( $_POST['popped_otd_primary'] ) ? 1 : 0 );
	}

	private static function collection_card( $collection, $index ) {
		$base = Popped_Settings::OPTION . '[collections][' . $index . ']';
		?>
		<section class="popped-panel popped-collection-editor" data-popped-collection>
			<div class="popped-panel__head"><div><p class="popped-eyebrow"><?php esc_html_e( 'Collection', 'popped' ); ?></p><h2 data-popped-collection-title><?php echo esc_html( $collection['name'] ?: __( 'Untitled collection', 'popped' ) ); ?></h2></div><button type="button" class="button-link-delete" data-popped-remove-collection><?php esc_html_e( 'Remove', 'popped' ); ?></button></div>
			<input type="hidden" name="<?php echo esc_attr( $base ); ?>[id]" value="<?php echo esc_attr( $collection['id'] ); ?>">
			<div class="popped-fields popped-fields--two">
				<label><span><?php esc_html_e( 'Collection name', 'popped' ); ?></span><input type="text" data-popped-collection-name name="<?php echo esc_attr( $base ); ?>[name]" value="<?php echo esc_attr( $collection['name'] ); ?>" required></label>
				<label><span><?php esc_html_e( 'Description', 'popped' ); ?></span><textarea name="<?php echo esc_attr( $base ); ?>[description]" rows="3"><?php echo esc_textarea( $collection['description'] ); ?></textarea></label>
				<label><span><?php esc_html_e( 'Stories from', 'popped' ); ?></span><select data-popped-collection-source name="<?php echo esc_attr( $base ); ?>[source]"><option value="category" <?php selected( $collection['source'], 'category' ); ?>><?php esc_html_e( 'Category', 'popped' ); ?></option><option value="tag" <?php selected( $collection['source'], 'tag' ); ?>><?php esc_html_e( 'Tag', 'popped' ); ?></option><option value="categories-tags" <?php selected( $collection['source'], 'categories-tags' ); ?>><?php esc_html_e( 'Category + Tag', 'popped' ); ?></option><option value="manual" <?php selected( $collection['source'], 'manual' ); ?>><?php esc_html_e( 'Stories I choose', 'popped' ); ?></option></select></label>
				<?php self::term_picker( $base, 'category', $collection['category'], __( 'Category', 'popped' ) ); self::term_picker( $base, 'tag', $collection['tag'], __( 'Tag', 'popped' ) ); ?>
				<div class="popped-collection-manual" data-popped-collection-field="manual">
					<label><span><?php esc_html_e( 'Choose stories', 'popped' ); ?></span><input type="search" data-popped-post-search aria-autocomplete="list" aria-expanded="false" autocomplete="off" placeholder="<?php esc_attr_e( 'Type at least two characters…', 'popped' ); ?>"></label>
					<div class="popped-admin-search-results" data-popped-search-results role="listbox" hidden></div><p class="popped-admin-search-status" data-popped-search-status role="status" aria-live="polite"></p>
					<ol class="popped-admin-selected-posts" data-popped-selected-posts data-input-base="<?php echo esc_attr( $base ); ?>[posts]"><?php foreach ( $collection['posts'] as $post_id ) : $post = get_post( $post_id ); if ( $post ) : ?><li draggable="true" data-post-id="<?php echo esc_attr( $post_id ); ?>"><span class="popped-drag" aria-hidden="true">⋮⋮</span><span><?php echo esc_html( get_the_title( $post ) ?: __( '(Untitled story)', 'popped' ) ); ?></span><input type="hidden" name="<?php echo esc_attr( $base ); ?>[posts][]" value="<?php echo esc_attr( $post_id ); ?>"><span class="popped-order-actions"><button type="button" class="button" data-popped-move-post="up" aria-label="<?php esc_attr_e( 'Move story up', 'popped' ); ?>">↑</button><button type="button" class="button" data-popped-move-post="down" aria-label="<?php esc_attr_e( 'Move story down', 'popped' ); ?>">↓</button><button type="button" class="button-link-delete" data-popped-remove-post><?php esc_html_e( 'Remove', 'popped' ); ?></button></span></li><?php endif; endforeach; ?></ol>
				</div>
				<?php self::media_named( $base . '[featured_image]', __( 'Featured image', 'popped' ), $collection['featured_image'], 'image' ); self::select_named( $base . '[order]', __( 'Order', 'popped' ), $collection['order'], array( 'newest' => __( 'Newest first', 'popped' ), 'chronological' => __( 'Oldest first', 'popped' ), 'manual' => __( 'Chosen order', 'popped' ) ) ); self::number_named( $base . '[count]', __( 'Number of stories', 'popped' ), $collection['count'], 1, 12, __( 'Recommended: 5. Enough for a lead plus supporting stories.', 'popped' ) ); self::select_named( $base . '[style]', __( 'Display style', 'popped' ), $collection['style'], array( 'editorial' => __( 'Editorial', 'popped' ), 'cards' => __( 'Cards', 'popped' ), 'feature' => __( 'Feature', 'popped' ), 'minimal' => __( 'Minimal', 'popped' ) ) ); ?>
			</div>
		</section>
		<?php
	}

	private static function term_picker( $base, $taxonomy, $slug, $label ) {
		$term = $slug ? get_term_by( 'slug', $slug, 'category' === $taxonomy ? 'category' : 'post_tag' ) : null;
		echo '<label class="popped-admin-search" data-popped-collection-field="' . esc_attr( $taxonomy ) . '"><span>' . esc_html( $label ) . '</span><input type="search" data-popped-term-picker="' . esc_attr( $taxonomy ) . '" value="' . esc_attr( $term ? $term->name : '' ) . '" aria-autocomplete="list" aria-expanded="false" autocomplete="off" placeholder="' . esc_attr__( 'Type at least two characters…', 'popped' ) . '"><input type="hidden" data-popped-term-value name="' . esc_attr( $base ) . '[' . esc_attr( $taxonomy ) . ']" value="' . esc_attr( $slug ) . '"><span class="popped-admin-search-results" data-popped-search-results role="listbox" hidden></span><small class="popped-admin-search-status" data-popped-search-status role="status" aria-live="polite"></small></label>';
	}

	/**
	 * Keep only referenced stories the current editor is allowed to read.
	 *
	 * @param int[] $post_ids Candidate post IDs.
	 * @return int[]
	 */
	private static function readable_post_ids( $post_ids ) {
		$readable = array();
		foreach ( Popped_Settings::id_list( $post_ids ) as $post_id ) {
			$post = get_post( $post_id );
			if ( $post && 'post' === $post->post_type && current_user_can( 'read_post', $post_id ) ) {
				$readable[] = $post_id;
			}
		}
		return array_values( array_unique( $readable ) );
	}

	/** @param array<int,int> $post_ids Selected posts. */
	private static function post_picker( $name, $label, $post_ids, $help = '', $max = 0, $reorder = true ) {
		echo '<div class="popped-admin-post-picker" data-popped-post-picker data-input-name="' . esc_attr( $name ) . '" data-max="' . esc_attr( $max ) . '" data-reorder="' . ( $reorder ? 'true' : 'false' ) . '"><label><strong>' . esc_html( $label ) . '</strong><input class="widefat" type="search" data-popped-post-search aria-autocomplete="list" aria-expanded="false" autocomplete="off" placeholder="' . esc_attr__( 'Type at least two characters…', 'popped' ) . '"></label><div class="popped-admin-search-results" data-popped-search-results role="listbox" hidden></div><p class="popped-admin-search-status" data-popped-search-status role="status" aria-live="polite"></p>';
		if ( $help ) { echo '<p class="description">' . esc_html( $help ) . '</p>'; }
		echo '<ol class="popped-admin-selected-posts" data-popped-selected-posts>';
		foreach ( $post_ids as $post_id ) {
			$post = get_post( $post_id );
			if ( ! $post || ! current_user_can( 'read_post', $post_id ) ) { continue; }
			echo '<li ' . ( $reorder ? 'draggable="true" ' : '' ) . 'data-post-id="' . esc_attr( $post_id ) . '">';
			if ( $reorder ) { echo '<span class="popped-drag" aria-hidden="true">⋮⋮</span>'; }
			echo '<span>' . esc_html( get_the_title( $post ) ?: __( '(Untitled story)', 'popped' ) ) . '</span><input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $post_id ) . '"><span class="popped-order-actions">';
			if ( $reorder ) { echo '<button type="button" class="button" data-popped-move-post="up" aria-label="' . esc_attr__( 'Move story up', 'popped' ) . '">↑</button><button type="button" class="button" data-popped-move-post="down" aria-label="' . esc_attr__( 'Move story down', 'popped' ) . '">↓</button>'; }
			echo '<button type="button" class="button-link-delete" data-popped-remove-post>' . esc_html__( 'Remove', 'popped' ) . '</button></span></li>';
		}
		echo '</ol></div>';
	}
	private static function setup_form_start( $mode ) { echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">'; wp_nonce_field( 'popped_setup' ); echo '<input type="hidden" name="action" value="popped_quick_setup"><input type="hidden" name="setup_mode" value="' . esc_attr( $mode ) . '">'; }
	private static function page_head( $title, $description, $eyebrow = 'Popped' ) { echo '<div class="popped-page-head"><div><p class="popped-eyebrow">' . esc_html( $eyebrow ) . '</p><h1>' . esc_html( $title ) . '</h1><p>' . esc_html( $description ) . '</p></div></div>'; }
	private static function form_open( $section ) { echo '<form class="popped-settings-form" method="post" action="options.php">'; settings_fields( 'popped_settings' ); echo '<input type="hidden" name="' . esc_attr( Popped_Settings::OPTION ) . '[_section]" value="' . esc_attr( $section ) . '">'; }
	private static function form_close() { submit_button( __( 'Save changes', 'popped' ) ); echo '</form>'; }
	private static function preset_group( $name, $label, $value, $choices ) { echo '<fieldset class="popped-choice-group"><legend>' . esc_html( $label ) . '</legend><div class="popped-choice-grid">'; foreach ( $choices as $key => $choice ) { $recommended = ! empty( $choice[2] ) ? '<em class="popped-recommended">' . esc_html__( 'Recommended', 'popped' ) . '</em>' : ''; echo '<label><input type="radio" name="' . esc_attr( Popped_Settings::OPTION ) . '[' . esc_attr( $name ) . ']" value="' . esc_attr( $key ) . '" ' . checked( $value, $key, false ) . '><span><strong>' . esc_html( $choice[0] ) . $recommended . '</strong><small>' . esc_html( $choice[1] ) . '</small></span></label>'; } echo '</div></fieldset>'; }
	private static function text( $name, $label, $value, $help = '' ) { echo '<label><span>' . esc_html( $label ) . '</span><input type="text" name="' . esc_attr( Popped_Settings::OPTION ) . '[' . esc_attr( $name ) . ']" value="' . esc_attr( $value ) . '">' . ( $help ? '<small>' . esc_html( $help ) . '</small>' : '' ) . '</label>'; }
	private static function number( $name, $label, $value, $min, $max, $help = '' ) { self::number_named( Popped_Settings::OPTION . '[' . $name . ']', $label, $value, $min, $max, $help ); }
	private static function number_named( $name, $label, $value, $min, $max, $help = '' ) { echo '<label><span>' . esc_html( $label ) . '</span><input type="number" min="' . esc_attr( $min ) . '" max="' . esc_attr( $max ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">' . ( $help ? '<small>' . esc_html( $help ) . '</small>' : '' ) . '</label>'; }
	private static function select( $name, $label, $value, $choices, $help = '' ) { self::select_named( Popped_Settings::OPTION . '[' . $name . ']', $label, $value, $choices, $help ); }
	private static function select_named( $name, $label, $value, $choices, $help = '' ) { echo '<label><span>' . esc_html( $label ) . '</span><select name="' . esc_attr( $name ) . '">'; foreach ( $choices as $key => $choice ) { echo '<option value="' . esc_attr( $key ) . '" ' . selected( $value, $key, false ) . '>' . esc_html( $choice ) . '</option>'; } echo '</select>' . ( $help ? '<small>' . esc_html( $help ) . '</small>' : '' ) . '</label>'; }
	private static function raw_select( $name, $label, $value, $choices ) { echo '<label><span>' . esc_html( $label ) . '</span><select name="' . esc_attr( $name ) . '">'; foreach ( $choices as $key => $choice ) { echo '<option value="' . esc_attr( $key ) . '" ' . selected( $value, $key, false ) . '>' . esc_html( $choice ) . '</option>'; } echo '</select></label>'; }
	private static function checkbox( $name, $label, $value, $help = '' ) { echo '<label class="popped-check"><input type="checkbox" name="' . esc_attr( Popped_Settings::OPTION ) . '[' . esc_attr( $name ) . ']" value="1" ' . checked( $value, true, false ) . '><span><strong>' . esc_html( $label ) . '</strong>' . ( $help ? '<small>' . esc_html( $help ) . '</small>' : '' ) . '</span></label>'; }
	/** @param string $role Semantic colour role. @return string */
	private static function colour_label( $role ) {
		$labels = array(
			'background' => __( 'Background', 'popped' ),
			'surface'    => __( 'Surface', 'popped' ),
			'text'       => __( 'Text', 'popped' ),
			'muted'      => __( 'Muted', 'popped' ),
			'accent'     => __( 'Accent', 'popped' ),
			'border'     => __( 'Border', 'popped' ),
		);
		return isset( $labels[ $role ] ) ? $labels[ $role ] : sanitize_text_field( $role );
	}

	private static function media( $name, $label, $value, $type ) { self::media_named( Popped_Settings::OPTION . '[' . $name . ']', $label, $value, $type ); }
	private static function media_named( $name, $label, $value, $type ) { echo '<label class="popped-media-field"><span>' . esc_html( $label ) . '</span><input type="hidden" data-popped-media-value name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '"><span class="popped-media-name">' . esc_html( $value ? get_the_title( $value ) : __( 'None selected', 'popped' ) ) . '</span><span><button type="button" class="button" data-popped-media="' . esc_attr( $type ) . '">' . esc_html__( 'Choose file', 'popped' ) . '</button> <button type="button" class="button-link-delete" data-popped-media-remove>' . esc_html__( 'Remove', 'popped' ) . '</button></span></label>'; }
}
