# Project plan

Status legend: ✅ done · 🔄 in progress · ⏳ pending · ⛔ blocked

## Goal

Replace the legacy classic theme `wptv2` on WordPress.tv with a new block theme, `wptv-27`, that:

- keeps every existing route working exactly as before (Phase 1),
- replaces hand-written legacy code with WordPress core features, core blocks and small, well-scoped custom code,
- keeps post/page content editable while the design is managed only in theme code (no Site Editor access),
- moves video submission to VideoPress blocks and a front-end form that fills the real fields, so moderators only review and publish (Phase 2),
- adopts the owner-approved WordPress.org visual language for the current design checkpoint, before further functional work.

## Current owner priority: automatic WordCampTV landing

**Local 0.4.1 implementation, corrected after owner review:** the landing begins with the native 1 + 5 spotlight, NOT a 22-video Recent videos grid or its pagination. The next heading is Latest WordCamps. Preserve the underlying main query/page size and existing later-page URLs. Both event sections remain complete and unpaginated. This supersedes the earlier misinterpretation of the video introduction. The manual featured-event menu and entire old sidebar are not restored. First-page-only automatic recent-event sections use recorded dates; the complete WordCamp directory groups events by years parsed from their names. Both use the approved data-only transient strategy. See [wordcamptv.md](wordcamptv.md) for implementation and [handoff.md](handoff.md) for current verification and limits.

43 focused PHP checks and 75 updated browser checks pass; the earlier 48 existing archive checks remain recorded.  core-block prototypes/template/generated instances validate. No full regression rerun, baseline changes, commit, push or site deployment for this feature. Review the local landing next; verify real recorded coverage and cold-cache cost in the test environment when authorized. Language filtering and year navigation are separate backlog B7/B8.

This supersedes pending tasks to reproduce the old featured/tag/menu WordCampTV page or obtain the manual menu as an implementation prerequisite. Do not reintroduce that requirement. Forms/admin parity and production-only verification remain open.

## Previous owner priority: design checkpoint

The version 0.3.4 checkpoint is published to GitHub, and the design handoff is integrated in `d02cbfd`. The owner accepted the existing category/event structure and deferred restructuring. **The completed 0.4.0 visual implementation is owner-approved for Git publication**, ahead of further functional work. Follow [design.md](design.md), [design-handoff.md](design-handoff.md) and the site-root `DESIGN.md`. Follow-up confirmations: official W mark in `charcoal-1`, `charcoal-4` ordinary input borders, borderless light search, featured tag archive link, and "All WordCamp Videos →" linking temporarily to `/category/wordcamptv/`. No Design team review is requested for now; do not claim official endorsement. During implementation the owner also approved 1:1 hero columns and reduced 50–59px title text at 782–1079px, retaining 5:7/70px on large screens. The implementation and prior follow-up docs remain uncommitted/unpushed at this closeout. WordCampTV/forms/admin gaps and the four residual feed hashes remain open. Git publication is not deployment or site synchronization. [design-baseline-0.3.4.md](design-baseline-0.3.4.md) is historical, not a replacement for the approved direction.

## Session closeout

See [handoff.md](handoff.md) for the resume point, verification limits and environment cleanup. The owner confirmed thumbnails on the synchronized Atomic test site. The local implementation now uses the approved WordPress.org tokens, chrome and presentation, including the owner's tablet adaptation, without an official Design team review. Specific block-editor changes have not yet been supplied. Gutenberg was removed, three optional plugins were reported disabled and the sync estimate fell to about 183 MB. The latest regression is not green; no production readiness is implied.

## Latest implementation milestone

**Git publication checkpoint:** the owner approved all visual refinements and requested commit/push of the accumulated 0.4.0 theme changes. This includes the code, assets, scoped tests and current documentation, not a site sync or deployment; the owner will handle the upload. No unrelated test suite is rerun merely for publishing. Existing functional and regression gaps remain open.

**Footer follow-up:** implemented the owner's three-column footer and supplied WordPress.org wordmark/trademark subfooter. Core blocks validate; footer-only checks cover 320/375/1440px, with desktop/mobile visual review. No query, route, plugin or stored-content changes; no broad-suite rerun or publication.

**Video-card follow-up:** implemented the owner-confirmed speaker/title spotlight captions, dated ordinary grids, unchanged undated Featured presentation and single native title links with progressive JavaScript card activation. Queries and the 1 + 5 + rest split are unchanged. Verification is scoped to the three patterns, 48 partition checks, 88 card browser checks and speaker rendering; the earlier broad-suite/full-route results below are historical, not rerun for this change. See the newest decision and site-root DESIGN.md.

**Owner review refinements (local 0.4.0):** exclude `year`/`location` only from homepage category cards by slug, expand single titles to their full introduction width, and switch the footer brand to Inter. The original 165 tests plus eight new category-card tests pass (173 total); the extended browser suite passes 196 assertions. No block markup, post query, archive route or editorial changes. The full milestone run below was not repeated for these scoped refinements.

Theme version **0.4.0 locally** implements the approved visual direction without changing stored content, template inventory, query settings, routes, pagination or editorial workflow. Native Navigation remains responsible for its dialog/focus; a small state-attribute supplement and a marked-thumbnail accessibility filter are scoped to presentation. All 165 existing checks pass unchanged, plus 188 browser assertions across ten homepage widths and 15 paths at mobile/desktop sizes. Changed templates/evaluated patterns pass editor validation; screenshots and DOM checks cover the main layouts. This is not a comprehensive WCAG conformance audit or production player verification.

The full **145-URL `design-0.4.0`** run has zero recorded field differences from `phase-1c-integrations`, no PHP notices, and the same four unaccepted feed hashes plus four expected API hashes against the untouched baseline. It is still **not green**. Those measurements preceded the current owner-approved Git publication checkpoint. No site synchronization or deployment was performed by the agent.

### Earlier 0.3.4 milestone (historical)

Theme version **0.3.4** adds category/event title bands and the legacy first-page spotlight split through core blocks and in-memory slices of the existing results. Forty-eight archive checks and the previous 117 checks pass (165 total); 14 targeted HTTP samples retain baseline status/query contracts, rendered video counts and the normalized event-feed body. No new full route run was performed for this presentation-only milestone. Version 0.3.3 added block fallback to the remaining theme-owned GUID consumers and general archive/page/search/404 templates. The 145-URL `phase-1c-integrations` run reduces unaccepted differences from 27 to **4 feed body hashes**, plus the same four expected API hashes; no PHP notices. Endpoint responses and GUID redirects now match the original baseline. API payload review also records block file data and removal of the deleted local MP4 reference. The baseline and expected allowances are unchanged, so regression remains **not green**. General templates and the new category/event layouts received targeted HTTP and desktop/mobile checks after that full run; special WordCampTV featured/event-menu parity and forms/admin remain unfinished. No stored content, plugins, remote site or current wptv3 tokens changed.

## Local tooling safety

The route helper requires a loaded Studio runtime, the local environment and an explicit test-request header. Eight isolated negative checks last passed, including requests with the header outside Studio. Ordinary browsing does not use test doubles. The separate guard patched into the Studio-managed loader is **not durable**: Studio can regenerate it during development/CLI operations, not only restarts/updates. Recheck that file immediately before every sync; do not assume both installed files are still protected. See [route test environment safety](../tests/routes/README.md#environment-safety).

## Releases

The work ships in two releases. Phases below map to them.

### Release 1: block theme with current functionality (Phases 0 and 1)

Purpose: a **quick, low-risk test** on the live site. A block theme with the new design that is **as faithful as possible to the current production behavior**: same features, same routes, same editorial workflow as `wptv2`, plus a first test of videos embedded as **VideoPress blocks** instead of `[wpvideo]` shortcodes. From there, features are replaced step by step.

The local site mirrors the test site, an Atomic site outside the multisite where some production features (such as the direct VideoPress upload) do not work. Production behavior is reproduced from the `wptv2` code; see [wpcom-constraints.md](wpcom-constraints.md#environments).

- Functional parity: every route, endpoint, feed, form and admin tool keeps working as today.
- The submission form keeps creating `pending` posts with a `[wpvideo <guid>]` shortcode and the `_wptv_submitted_video` custom fields; moderators keep the per-field **Approve** review box. No workflow changes.
- **Block video test:** the theme supports posts whose video is a VideoPress block, in addition to shortcode posts. In production a moderator converts **a few existing posts by hand** (shortcode → block) and checks them. The form is not switched to blocks in this release.
- Design: keep the accepted functional structure; replace the interim wptv3 presentation with the owner-approved WordPress.org visual language under the current design priority.
- Rollout: WordPress.com **test site first**, then production. `wptv2` stays installed so production can switch back immediately.

### Release 2: new submission workflow and migration (Phase 2)

Purpose: remove the manual copying that moderators do today. Starts only after release 1 passes its production test.

- Front-end submission form that writes values straight to their real fields (taxonomies, excerpt, meta) and embeds the video as a VideoPress block. Moderators only review and publish.
- Field assistance to prevent inconsistent data, for example **AJAX autocomplete** of existing speakers as the user types.
- **Migration script** that converts the existing `[wpvideo]` shortcodes to VideoPress blocks.
- Further design polish and the agreed [backlog](backlog.md) items. The initial WordPress.org visual-language implementation is now the current owner priority, not blocked on Design team consultation.

## Phase 0: Discovery ✅ (open questions pending)

| Task | Status |
|---|---|
| Inventory the local site (themes, plugins, taxonomies, permalinks, content counts) | ✅ |
| Analyze `wptv2` (templates, `functions.php`, bundled "plugins", page templates) | ✅ See [legacy-wptv2.md](legacy-wptv2.md) |
| Build the route inventory | ✅ See [routes.md](routes.md) |
| Review `wptv3` (proof of concept) for reusable parts | ✅ See [legacy-wptv3.md](legacy-wptv3.md) |
| Set up the project documentation and repository | ✅ |
| Resolve the open questions that block Phase 1 | ⏳ See [open-questions.md](open-questions.md) |
| Write the regression test URL list and capture the `wptv2` baseline locally | ✅ 145 URLs, deterministic. See [`tests/routes/`](../tests/routes/README.md) |

## Phase 1: Route-identical theme (release 1) 🔄

### 1a. Foundation and routing ✅

- ✅ Theme skeleton: `style.css`, `theme.json`, minimal `functions.php` that only loads `inc/`, placeholder `index.html` with header/footer parts.
- ✅ Taxonomies registered with the **exact** same arguments as `wptv2` (`inc/taxonomies.php`).
- ✅ `pre_get_posts` with the same `posts_per_page` values per view (`inc/query.php`).
- ✅ Redirects: `/upload`, `/category/social-learning*` (`inc/redirects.php`), `?guid=` (`inc/endpoints/unisubs.php`).
- ✅ Endpoints: `/oembed/`, `/api/`, `/unisubs/` (`inc/endpoints/`).
- ✅ `<head>` output: title parts, MRSS filters and excerpt slides (`inc/content.php`), archive canonical, prev/next, JSON-LD (`inc/head.php`), Open Graph/Twitter tags (`inc/open-graph.php`), oEmbed discovery.
- ✅ `?flush_rules=1` for super admins (`inc/maintenance.php`).
- ✅ Publishing hooks (`inc/publishing.php`), attachment title sync (`inc/attachments.php`).
- **Exit criterion:** ✅ met. 141/145 identical, 4 expected differences, no PHP notices.

### 1b. Templates and blocks 🔄

Design level: accepted 0.3.4 functional structure with the owner-approved WordPress.org presentation implemented locally in 0.4.0; owner visual review and production checks remain.

- ✅ WordPress.org palette/type/spacing presets and bundled Inter/EB Garamond; body-color preset `charcoal-1`. The old `ink` preset and temporary migration aliases are retired.
- ✅ Header and footer as hidden theme patterns; provisional Navigation links.
- ✅ Design lockdown (`inc/lockdown.php`): no Site Editor for anyone, theme files win over database copies, user global styles and Additional CSS ignored, no custom design controls.
- ✅ Front page: latest video with two hero buttons → Featured → inherited recent videos → categories → events → Get involved (`patterns/front-page.php`, `inc/block-queries.php`). Existing query contracts and 11 checks in `tests/blocks/front-page.php` remain unchanged; tablet 1:1 adaptation is owner-confirmed.
- ✅ VideoPress thumbnail filter with attachment and placeholder fallbacks.
- ✅ Single video: hidden `wptv/single` pattern and thin `single.html` wrapper. Core title, speakers, player, full description with slides link, published date and terms, native Jetpack likes/sharing, comments and reply form, previous/next, breadcrumbs, CC BY-SA. 52/52 pattern blocks and 3/3 template blocks valid; desktop/mobile review on shortcode and block posts, existing comment thread checked. No stored post content changed.
- ✅ General page, archive, search and 404 templates, with a shared inherited core video grid. 19 read-only checks; templates and rendered patterns validated; desktop/mobile review and nine HTTP samples preserve statuses and query page sizes. Other taxonomies retain the general archive fallback.
- ✅ Category/event templates and hidden spotlight pattern: first-page, more-than-six-results 1 + 5 + rest split; native term title/breadcrumbs and category descriptions; later pages retain the full grid. In-memory isolated clones preserve the main query and pagination, including core's by-reference posts global. Forty-eight checks pass; templates/patterns validated and category/event desktop/mobile review completed. Local descriptions are empty; review real production descriptions separately.
- ✅ Owner-approved replacement of `category-wordcamptv`, implemented locally in 0.4.1: native 1 + 5 video introduction, automatic recorded-date event sections and name/year directory, using documented transients. Legacy featured/tag/menu/sidebar restoration is explicitly superseded, not required for parity. Local visual review and real test-site data/cold-cache verification remain next.
- ✅ PHP-only `wptv/producer` and `wptv/video-downloads` (`supports.autoRegister`, `inc/video-blocks.php`). Legacy producer name/profile rules, MP4 quality labels, HLS exclusion, original/OGG fallback and TTML/subtitle contribution URLs; all multisite-only calls guarded. 18 checks in `tests/blocks/single.php` and 12 isolated API simulations in `tests/blocks/single-multisite.php --skip-wordpress`. These simulations do not replace production verification. Block GUID support remains Phase 1c.
- ⏳ Desktop and mobile check of every template (functional, not pixel polish).

### 1c. VideoPress block support 🔄

Posts may embed the video either as `[wpvideo <guid>]` or as a VideoPress block. Both must behave the same.

- ✅ `get_guid()` reads the current `videopress/video` and earlier `jetpack/videopress` block names, including nested blocks; legacy last-shortcode behavior and shortcode precedence are preserved.
- ✅ Thumbnail lookup retains the production API and falls through to the standalone plugin poster resolver on Atomic/Studio. Featured images and the placeholder remain fallbacks. 27 deterministic checks, 8 multisite simulations and a missing-plugin smoke check pass. All 12 real homepage posters return HTTP 200 with JPEG content; desktop/mobile DOM and screenshots reviewed. The owner subsequently synchronized the site and confirmed that thumbnails work on Atomic. Real multisite verification remains pending; this confirmation does not complete the broader Atomic rollout checklist.
- ✅ Route-test APIs and thumbnail suppression now require an explicit request header from `run.sh`; ordinary Studio browsing uses real VideoPress data.
- ✅ Local theme-consumer audit and block fallback: oEmbed/discovery, unisubs/GUID lookup, API, Open Graph and downloads/subtitles now support block-only content while retaining each legacy first-shortcode parser. JSON-LD already uses shared helpers; theme MRSS reads attachments, not shortcode text. 22 integration simulations and a missing-production-API smoke check pass.
- ⏳ Verify real built-in multisite APIs, Open Graph, JSON-LD, MRSS/player output, downloads and subtitle tracks on production; local doubles are not production evidence.
- **Regression warning:** `phase-1c-integrations` covers 145 URLs after the conversion and media cleanup. Four unaccepted feed body hashes remain (single, language, main and rss2 query var), down from 27 fields across 11 URLs. oEmbed/unisubs/GUID redirects now match. Four pre-existing expected API hashes remain, with reviewed block-file/removed-MP4 fixture changes as well as existing year/location differences. No PHP notices. Keep the baseline and expected allowances untouched; do not call this green or normalize away feed content/build-date differences. See the latest [decision](decisions.md#phase-1c-integrations-and-general-phase-1b-templates).
- The single template renders the player from post content in both cases; no duplicate player.
- Shortcode posts keep producing exactly the same output (route test unchanged).
- Local fixtures: two or three posts converted by hand to the block markup that WordPress.com saves. Checks in `tests/blocks/` comparing helper output for the shortcode and block versions of the same video.
- Production test: a moderator converts a few existing posts by hand and checks player, thumbnail, single page, `/api/`, `/oembed/` and feeds.

### 1d. Forms and admin (parity) ⏳

- Anonymous video upload (`/submit-video/`) and subtitle upload (`/subtitle/`): form blocks plus the existing `admin-post.php` handlers, unchanged in behavior. The form still saves `[wpvideo]` and `_wptv_submitted_video`.
- Hardcoded IDs in the legacy handler (for example the WordCampTV category `12784353`) are resolved by slug.
- Admin: video review box with the per-field **Approve** buttons, subtitle moderation, slides URL field, event term meta.

### 1e. Verification and rollout ⏳

1. Full route regression run locally against the `wptv2` baseline.
2. Activate on the **test site** (https://wptv.nilovelez.com/, Atomic). Checklist: routes sample, front page, single (shortcode and block posts), archives, search, feeds, `/api/`, `/oembed/`, `/unisubs/`, submission form rendering and validation, subtitle form, review box, design lockdown, PHP error log. Multisite-only features must fail gracefully here (no fatal errors, sections hidden).
3. Deploy to **production** through the WP.com employee and activate in an agreed window. Repeat the checklist, plus the **production-only** items: video submission end to end (VideoPress upload and GUID), thumbnails, downloads, subtitles, video data in JSON-LD and Open Graph. Rollback: reactivate `wptv2` (kept installed) and flush rewrites.
4. Production block test: a few posts converted by hand (see 1c).
5. **Exit criterion:** release 1 runs in production with no regressions reported during the agreed observation period.

## Phase 2: New workflow and improvements (release 2) ⏳

Detailed after release 1 passes. Route changes are allowed only through [backlog.md](backlog.md) and [decisions.md](decisions.md), with a new baseline or documented expected differences.

### 2a. Submission form that fills the real fields

Field-by-field specification: [submission-form.md](submission-form.md).

- The form writes each value to its real destination: title (max 100 characters), `speakers`, `event`, `language` and `producer-username` terms, locations, categories, year, excerpt (description), slides URL meta, WordCampTV flag. All rules are validated server-side.
- The uploaded video is embedded as a **VideoPress block**; its poster becomes the featured image.
- **Autocomplete** for speakers and events through the core REST API; new speakers and events are created when the moderator approves. Language, location and category only accept existing terms.
- Producer username: optional, a single value, validated against WordPress.org.
- Location and Year move out of categories into internal taxonomies with no public URLs; Year is assigned from Date recorded (B5 in [backlog.md](backlog.md)).
- Submitter data (name, email, IP, submission date) stays private for moderators.
- The per-field Approve review box is replaced by a read-only submission summary. Moderators review, correct if needed, and publish.
- Spam and abuse protection kept at least at the current level.

### 2b. Shortcode-to-block migration

- Script that converts every `[wpvideo <guid> ...]` in post content to the equivalent VideoPress block, keeping all shortcode attributes that the block supports.
- Batched, idempotent and resumable; dry-run mode with a report; logs every changed post; the previous content stays recoverable (revisions or stored original).
- Run on the test site first, then on production by a WP.com employee.
- Shortcode support in the theme helpers stays as a fallback for anything not migrated.

### 2c. Other improvements

- Fine design polish and the WordPress.org visual language (documentation pending from the project owner).
- Backlog items (legacy bug fixes, retiring `producer` and `flavor` with 301s).
- Retire obsolete features (for example `/unisubs/`, if confirmed unused).
- Performance, accessibility and SEO review.
