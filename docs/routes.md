# Route inventory

**Rule:** in Phase 1, every route below must behave exactly as it does with `wptv2`: same HTTP status, same final URL after redirects, same canonical URL, same pagination boundaries, same payload for machine-readable endpoints.

Permalink structure: `/%year%/%monthnum%/%day%/%postname%/`

## Core routes

| Route | Notes |
|---|---|
| `/` | Front page shows the latest posts (`show_on_front = posts`). 8 posts per page. |
| `/page/N/` | Front page pagination (8 per page). |
| `/YYYY/MM/DD/post-name/` | Single video (post). |
| `/YYYY/`, `/YYYY/MM/`, `/YYYY/MM/DD/` | Date archives, 10 per page. |
| `/<page-slug>/` | Pages: `about`, `get-involved`, `submit-video`, `subtitle` (production only), and others. |
| `/category/<slug>/` | Hierarchical, for example `/category/year/2024/` or `/category/location/<city>/`. 22 per page. |
| `/category/wordcamptv/` | Special landing page (`category-wordcamptv.php` in `wptv2`). |
| `/tag/<slug>/` | 10 per page. |
| `/type/<format>/` | Post format archives. |
| `/author/<nicename>/` | Author archives, 10 per page. |
| `/?s=<query>` | Search, 10 per page. |
| `/feed/`, `/<archive>/feed/`, `/comments/feed/` | RSS. WordPress.com adds MediaRSS (filtered by the theme). |
| `/wp-json/wp/v2/speakers`, `/language`, `/event` | REST bases declared by the taxonomies. |

## Theme taxonomies

All are attached to `post`. The rewrite slug equals the taxonomy name.

| Taxonomy | Route | REST base | Per page |
|---|---|---|---|
| `event` | `/event/<slug>/` | `event` | 22 |
| `speakers` | `/speakers/<slug>/` | `speakers` | 10 |
| `language` | `/language/<slug>/` | `language` | 10 |
| `producer` | `/producer/<slug>/` | none | 10 |
| `producer-username` | `/producer-username/<slug>/` | none | 10 |
| `flavor` | `/flavor/<slug>/` | none | 10 |

## Pagination (`posts_per_page`)

Set in `pre_get_posts` for the main query, and only when `posts_per_page` is not already set:

| View | Per page |
|---|---|
| Front page | 8 |
| Category archive | 22 |
| `event` taxonomy archive | 22 |
| Any other archive (tags, other taxonomies, dates, authors) and search | 10 |
| Everything else | 22 |

Changing these values changes which `/page/N/` URLs exist, so they are part of the route contract.

## Custom endpoints

| Route | Query var | Behavior |
|---|---|---|
| `/oembed/?url=<permalink>&format=json\|xml&maxwidth=&maxheight=` | `oembed` | Custom oEmbed provider. The theme also prints discovery `<link>` tags on single posts that point here. |
| `/api/<method>.<format>` | `wptvapi` | Legacy JSON API. Methods: `videos`, `events`, `speakers`, `languages`, `tags`, `categories`. Formats: `json`, `array`. Accepts `posts_per_page` and the taxonomy query vars (for example `/api/videos.json?event=<slug>`). |
| `/unisubs/?guid=<guid>` or `/unisubs/?url=<permalink>` | `unisubs`, `guid` | Video data for Amara/Universal Subtitles. `redirect_to_swf` and `format` parameters. |
| `/<anything>?guid=<videopress-guid>` | `guid` | Redirects to the post that embeds that VideoPress video. Works on any URL. |

## Redirects

Only run on 404 requests (`template_redirect`, priority 1):

| From | To | Status |
|---|---|---|
| `/upload` (with or without slashes) | `/submit-video/` | 301 |
| `/category/social-learning…` | `/category/learn-wordpress-online-workshops…` (rest of the path kept) | 301 |

## Form handlers

| Route | Action | Notes |
|---|---|---|
| `/submit-video/` | Page using the "Anon video upload" page template | Form posts to `admin-post.php`. Reads `error`, `success` and prefill `GET` parameters. |
| `/subtitle/?video=<attachment-id>` | Page using the "Upload Subtitles" page template | Reads `error` and `success`. |
| `/wp-admin/admin-post.php` `action=wptv_video_upload` | Logged-in and anonymous users | Redirects back to `/submit-video/`. Supports `?xhr`. |
| `/wp-admin/admin-post.php` `action=wptv_video_upload_subtitles` | Logged-in and anonymous users | Redirects back to `/subtitle/`. |
| `/wp-admin/admin-ajax.php` `action=wptv_get_subtitles_nonce` | Logged-in and anonymous users | Returns a nonce for the subtitle form. |

## Maintenance

| Route | Notes |
|---|---|
| `/?flush_rules=1` | Flushes rewrite rules for super admins (and one hardcoded user ID). Whether to keep it is an open question. |

## Regression test

✅ Baseline captured in Phase 0. Tooling and instructions: [`tests/routes/`](../tests/routes/README.md).

- 145 URLs generated from real data. They cover every row above, plus pagination boundaries (page 2, last page, last page + 1), terms just over the page size, non-ASCII slugs, unknown terms, feeds, redirects, endpoints with valid and invalid input, and the core REST and oEmbed routes.
- Checks for each URL: HTTP status, `Location` (redirects are not followed), content type, main query data (`X-WPTV-Route` header from a local-only mu-plugin: 404 flag, found posts, page count, per page, paged, queried object), canonical, `<title>`, `rel="prev"` / `rel="next"`, and the normalized body of non-HTML responses.
- Baseline: `tests/routes/results/baseline-wptv2/` (103 × 200, 5 × 301, 2 × 302, 35 × 404). Two consecutive `wptv2` runs match exactly.
- The baseline belongs to the local database it was captured on. If the content changes, regenerate the URL list and capture it again.

Known limitation: WordPress.com-only VideoPress helpers are stubbed with fake, deterministic data, so file URLs, thumbnails, downloads and subtitles can only be fully checked on the WordPress.com test site.

Legacy quirks that are part of the Phase 1 contract and will be fixed later (see [backlog.md](backlog.md), B1 and B2): `/api/videos.*` sends JSON as `text/html`, and with `?guid=` the `/unisubs/` `permalink` field points to the latest post.

The `producer` and `flavor` routes stay unchanged in Phase 1. They will be retired later with 301 redirects (backlog B3).
