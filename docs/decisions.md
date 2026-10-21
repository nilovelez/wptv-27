# Decision log

Record every significant decision here, newest first. Each entry says what was decided, why, and what it affects. Do not delete entries. If a decision is reversed, add a new entry that references the old one.

Template:

```
## YYYY-MM-DD: Short title

- **Decision:** what was decided.
- **Reason:** why.
- **Affects:** files, features or routes involved.
- **Status:** accepted | superseded by <entry>
```

---

## 2026-10-21: Use the featured image as the block player poster

- **Finding:** VideoPress no longer exposes poster/download/loop settings through the shortcode workflow; the owner confirmed with the VideoPress team that this is permanent and the block is the configuration surface. In local tests (video not owned by the site) and on the test host (video uploaded by the owner), the block's poster control stores only a per-post `poster` URL with `posterData.type: media-library` and passes it to the embed as a parameter. It does not update the VideoPress video. The actual video poster is edited under Jetpack > VideoPress, which does not exist on WordPress.com multisite sites, where VideoPress is native. In the local VideoPress version, `allowDownload`, sharing menu, privacy and rating mirror video-level settings that only an owner can change; the theme cannot enforce them by rewriting block attributes.
- **Decision:** the post featured image becomes the video poster. Remove poster settings from the VideoPress block and discard stored block poster attributes on save. At render, pass the featured image URL to the player; without one, fall back to the VideoPress poster, as today. Cards, Open Graph, feeds and the theme oEmbed use the same image. The `post` type is used only for videos.
- **Rejected:** block poster controls, because the value is an untracked URL inside content and would compete with the featured image. Setting a poster from the upload script would cover only uploads and depends on unconfirmed multisite VideoPress support; it remains a possible future enhancement.
- **Accepted limitations:** embeds outside WordPress.tv keep the VideoPress poster. The local count of 32 published posts with featured images is erroneous legacy data; featured images should not exist on videos today. Most older videos have manually set VideoPress posters; roughly the last year is broken. Clean up erroneous production featured images before shipping, or they will override VideoPress posters. Theme download links remain until production videos are confirmed downloadable through VideoPress. The owner found Allow download disabled on a 2016 production video and on a newly uploaded test-host video, so the effective default is disabled; see [open question 27](open-questions.md).
- **Image sizes (planned):** thumbnail 320x180 for the media library, one 640x360 size for almost all theme uses, 1920px `-scaled` originals, and disable other core intermediate sizes. Verify WordPress.com's image handling before relying on generated files.
- **Affects:** future editor prototype, VideoPress rendering, featured-image usage, image sizes and backlog B9. Not implemented yet.
- **Status:** accepted by the owner.

## 2026-10-20: Correct the WordCampTV video introduction

- **Owner correction:** the landing must start with one latest video player and the next five small videos, like other categories, not 22 Recent videos cards. Remove that heading/grid/pagination from the landing; rename the next section Latest WordCamps. Both automatic event sections remain complete and unpaginated.
- **Implementation:** reuse the native spotlight and in-memory partitions; suppress the inherited remaining-video grid only on WordCampTV page one. Preserve the 22-post main query and existing later-page URLs. Small nonempty WordCampTV archives show one lead and up to five secondary videos rather than an empty introduction. Other category/event thresholds are unchanged.
- **Evidence:** the corrected template (10 blocks) and section pattern (8) validate. 75 updated browser checks pass, including exactly one lead/five secondary videos, original first-six ordering, no landing grid/Recent videos heading, renamed section, complete directories and preserved page-two results. Desktop/mobile screenshots confirm the shared category presentation. No new full regression, publication or site sync.
- **Supersedes:** the earlier unpartitioned 22-card landing assumption in this implementation checkpoint. The earlier evidence below describes that earlier version, not the corrected layout.

## 2026-10-20: Automatic WordCampTV landing and data-only transients

- **Decision:** the owner replaces, rather than restores, the old WordCampTV page. Keep recent category videos and pagination; discard the entire legacy left sidebar and dependence on `featured_wordcamps`. Add automatically selected recent event sections and a complete year-grouped event directory. No menu/content data is deleted.
- **Dates:** recent events use valid `_wptv_submitted_video['recorded']` dates within twelve calendar months, newest first, without publication-date fallback. Their four category videos use stored publication dates. The owner acknowledges that both fields have historical limitations. The complete directory instead requires WordCamp plus one distinct 2000–2099 year in the event name; year groups descend and names sort alphabetically.
- **Caching:** approve two site-local data-only transients: recent event IDs/dates for at most one hour (capped at local midnight), directory year/ID groups for 24 hours. Rebuild on demand, including empty results; no cron, extra table, HTML cache or cached private submission data. Standard publication, metadata, category/event relationship and term changes invalidate the appropriate indexes. Departures and direct removals are explicitly covered.
- **Implementation:** local 0.4.1 adds `category-wordcamptv.html` and three hidden core-block patterns, with selection/invalidation in `inc/wordcamps.php` and core-block composition in `inc/wordcamp-blocks.php`. This is an approved exception to the previous template inventory and generic category spotlight. Existing routes, 22-post main query and other category/event layouts stay intact. Extra event sections appear only on page one.
- **Evidence:** 43 focused PHP checks, 60 browser checks and the 48 existing archive checks pass. Three patterns (10/5/8 blocks), the new template (11) and populated generated core blocks (35) validate. Desktop/mobile visual checks include the mobile page tail. Synthetic date selection and populated-section previews are isolated test mechanisms, never published data.
- **Limits:** the local recent window has no qualifying data; the directory has 58 events across five years. Cold recent lookup still scans eligible serialized metadata with SQL year prefiltering and 500-row cursor batches. Real production cost/data coverage and multisite behavior need verification. Four unresolved feed differences remain; no baseline, route inventory or allowance was changed. No new full regression, commit, push or site sync for this feature.
- **Documentation:** [wordcamptv.md](wordcamptv.md) records payloads, expiry, exact hook coverage, SQL costs, diagnostics, failure behavior and test instructions. Language filters and year navigation are backlog B7/B8, not part of this landing implementation.

## 2026-10-20: Owner approval and Git publication of 0.4.0

- **Decision:** the owner approved the completed visual implementation and all subsequent refinements and requested publication of all accumulated theme changes to GitHub. Keep version 0.4.0 for this first publication of the implementation. Commit/push to `origin/trunk`; do not synchronize or deploy the site. The owner will handle the upload.
- **Scope:** include implementation, local assets, tests and documentation. Footer refinements now specify 2:1:1 desktop columns, a full-width brand above two equal mobile link columns, a 27px-high WordPress.org wordmark, decorative external-link arrows and zero top margin on the two brand paragraphs. About is removed only from main navigation.
- **Limits:** visual approval and Git publication do not close the four known feed mismatches, real multisite/accessibility verification or unfinished functional work. Existing scoped verification remains the evidence; no broad suite or full regression is repeated merely for publishing.
- **Status:** approved by the project owner for Git publication, not official Design team approval or deployment authorization.

## 2026-10-20: Three-column footer and WordPress.org subfooter

- **Decision:** use the owner's exact three-column link structure: text-only WordPress.TV and description, Contribute, About. Remove Watch and the separate WordPress column. Use the supplied WordPress.org wordmark and existing trademark notice in the subfooter, not a new copyright claim.
- **Implementation:** core blocks throughout; the supplied SVG is a local white image linked to WordPress.org. Below 782px the brand spans the width above the two link columns. This is a scoped owner-requested exception to the previous global-footer restriction, not trademark or official design approval.
- **Verification:** 20 valid blocks; footer-only checks at 320/375/1440px verify columns, exact links, logo loading, notice and no overflow. Desktop/mobile screenshots reviewed. No broad suites, route tests, commit, push or sync.
- **Status:** implemented locally; supersedes the earlier four-column footer and text-brand subfooter.

## 2026-10-20: Video card fields and progressive pointer activation

- **Decision:** spotlight captions show all speakers (comma-separated, trailing colon) then title, never event terms. Ordinary video grids show thumbnail, speakers, title and date; Featured retains its existing undated presentation. Omit absent speakers without an author fallback. Only titles are native links.
- **Implementation:** core blocks remain editable. Marked Post Terms output drops anchor tags because core has no unlinked-display option; other term blocks are untouched. Unlinked thumbnails retain empty alt text. Frontend-only `assets/js/cards.js` activates each card using its title URL, preserving selection, controls, native keyboard links, modified/middle clicks and the no-JavaScript fallback. Cancel middle mousedown on noninteractive card areas to prevent browser autoscroll. The player is never part of the clickable caption. Term cards retain their existing treatment. Keep the dormant legacy category-event hook for compatibility; no partition/query code changed.
- **Verification:** the three edited patterns validate (59/12/17 blocks); 48 partition checks and 88 focused card browser checks pass, plus read-only zero/one/multiple-speaker and unmarked-block checks. The archive caption assertion intentionally follows the new owner-approved fields. Desktop/mobile visual checks cover home and category; browser checks also cover events and page two. No broad browser suite or full route run, baseline changes, new dependencies, commit, push or sync.
- **Status:** implemented locally after the owner's confirmations. Supersedes earlier linked-speaker/stretched-video-link and event-caption presentation decisions, not archive partition behavior.

## 2026-10-20: Owner visual-review refinements

- **Decision:** exclude the structural `year` and `location` categories only from the homepage category cards; let single-video titles use the full introduction width instead of 22ch; use Inter for the footer site name while retaining its size and weight.
- **Implementation:** CSS changes are scoped to the single introduction and footer brand. Core Term Template does not forward an exclusion argument, so `inc/block-queries.php` delegates to the original registered renderer for the marked homepage category template with a temporary `get_terms_args` filter. Resolve excluded IDs by slug and remove the filter in `finally`, including on exceptions. Other term templates, ordinary category queries and archive routes remain unchanged. No block markup or stored content changes.
- **Verification:** the original 165 PHP assertions pass again, plus eight new read-only category-card checks (exclusion, native ordering, ordinary/unmarked query isolation and exception cleanup). The browser suite now passes 196 assertions, adding footer font, category omission and full-width single-title checks. Desktop/mobile screenshots reviewed; the sample long title goes from three desktop lines to two without reducing its size. PHP lint and whitespace checks pass.
- **Limits:** no new full route run for these scoped refinements; `design-0.4.0` remains the latest milestone run, with four unresolved feed hashes. No baseline/allowance changes, commit, push, sync or deployment.
- **Status:** implemented locally at the owner's request.

## 2026-10-20: Local WordPress.org visual implementation (0.4.0)

- **Decision:** implement the owner-approved direction in the existing standalone theme: .org preset slugs and values, local Inter/EB Garamond, 680px/1160px widths, white W-mark navigation, charcoal footer, video/term cards, reordered homepage and Get involved band, single player band, two-column details/comments, and recolored title bands. Remove migrated preset aliases and retire the interim frame/tile styles. No parent theme, plugins, content migration or build step.
- **Tablet clarification:** the owner confirmed a combined adaptation: hero columns become 1:1 and the heading reduces to a fluid 50–59px at 782–1079px. Large screens retain 5:7 and 70px; the existing mobile size/stack remain. This prevents breaking the WordPress.tv name at intermediate widths. Current docs override the older static mockups here.
- **Small scoped code:** core Navigation still owns its dialog, keyboard behavior and focus. A frontend-only MutationObserver mirrors its state into `aria-expanded` and `aria-controls`; it does not implement another menu. A featured-image render filter only affects `wptv-card__thumbnail`: redundant thumbnail links leave the tab order/accessibility tree and their images get empty alt text; the adjacent title supplies the accessible card link. Editorial images are untouched. A stretched Site Title link includes the decorative W without duplicate anchors.
- **Style detail:** button states remain declared in `theme.json`. Variation-scoped nested CSS there repeats the state colors after variation defaults because WordPress 7.1 emits those defaults after the zero-specificity element state rules; browser inspection caught the resulting outline-hover override. This avoids a separate stylesheet or an editor/frontend mismatch.
- **Preservation:** existing template inventory, inherited/independent query definitions, taxonomy queries, spotlight markers/split, ordering, page sizes, route code, feeds, endpoint handlers and editorial workflow stay unchanged. No edits to reference themes, historical design snapshot, route baseline, inventory or expected allowances.
- **Verification:** all 165 existing checks pass unchanged. Changed templates and evaluated PHP patterns pass editor validation without serialization fixes. The new read-only browser script passes 188 assertions: ten homepage widths (320–1440px), tablet ratios/type, local fonts, button hover, menu/search/focus and thumbnail semantics, plus 15 paths at 375px/1440px including shortcode/block singles, listings/page two, empty search, a page and 404. Desktop/mobile screenshots and DOM inspection complement those assertions; these are not a complete WCAG audit or a production playback test.
- **Regression:** `design-0.4.0` requests all 145 fixed URLs. It has **zero recorded field differences from `phase-1c-integrations`**, no PHP notices, and the same four unaccepted feed hashes plus four expected API hashes against the untouched wptv2 baseline. The suite is still **not green**. No new routing differences were found; existing release blockers remain.
- **Affects:** presentation files, `inc/setup.php`, `assets/js/navigation.js`, a browser test, screenshot, live design docs and handoff. Version 0.4.0 is local and uncommitted at this closeout; no push, remote sync or deployment.
- **Status:** implemented locally, ready for owner visual review; WordCampTV/forms/admin parity, feed review and actual multisite verification remain open.

## Owner follow-up: design details confirmed

- **Decision:** use the official W mark in `charcoal-1`; keep `charcoal-4` borders on ordinary fields and borderless search fields on `light-grey-2`. "All featured videos →" links to the `featured` tag archive. Recent events uses the exact label "All WordCamp Videos →" linking to `/category/wordcamptv/` temporarily; this may change later. No Design team review is requested for now.
- **Reason:** the owner answered the handoff's confirmation questions before implementation.
- **Limits:** owner approval does not establish trademark permission, official endorsement or accessibility conformance. Verify rendered focus and search usability. Existing title-band scope, functional gaps and regression warnings remain unchanged.
- **Affects:** design specification, site-root `DESIGN.md`, handoff, plan and questions; documentation only. Mockups predate these confirmations and do not override the final events-link label.
- **Status:** accepted; implementation pending.

## Design direction: WordPress.org visual language

- **Decision:** replace the interim wptv3 look with the visual language of the current WordPress.org sites, as specified in [design.md](design.md) and the site-root `DESIGN.md`. Tokens copy the `wporg-parent-2021` slugs and values; components are rebuilt with core blocks because WordPress.tv has no access to `wporg-mu-plugins`. White local navigation bar with the WordPress W mark on every page; no copy of the .org global header. Single-video player on a full-width `charcoal-1` band holding only the player. Front page: recent videos before categories and events, two hero buttons and a closing "Get involved" band. Local fonts only; no RTL support for now.
- **Reason:** WordPress.tv is a WordPress.org community site. The owner reviewed the proposal and the mockups in `docs/mockups/` and approved them.
- **Limits:** owner approval, not WordPress.org Design team approval. Presentation only: templates, queries, the spotlight split, page sizes, pagination, routes and the editorial flow stay as accepted for 0.3.4. Input borders use `charcoal-4` instead of the parent's `charcoal-5` to meet WCAG 2.2 AA. Pending owner confirmations are listed in [design-handoff.md](design-handoff.md#decisions-needing-owner-confirmation).
- **Affects:** documentation only in this change (`docs/design.md`, `docs/design-handoff.md`, `docs/mockups/`, site-root `DESIGN.md`, `AGENTS.md`). Implementation follows [design-handoff.md](design-handoff.md).
- **Status:** accepted; implementation pending.

## Version 0.3.4 Git checkpoint and design handoff

- **Decision:** the owner accepted the current category/event presentation for this version; restructuring is deferred. Commit and publish all pending theme work to GitHub before design collaboration. This is a Git checkpoint, not a site sync, deployment or declaration that regression is green.
- **Next priority:** pause further functional implementation and address design next. Claude Code is researching WordPress/WordPress.org guidelines and will update the design document and provide implementation instructions. Read that handoff before changing the current look. Do not treat this as already-received official Design team approval, and do not overwrite concurrent design work.
- **Baseline:** the active site-root `DESIGN.md` is outside this theme repository. Preserve its pre-handoff contents verbatim in `docs/design-baseline-0.3.4.md` as a historical snapshot, not a competing live specification. The original file is left untouched for Claude Code.
- **Limits:** WordCampTV's special featured/menu layout, forms/admin, real multisite verification and four unaccepted feed hashes remain tracked. Owner acceptance does not complete those tasks or authorize changing routes, baseline or expected allowances.
- **Affects:** Git checkpoint and handoff/plan/question documentation only; no new design or functional changes in this closeout.
- **Status:** accepted

## Phase 1b category and event spotlight (version 0.3.4)

- **Decision:** add `category.html`, `taxonomy-event.html` and a shared hidden spotlight pattern. Keep the current wptv3 tokens, header/footer, native breadcrumbs and one unprefixed archive h1. Categories display their stored term description; events retain the legacy omission of term descriptions. Category spotlight captions link event terms; event secondary items do not repeat the current event.
- **Legacy split:** on page one only, with more than six results, show the first main-query video as the player, the next five as secondary videos and all remaining results in the existing grid. Later pages and archives with six or fewer results show every queried video in the grid. The special WordCampTV featured/tag query and featured-event menu remain unfinished; this category fallback does not complete that landing page.
- **Core-first implementation:** core Query, Post Template, Post Content, title, image, term-description and pagination blocks supply the presentation. `inc/archives.php` short-circuits only marked inherited Post Templates and delegates their rendering to core with in-memory slices of an isolated query clone. No second post query, database offset, page-size override, route or content rewrite is introduced. Restore the global query/post in `finally`. Explicitly detach the cloned `posts` property: `WP::register_globals()` links it to the global posts array by reference, which a shallow clone otherwise retains. Tests reproduce that real HTTP bootstrap condition.
- **Verification:** 48 read-only archive checks cover ordered, unique 1 + 5 + 16 results, unchanged original query/loop/totals, global-post restoration, zero/one/six/seven-result boundaries, page two, feeds, independent queries and prior short-circuits. All prior 117 checks pass again (165 total). Both new templates, the spotlight pattern and the changed grid pattern pass editor validation. Category/event desktop/mobile screenshots and DOM checks confirm the two-column/stacked layout and existing gutters; removed an extra 20px core margin before the already-padded spotlight. Offscreen lazy images are not forced eager for screenshots.
- **HTTP:** 14 targeted samples retain the unchanged baseline status/query contracts, unique rendered video counts and one h1, including category/event first, second, last and past-last pages, Zaragoza's 23-result boundary, and unaffected tag/speaker archives. The event feed matches its original normalized body hash. This is not another full 145-URL run; the latest full regression remains `phase-1c-integrations`, with four unaccepted feed hashes. No baseline, inventory or allowances changed.
- **Limits:** all local category descriptions are empty, so actual production description content still needs visual review. The player uses the existing Post Content approach (as on the homepage/single); production-only VideoPress behavior and the unanswered content-shape question remain open. No forms/admin or editorial workflow changes, plugins, theme switches, commit, push or remote sync.
- **Affects:** `inc/archives.php`, bootstrap, category/event templates, spotlight/grid patterns, `style.css` version 0.3.4, tests and documentation.
- **Status:** accepted; special WordCampTV, forms/admin, full regression and production verification remain open.

## Phase 1c integrations and general Phase 1b templates

- **Decision:** add block-only fallback to oEmbed, discovery links, unisubs, GUID redirects, API files/subtitles, Open Graph and the download sidebar. Preserve each consumer's legacy selection rules: endpoint regex parsers and the production shortcode API use their first match; the general helper keeps its last-shortcode behavior. A legacy marker never falls through to a block when legacy data is unavailable. Block fallback uses the existing last-valid-nested-block extractor.
- **Lookup:** GUID redirects/unisubs keep the original published-post shortcode search first. Only a valid eight-character GUID enters the fallback, which searches published posts in batches of 100 and verifies parsed block GUIDs, rejecting textual mentions, cached iframe HTML and mixed shortcode content. No index, meta writes, migration, route names, query vars, redirect codes or page sizes change. Preserve the unisubs permalink bug (B2).
- **Audit:** JSON-LD already uses shared GUID/details/file helpers. The theme's MRSS filter reads the original attachment rather than shortcode content. Built-in platform MRSS/player serialization still needs production verification; no synthetic feed markup or invented media URLs were introduced. Missing production APIs still omit unavailable output on Studio/Atomic.
- **Verification:** 22 new process-isolated integration checks, 27 GUID/image checks, 8 simulated production image checks, 12 simulated download/subtitle checks, 18 single checks and 11 homepage checks pass. A plugins-skipped smoke check confirms block extraction and closed failure for unavailable details/downloads. No stored content was changed.
- **Regression:** `phase-1c-integrations` requests all 145 fixed URLs with wptv-27 active. **Four unaccepted differences remain**, down from 27 fields across 11 URLs; all are feed body hashes (single feed, language feed, main feed and rss2 query-var feed). The single feed difference is lastBuildDate only; other feeds contain changed build dates, the owner's block conversion and renderer paragraph/lazy attributes. oEmbed JSON/XML/maxwidth, unisubs URL/GUID and both GUID redirects now match the original baseline. No PHP notices. Four pre-existing expected API hashes still differ: their bodies were also reviewed for restored block file data and removal of the deleted local MP4 reference, not just the existing slug-based year/location differences. No baseline, URL list or expected allowances changed. This is **not a green regression**.
- **Templates:** general archive, ordinary page, search and 404 now use core blocks and the existing wptv3 structure/tokens. A hidden shared video-grid pattern inherits the main query without page-size, offset or ordering overrides. Existing header/footer and institutional content remain untouched. Category/event heroes, descriptions and WordCampTV's featured-event menu are not completed by the general archive fallback; forms/admin parity remains pending.
- **Template verification:** 19 read-only checks pass. Four template files and three patterns (including rendered translated content) passed editor validation. Desktop/mobile screenshots and DOM checks cover archive, page, search and 404. Removed only the extra core block gap before already-padded archive/page sections. Nine HTTP samples retain one h1 each, category page size 22, tag/speaker/search page size 10, empty search 200 and unknown page 404. Full route run preceded these markup-only additions; post-addition HTTP checks are targeted, not a second full run.
- **Affects:** `inc/video.php`, endpoint/social/download consumers, general templates/patterns, `style.css` version 0.3.3, tests and project documentation. Current wptv3 design is unchanged; no plugins installed, remote sync, theme switch or content migration.
- **Status:** accepted; production compatibility and release readiness remain open.

## Session closeout: Atomic verification, current design and environment cleanup

- **Verification:** the owner completed synchronization and confirmed that thumbnails work on the Atomic test site. This closes the thumbnail-specific Atomic check, not the overall rollout checklist or production multisite verification.
- **Design decision:** retain the current wptv3-based design. The owner will consult the WordPress.org Design team; do not begin a WordPress.org redesign or adopt suggested documentation/tokens as an approved new direction. This holds the earlier Phase 2 visual-language proposal pending new guidance.
- **Editor:** the owner is reviewing the Classic-to-block editor transition and will provide specific adjustments. No editor implementation changes or workflow modernization were approved in this exchange.
- **Environment:** the owner removed the large test attachment locally and on Atomic, retained the remote VideoPress reference, removed inactive Gutenberg and reported disabling Gravatar Enhanced, Layout Grid and Page Optimize. The local orphan MP4 disappeared when the site was stopped and remained absent after restart. Studio's reported sync estimate fell to about 183 MB after Gutenberg removal; remote physical-file removal was not independently verified.
- **Safety:** do not delete remote VideoPress attachment records to save local file space; the plugin can delete the remote video. Studio's generated loader can overwrite its guard; the independent route helper remains opt-in and runtime-guarded. The previous route run predates media cleanup and is still not green; no baseline or expected differences were changed.
- **Affects:** documentation only. See [handoff.md](handoff.md), [plan.md](plan.md), [open-questions.md](open-questions.md), [design.md](design.md) and [wpcom-constraints.md](wpcom-constraints.md).
- **Status:** accepted

## Phase 1c first step: GUID extraction and real posters

- **Decision:** the shared video helper recognizes saved `videopress/video` and earlier `jetpack/videopress` blocks, recursively. Existing shortcodes take precedence and retain last-shortcode behavior; without shortcodes the last valid eight-character alphanumeric block GUID wins. Ignore cached iframe HTML and do not rewrite stored content.
- **Decision:** preserve the production thumbnail API first; when unavailable or empty, reuse the callable standalone plugin `Inline_Player::get_poster_url()` resolver. It owns poster attributes, GUID-matched attachment metadata, privacy and transient-cached public API details. Do not construct image paths, introduce a second HTTP/cache implementation or replace featured images. Missing dependencies/data retain the featured image or bundled placeholder.
- **Decision:** local route-test logging, API doubles and response headers require `X-WPTV-Route-Test: 1`, sent by `run.sh`. Those requests additionally suppress real thumbnail resolution through `wptv_pre_video_image_url`. Ordinary Studio requests and WP-CLI use real plugin APIs. Environment guards remain mandatory before honoring the header.
- **Verification:** 27 deterministic GUID/image checks and 8 simulated production image checks pass without HTTP; absent dependencies return false with plugins skipped. Existing 11 homepage, 18 single and 12 simulated download/subtitle checks pass. All 12 homepage poster URLs return HTTP 200/image/jpeg. Desktop/mobile DOM uses real image URLs with unchanged frames and lazy loading; full-page screenshots leave some offscreen lazy images unloaded. No block markup was changed or imported.
- **Regression:** the 145-URL run is **not green**: 27 unaccepted field differences across 11 URLs, plus the same four expected API differences; no PHP notices. oEmbed JSON/XML/maxwidth and unisubs URL return 501 because they explicitly require `[wpvideo` in converted post 126193. Unisubs GUID lookup and both GUID redirects still search the shortcode text, producing 404/no redirect. Four feed body hashes differ after conversion (modified/build timestamps, player serialization and renderer markup/lazy attributes). The API body's already-expected differences also include the converted post losing shortcode-only video data. These are remaining 1c integration work, not a reason to change routes, baseline, content or expected-difference allowances in this thumbnail-only step.
- **Managed loader warning:** a final negative check caught Studio regenerating `99-studio-loader.php` during development/CLI operations, removing the earlier environment guard. Reapplied the guard and reran all eight negative checks. It must be rechecked immediately before sync; the earlier managed-file patch is not durable. The route helper's independent runtime/header guards survive regeneration.
- **Scope:** no template, CSS layout, route, endpoint or editorial workflow changes; theme version 0.3.2. Test on Atomic after sync; confirm the built-in multisite block/API separately. Phase 1c is not complete and deployment readiness is not established.
- **Affects:** `inc/video.php`, the installed/versioned route helper, `tests/routes/run.sh`, image checks and environment/plan docs.
- **Status:** accepted

## Local tooling: MU-plugins fail closed outside Studio

- **Decision:** allow full test-site syncs without manually excluding the two local MU-plugin files. The route helper requires both the local environment and an actually loaded Studio runtime. The Studio loader verifies its runtime directory before defining database/environment constants. Both return without side effects elsewhere, including Atomic and multisite.
- **Reason:** the owner prefers automatic environment guards to repeated selective syncs. A copied loader must not label a remote site as local or enable deterministic VideoPress test doubles.
- **Affects:** the installed MU-plugins, the versioned route helper and isolated environment checks. No theme behavior or public routes change. The Studio-managed loader is outside the repository and may be regenerated; recheck its guard after Studio restarts/updates. This does not fix thumbnails in Studio.
- **Verification:** eight isolated negative checks (both files; local-without-Studio, staging, production and WordPress.com); local WordPress bootstrap still loads the test helper with `wptv-27` active.
- **Status:** accepted

## Phase 1b review: Video details use wptv3-style rows

- **Decision:** use CSS-only label/value rows for published date, taxonomy terms, producer, downloads and subtitles: 1:2 columns, 1rem gutter, 0.5rem row spacing, regular-weight labels. Multiple term links remain inline and wrap within their value column, including on mobile.
- **Reason:** stacked labels wasted the wider details column. The owner requested the compact wptv3 treatment.
- **Affects:** `style.css` (version 0.3.1), [design.md](design.md). No PHP, block markup, content or route changes.
- **Status:** accepted

## Phase 1b: Single-video pattern and guarded metadata blocks

- **Decision:** `single.html` only references header, the hidden `wptv/single` PHP pattern, and footer. Follow functional `wptv3`: title/speakers, wide player, native Jetpack reactions, description/details columns, comments, previous/next and breadcrumbs. Core blocks handle everything except producer credit and VideoPress downloads/subtitles, registered in PHP with `supports.autoRegister` and no build step.
- **Legacy behavior:** producer username links to its WordPress.org profile, using the producer name when both taxonomies exist; name-only credits link to the existing producer archive. Downloads retain Low/Med/High MP4 labels, HLS-only exclusion, original attachment/API fallback, OGG, TTML and `/subtitle/?video=<attachment ID>`. The sidebar uses the first shortcode, as wptv2 does, even though the general GUID helper uses the last. Missing APIs, classes, terms or data must not emit empty sections or PHP warnings. No routes or stored content change.
- **Full description:** core Post Excerpt truncates explicit excerpts and strips links. A `render_block_core/post-excerpt` filter in `inc/single.php`, scoped to the `wptv-single__excerpt` instance, preserves the full legacy excerpt and its slides link using the standard excerpt output filters. Other excerpt blocks, archives and feeds are unchanged.
- **Verification:** 52/52 pattern blocks and 3/3 template blocks valid. 18 read-only checks plus 12 isolated multisite API simulations. Four sample single URLs return 200; native comments render an existing six-comment thread, including a reply. A block-only local video renders through Post Content; extracting its GUID for metadata is still Phase 1c. Actual download/subtitle behavior remains production-only verification.
- **Environment:** Machete adds independent sharing controls locally; leave its settings and the reference themes unchanged. Record this as a test-environment difference, not custom theme functionality.
- **Review workflow:** leave `wptv-27` active after changes, as requested by the owner, instead of returning to `wptv3`.
- **Affects:** `templates/single.html`, `patterns/single.php`, `inc/video-blocks.php`, `inc/single.php`, `functions.php`, `style.css`, `tests/blocks/`, `AGENTS.md`.
- **Status:** accepted

## Release split: parity test first, new submission workflow second

- **Decision:** the project ships in two releases. **Release 1** (Phase 1) is the block theme with the current functionality and workflow, tested on the WordPress.com test site and then activated in production, with `wptv2` kept installed for immediate rollback. **Release 2** (Phase 2) replaces the per-field moderation workflow with a front-end form that fills the real fields, embeds videos as VideoPress blocks, adds autocomplete for existing terms, and ships a shortcode-to-block migration script.
- **Release 1 details:** the submission form keeps saving `[wpvideo <guid>]` and the `_wptv_submitted_video` custom fields, and the Approve review box is kept. The theme supports VideoPress blocks as well as shortcodes; the block is tested in production on a few existing posts converted by hand. Design level is functional `wptv3`; fine polish moves to release 2.
- **Reason:** VideoPress no longer supports the shortcode workflow, and the per-field Approve process is slow for moderators. A parity release first keeps the risk of the theme switch separate from the workflow change.
- **Affects:** [plan.md](plan.md) (new 1c VideoPress block support, 1d forms and admin, 1e rollout; Phase 2a/2b), `inc/video.php`, submission and admin code.
- **Status:** accepted

## Phase 1b: Front-page query implementation and design safeguards

- **Decision:** `front-page.html` references the hidden `wptv/front-page` pattern. The independent latest-video query requests one post; Featured requests four posts tagged `featured`, without a category constraint or offset. Recent videos use the inherited main query without a page-size override. Event terms use descending term IDs (not event dates); categories show all nonempty top-level terms, ordered by count.
- **Implementation:** the query object's `wptvQuery` marker is provided to Post Template through core query context. `inc/block-queries.php` resolves only known theme markers (`wptv/featured`, `wptv/wordcamp`) to term IDs, leaves unmarked/inherited queries untouched, and fails closed when a term is missing. Empty native tax queries must not be nested inside an AND condition.
- **Thumbnails:** brought forward from the later Phase 1b step because the front-page grids need them. `post_thumbnail_html` supplies VideoPress imagery through the existing guarded helper, preserves an existing attachment image as fallback, and uses the bundled placeholder when neither is available. Post queries and routes are unchanged.
- **Design safeguards:** rename the body-color preset from `text` to `ink`; `text` generates a `.has-text-color` utility that collides with core's generic text-color marker and overrides other colors. Bump the theme version whenever the pattern file inventory changes, so core invalidates its cached pattern list on deployment.
- **Verification:** 49/49 pattern blocks and 3/3 template blocks validated; 11 read-only checks in `tests/blocks/front-page.php`; desktop/mobile visual review. HTTP checks retain 8 posts on `/` and `/page/2/`, 2 on `/page/490/`, and a 404 on `/page/491/`. Full route regression remains scheduled for the end of Phase 1b.
- **Affects:** `patterns/front-page.php`, `templates/front-page.html`, `inc/block-queries.php`, `inc/video.php`, `inc/setup.php`, `theme.json`, `style.css`, `tests/blocks/front-page.php`.
- **Status:** accepted

## Phase 0: Project documentation lives in the theme repository

- **Decision:** project documentation lives in `docs/` and `AGENTS.md` inside the theme repository. Both are excluded from theme zips with `export-ignore` in `.gitattributes`. Clean zips are built with `git archive` (see README).
- **Reason:** the documentation travels with the code, is versioned with it, and is read automatically by AI coding agents. Contributors clone the full repo, while deploys only get the theme files.
- **Affects:** `.gitattributes`, `README.md`, `AGENTS.md`, `docs/`.
- **Status:** accepted

## Phase 0: US English for everything in the repository

- **Decision:** code, function names, comments, translatable strings, commit messages and documentation are written in US English (en_US).
- **Reason:** WordPress project standard. The project owner may communicate in Spanish, but the repository is English-only.
- **Affects:** whole repository.
- **Status:** accepted

## Phase 0: New block theme from scratch, `wptv-27`

- **Decision:** build a new block theme named `wptv-27` from scratch, instead of evolving `wptv2` or `wptv3`.
- **Reason:** `wptv2` is an old classic theme with logic embedded in its templates. `wptv3` is a proof of concept that is only used as a design and structure reference.
- **Affects:** whole project.
- **Status:** accepted

## Phase 0: Phase 1 must not change any route

- **Decision:** in Phase 1, every route served by `wptv2` must behave identically with `wptv-27`, verified by a regression test against a `wptv2` baseline.
- **Reason:** WordPress.tv URLs are linked from WordCamp sites, embeds, feeds and external consumers of `/api/` and `/oembed/`. Breaking them has a high cost and is hard to detect.
- **Affects:** taxonomies, `pre_get_posts`, rewrite rules, endpoints, redirects. See [routes.md](routes.md).
- **Status:** accepted

## Phase 0: Use `wptv3` as the interim design reference

- **Decision:** the visual design starts from `wptv3`. It will later be aligned with the WordPress.org visual language, once the project owner provides the documentation.
- **Reason:** `wptv3` already explores a block-based design for the site.
- **Affects:** `theme.json`, templates, patterns.
- **Status:** accepted

## Phase 0: Keep the `wptv` text domain

- **Decision:** the text domain stays `wptv`, as in `wptv2`.
- **Reason:** existing translations keep working.
- **Affects:** all translatable strings.
- **Status:** accepted

## Phase 0: Theme-agnostic route regression test

- **Decision:** routes are verified by `tests/routes/`: a fixed URL list generated from real data, a curl runner that does not follow redirects, and a compare script. Main query data comes from an `X-WPTV-Route` header added by a local-only mu-plugin, never from theme markup. The `wptv2` baseline is committed.
- **Reason:** both themes render different markup, so counting posts in HTML would not compare. The header exposes exactly what decides a route (404 flag, found posts, pages, per page, queried object). Not following redirects makes redirect targets exact.
- **Affects:** `tests/routes/` (export-ignored, never deployed).
- **Status:** accepted

## Phase 0: Deterministic stubs for WordPress.com-only VideoPress helpers

- **Decision:** the local-only mu-plugin defines missing VideoPress helpers. `video_get_info_by_guid()` returns fake details derived from the GUID only, `videopress_2015_player_get_html()` returns fixed markup, and file, thumbnail and subtitle helpers return no data. The mu-plugin does nothing on WordPress.com (`IS_WPCOM`).
- **Reason:** without stubs, `wptv2` singles and endpoints fail locally. Stubs that return "no data" only exercise error paths. Deterministic fake data exercises the full code paths and keeps runs identical.
- **Affects:** `tests/routes/mu-plugin/wptv-route-test.php`. Real video output must be checked on the WordPress.com test site.
- **Status:** accepted

## Phase 0: Legacy quirks are part of the route contract

- **Decision:** Phase 1 reproduces observable `wptv2` behavior even when it is a bug (for example, the wrong `permalink` in `/unisubs/?guid=`), unless the project owner decides otherwise. Each quirk is tracked in [backlog.md](backlog.md) and fixed after Phase 1.
- **Reason:** external consumers may depend on the current behavior, and Phase 1 must not change routes.
- **Affects:** endpoints, see [routes.md](routes.md).
- **Status:** accepted

## Phase 0: Retire the `producer` and `flavor` taxonomies after Phase 1

- **Decision:** both taxonomies will be removed after Phase 1. Every existing URL (term archives, pagination, feeds, query vars) gets a 301 to a mapped replacement, never a 404. Phase 1 still registers them with the exact `wptv2` arguments.
- **Reason:** decided by the project owner. Removing them in Phase 1 would break the no-route-changes rule.
- **Affects:** taxonomy registration, `/producer/*`, `/flavor/*`, the producer credit, `/api/`. See B3 in [backlog.md](backlog.md).
- **Status:** accepted (deferred)

## Phase 1a: Look up the year and location categories by slug

- **Decision:** `/api/videos.*` finds the year and location category branches with `get_category_by_slug( 'year' | 'location' )` instead of the hardcoded production IDs 91093 and 6418. On 2025 production data, 91093 is `year` and 6418 is `location` (checked through the public `/api/categories.json`), so production output does not change.
- **Reason:** the project rule is never to hardcode IDs. Locally the legacy IDs never match, so the local baseline differs on 4 URLs; these are listed in `tests/routes/expected-differences.tsv`.
- **Affects:** `inc/endpoints/api.php`, the route test.
- **Status:** accepted

## Phase 1a: Expected differences in the route test

- **Decision:** `compare.sh` reads `tests/routes/expected-differences.tsv` (label, column, reason) and reports those differences as EXPECTED, not as failures. Every entry needs a matching decision in this file.
- **Reason:** some correct changes only show up locally because of local data (see above). The list must stay short.
- **Affects:** `tests/routes/`.
- **Status:** accepted

## Phase 1a: Drop the hardcoded user from `?flush_rules=1`

- **Decision:** only super admins can flush rewrite rules with `?flush_rules=1`. The extra hardcoded user ID 4637740 from `wptv2` is dropped.
- **Reason:** no hardcoded IDs. Anonymous behavior, which is the only part the route test sees, does not change. Whether the feature is still needed is an open question.
- **Affects:** `inc/maintenance.php`.
- **Status:** accepted (review with the project owner)

## Phase 1b: No block.json blocks; core blocks and PHP-only blocks instead

- **Decision:** the theme ships no `block.json` blocks and no build step. Breadcrumbs use `core/breadcrumbs`; term lists use `core/terms-query` + `core/term-template`. The few features core cannot cover (producer credit, video downloads and subtitles) are PHP-only blocks: `register_block_type()` with a `render_callback` and `supports.autoRegister` (WordPress 7.0+), which the editor previews with ServerSideRender. Replaces the earlier plan of `wptv/breadcrumbs` and other `block.json` blocks.
- **Reason:** decided by the project owner until it is confirmed that a theme can register `block.json` blocks on WordPress.com. PHP-only blocks need no JavaScript, no build and no `build/` folder in the repo.
- **Affects:** `inc/blocks/`, templates. Requires WordPress 7.0+ in production (open question on the production version).
- **Status:** accepted

## Phase 1b: Drop "Popular videos"; front page follows wptv3

- **Decision:** the front page follows the `wptv3` structure, without a "Popular videos" section. More useful sections such as recent events and video categories take its place. The `wptv/popular` Query Loop variation is dropped. `wptv_post_views` is still initialized on publish (Phase 1 contract).
- **Reason:** decided by the project owner.
- **Affects:** `templates/front-page.html`.
- **Status:** accepted

## Phase 1b: The design lives in code; nobody can change it from the admin

- **Decision:** the theme files are the only source of truth for the design.
  - Nobody, administrators included, can open the Site Editor.
  - Templates and parts are built from theme patterns (`patterns/*.php`, referenced with `wp:pattern`). Copies of templates and parts saved in the database are ignored.
  - Global styles saved by users and Additional CSS are ignored or disabled. Colors, fonts and spacing come only from `theme.json`.
  - Editors and administrators can still edit the content of posts and static pages (for example `get-involved`, or the side text of `/submit-video/`). Custom colors, font sizes and spacing are disabled in `theme.json`, so content edits cannot restyle the site.
- **Reason:** decided by the project owner. In a block theme, an unprotected Site Editor lets anyone change the design, and a database copy of a template silently hides later theme deploys. Deploys already go through code.
- **Affects:** `theme.json`, `inc/lockdown.php`, `templates/`, `parts/`, `patterns/`.
- **Status:** accepted

## Phase 1b: Provisional navigation links

- **Decision:** the header uses a Navigation block with provisional links based on the current wordpress.tv header, editable in the Site Editor, until the production menus are known.
- **Reason:** local classic menus are empty and the production menu contents are unknown.
- **Affects:** `parts/header.html`.
- **Status:** accepted (provisional)
