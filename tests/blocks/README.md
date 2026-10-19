# Block checks (local development only)

Run from the site root through WP-CLI with `wptv-27` active:

```sh
wp eval-file wp-content/themes/wptv-27/tests/blocks/front-page.php
wp eval-file wp-content/themes/wptv-27/tests/blocks/single.php
wp eval-file wp-content/themes/wptv-27/tests/blocks/single-multisite.php --skip-wordpress
```

- `front-page.php`: 11 checks of scoped slug constraints and independent/inherited query settings.
- `single.php`: 18 checks of registered blocks, pattern structure, producer variants, complete excerpts/slides and missing video/subtitle data. Uses temporary in-process filters, never database writes.
- `single-multisite.php`: 12 checks of simulated MP4/OGG/original downloads, HLS exclusion, TTML labels/tracks and the legacy subtitle URL. Requires `--skip-wordpress` to define test doubles before WordPress and the local MU stubs load. It then bootstraps the local site. Never run this script on WordPress.com or any production site.

These scripts do not edit posts, terms or options. The multisite simulations are not evidence of production integration: direct uploads, real download files and subtitle tracks still need verification on the production multisite.

Validate pattern markup separately: dump the registered pattern's `content` to a scratch HTML file, then use Studio's `validate_blocks`. Theme templates/parts must also pass validation. These checks complement, not replace, the milestone-only [route regression test](../routes/README.md).
