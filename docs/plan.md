# Project plan

Status legend: ✅ done · 🔄 in progress · ⏳ pending · ⛔ blocked

## Goal

Replace the legacy classic theme `wptv2` on WordPress.tv with a new block theme, `wptv-27`, that:

- keeps every existing route working exactly as before (Phase 1),
- replaces hand-written legacy code with WordPress core features, core blocks and small, well-scoped custom code,
- is fully editable with the Site Editor,
- later adopts the WordPress.org visual language (Phase 2).

## Phase 0: Discovery ✅ (open questions pending)

| Task | Status |
|---|---|
| Inventory the local site (themes, plugins, taxonomies, permalinks, content counts) | ✅ |
| Analyze `wptv2` (templates, `functions.php`, bundled "plugins", page templates) | ✅ See [legacy-wptv2.md](legacy-wptv2.md) |
| Build the route inventory | ✅ See [routes.md](routes.md) |
| Review `wptv3` (proof of concept) for reusable parts | ✅ See [legacy-wptv3.md](legacy-wptv3.md) |
| Set up the project documentation and repository | ✅ |
| Resolve the open questions that block Phase 1 | ⏳ See [open-questions.md](open-questions.md) |
| Write the regression test URL list and capture the `wptv2` baseline locally (HTTP status, final URL, canonical, `<title>`, result counts, endpoint payloads) | ✅ 145 URLs, deterministic (two `wptv2` runs match). See [`tests/routes/`](../tests/routes/README.md) |

## Phase 1: Route-identical theme ⏳

### 1a. Foundation and routing ✅

- ✅ Theme skeleton: `style.css`, `theme.json`, minimal `functions.php` that only loads `inc/`, placeholder `index.html` with header/footer parts.
- ✅ Taxonomies registered with the **exact** same arguments as `wptv2` (`inc/taxonomies.php`).
- ✅ `pre_get_posts` with the same `posts_per_page` values per view (`inc/query.php`).
- ✅ Redirects: `/upload`, `/category/social-learning*` (`inc/redirects.php`), `?guid=` (`inc/endpoints/unisubs.php`).
- ✅ Endpoints: `/oembed/`, `/api/`, `/unisubs/` (`inc/endpoints/`).
- ✅ `<head>` output: title parts, MRSS filters and excerpt slides (`inc/content.php`), archive canonical, prev/next, JSON-LD (`inc/head.php`), Open Graph/Twitter tags (`inc/open-graph.php`), oEmbed discovery.
- ✅ `?flush_rules=1` for super admins (`inc/maintenance.php`).
- ✅ Publishing hooks: `wptv_post_views` initialization, stats bump, upload space (`inc/publishing.php`), attachment title sync (`inc/attachments.php`, loaded on every site as in `wptv2`). Checked with a one-off WP-CLI script (5/5 cases).
- **Exit criterion:** the regression test passes against the `wptv2` baseline for all non-visual checks. ✅ Met: 141/145 identical, 4 expected differences (see `tests/routes/expected-differences.tsv`), no PHP notices.

### 1b. Templates and blocks

- Templates: front page, single, page, archive, category, `category-wordcamptv`, taxonomy (event and the others), search, 404.
- Template parts: header, footer, video meta sidebar.
- Custom dynamic blocks only where core cannot do the job (breadcrumbs, producer credit, video downloads/subtitles).
- Query Loop variation for "Popular Videos".
- Design based on `wptv3` as an interim reference.

### 1c. Forms, admin and verification

- Anonymous video upload (`/submit-video/`) and subtitle upload (`/subtitle/`): dynamic form blocks plus the existing `admin-post.php` handlers.
- Admin: video review box, subtitle moderation, slides URL field, event term meta.
- Full regression run and manual review on the WordPress.com test site.

## Phase 2: Improvements ⏳

To be detailed once Phase 1 is accepted. Agreed work is tracked in [backlog.md](backlog.md) (legacy bug fixes, retiring the `producer` and `flavor` taxonomies with 301s). Other candidates:

- Adopt the WordPress.org visual language (documentation pending from the project owner).
- Retire obsolete features (for example `/unisubs/`, if confirmed unused).
- Performance, accessibility and SEO review.
