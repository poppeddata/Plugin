<?php
/**
 * Plugin Name:       Popped
 * Description:       A Gutenberg-native editorial archive, historical timeline and discovery toolkit for WordPress.
 * Version:           2.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            Popped
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       popped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'POPPED_VERSION', '2.1.0' );
define( 'POPPED_FILE', __FILE__ );
define( 'POPPED_DIR', plugin_dir_path( __FILE__ ) );
define( 'POPPED_URL', plugin_dir_url( __FILE__ ) );

require_once POPPED_DIR . 'includes/class-popped-settings.php';
require_once POPPED_DIR . 'includes/class-popped-block-config.php';
require_once POPPED_DIR . 'includes/class-popped-query.php';
require_once POPPED_DIR . 'includes/class-popped-components.php';
require_once POPPED_DIR . 'includes/class-popped-blocks.php';
require_once POPPED_DIR . 'includes/class-popped-setup.php';
require_once POPPED_DIR . 'includes/class-popped-admin.php';
require_once POPPED_DIR . 'includes/class-popped-templates.php';
require_once POPPED_DIR . 'includes/class-popped-plugin.php';

register_activation_hook( __FILE__, array( 'Popped_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Popped_Plugin', 'deactivate' ) );

Popped_Plugin::instance();
