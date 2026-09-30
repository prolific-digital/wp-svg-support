# Changelog

All notable changes to the "WP SVG" plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2026-09-29

### Security
- Sanitize every uploaded SVG with `enshrined/svg-sanitize` (shipped in `vendor/`) on `wp_handle_upload_prefilter` and `wp_handle_sideload_prefilter`. Scripts, `on*` event handlers, `javascript:` links and remote references are removed; an upload that cannot be sanitized (or a gzip-compressed SVG) is rejected. SVGs uploaded with earlier versions were not sanitized and should be reviewed.
- Only users with the `upload_files` capability can upload SVGs. The capability is filterable with `wp_svg_support_upload_capability`.
- A `.svg` file is accepted only when its content is SVG markup (`wp_check_filetype_and_ext`), so WordPress 6.5+ accepts sanitized SVGs without trusting the extension alone.

### Changed
- Adopted the Prolific plugin standard. Updates now come from the Prolific Digital license server (`api.prolificdigital.io`). Set `PROLIFIC_LICENSE_KEY_WP_SVG_SUPPORT` in `wp-config.php` (or use the `prolific_license_key` filter) to receive updates.
- The main file is renamed from `index.php` to `wp-svg-support.php`. Reactivate the plugin once after updating.
- License changed from GPL-3.0 to GPL-2.0-or-later.
- Requires WordPress 6.5+ and PHP 8.4+.

### Added
- `WP_SVG_SUPPORT_VERSION` constant and translation loading from `languages/`.
- `readme.txt`, coding-standards config and CI/release workflows.

## [1.0.0] - 2023-07-30

### Added

- Initial release of the plugin.
- Implemented support for uploading SVG files to the media library.
- Clean and well-documented code using Object-Oriented Programming (OOP) principles.
- Utilized the `upload_mimes` filter to enable SVG files with the 'image/svg+xml' MIME type.
- Enhanced website creativity by incorporating scalable vector graphics into design elements.

[Unreleased]: https://github.com/prolific-digital/wp-svg-support/compare/v1.1.0...HEAD
[1.1.0]: https://github.com/prolific-digital/wp-svg-support/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/prolific-digital/wp-svg-support/releases/tag/v1.0.0
