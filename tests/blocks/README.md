# Block checks (local development only)

Run from the site root through WP-CLI with `wptv-27` active:

```sh
wp eval-file wp-content/themes/wptv-27/tests/blocks/front-page.php
wp eval-file wp-content/themes/wptv-27/tests/blocks/single.php
wp eval-file wp-content/themes/wptv-27/tests/blocks/single-multisite.php --skip-wordpress
wp eval-file wp-content/themes/wptv-27/tests/blocks/video-images.php
wp eval-file wp-content/themes/wptv-27/tests/blocks/video-images-multisite.php --skip-wordpress
```

- `front-page.php`: 11 checks of scoped slug constraints and independent/inherited query settings.
- `single.php`: 18 checks of registered blocks, pattern structure, producer variants, complete excerpts/slides and missing video/subtitle data. Uses temporary in-process filters, never database writes.
- `single-multisite.php`: 12 checks of simulated MP4/OGG/original downloads, HLS exclusion, TTML labels/tracks and the legacy subtitle URL. Requires `--skip-wordpress` to define test doubles before WordPress and the local MU stubs load. It then bootstraps the local site. Never run this script on WordPress.com or any production site.

- `video-images.php`: 27 checks of shortcode/block GUID extraction, nested and mixed content, malformed attributes, standalone posters, privacy, missing data, the route-test override, featured images and placeholders. Uses virtual posts and intercepted plugin transients, with HTTP blocked. No manual block fixture is imported into WordPress.
- `video-images-multisite.php`: 8 checks of simulated production thumbnail priority, block/shortcode GUIDs and safe plugin fallback. Requires `--skip-wordpress`; never run on production. HTTP is blocked and no media is written.
- A separate missing-dependency smoke check with `--skip-plugins` verifies that absent production and standalone APIs return no image without errors.

These scripts do not edit posts, terms or options. The multisite simulations are not evidence of production integration: direct uploads, real download files and subtitle tracks still need verification on the production multisite.

Validate pattern markup separately: dump the registered pattern's `content` to a scratch HTML file, then use Studio's `validate_blocks`. Theme templates/parts must also pass validation. These checks complement, not replace, the milestone-only [route regression test](../routes/README.md).
