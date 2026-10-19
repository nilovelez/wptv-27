# Project plan

Status legend: ✅ done · 🔄 in progress · ⏳ pending · ⛔ blocked

## Goal

Replace the legacy classic theme `wptv2` on WordPress.tv with a new block theme, `wptv-27`, that:

- keeps every existing route working exactly as before (Phase 1),
- replaces hand-written legacy code with WordPress core features, core blocks and small, well-scoped custom code,
- is fully editable with the Site Editor,
- later adopts the WordPress.org visual language (Phase 2).

## Phase 0: Discovery 🔄

| Task | Status |
|---|---|
| Inventory the local site (themes, plugins, taxonomies, permalinks, content counts) | ✅ |
| Analyze `wptv2` (templates, `functions.php`, bundled "plugins", page templates) | ✅ See [legacy-wptv2.md](legacy-wptv2.md) |
| Build the route inventory | ✅ See [routes.md](routes.md) |
| Review `wptv3` (proof of concept) for reusable parts | ⏳ |
| Set up the project documentation and repository | 🔄 |
| Resolve the open questions that block Phase 1 | ⏳ See [open-questions.md](open-questions.md) |
| Write the regression test URL list and capture the `wptv2` baseline locally (HTTP status, final URL, canonical, `<title>`, result counts, endpoint payloads) | ⏳ Needs approval to switch the local site to `wptv2` temporarily |

## Phase 1: Route-identical theme ⏳

### 1a. Foundation and routing

- Theme skeleton: `style.css`, `theme.json`, minimal `functions.php` that only loads `inc/`.
- Taxonomies registered with the **exact** same arguments as `wptv2` (`producer`, `producer-username`, `speakers`, `flavor`, `language`, `event`).
- `pre_get_posts` with the same `posts_per_page` values per view.
- Redirects (`/upload`, `/category/social-learning*`, `?guid=`).
- Endpoints: `/oembed/`, `/api/`, `/unisubs/`.
- `<head>` output: title parts, archive canonical, prev/next, JSON-LD, Open Graph/Twitter tags.
- Publishing hooks: `wptv_post_views` initialization, stats bump, attachment title sync, MRSS filters, upload space.
- **Exit criterion:** the regression test passes against the `wptv2` baseline for all non-visual checks.

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

To be detailed once Phase 1 is accepted. Candidates:

- Adopt the WordPress.org visual language (documentation pending from the project owner).
- Retire obsolete features (for example `/unisubs/`, if confirmed unused).
- Performance, accessibility and SEO review.
