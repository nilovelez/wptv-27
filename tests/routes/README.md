# Route regression test

Proves that `wptv-27` serves every route exactly like `wptv2`. See [`docs/routes.md`](../../docs/routes.md) for the route contract.

The test is theme-agnostic. It never parses theme markup to count posts. Instead, a local-only mu-plugin exposes the main query in an `X-WPTV-Route` response header.

## Files

| File | Purpose |
|---|---|
| `mu-plugin/wptv-route-test.php` | Local-only helper: stubs WordPress.com-only VideoPress functions and adds the `X-WPTV-Route` header. Runs only in a loaded Studio runtime with the `local` environment type; inert elsewhere, including if copied by a full sync. Still exclude it from production release packages. |
| `generate-urls.php` | Builds `urls.txt` from real site data (WP-CLI `eval-file`). |
| `urls.txt` | The fixed URL list (committed). |
| `run.sh` | Requests every URL and writes `results/<run-name>/`. |
| `compare.sh` | Compares two runs and lists every difference. |
| `expected-differences.tsv` | Known, intended differences (label, column, reason), reported as EXPECTED. |
| `results/baseline-wptv2/` | The reference run, captured with `wptv2` active (committed). |

## What is recorded per URL

- HTTP status and `Location` header. Redirects are not followed.
- Content type.
- `X-WPTV-Route`: `404`, `found`, `count`, `pages`, `per_page`, `paged` and `queried` object of the main query.
- For HTML documents: `<link rel="canonical">`, `<title>`, `rel="prev"` and `rel="next"`.
- For everything else (JSON, XML, feeds, plain-text errors): the body, normalized (relative URLs, random secrets and nonces masked), stored in `bodies/`, plus its SHA-1. HTML is detected by content, not by `Content-Type`, so a wrong header never hides a body change.
- PHP notices: the mu-plugin turns `display_errors` off (as on production) and logs notices to `wp-content/wptv-route-test.log`. `run.sh` clears the log before a run and saves the distinct notices to `php-notices.txt`. They are not compared, but any notice from `wptv-27` is a bug.
- Response times go to `timings.tsv` and are never compared.

## Setup

1. Copy the helper into the site. It must be active for **every** run, so all runs share the same stubs:

   ```bash
   cp wp-content/themes/wptv-27/tests/routes/mu-plugin/wptv-route-test.php wp-content/mu-plugins/
   ```

2. All commands below run from the WordPress root. With WordPress Studio, run WP-CLI through Studio (`studio wp …`) or the Studio AI agent.

## Environment safety

The route helper checks both `wp_get_environment_type() === 'local'` and an already loaded Studio admin API file under the current machine's temporary `studio-mu-plugins-*` directory. A copied loader or a `local` setting alone is insufficient. It returns before changing logging, defining VideoPress test doubles or registering hooks outside Studio.

The site's Studio-managed `99-studio-loader.php` also has a local guard before its database/environment constants: its configured runtime directory must belong to this machine's temporary directory and contain the Studio admin API file. This local file is not part of the theme repository. **Studio may regenerate the loader; recheck this guard after a Studio restart/update before syncing MU-plugins.** The route helper's own guard does not depend on that modification and survives loader regeneration.

Isolated negative checks (direct PHP, not WP-CLI):

```bash
for environment in local staging production wpcom; do
    php tests/routes/check-environment-guard.php route "$environment"
    php -d sys_temp_dir=/non-studio-runtime tests/routes/check-environment-guard.php loader "$environment"
done
```

The loader checks expect the site's sibling `wp-content/mu-plugins/99-studio-loader.php`. Use a nonexistent, non-Studio temporary path for the simulated remote environment. On Windows, use `-d sys_temp_dir=C:/non-studio-runtime`.

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

`compare.sh` exits with `0` when everything matches, and with `1` (plus a list of differences) otherwise. Differences listed in `expected-differences.tsv` are printed as `EXPECTED` and do not make the run fail; each entry must be backed by a decision in `docs/decisions.md`.

## When to run

A full run is slow (several minutes) and needs a theme switch, so do not run it after every change.

- **Run it:** at the end of each sub-phase (1a, 1b, 1c) and before a deploy; and after changes to routing code: `functions.php` loading, `inc/taxonomies.php`, `inc/query.php`, `inc/redirects.php`, `inc/endpoints/`, `inc/head.php`, or anything hooked to `init`, `pre_get_posts`, `template_redirect`, `query_vars` or `document_title_parts`. Batch several such changes into one run.
- **Skip it:** for template, template-part, `theme.json`, CSS, pattern and block-rendering changes. For those, request the affected URLs once with `curl -sI` (status) or check the page in the browser, and watch `wp-content/wptv-route-test.log` for PHP notices.

## Rules

- **Same data for every run.** The baseline is only valid for the database it was captured on. If the content changes (new posts, new import), regenerate `urls.txt` and capture a new baseline with `wptv2` before comparing.
- **Always flush rewrite rules** after switching themes, because the taxonomies and endpoints are registered by the theme.
- Intentional differences (for example, a route deliberately retired in Phase 2) must be recorded in [`docs/decisions.md`](../../docs/decisions.md).

## Known limitations

- WordPress.com-only VideoPress helpers are stubbed. `video_get_info_by_guid()` returns fake but deterministic details derived from the GUID, so endpoint, JSON-LD and Open Graph code paths run. File URLs, thumbnails and subtitles return no data, so download and subtitle output is not covered. Check real video data on the WordPress.com test site.
- `wptv_post_views` is not updated locally, so "popular" queries reflect imported values only.
- `/subtitle/` does not exist in the local data.
- The local site is slow (several seconds per request). A full run takes a few minutes.
