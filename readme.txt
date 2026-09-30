=== WP SVG ===
Contributors: prolificdigital
Tags: svg, media, upload, mime
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 8.4
Stable tag: 1.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds support for uploading SVG files to the Media Library.

== Description ==

Adds `image/svg+xml` to WordPress's allowed upload types so SVG files can be uploaded to the Media Library like any other image. There are no settings and the plugin stores no data.

SVG files can contain scripts, and this plugin does not sanitize them. Only let trusted users upload SVGs.

== Installation ==

1. Upload the `wp-svg-support` folder to `/wp-content/plugins/`, or install the release zip from **Plugins > Add New > Upload Plugin**.
2. Activate the plugin.
3. To receive automatic updates, add your license key to `wp-config.php`:
   `define( 'PROLIFIC_LICENSE_KEY_WP_SVG_SUPPORT', 'your-key' );`

== Changelog ==

= 1.1.0 =
* Updates now come from the Prolific Digital license server. Define `PROLIFIC_LICENSE_KEY_WP_SVG_SUPPORT` to receive them.
* Main file renamed to `wp-svg-support.php`; reactivate the plugin once after updating.
* License changed from GPL-3.0 to GPL-2.0-or-later.
* Requires WordPress 6.5+ and PHP 8.4+.

= 1.0.0 =
* Initial release.
