<?php
/**
 * Security regression check: SVG uploads must be sanitized.
 *
 * Run with the plugin active:
 *   ddev wp eval-file wp-content/plugins/wp-svg-support/tests/security/svg-upload.php
 *
 * Uploads a malicious SVG through WordPress's real upload handling
 * (wp_handle_sideload, which runs the wp_handle_sideload_prefilter and
 * wp_check_filetype_and_ext filters) as the `e2e` user, prints the stored
 * file, then checks that a user without `upload_files` cannot upload SVGs.
 * Exits non-zero on failure. Every file it creates is removed.
 *
 * @package WPSVGSupport
 */

defined( 'ABSPATH' ) || exit;

require_once ABSPATH . 'wp-admin/includes/file.php';

$wp_svg_support_failures = 0;

$wp_svg_support_payload = <<<'SVG'
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="10" height="10" onload="alert('onload')">
	<script>alert('script')</script>
	<a xlink:href="javascript:alert('link')"><rect width="10" height="10" fill="red" onclick="alert('click')"/></a>
	<circle cx="5" cy="5" r="2" fill="blue"/>
</svg>
SVG;

/**
 * Sideload one SVG string as the given user and return the result.
 *
 * @param string $contents SVG markup.
 * @param int    $user_id  User to act as.
 * @return array wp_handle_sideload() result.
 */
function wp_svg_support_test_sideload( $contents, $user_id ) {
	wp_set_current_user( $user_id );
	$tmp = wp_tempnam( 'evil.svg' );
	file_put_contents( $tmp, $contents ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	$file   = array(
		'name'     => 'wp-svg-support-security-test.svg',
		'type'     => 'image/svg+xml',
		'tmp_name' => $tmp,
		'error'    => 0,
		'size'     => filesize( $tmp ),
	);
	$result = wp_handle_sideload( $file, array( 'test_form' => false ) );
	if ( file_exists( $tmp ) ) {
		wp_delete_file( $tmp );
	}
	return $result;
}

$wp_svg_support_e2e = get_user_by( 'login', 'e2e' );
if ( ! $wp_svg_support_e2e ) {
	WP_CLI::error( 'The e2e user does not exist.' );
}

// 1. Malicious SVG as a user with upload_files.
$wp_svg_support_result = wp_svg_support_test_sideload( $wp_svg_support_payload, $wp_svg_support_e2e->ID );
if ( isset( $wp_svg_support_result['error'] ) ) {
	WP_CLI::warning( 'Upload rejected: ' . $wp_svg_support_result['error'] );
	++$wp_svg_support_failures;
} else {
	$wp_svg_support_stored = file_get_contents( $wp_svg_support_result['file'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	WP_CLI::log( 'Stored ' . $wp_svg_support_result['file'] . ' (' . $wp_svg_support_result['type'] . '):' );
	WP_CLI::log( $wp_svg_support_stored );
	wp_delete_file( $wp_svg_support_result['file'] );
	if ( preg_match( '/<script|\son[a-z]+\s*=|javascript:/i', $wp_svg_support_stored ) ) {
		WP_CLI::warning( 'FAIL: stored SVG still contains script, on* handlers or javascript: links.' );
		++$wp_svg_support_failures;
	} else {
		WP_CLI::log( 'PASS: stored SVG is clean.' );
	}
	if ( ! str_contains( $wp_svg_support_stored, '<circle' ) ) {
		WP_CLI::warning( 'FAIL: legitimate SVG content was lost.' );
		++$wp_svg_support_failures;
	}
	if ( 'image/svg+xml' !== $wp_svg_support_result['type'] ) {
		WP_CLI::warning( 'FAIL: stored type is ' . $wp_svg_support_result['type'] );
		++$wp_svg_support_failures;
	}
}

// 2. A file that is not valid SVG must be rejected.
$wp_svg_support_result = wp_svg_support_test_sideload( "<?php echo 'not an svg';", $wp_svg_support_e2e->ID );
if ( isset( $wp_svg_support_result['error'] ) ) {
	WP_CLI::log( 'PASS: invalid SVG rejected: ' . $wp_svg_support_result['error'] );
} else {
	wp_delete_file( $wp_svg_support_result['file'] );
	WP_CLI::warning( 'FAIL: invalid SVG was accepted.' );
	++$wp_svg_support_failures;
}

// 3. A logged-out user (no upload_files) must not be allowed SVG uploads.
wp_set_current_user( 0 );
if ( isset( get_allowed_mime_types()['svg'] ) ) {
	WP_CLI::warning( 'FAIL: svg is an allowed type for a user without upload_files.' );
	++$wp_svg_support_failures;
} else {
	WP_CLI::log( 'PASS: svg is not an allowed type without upload_files.' );
}

if ( $wp_svg_support_failures ) {
	WP_CLI::error( "{$wp_svg_support_failures} check(s) failed." );
}
WP_CLI::success( 'All SVG upload checks passed.' );
