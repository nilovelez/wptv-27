# Session handoff

## Current state

- Theme: `wptv-27`, version **0.3.4**, branch `trunk`. Leave it active locally. This handoff accompanies the version 0.3.4 Git checkpoint, following `02099d0`. The owner requested committing and publishing all pending theme changes to GitHub before design collaboration. Git publication is not a WordPress sync or production deployment; neither is part of this checkpoint.
- Phase 1a foundation is complete. Phase 1b homepage, single, general archive/page/search/404 and category/event title/spotlight layouts are implemented. The special WordCampTV featured/tag query and event-menu landing page remain pending; its category fallback is not complete landing-page parity. Other taxonomies use the general archive fallback.
- Phase 1c local theme-owned GUID consumers now support block fallback. Real multisite integrations and four residual feed hashes keep the phase open. No stored content, plugins, route definitions or page sizes were changed.
- The owner synchronized the site to the Atomic test environment and **confirmed that thumbnails work there**. This is owner-reported verification, not an independent remote audit or proof of multisite production compatibility.
- The owner accepted the current category/event structure for this version; broader restructuring is deferred. **Pause further functional work and address design next.** Claude Code is researching WordPress/WordPress.org design guidance and will update the design document and supply implementation instructions. Wait for that handoff before changing the current wptv3 tokens/layout. This is not a claim that official Design team guidance has already arrived.
- The site-root `DESIGN.md` lives outside this theme repository. Its pre-design contents are preserved verbatim in [design-baseline-0.3.4.md](design-baseline-0.3.4.md) as historical reference, not a second live source of instructions. Do not overwrite Claude Code's forthcoming design updates with that snapshot.
- The owner is reviewing the transition from Classic Editor to the block editor and will supply specific changes. No editor redesign or new field/moderation workflow has been approved or implemented yet.

## Git history (not deployment evidence)

- `cfc9aa0`: single-video pattern, guarded producer/download blocks and full description.
- `f141c72`: CSS-only 1:2 label/value metadata rows following wptv3.
- `c1dbb9c`: initial local MU-plugin environment guards.
- `501f508`: shortcode/block GUID extraction, real standalone VideoPress posters and opt-in route-test doubles; version 0.3.2.
- `02099d0`: previous documentation closeout (Atomic thumbnail confirmation and environment cleanup).
- Version 0.3.4 checkpoint: block fallback for endpoint/social/API/download consumers, published-post GUID lookup with parsed-block verification, general listing/page/search/404 templates, category/event spotlight rendering and templates, tests and documentation. See Git history for the checkpoint commit IDs. Keep the four-feed regression warning below; owner acceptance is not evidence of production readiness.

## Thumbnail behavior and verification

- Legacy shortcodes retain precedence and last-shortcode behavior. Otherwise the last valid nested `videopress/video` or earlier `jetpack/videopress` block supplies the GUID.
- Keep the production internal thumbnail API first; fall through to the callable standalone plugin poster resolver. Preserve plugin privacy/cache handling, existing featured images and the bundled fallback. No invented CDN paths, content rewrites or automatic featured-image assignments.
- Local post **126193** was manually converted by the owner to `videopress/video`, GUID `ob0EdJL3`. Post **133522** is the existing block-only fixture, GUID `HVQl3ZYz`.
- Last code verification: 27 GUID/image checks, 8 simulated multisite image checks, 11 homepage checks, 18 single checks and 12 simulated download/subtitle checks passed. A plugins-skipped smoke check covered absent APIs. All 12 real homepage poster URLs returned HTTP 200/image/jpeg. Atomic thumbnail success was subsequently confirmed by the owner.
- Built-in multisite serialization, actual production thumbnail APIs and other production-only integrations still require verification.

## Regression is still blocked

The latest full run, **`phase-1c-integrations`, after media cleanup**, covers 145 URLs and reports **4 unaccepted field differences across 4 URLs**, plus the same four expected API differences; no PHP notices. This is down from the previous 27 differences across 11 URLs.

- oEmbed JSON/XML/maxwidth, unisubs URL/GUID and both GUID redirects now match the original wptv2 baseline.
- Four feed body hashes still differ: single feed (lastBuildDate only), language feed, main feed and rss2 query-var feed (build dates, converted block player and paragraph/lazy renderer attributes).
- Expected API bodies were also inspected rather than blindly waived: block file data is restored and the deleted local MP4 reference is gone, alongside existing slug-based year/location differences. No baseline, fixed URL list or expected-difference allowances changed.
- Remaining theme-owned consumers were audited: Open Graph/API/downloads use their original first-shortcode selection with block fallback; JSON-LD already uses shared helpers. The theme MRSS filter reads attachments. Actual platform MRSS, production APIs and serialization remain unverified.

Do not describe this as a passing regression or production readiness. Keep route names, query parameters, redirects, the deferred unisubs permalink bug and pagination contracts unchanged. The full run preceded the general template additions; nine targeted HTTP samples after those markup-only changes retained statuses, query sizes and one h1 per view.

## Current verification

- Passing checks: 48 category/event checks plus all previous 117 (22 integration, 19 general templates, 27 GUID/images, 8 simulated production images, 12 simulated downloads/subtitles, 18 single and 11 homepage): **165 total**, rerun successfully. The earlier missing-production-API smoke check remains recorded; it was not rerun in the category/event step.
- New category/event templates, the hidden spotlight pattern and the modified grid pattern pass editor validation. Desktop/mobile screenshots and DOM checks cover both layouts, plus the second-page category grid. Removed the extra 20px core margin before the spotlight; existing tokens/header/footer remain unchanged.
- Fourteen targeted HTTP samples preserve the original baseline status/query contracts, ordered unique rendered counts and one h1 on category/event first, second, last and past-last pages, Zaragoza's 23-result boundary and unaffected tag/speaker views. The event feed matches the original normalized body hash. This is not a new full regression run.
- `inc/archives.php` renders marked inherited Post Templates with isolated in-memory main-query slices (1 + 5 + rest). Explicitly unset the cloned `posts` property before assigning the slice: core binds it to `$GLOBALS['posts']` by reference during real HTTP bootstrap. The tests reproduce that condition and assert the original query, loop state, totals and global post remain unchanged. No database offsets or extra post queries.
- Category descriptions use core Term Description; local descriptions are all empty, so production copy still needs review. Lead players use the existing core Post Content approach, so the unresolved production content-shape question still matters. The special WordCampTV featured/tag/menu layout and forms/admin remain unfinished.
- New general template files and three hidden patterns passed editor validation, including the evaluated/translated pattern content.
- Archive, page, search and 404 received desktop/mobile screenshots and DOM checks. Removed the core's extra 20px margin before already-padded page/archive sections. Existing header/footer, tokens, homepage and single design remain unchanged; offscreen lazy thumbnails are not forced eager for screenshots.
- Simulations do not prove real production files, subtitles or private-video behavior. No real remote audit was run.

## Environment cleanup and sync size

- The owner deleted attachment **133523** locally and on the test environment. Its local MP4 (`uploads/2026/05/video-bd9a03908b.mp4`, 297,547,310 bytes) initially remained on disk with access denied. It disappeared when the local site was stopped; after restarting, its absence was confirmed. This is consistent with a pending deletion/open handle, not a proven ACL diagnosis. Remote physical-file removal was not independently checked.
- Preserve attachment **133382**, the remote VideoPress reference for `HVQl3ZYz`. It is not another large local MP4. The plugin's attachment deletion hook can request remote video deletion, so do not delete it merely to reduce sync size.
- The disk audit found approximately 418 MiB under `wp-content`, dominated by the inactive Gutenberg plugin (230 MiB), Jetpack (96 MiB), themes (28 MiB), SQLite data (23 MiB), VideoPress (17 MiB) and translations (12 MiB). Uploads were only 0.21 MiB after MP4 cleanup. These are a historical measurement, not a current inventory.
- The owner removed Gutenberg and reported Studio's sync estimate falling to **about 183 MB**, then completed synchronization. Core's block editor remains available.
- The owner also reported disabling **Gravatar Enhanced, Layout Grid and Page Optimize**. Treat this as environment configuration, not a theme dependency change or evidence about production plugins. Existing saved content that uses plugin blocks may need separate review; wptv-27's designed templates do not depend on Layout Grid.

## Local MU-plugin caveat

- `wptv-route-test.php` requires an actual loaded Studio runtime, the local environment and `X-WPTV-Route-Test: 1`. `run.sh` sends that header. Ordinary local browsing/CLI must use real VideoPress APIs, not deterministic doubles.
- Those test requests suppress real thumbnail lookup through `wptv_pre_video_image_url` to keep snapshots deterministic. Runtime/header guards survive Studio loader regeneration.
- **The guard patched into Studio-managed `99-studio-loader.php` is not durable.** Studio was observed regenerating that file during development/CLI operations, removing the guard. Recheck immediately before syncing MU-plugins; do not promise that a previous patch still protects the current generated loader. Its machine-specific contents are not versioned in this theme repository.

## Next session

1. Start from this handoff and the [plan](plan.md); keep `wptv-27` active. Read the latest site-root `DESIGN.md`, versioned design documentation and Claude Code's supplied instructions before any design implementation. Do not restore the historical 0.3.4 snapshot over new guidance.
2. **Design is the next priority.** The owner accepted the current category/event structure for this version and deferred restructuring. Do not continue special WordCampTV or forms/admin implementation before the design handoff. Those functional gaps remain tracked, not completed or silently removed; preserve the Release 1 submission and moderation workflow.
3. Review the four residual feed hashes and the documented fixture changes without rewriting the baseline or broadening allowances. Obtain real multisite integration verification through the production contact.
4. Re-run full regression at the next routing/integration milestone, not for each template/CSS change. The owner's specific editor adjustments still have not been supplied; do not invent them.
5. Production rollout remains blocked on unfinished templates/forms, block integrations, regression and production-only verification. Retain wptv2 for rollback.
