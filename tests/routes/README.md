# Route regression test

Proves that `wptv-27` serves every route exactly like `wptv2`. See [`docs/routes.md`](../../docs/routes.md) for the route contract.

The test is theme-agnostic. It never parses theme markup to count posts. Instead, a local-only mu-plugin exposes the main query in an `X-WPTV-Route` response header.

## Files

| File | Purpose |
|---|---|
| `mu-plugin/wptv-route-test.php` | Local-only helper: stubs WordPress.com-only VideoPress functions and adds the `X-WPTV-Route` header. **Never deploy it.** It also bails out on WordPress.com as a safety net. |
| `generate-urls.php` | Builds `urls.txt` from real site data (WP-CLI `eval-file`). |
| `urls.txt` | The fixed URL list (committed). |
| `run.sh` | Requests every URL and writes `results/<run-name>/`. |
| `compare.sh` | Compares two runs and lists every difference. |
| `results/baseline-wptv2/` | The reference run, captured with `wptv2` active (committed). |

## What is recorded per URL

- HTTP status and `Location` header. Redirects are not followed.
- Content type.
- `X-WPTV-Route`: `404`, `found`, `count`, `pages`, `per_page`, `paged` and `queried` object of the main query.
- For HTML documents: `<link rel="canonical">`, `<title>`, `rel="prev"` and `rel="next"`.
- For everything else (JSON, XML, feeds, plain-text errors): the body, normalized (relative URLs, random secrets and nonces masked), stored in `bodies/`, plus its SHA-1. HTML is detected by content, not by `Content-Type`, because some legacy endpoints send JSON as `text/html`.
- Response times go to `timings.tsv` and are never compared.

## Setup

1. Copy the helper into the site. It must be active for **every** run, so all runs share the same stubs:

   ```bash
   cp wp-content/themes/wptv-27/tests/routes/mu-plugin/wptv-route-test.php wp-content/mu-plugins/
   ```

2. All commands below run from the WordPress root. With WordPress Studio, run WP-CLI through Studio (`studio wp …`) or the Studio AI agent.

## Capturing the baseline

```bash
wp theme activate wptv2
wp rewrite flush
wp eval-file wp-content/themes/wptv-27/tests/routes/generate-urls.php   # only when the URL list must change
bash wp-content/themes/wptv-27/tests/routes/run.sh http://localhost:8882 baseline-wptv2
```

Then switch back to the theme you were using.

## Testing wptv-27

```bash
wp theme activate wptv-27
wp rewrite flush
bash wp-content/themes/wptv-27/tests/routes/run.sh http://localhost:8882 wptv-27
bash wp-content/themes/wptv-27/tests/routes/compare.sh baseline-wptv2 wptv-27
```

`compare.sh` exits with `0` when everything matches, and with `1` (plus a list of differences) otherwise.

## Rules

- **Same data for every run.** The baseline is only valid for the database it was captured on. If the content changes (new posts, new import), regenerate `urls.txt` and capture a new baseline with `wptv2` before comparing.
- **Always flush rewrite rules** after switching themes, because the taxonomies and endpoints are registered by the theme.
- Intentional differences (for example, a route deliberately retired in Phase 2) must be recorded in [`docs/decisions.md`](../../docs/decisions.md).

## Known limitations

- WordPress.com-only VideoPress helpers are stubbed. `video_get_info_by_guid()` returns fake but deterministic details derived from the GUID, so endpoint, JSON-LD and Open Graph code paths run. File URLs, thumbnails and subtitles return no data, so download and subtitle output is not covered. Check real video data on the WordPress.com test site.
- `wptv_post_views` is not updated locally, so "popular" queries reflect imported values only.
- `/subtitle/` does not exist in the local data.
- The local site is slow (several seconds per request). A full run takes a few minutes.
