# Imported-data editor checkpoint: route regression

## Scope and setup

- Captured on 2026-10-22, with theme version 0.4.1 and code through `e68ce32` (including editor follow-ups `d575f56`, `2aba8b5`, `e05ee3e` and `ea7c502`). No version bump, push or deployment accompanies this test.
- The production WXR import and subsequent category-parent corrections make the historical database baseline unsuitable for a current-data comparison.
- Regenerated `tests/routes/urls.txt` using the existing generator under `wptv2`: **145 URLs**. This refresh changes 80 inventory lines; it does not modify the generator or route contract. Historical inventories remain in Git, and historical result directories are untouched.
- Captured `imported-editor-wptv2`, switched to `wptv-27`, flushed rewrites after both activations, then captured `imported-editor-wptv27`. Both captures use the same regenerated inventory and the current imported dataset, with no content or taxonomy edits by the test.
- The existing guarded MU-plugin and explicit route-test request header were used for both captures. VideoPress test doubles and image suppression remain unchanged. Real uploads, encoding, poster access and multisite behavior are outside this test.
- Left `wptv-27` active. No expected-difference allowance was added or changed.

## Result

**Not an exact-match pass:** `compare.sh` exits 1, with **four unaccepted feed-body hashes**, plus four API-body hashes classified as expected by the existing allowance file.

| Measurement | Result |
|---|---|
| Captured URLs | 145 per theme; identical labels and paths |
| Fully identical rows | 137 |
| Rows differing only in an already-allowed API body hash | 4 |
| Rows differing only in an unaccepted feed body hash | 4 |
| HTTP statuses | Identical: 104 responses with 200, 34 with 404, 5 with 301, 2 with 302 |
| Redirect locations, content types, recorded query data, canonical URLs, titles and pagination links | Identical for every inventory row |
| PHP notices | None in either run |

Five native REST/oEmbed endpoint rows have no main-query header in either capture: core oEmbed, speakers, language, event and posts filtered by event. This is symmetric, not a missing-header problem introduced by the new theme. All remaining 140 rows have the query header in both captures.

The 34 matching 404 responses include deliberately invalid and past-last-page routes. Matching responses do not prove that unavailable production-only functionality exists locally.

## Four feed differences: exact cause checked

- `category: wordcamptv feed`
- `language: feed`
- `feed: main`
- `feed: rss2 query var`

Each current feed adds exactly one `posterUrl` parameter to the VideoPress iframe for GUID `8MoPO8Eo`, using its featured image. Removing only that parameter in a read-only diagnostic makes each feed body identical to its new `wptv2` reference. No files were rewritten or hashes normalized away for comparison.

This is the owner-approved featured-image-as-poster behavior, now visible in feed rendering. It is not a query or route change. The differences remain unaccepted in the test: an intentional feature does not automatically authorize changing test allowances.

These are not the same four feed labels or causes as the historical pre-import run. In particular, the new single-video feed matches; the WordCampTV category feed now differs. The earlier timestamp/renderer differences must not be reported as the cause of this run.

## Four expected API hashes: payloads inspected

The existing allowance file covers:

- `api: videos.json`
- `api: videos.json posts_per_page`
- `api: videos.json by event`
- `api: videos.json page 2`

All four retain identical query totals and the same ordered video permalinks. Differences in `category`, `year` and `location` are the existing local-ID issue: the legacy API hardcodes production structural-category IDs, while the new theme resolves them by slug.

The first two responses also differ in the `video.mp4` field for two block-based videos: the legacy shortcode-only path returns an empty array, while block support returns the quality keys `low`, `med` and `high`, each false under the local test doubles. Their original-file URLs are unchanged. This additional block-support difference is recorded explicitly rather than justified solely by the allowance file's year/location explanation. No other top-level video fields differ in the inspected payloads.

## Evidence and limits

- Captures: `tests/routes/results/imported-editor-wptv2/` and `tests/routes/results/imported-editor-wptv27/`, including result rows, timings and normalized non-HTML bodies.
- Compare: `bash tests/routes/compare.sh imported-editor-wptv2 imported-editor-wptv27`.
- Historical `baseline-wptv2`, `phase-1c-integrations` and `design-0.4.0` captures are preserved, not overwritten.
- This is a route-contract comparison, not a screenshot, accessibility, upload or production-readiness certification. The editor suite passed separately after the final editor changes; it was not rerun as part of these two route captures.
- Subtitle player parameters, the subtitle submission form and incoming-subtitle management remain deferred to the next delivery.
