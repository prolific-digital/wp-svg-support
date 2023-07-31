<?php
/*
Plugin Name: WP SVG
Description: Adds support for uploading SVG files to the media library.
Version: 1.0.0
Author: Prolific Digital
Author URI: https://www.prolificdigital.com
License: GPLv3 or later
*/

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}

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
