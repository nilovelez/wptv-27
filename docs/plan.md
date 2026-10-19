# Project plan

Status legend: ✅ done · 🔄 in progress · ⏳ pending · ⛔ blocked

## Goal

Replace the legacy classic theme `wptv2` on WordPress.tv with a new block theme, `wptv-27`, that:

- keeps every existing route working exactly as before (Phase 1),
- replaces hand-written legacy code with WordPress core features, core blocks and small, well-scoped custom code,
- keeps post/page content editable while the design is managed only in theme code (no Site Editor access),
- moves video submission to VideoPress blocks and a front-end form that fills the real fields, so moderators only review and publish (Phase 2),
- later adopts the WordPress.org visual language (Phase 2).

## Releases

The work ships in two releases. Phases below map to them.

### Release 1: block theme with current functionality (Phases 0 and 1)

Purpose: a **quick, low-risk test** on the live site. A block theme with the new design that is **as faithful as possible to the current production behavior**: same features, same routes, same editorial workflow as `wptv2`, plus a first test of videos embedded as **VideoPress blocks** instead of `[wpvideo]` shortcodes. From there, features are replaced step by step.

The local site mirrors the test site, an Atomic site outside the multisite where some production features (such as the direct VideoPress upload) do not work. Production behavior is reproduced from the `wptv2` code; see [wpcom-constraints.md](wpcom-constraints.md#environments).

- Functional parity: every route, endpoint, feed, form and admin tool keeps working as today.
- The submission form keeps creating `pending` posts with a `[wpvideo <guid>]` shortcode and the `_wptv_submitted_video` custom fields; moderators keep the per-field **Approve** review box. No workflow changes.
- **Block video test:** the theme supports posts whose video is a VideoPress block, in addition to shortcode posts. In production a moderator converts **a few existing posts by hand** (shortcode → block) and checks them. The form is not switched to blocks in this release.
- Design: **functional `wptv3`**. Every template has the `wptv3` structure and base styles; fine visual polish moves to release 2.
- Rollout: WordPress.com **test site first**, then production. `wptv2` stays installed so production can switch back immediately.

### Release 2: new submission workflow and migration (Phase 2)

Purpose: remove the manual copying that moderators do today. Starts only after release 1 passes its production test.

- Front-end submission form that writes values straight to their real fields (taxonomies, excerpt, meta) and embeds the video as a VideoPress block. Moderators only review and publish.
- Field assistance to prevent inconsistent data, for example **AJAX autocomplete** of existing speakers as the user types.
- **Migration script** that converts the existing `[wpvideo]` shortcodes to VideoPress blocks.
- Fine design polish, WordPress.org visual language and the agreed [backlog](backlog.md) items.

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

Design level: functional `wptv3` (structure and base styles; no fine polish).

- ✅ Visual tokens and bundled Inter/EB Garamond fonts; body-color preset `ink`.
- ✅ Header and footer as hidden theme patterns; provisional Navigation links.
- ✅ Design lockdown (`inc/lockdown.php`): no Site Editor for anyone, theme files win over database copies, user global styles and Additional CSS ignored, no custom design controls.
- ✅ Front page: latest video, Featured, recent events, video categories, paginated recent videos (`patterns/front-page.php`, `inc/block-queries.php`). 11 checks in `tests/blocks/front-page.php`.
- ✅ VideoPress thumbnail filter with attachment and placeholder fallbacks.
- ⏳ Single video: title, player, description, video details (terms, producer, downloads, CC license), likes/sharing, comments, previous/next, breadcrumbs.
- ⏳ Remaining templates: page, archive, category, `category-wordcamptv`, taxonomy (event hero and the others), search, 404.
- ⏳ PHP-only blocks (`supports.autoRegister`) only where core cannot do the job: producer credit, video downloads/subtitles.
- ⏳ Desktop and mobile check of every template (functional, not pixel polish).

### 1c. VideoPress block support ⏳

Posts may embed the video either as `[wpvideo <guid>]` or as a VideoPress block. Both must behave the same.

- Extend the video helpers (`inc/video.php`, `get_guid()`) to read the GUID from the VideoPress block as well as from the shortcode. Everything that depends on the GUID then works for both: thumbnails, JSON-LD, Open Graph, `/api/`, `/oembed/`, MRSS, downloads and subtitles.
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
