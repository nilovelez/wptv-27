# Meta review route regression checkpoint

## Scope and result

Owner-authorized local regression after `db2a3dc`, `3dfe772` and `817d457`, following the review of [WordPress/wordpress.org#1186](https://github.com/WordPress/wordpress.org/pull/1186). Production theme code is unchanged from `817d457`; local test instrumentation was adapted in `89c2a38`.

**Result: PASS with four existing expected API body differences.**

```text
OK: meta-review-wptv27 matches meta-review-wptv2 (4 expected difference(s)).
```

- 145 URLs captured under each theme against the same imported dataset and regenerated inventory.
- 141 rows fully identical; four API rows differ only in their body hashes and are covered by the existing allowances.
- All recorded query, status, redirect, content-type, canonical, title and pagination fields match.
- Both runs: 104 HTTP 200, 34 HTTP 404, five HTTP 301 and two HTTP 302 responses. Matching local 404s do not establish production endpoint availability.
- Both runs contain 140 query headers. The five native REST/core-oEmbed responses lack the header in both themes, as at the previous checkpoint.
- No PHP notices in either valid run.
- `compare.sh` exits 0. Expected differences and response normalization were not changed.

## Data and fixture preparation

The owner reported a post resynchronization after the previous checkpoint. Therefore the URL generator ran under `wptv2` and produced a fresh 145-URL inventory; 14 route rows and the generation comment changed. Both captures use that same inventory. Older inventories/captures remain in Git history and older result directories were not overwritten.

With owner permission, only the content of post 141567 was restored from revision 143420 to a VideoPress block, retaining GUID `8MoPO8Eo`. The scratch content passed live-editor validation (1/1). Other post fields were preserved except normal modification timestamps; the original WordPress post GUID was explicitly preserved. A pre-change backup is outside theme Git in `wp-content/wptv-work/block-fixture-141567/`. No bulk content conversion was performed.

The regenerated inventory chooses the newer shortcode video 142018 (`SL1VrVwt`) for its GUID endpoint rows. It includes restored block post 141567 as the video with slides and in feed/API data. Therefore the new bounded block-GUID lookup was additionally checked directly under `wptv-27`:

- Shared GUID resolver returns post 141567 for `8MoPO8Eo`.
- `/?guid=8MoPO8Eo` returns HTTP 302 to post 141567's permalink.
- `/unisubs/?guid=8MoPO8Eo` returns HTTP 200 JSON identifying post 141567 and that GUID.

These supplementary requests use the same explicit local helper header. They are not a claim that the legacy theme supports block-only GUID lookup. The known unisubs response permalink issue (B2) remains unchanged.

## Instrumentation issue and correction

The initial attempts were discarded: Studio now loads its runtime from the current user's `.studio/mu-plugins/<hash>/`, while the helper only recognized the older temporary directory. Its safety guard correctly remained inactive, so those attempts lacked query headers and were not valid route comparisons. One initial attempt also hit the command timeout.

`89c2a38` recognizes both runtime locations without removing the local-environment, loaded-admin-API or explicit-header requirements. Four isolated negative checks passed: outside Studio, the helper stays inactive in local, staging, production and WordPress.com environments even with the request header. In the real local runtime, a request with the header exposes query data; ordinary browsing does not. The installed local helper matches the tracked source. The Studio-managed loader was not edited.

After correction, **both captures were repeated completely**, with rewrite rules explicitly flushed after each theme switch. The invalid attempts were replaced, not used in the comparison. Never sync either local MU-plugin to production.

## Remaining API differences

The four expected labels remain:

- `api: videos.json`
- `api: videos.json posts_per_page`
- `api: videos.json by event`
- `api: videos.json page 2`

Payload review found category/year/location partition differences from slug-based roots versus the legacy production IDs. The main videos response additionally has the already documented block-support difference for 141567: legacy `mp4: []` versus `low`, `med`, `high` keys set to false by the no-media local doubles. Non-video top-level response data is identical; no other classes of payload differences were found. These body-hash allowances predate this run and were not extended.

The fixture using `posts_per_page=3` remains below the approved cap. This suite does not independently exercise the cap at 100, authenticated flush confirmation, protected-video access or successful VideoPress processing; see the review's focused checks and their environment limits.

## Previous feed warning

The four poster-related feed differences from the imported-editor checkpoint do not occur in the current pair. All four current bodies include `8MoPO8Eo`, but none has a `posterUrl` parameter. Post 141567 currently has no featured image (`thumbnail ID 0`), and its featured-poster resolver returns no URL.

This is a different current-data result, **not acceptance of the old differences or proof that the poster behavior changed**. Historical evidence remains intact; no waiver was added or removed.

## Evidence and limits

- `tests/routes/urls.txt`
- `tests/routes/results/meta-review-wptv2/`
- `tests/routes/results/meta-review-wptv27/`
- Comparison: `bash tests/routes/compare.sh meta-review-wptv2 meta-review-wptv27`

`wptv-27` is active after the run. No deployment, synchronization, PR export update or production validation was performed. The helper is development-only and excluded from the theme export. This result reduces local regression risk; it does not establish the Release 1 baseline, which still requires controlled production validation and observation.
