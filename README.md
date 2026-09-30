# WP SVG WordPress Plugin

The WP SVG WordPress plugin empowers your website by adding seamless support for uploading SVG files to the media library. Developed by [Prolific Digital](https://www.prolificdigital.com/), this plugin ensures you can incorporate stunning scalable vector graphics into your creative projects effortlessly.

## Features

- Adds support for uploading SVG files to the media library.
- Clean, well-documented, and object-oriented code for optimal performance.

## Installation

1. Download `wp-svg-support.zip` from the latest [GitHub release](https://github.com/prolific-digital/wp-svg-support/releases).
2. Upload the plugin to your WordPress site's plugins directory.
3. Activate the WP SVG plugin through the 'Plugins' menu in WordPress.

> Requires WordPress 6.5+ and PHP 8.4+.

## Usage

Once the WP SVG plugin is activated, you can start uploading SVG files just like any other media asset. Go to your WordPress media library, click the "Add New" button, and select your SVG file to upload. Now you have the freedom to utilize vector graphics alongside your other media assets.

## FAQ

### Can I use WP SVG alongside other media formats?

Absolutely! WP SVG seamlessly integrates with your existing media library, allowing you to upload and manage SVG files alongside other supported formats like JPEG, PNG, and GIF.

### Is there any risk in allowing SVG file uploads?

Yes. SVG files can contain scripts, and this plugin does not sanitize uploads; it only adds `image/svg+xml` to the allowed upload types. Only let trusted users upload SVGs, and only upload files from sources you trust.

### Does WP SVG work with the latest WordPress version?

Yes, WP SVG is fully compatible with the latest WordPress version, and we strive to keep the plugin up-to-date with ongoing WordPress releases.

### Can I customize the SVG file display in the media library?

Yes, WP SVG uses the same media library display as other supported formats, providing a seamless user experience without any additional configuration required.

## Updates

Updates are served by the Prolific Digital license server. Add the site's license key to `wp-config.php`:

```php
define( 'PROLIFIC_LICENSE_KEY_WP_SVG_SUPPORT', 'your-key' );
```

You can also supply the key with the `prolific_license_key` filter, which receives `( $key, 'wp-svg-support' )`.

## Development

The dev site is the DDEV sandbox at `https://wp-sandbox.ddev.site`. Run commands in the container:

```sh
ddev exec -d /var/www/html/wp-content/plugins/wp-svg-support composer install
ddev exec -d /var/www/html/wp-content/plugins/wp-svg-support composer lint    # php -l on every file
ddev exec -d /var/www/html/wp-content/plugins/wp-svg-support composer phpcs   # WordPress-Extra + PHPCompatibilityWP
```

`vendor/` is not committed. A git checkout needs `composer install` before the updater loads. The plugin itself still runs without it.

## Releasing

1. Bump the version in the plugin header, `WP_SVG_SUPPORT_VERSION` and `readme.txt` `Stable tag`.
2. Add a `## [x.y.z] - YYYY-MM-DD` section to `CHANGELOG.md` and a `= x.y.z =` entry to `readme.txt`.
3. Merge to `main`, then push a `vx.y.z` tag. The release workflow builds `wp-svg-support.zip` (with `composer install --no-dev`, honoring `.distignore`) and attaches it to a GitHub Release.

CI and release logic live in the shared workflows in [`prolific-digital/.github`](https://github.com/prolific-digital/.github).
## Support and Feedback

For support, bug reporting, or feature requests, please reach out to us through [GitHub](https://github.com/prolific-digital/wp-svg-support/issues).

## Contributing

We welcome contributions to improve this plugin. To contribute:

1. Fork the project.
2. Make your feature addition or bug fix.
3. Send us a pull request with a brief description of your changes.

> Before sending a pull request for a feature addition, please discuss it with us first. We want to ensure that changes align with the project's direction and goals.

## License

WP SVG is licensed under GPL-2.0-or-later. See [LICENSE](LICENSE).

## Versioning

We use Semantic Versioning for versioning. For the versions available, see the [tags on this repository](https://github.com/prolific-digital/wp-svg-support/tags).

---

_Proudly crafted by Prolific Digital_
