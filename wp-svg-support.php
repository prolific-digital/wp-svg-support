<?php
/**
 * Plugin Name:       WP SVG
 * Plugin URI:        https://prolificdigital.com
 * Description:       Adds support for uploading SVG files to the Media Library.
 * Version:           1.1.0
 * Requires at least: 6.5
 * Requires PHP:      8.4
 * Author:            Prolific Digital
 * Author URI:        https://prolificdigital.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-svg-support
 * Domain Path:       /languages
 * Update URI:        https://api.prolificdigital.io/api/update?plugin=wp-svg-support
 *
 * @package WPSVGSupport
 */

defined( 'ABSPATH' ) || exit;

define( 'WP_SVG_SUPPORT_VERSION', '1.1.0' );

define( 'WP_SVG_SUPPORT_PLUGIN_FILE', __FILE__ );
define( 'WP_SVG_SUPPORT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Composer dependencies (plugin-update-checker). Present in release zips;
// in a git checkout run `composer install` first.
if ( is_readable( WP_SVG_SUPPORT_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	require_once WP_SVG_SUPPORT_PLUGIN_DIR . 'vendor/autoload.php';
}

require_once WP_SVG_SUPPORT_PLUGIN_DIR . 'includes/update-checker.php';

/**
 * Load bundled translations from /languages (the header's Domain Path).
 */
add_action(
	'init',
	static function () {
		load_plugin_textdomain( 'wp-svg-support', false, dirname( plugin_basename( WP_SVG_SUPPORT_PLUGIN_FILE ) ) . '/languages' );
	}
);

class WP_SVG {
  public function __construct() {
    add_filter('upload_mimes', array($this, 'add_svg_mime_type'));
  }

  // Allow SVG files to be uploaded to the media library.
  public function add_svg_mime_type($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
  }
}

// Initialize the plugin.
function wp_svg_init() {
  new WP_SVG();
}

add_action('plugins_loaded', 'wp_svg_init');
