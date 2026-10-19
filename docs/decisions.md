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
