# Proof of concept review: `wptv3`

`wptv3` is an earlier attempt at a block theme for WordPress.tv, installed locally at `wp-content/themes/wptv3`. It is a **design and structure reference only**. **Do not edit it**, and do not copy its code without reviewing it against [legacy-wptv2.md](legacy-wptv2.md) and [routes.md](routes.md).

## Structure

- Block theme (`theme.json` v3, `templates/`, `parts/`, `patterns/`). Text domain `wordpresstv3`.
- `functions.php` includes `includes/wptv3.php`, which is largely the `wptv2` code moved into the theme (taxonomies, queries, head output, endpoints, uploads).
- Legacy presentation helpers turned into shortcodes (`includes/wptv3-shortcodes.php`) that are used inside templates: `[wptv_video_speakers]`, `[wptv_video_details_single]`, `[wptv_breadcrumbs]`, and others.
- `post_thumbnail_html` filter for VideoPress thumbnails (`includes/wptv3-video-thumbnails.php`). The idea is reusable.

## Worth reusing (as reference)

- **Visual direction:** palette (`wp-blue #3858e9`, `wp-coal #1e1e1e`, `wp-orange #d54e21`, grays), Inter + EB Garamond (local `woff2` files), 960px content and 1280px wide sizes, fluid spacing scale.
- **Layout ideas:** 4-column Query Loop grids with 16:9 featured images, dark "featured" bands on the home page, single video page with an excerpt column and a "Video details" column, CC BY-SA license image block.
- **Thumbnail approach:** filtering `post_thumbnail_html` so the core Post Featured Image block shows VideoPress thumbnails.
- Jetpack Like and Sharing buttons blocks on single videos.

## Do not carry over

| Issue | Where |
|---|---|
| Hardcoded IDs in block queries: term `2017` (tag `featured`), term `1369` (category `wordcamptv`), navigation `ref:133516` | `templates/home.html`, `parts/header.html` |
| Shortcodes in templates instead of blocks | `templates/single.html` and others |
| References to things that do not exist: palette slug `base-2`, pattern `twentytwentyfour/hidden-post-meta` | `templates/home.html`, `parts/header.html`, `parts/post-meta.html` |
| Text domain changed to `wordpresstv3` (existing `wptv` translations are lost) | everywhere |
| Hardcoded `perPage` on inherited queries (ignored, misleading) | `templates/archive.html` |
| Legacy code copied as is, including dead code and bad practices | `includes/` |

## Route regression results (vs `wptv2` baseline)

Run on the local site: **45 differences**. See [routes.md](routes.md) and `tests/routes/`.

| Problem | Routes affected |
|---|---|
| `producer` and `flavor` taxonomies are not registered, so their archives return 404 and their feeds are empty | `/producer/*`, `/flavor/*` |
| Unknown terms trigger a PHP warning (`Attempt to read property "taxonomy" on null`, `includes/wptv3.php` line 324). The warning is printed before the headers, so the response is **200 instead of 404** and no other headers are sent | `/event/<unknown>/`, `/speakers/<unknown>/`, `/language/<unknown>/`, `/producer-username/<unknown>/` |
| `/api/videos.json?event=<slug>` ignores the event filter and returns the front page query | `/api/videos.*` |
| `/api/` payloads differ from `wptv2` | `/api/videos.*`, `/api/events.json` |

## Lessons for wptv-27

- Register **all six** taxonomies with the exact `wptv2` arguments.
- Never read properties of `get_queried_object()` without checking it (it is `null` on 404s).
- Local sites display PHP warnings, and a warning printed before the headers silently changes the status code. Treat every notice and warning as a bug.
- Use slugs, never IDs, in block queries and code.
