# Session handoff

## Current state

- Theme: `wptv-27`, version **0.4.1**, branch `trunk`. Leave it active. This checkpoint follows published 0.4.0 (`14a9b88`) and includes the automatic WordCampTV landing, the corrected native 1 + 5 introduction, the caption/archive refinements and the persistent proportional-workflow rules. The owner authorized Git publication of these changes. No site synchronization or deployment is included; Git publication is not deployment.
- Phase 1a foundation is complete. Phase 1b homepage, single, general archive/page/search/404 and category/event title/spotlight layouts are implemented. The owner has explicitly replaced the legacy WordCampTV featured/tag/menu requirement with the new automatic landing, now implemented locally in 0.4.1; see [wordcamptv.md](wordcamptv.md). Do not restore the manual menu or removed sidebar. Other taxonomies use the general archive fallback.
- Phase 1c local theme-owned GUID consumers now support block fallback. Real multisite integrations and four residual feed hashes keep the phase open. No stored content, plugins, route definitions or page sizes were changed.
- The owner synchronized the site to the Atomic test environment and **confirmed that thumbnails work there**. This is owner-reported verification, not an independent remote audit or proof of multisite production compatibility.
- The owner accepted the existing category/event structure and prioritized design. **The completed visual implementation is approved by the project owner for Git publication.** Follow `docs/design.md`, `docs/design-handoff.md` and the site-root `DESIGN.md`, not the interim wptv3 look. Confirmed details include the charcoal W, ordinary input borders, borderless light search, featured tag link and temporary "All WordCamp Videos →" link to `/category/wordcamptv/`. During implementation the owner also approved equal hero columns and smaller text on tablets (782–1079px), retaining 5:7/70px on large screens. No Design team review is requested; do not claim official approval.
- The site-root `DESIGN.md` lives outside this theme repository. Its pre-design contents are preserved verbatim in [design-baseline-0.3.4.md](design-baseline-0.3.4.md) as historical reference, not a second live source of instructions. Do not overwrite the approved design updates with that snapshot.
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

The latest full run, **`design-0.4.0`**, covers all 145 fixed URLs and reports **4 unaccepted field differences across 4 URLs**, plus the same four expected API hashes; no PHP notices (zero-byte route log). Every recorded field is identical to the previous full `phase-1c-integrations` run: **zero new differences**. This remains down from the earlier 27 differences across 11 URLs, but is not green.

- oEmbed JSON/XML/maxwidth, unisubs URL/GUID and both GUID redirects now match the original wptv2 baseline.
- Four feed body hashes still differ: single feed (lastBuildDate only), language feed, main feed and rss2 query-var feed (build dates, converted block player and paragraph/lazy renderer attributes).
- Expected API bodies were also inspected rather than blindly waived: block file data is restored and the deleted local MP4 reference is gone, alongside existing slug-based year/location differences. No baseline, fixed URL list or expected-difference allowances changed.
- Remaining theme-owned consumers were audited: Open Graph/API/downloads use their original first-shortcode selection with block fallback; JSON-LD already uses shared helpers. The theme MRSS filter reads attachments. Actual platform MRSS, production APIs and serialization remain unverified.

Do not describe this as a passing regression or production readiness. Keep route names, query parameters, redirects, the deferred unisubs permalink bug and pagination contracts unchanged. The latest full run includes the general/category/event templates and the visual implementation. The baseline, fixed URL list and expected allowances remain untouched.

## Current verification (local 0.4.1)

- WordCampTV introduction corrected after owner review: one lead player plus five small videos, no Recent videos heading/22-card grid/pagination on the landing; heading Latest WordCamps. Both event sections remain complete and unpaginated. Existing later-page URLs retain their inherited grid. 43 focused PHP checks and 75 updated browser checks at 320/782/1440px pass; the previous 48 archive checks remain recorded. Three patterns validate (10/5/8 blocks), the corrected category template validates (10) and populated generated block instances validate (35). Browser checks verify the ordered first six main-query IDs in the spotlight, all 22 on the existing second-page URL, first-page-only event sections, the existing feed and populated event cards via an isolated response preview. No content/terms/menus were changed; tests write only disposable theme caches and optional temporary browser fixtures.
- Local observation on 2026-10-20: directory has 58 events in five year groups; the twelve-month recorded window has no qualifying events. The public empty state is expected. SQL-only date fixtures are restricted to CLI tests and never cached as public event data. Desktop/mobile inspection covers the landing, including its long mobile tail; no visual defects required a polish edit. Production recorded coverage, actual multisite and cold-cache cost still need verification.
- Changes are local only. No full route rerun, baseline/inventory/allowance edits, commit, push or synchronization. [wordcamptv.md](wordcamptv.md) documents exact date/name rules, two transient payloads/TTLs, hook coverage, SQL scan limitations and diagnostics. Language filters/year navigation remain backlog B7/B8.

## Previous verification (0.4.0)

- **Final owner-approved refinements:** remove About only from main navigation. Footer: text-only WordPress.TV/description, Contribute and About columns in a 2:1:1 desktop ratio, brand spanning the full mobile row above two equal link columns. Use the supplied local WordPress.org wordmark at 27px high and the trademark notice in the subfooter. Make WordPress, Powered by VideoPress and Privacy carry decorative external-link arrows. Both paragraphs immediately below the brand/wordmark have zero top margin. The final footer's 20 blocks validate; focused footer checks and desktop/mobile screenshots cover these adjustments. No unrelated suites or full regression were rerun for publication.

- **Latest video-card follow-up:** spotlight captions now show speakers and title without event terms; ordinary grids add dates, Featured stays undated. Speakers/thumbnails/dates are unlinked; `cards.js` expands pointer activation from each native title link, excluding the player and preserving keyboard/no-JavaScript use. The legacy category-event render hook remains available but is unused by the revised pattern. Three patterns validated; 48 partition checks and 88 focused card browser checks pass, with additional zero/one/multiple-speaker checks. The 173/196 counts below describe the earlier checkpoint, not a broad-suite rerun after this change. The checks preceded the owner's Git publication request; no full route rerun or site sync was performed.

- Owner visual-review refinements are implemented: omit `year`/`location` only from homepage category cards, remove the single title's 22ch cap, and switch the footer brand to Inter without changing its size. No markup changed. The category exclusion delegates to core only for the marked category Term Template, resolves IDs by slug, and removes its temporary query filter in `finally`. Eight new read-only checks cover omissions, ordering, unaffected queries and exception cleanup. These scoped changes did not trigger another full route run.

- All **165 original checks plus eight category-card checks (173 total)** passed again, without weakening any original assertions. Simulated production checks remain simulations, not actual multisite verification. The earlier absent-plugin smoke check was not rerun in this design step.
- **196 browser checks** pass in `tests/blocks/design-browser.cjs`: homepage widths 320, 375, 600, 782, 890, 960, 1024, 1040, 1080 and 1440px; 15 paths at 375px/1440px, including three shortcode/block singles, homepage page two, category/event first and second pages, tag/speaker, matching/empty search, About and 404. Checks cover status, one h1, overflow, hero ratios/type, button padding/hover, fonts, decorative thumbnails, native menu focus/closing/search and nonzero single-player dimensions. New assertions verify category exclusions, header/footer font-family parity and full-width single titles.
- All changed template markup and evaluated PHP patterns passed Studio editor validation without serialization fixes or `core/html` blocks. Local variable-font files are unchanged. No plugins, stored content, template inventory, post-query settings or route logic changed; only the requested homepage term-card exclusions were added.
- Desktop/mobile screenshots and rendered-DOM inspection cover homepage (including its mobile tail), single, category, event page two, search, page and 404. The 1040px screenshot viewport correctly shows the collapsed navigation and stacked details; browser checks also run at 1440px. The theme thumbnail was refreshed.
- Two presentation supplements are scoped in `inc/setup.php`: registering .org block style names and rendering marked thumbnails/speakers without redundant links; `assets/js/navigation.js` mirrors core menu state and `assets/js/cards.js` progressively expands card pointer activation. Core still owns focus, dialog behavior and keyboard handling. Theme JSON contains the button-state ordering workaround documented in the decision log.
- Hosted player rendering can settle after an initial capture: the 1040px block-player iframe and its internal video/poster measured 880px wide after loading. Do not mistake a transient hosted-player screenshot for a confirmed outer-layout defect, or these checks for playback/production evidence. Offscreen lazy thumbnail/badge captures are not a reason to force eager loading.
- The full 145-URL run is recorded above. Accessibility remains a WCAG 2.2 AA target, not a conformance certification; comprehensive keyboard/assistive-technology and real production-content review are still needed.

### Earlier 0.3.4 verification (historical)

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

1. Start from this handoff and the [plan](plan.md); keep `wptv-27` active. Check Git status and preserve any subsequent owner work on the approved 0.4.0 implementation. Read the latest site-root `DESIGN.md` and versioned guidance; never restore the historical snapshot.
2. **Continue review of the WordCampTV landing.** The owner authorized Git publication of this 0.4.1 checkpoint, not publication of every subsequent change. Verify recorded coverage and cold-cache cost against real test-site data when authorized. The old WordCampTV featured/menu gap was explicitly superseded by the approved product redesign, not silently omitted. Other category/event layouts and the Release 1 submission/moderation workflow stay intact; forms/admin gaps remain open.
3. Review the four residual feed hashes and the documented fixture changes without rewriting the baseline or broadening allowances. Obtain real multisite integration verification through the production contact.
4. Re-run full regression at the next routing/integration milestone, not for each template/CSS change. The owner's specific editor adjustments still have not been supplied; do not invent them.
5. Production rollout remains blocked on unfinished templates/forms, block integrations, regression and production-only verification. Retain wptv2 for rollback.
