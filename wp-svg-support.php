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

// Composer dependencies (plugin-update-checker, svg-sanitize). Present in release zips;
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

/**
 * Adds sanitized SVG upload support to the Media Library.
 */
class WP_SVG {

	/**
	 * Registers the upload filters.
	 */
	public function __construct() {
		add_filter( 'upload_mimes', array( $this, 'add_svg_mime_type' ), 10, 2 );
		add_filter( 'wp_handle_upload_prefilter', array( $this, 'sanitize_svg_upload' ) );
		add_filter( 'wp_handle_sideload_prefilter', array( $this, 'sanitize_svg_upload' ) );
		add_filter( 'wp_check_filetype_and_ext', array( $this, 'check_svg_filetype' ), 10, 5 );
	}

	/**
	 * The capability a user needs to upload SVG files.
	 *
	 * @return string
	 */
	public static function upload_capability() {
		/**
		 * Filters the capability required to upload SVG files.
		 *
		 * Defaults to `upload_files`. Return a stricter capability such as
		 * `manage_options` to limit SVG uploads to administrators.
		 *
		 * @param string $capability Capability name.
		 */
		return (string) apply_filters( 'wp_svg_support_upload_capability', 'upload_files' );
	}

	/**
	 * Whether a user may upload SVG files.
	 *
	 * @param int|WP_User|null $user User ID or object. Defaults to the current user.
	 * @return bool
	 */
	public static function user_can_upload_svg( $user = null ) {
		$capability = self::upload_capability();
		if ( null === $user ) {
			return current_user_can( $capability );
		}
		return user_can( $user, $capability );
	}

	/**
	 * Allow SVG files to be uploaded by users with the upload capability.
	 *
	 * @param array            $mimes Allowed MIME types keyed by extension.
	 * @param int|WP_User|null $user  User the list is for, or null for the current user.
	 * @return array
	 */
	public function add_svg_mime_type( $mimes, $user = null ) {
		if ( self::user_can_upload_svg( $user ) ) {
			$mimes['svg'] = 'image/svg+xml';
		}
		return $mimes;
	}

	/**
	 * Whether an upload array describes an SVG file.
	 *
	 * @param array $file Upload array from $_FILES or a sideload.
	 * @return bool
	 */
	private static function is_svg_upload( $file ) {
		$name = isset( $file['name'] ) ? (string) $file['name'] : '';
		$type = isset( $file['type'] ) ? (string) $file['type'] : '';
		return 'svg' === strtolower( pathinfo( $name, PATHINFO_EXTENSION ) ) || 'image/svg+xml' === $type;
	}

	/**
	 * Sanitize an SVG before WordPress moves it into the uploads folder.
	 *
	 * Runs on `wp_handle_upload_prefilter` and `wp_handle_sideload_prefilter`,
	 * so it covers the Media Library, the block editor, REST `/wp/v2/media`
	 * and media_handle_sideload(). The temporary file is rewritten with the
	 * sanitized markup; the upload is rejected if sanitizing fails.
	 *
	 * @param array $file Upload array.
	 * @return array
	 */
	public function sanitize_svg_upload( $file ) {
		if ( ! empty( $file['error'] ) || ! self::is_svg_upload( $file ) ) {
			return $file;
		}

		if ( ! self::user_can_upload_svg() ) {
			$file['error'] = __( 'Sorry, you are not allowed to upload SVG files.', 'wp-svg-support' );
			return $file;
		}

		if ( ! class_exists( \enshrined\svgSanitize\Sanitizer::class ) ) {
			$file['error'] = __( 'SVG uploads are disabled because the SVG sanitizer is missing. Run composer install in the WP SVG plugin folder.', 'wp-svg-support' );
			return $file;
		}

		$path  = isset( $file['tmp_name'] ) ? (string) $file['tmp_name'] : '';
		$dirty = ( '' !== $path && is_readable( $path ) ) ? file_get_contents( $path ) : false; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local temp file.
		$clean = false;

		// Compressed SVGs are not supported: the markup cannot be inspected safely.
		if ( is_string( $dirty ) && '' !== $dirty && ! str_starts_with( $dirty, "\x1f\x8b" ) ) {
			$sanitizer = new \enshrined\svgSanitize\Sanitizer();
			$sanitizer->removeRemoteReferences( true );
			$clean = $sanitizer->sanitize( $dirty );
		}

		if ( ! is_string( $clean ) || '' === trim( $clean ) ) {
			$file['error'] = __( 'This SVG file could not be sanitized, so it was not uploaded.', 'wp-svg-support' );
			return $file;
		}

		if ( false === file_put_contents( $path, $clean ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- local temp file.
			$file['error'] = __( 'The sanitized SVG file could not be saved.', 'wp-svg-support' );
			return $file;
		}

		$file['size'] = strlen( $clean );
		return $file;
	}

	/**
	 * Confirm the real type of an uploaded SVG.
	 *
	 * WordPress 6.5+ compares the extension with the type fileinfo detects
	 * and rejects SVGs it cannot match. This accepts a `.svg` file only when
	 * the user may upload SVGs and the file content is SVG markup (it has
	 * already been sanitized by sanitize_svg_upload()).
	 *
	 * @param array        $data      Values for ext, type and proper_filename.
	 * @param string       $file      Full path to the file.
	 * @param string       $filename  The file's name.
	 * @param array|null   $mimes     Allowed MIME types keyed by extension.
	 * @param string|false $real_mime The detected MIME type, or false.
	 * @return array
	 */
	public function check_svg_filetype( $data, $file, $filename, $mimes, $real_mime = false ) {
		if ( ! empty( $data['ext'] ) && ! empty( $data['type'] ) ) {
			return $data;
		}
		if ( 'svg' !== strtolower( pathinfo( (string) $filename, PATHINFO_EXTENSION ) ) ) {
			return $data;
		}
		if ( is_array( $mimes ) && ! isset( $mimes['svg'] ) ) {
			return $data;
		}
		if ( ! self::user_can_upload_svg() ) {
			return $data;
		}

		$svg_types = array( 'image/svg+xml', 'image/svg', 'text/xml', 'application/xml', 'text/plain', 'text/html' );
		if ( $real_mime && ! in_array( $real_mime, $svg_types, true ) ) {
			return $data;
		}

		$head = is_readable( $file ) ? file_get_contents( $file, false, null, 0, 4096 ) : false; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local temp file.
		if ( ! is_string( $head ) || false === stripos( $head, '<svg' ) ) {
			return $data;
		}

		$data['ext']  = 'svg';
		$data['type'] = 'image/svg+xml';
		return $data;
	}
}

/**
 * Initialize the plugin.
 */
function wp_svg_init() {
	new WP_SVG();
}

add_action( 'plugins_loaded', 'wp_svg_init' );
