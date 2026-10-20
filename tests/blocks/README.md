# Block checks (local development only)

## WordCampTV (local 0.4.1)

43 focused PHP checks and 75 browser checks cover the owner-approved replacement. Run only on the local development site. The PHP suite writes/rebuilds the two disposable theme transients, never content/metadata/terms; the optional flag exports two temporary HTML/JSON browser fixtures. The populated recent-section preview is injected into one browser response only, not saved or cached as public recording data. See [the technical contract](../../docs/wordcamptv.md).

```sh
wp eval-file wp-content/themes/wptv-27/tests/blocks/wordcamps.php prepare-browser
node wp-content/themes/wptv-27/tests/blocks/wordcamps-browser.cjs
rm wp-content/wptv-wordcamps-expected.json wp-content/wptv-wordcamp-preview.html
```

Set `WPTV_PLAYWRIGHT_PATH` to the existing Studio Playwright module if normal Node resolution cannot find it; do not install another dependency. `WPTV_TEST_URL` optionally overrides localhost:8882. Browser checks cover the first/second category pages at 320/782/1440px, the native one-large/five-small spotlight without the 22-card landing grid, ordered first-six/later-page main-query IDs, the Latest WordCamps label, directory membership/year order, responsive columns, feed exclusion and existing card behavior with a real-event preview. The 48 existing archive checks also pass unchanged. No full route regression was repeated and its four known feed differences remain open.

## Existing suites

Run from the site root through WP-CLI with `wptv-27` active:

```sh
wp eval-file wp-content/themes/wptv-27/tests/blocks/front-page.php
wp eval-file wp-content/themes/wptv-27/tests/blocks/category-cards.php
wp eval-file wp-content/themes/wptv-27/tests/blocks/single.php
wp eval-file wp-content/themes/wptv-27/tests/blocks/listings.php
wp eval-file wp-content/themes/wptv-27/tests/blocks/archives.php
wp eval-file wp-content/themes/wptv-27/tests/blocks/video-integrations.php --skip-wordpress
wp eval-file wp-content/themes/wptv-27/tests/blocks/single-multisite.php --skip-wordpress
wp eval-file wp-content/themes/wptv-27/tests/blocks/video-images.php
wp eval-file wp-content/themes/wptv-27/tests/blocks/video-images-multisite.php --skip-wordpress
```

- `front-page.php`: 11 checks of scoped slug constraints and independent/inherited query settings.
- `category-cards.php`: eight read-only checks of the homepage-only `year`/`location` exclusions, remaining card order, untouched ordinary/unmarked category queries and filter cleanup after a renderer exception. IDs resolve from the current site's slugs, without changing stored terms.
- `listings.php`: 19 read-only checks for the general archive/page/search/404 files, registered hidden patterns, translated content, inherited queries and native pagination/empty states. Dedicated category/event coverage is in `archives.php`; the owner-approved automatic WordCampTV replacement has its own `wordcamps.php` coverage.
- `archives.php`: 48 read-only category/event checks. Covers hidden pattern/core markup, inherited-only queries, ordered unique 1 + 5 + 16 partitioning, unchanged original query/loop/totals, global-post restoration, zero/one/six/seven-result boundaries, later pages, feed exclusion, independent queries and existing short-circuits. Reproduces core's by-reference global posts array; a shallow clone alone does not isolate that array. Player rendering and remote thumbnails are suppressed in-process to avoid network requests. Tests complement the 14 targeted HTTP samples and desktop/mobile review; they do not prove real production descriptions/player content or complete the WordCampTV landing page.
- `video-integrations.php`: 22 process-isolated simulations of first-shortcode compatibility, nested/mixed block fallback, API files/subtitles/HLS, social tags, download and JSON-LD helper data, missing details, legacy-first GUID lookup and batched candidate verification. Uses in-memory posts/cache and temporary query filters, not database writes. Requires `--skip-wordpress`; never run on production. HTTP endpoint responses are covered separately by the milestone route suite.
- `single.php`: 18 checks of registered blocks, pattern structure, producer variants, complete excerpts/slides and missing video/subtitle data. Uses temporary in-process filters, never database writes.
- `single-multisite.php`: 12 checks of simulated MP4/OGG/original downloads, HLS exclusion, TTML labels/tracks and the legacy subtitle URL. Requires `--skip-wordpress` to define test doubles before WordPress and the local MU stubs load. It then bootstraps the local site. Never run this script on WordPress.com or any production site.

- `video-images.php`: 27 checks of shortcode/block GUID extraction, nested and mixed content, malformed attributes, standalone posters, privacy, missing data, the route-test override, featured images and placeholders. Uses virtual posts and intercepted plugin transients, with HTTP blocked. No manual block fixture is imported into WordPress.
- `video-images-multisite.php`: 8 checks of simulated production thumbnail priority, block/shortcode GUIDs and safe plugin fallback. Requires `--skip-wordpress`; never run on production. HTTP is blocked and no media is written.
- A separate missing-dependency smoke check with `--skip-plugins` verifies that absent production and standalone APIs return no image without errors.

## Visual browser checks (0.4.0)

`design-browser.cjs` uses an **existing** Playwright installation; it adds no theme dependency or build step. Run it with Node from the site root (not through WP-CLI):

```sh
WPTV_PLAYWRIGHT_PATH=/path/to/existing/playwright node wp-content/themes/wptv-27/tests/blocks/design-browser.cjs
```

Omit `WPTV_PLAYWRIGHT_PATH` if `playwright` is already resolvable by Node. `WPTV_TEST_URL` optionally overrides the local `http://localhost:8882` default. Do not run it against production as a substitute for an agreed remote verification plan.

The current local run passes **196 assertions**: ten homepage widths (320, 375, 600, 782, 890, 960, 1024, 1040, 1080, 1440px), preserved tablet/desktop hero ratios, unbroken site name, font availability, matching button padding and outline hover, decorative card thumbnails, confirmed events destination, category-card exclusions, header/footer font parity, full-width single titles, menu attributes, native focus/closing and search. Fifteen paths at 375px and 1440px cover three shortcode/block singles, homepage page two, category/event first and second pages, tag/speaker, matching/empty search, About and 404. They check statuses, one h1, overflow, later-page spotlight exclusion and single-player dimensions. Core focus effects are awaited before asserting keyboard behavior. These checks do not certify WCAG conformance, actual playback, platform APIs or production content.

At the earlier owner-refinement checkpoint, 165 original PHP checks plus eight category-card checks passed (173 total). The later video-card change intentionally updates the archive caption assertion from category-only event terms to speaker captions without events; its other query/partition assertions remain intact. That change reran only the 48 archive checks, focused speaker rendering and the card browser checks below, not the entire suites.

### Focused video-card checks

```sh
WPTV_PLAYWRIGHT_PATH='/path/to/existing/playwright' node wp-content/themes/wptv-27/tests/blocks/cards-browser.cjs
```

`cards-browser.cjs` passes 88 targeted checks on home, category/event archives and category page two at 375px/1440px. Covers field order, undated Featured, one native title link, unlinked images/speakers/dates, colons, pointer and keyboard activation, modified/middle clicks without autoscroll, text selection, controls, cancellation, caption/player separation and native navigation with JavaScript disabled. No database writes or dependency installation. The broader 196-check suite above was not rerun for this scoped change. The full `design-0.4.0` route run covers 145 fixed URLs: zero recorded field differences from `phase-1c-integrations`, no PHP notices, and the same four unaccepted feed hashes plus four expected API hashes against the untouched baseline. Regression is not green.

These scripts do not edit posts, terms or options. The multisite simulations are not evidence of production integration: direct uploads, real download files and subtitle tracks still need verification on the production multisite.

Validate pattern markup separately: dump the registered pattern's `content` to a scratch HTML file, then use Studio's `validate_blocks`. Theme templates/parts must also pass validation. These checks complement, not replace, the milestone-only [route regression test](../routes/README.md).
