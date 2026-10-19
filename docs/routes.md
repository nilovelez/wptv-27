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

⏳ To do in Phase 0. The test will request a fixed list of representative URLs (at least one per row above, including paginated and edge cases) with `wptv2` active, store the results as the baseline, and compare `wptv-27` against it.

Checks for each URL: HTTP status, final URL after redirects, `<link rel="canonical">`, `<title>`, `rel="prev"` / `rel="next"`, number of posts listed, and the response body for JSON/XML endpoints.

Known limitation: WordPress.com-only VideoPress functions are not available locally, so video-dependent output (thumbnails, downloads, subtitles, JSON-LD video data, Open Graph video tags) can only be fully checked on the WordPress.com test site.
