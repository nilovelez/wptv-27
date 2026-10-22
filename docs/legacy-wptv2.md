# Legacy theme analysis: `wptv2`

`wptv2` is the classic theme currently running on WordPress.tv. It is installed locally at `wp-content/themes/wptv2` for reference. **Do not edit it.**

## File map

| File | Role |
|---|---|
| `functions.php` | `WordPressTV_Theme` class (setup, taxonomies, queries, head output, video helpers), `WordCampTV_Walker_Nav_Menu`, enqueue, misc filters |
| `header.php` / `footer.php` | Markup, GTM, Typekit, feed links, search form, menus |
| `front-page.php` | Featured video, WordCampTV featured, latest and popular videos |
| `category.php` | Category archive with a "hero" built from the first 6 posts on page 1 |
| `category-wordcamptv.php` | WordCampTV landing page: featured videos plus the "Featured WordCamps" menu rendered by a custom walker |
| `taxonomy-event.php` | Event archive, same hero pattern as `category.php` |
| `archive.php` | Generic archive (dates, tags, authors, other taxonomies), also used by `search.php` |
| `single.php` + `sidebar-single.php` | Single video: video player, comments, metadata sidebar (terms, downloads, subtitles, producer, license) |
| `page.php`, `404.php`, `index.php` | Pages, not found, fallback |
| `breadcrumbs.php`, `pagination.php`, `comments.php` | Partials |
| `sidebar*.php` | Resources (Links manager) plus CC license, dynamic sidebars |
| `anon-upload-template.php` | Page template "Anon video upload" (`/submit-video/`): CSS, JS and form inlined |
| `upload-subtitles-template.php` | Page template "Upload Subtitles" (`/subtitle/`) |
| `plugins/rewrite.php` | `?flush_rules=1` |
| `plugins/wordpresstv-oembed/` | `/oembed/` provider and discovery tags |
| `plugins/wordpresstv-rest/` | `/api/` legacy JSON API |
| `plugins/wordpresstv-unisubs/` | `/unisubs/` endpoint and global `?guid=` redirect |
| `plugins/wordpresstv-anon-upload/` | Upload handler (`admin-post.php`) and admin review box |
| `plugins/wordpresstv-upload-subtitles/` | Subtitle upload handler, AJAX nonce, admin moderation |
| `plugins/wordpresstv-open-graph/` | Twitter player card and Open Graph video tags (Jetpack filter) |
| `plugins/wordpresstv-event-meta/` | Event term meta fields |
| `inc/wpcom.php` | Loads a VIP widget. **Not included anywhere, dead code.** |

`setup()` only runs when the `wptv_setup_theme` filter returns true. `setup_always()` always runs. This suggests the theme is also used on blog.wordpress.tv with part of the features disabled (open question).

## Data model

| Item | Details |
|---|---|
| Videos | Regular `post` entries. The video is a `[wpvideo <guid>]` shortcode in the content (4036 of 4046 local posts). |
| Taxonomies | `category` (218, with `year` and `location` parent branches), `post_tag` (571), `event` (127), `speakers` (1029), `language` (21), `producer` (18), `producer-username` (139), `flavor` (10) |
| Post meta | `wptv_post_views` (popularity, from stats), `_wptv_slides_url`, `_wptv_submitted_video` (anonymous upload flag) |
| Term meta (`event`) | `youtube_playlist_id`, `hashtag`, `owners`, `owners_slack` |
| Menus | `primary`, `footer`, `featured_wordcamps` (empty in the local copy) |
| Links manager | 10 bookmarks, shown as "Resources" in the sidebar |
| Hardcoded IDs | Category parents `91093` (Year) and `6418` (Location) in the API, user `4637740` in `rewrite.php`. Local IDs differ. |

## Replacement map

Legend: **Core** = WordPress core / core block · **Keep** = same logic, cleaned up · **Block** = small custom dynamic block · **Drop** = removed · **Pending** = not ported yet

Updated on October 22, 2026 against the implementation; the full audit is in [review-parity.md](review-parity.md).

### Theme setup and head

| `wptv2` | `wptv-27` | Type |
|---|---|---|
| `add_theme_support( 'title-tag' )` + `document_title_parts` ("Videos in…", "Videos from…", "Videos of…", "… Videos", "Videos tagged…") | Same filter | Keep |
| `rel_canonical()` for front page and term archives (core only does singular) | Same logic | Keep |
| `archive_link_rel_prev_next()` | Same logic (pending decision) | Keep |
| `json_ld()` VideoObject | Same output, data from VideoPress helpers | Keep |
| Open Graph / Twitter player tags | Same Jetpack filter | Keep |
| Manual RSS `<link>` tags | Core automatic feed links for the site feed; the blog feed link is dropped (blog.wordpress.tv is closing; see [decisions](decisions.md)) | Core |
| GTM inline script | Not implemented; depends on open question 11 | Pending |
| Typekit | Local fonts declared in `theme.json` | Core |
| IE conditional comments, `ie6.css`, `profile` / `pingback` links | none | Drop |
| `videopress_flash_params()` (Flash, jQuery) | none | Drop |
| `wptv_widont()` on `the_title` | CSS `text-wrap: pretty` | Drop |

### Content model and queries

| `wptv2` | `wptv-27` | Type |
|---|---|---|
| `register_taxonomies()` | Identical arguments, plus `show_in_rest` and `show_admin_column` where they do not affect routes | Keep |
| `posts_per_page()` | Same values (route contract) | Keep |
| `redirects()` | Same redirects | Keep |
| `register_nav_menus()` | Fixed Navigation links in `patterns/header.php`; owner-specified footer; automatic WordCampTV landing instead of `featured_wordcamps` | Core |
| `widgets_init()` | Template parts | Drop |
| Slides URL meta box + `wptv_excerpt_slides` | `register_post_meta()` (`show_in_rest`) + block-editor panel; the link is still appended through `get_the_excerpt` (`inc/content.php`) | Core |
| Event term meta form | Not ported: never used (no stored values); see [decisions](decisions.md) and backlog B14 | Drop |
| `publish_post` → `wptv_post_views = 0` | Same (the popular query depends on it) | Keep |
| `transition_post_status` → `bump_stats_extras()` | Same, guarded | Keep |
| `blog_upload_space` filter | Same | Keep |
| MRSS filters | Same | Keep |
| `wptv_update_attachment_titles()` | Same | Keep |
| `locale_to_wp_locale()` map | Same data | Keep |

### Templates and presentation

| `wptv2` | `wptv-27` | Type |
|---|---|---|
| Header search form | Search block (`name="s"`, action `/`) | Core |
| Front page: featured (sticky + tag `featured`) | Query Loop | Core |
| Front page: WordCampTV featured, latest | Query Loops | Core |
| Front page: popular (`wptv_post_views`, last 180 days) | Dropped; replaced by recent events and video categories (`core/terms-query`) | Drop |
| Category/event "hero" (first 6 posts, page 1 only) | Single inherited Query Loop, first items styled with CSS (keeps pagination intact) | Core |
| `WordCampTV_Walker_Nav_Menu` | Replaced by the automatic WordCampTV landing ([wordcamptv.md](wordcamptv.md)) | Drop |
| `archive.php` title switch | Query Title block | Core |
| `the_video()` (strips `[wpvideo]` and renders it separately) | Post Content block; player size/HD and surrounding text still to verify (D1, D2 in [review-parity.md](review-parity.md)) | Core |
| `the_video_image()` / `get_the_video_image()` | `post_thumbnail_html` / `has_post_thumbnail` filters → Post Featured Image block | Keep |
| Breadcrumbs (priority category + first event) | `core/breadcrumbs` with the owner's trail filter (`inc/setup.php`); no trail on taxonomy archives | Core |
| Sidebar: date, excerpt, event, speakers, tags, language | Post Date, Post Excerpt, Post Terms | Core |
| Sidebar: downloads and subtitles (VideoPress API) | `wptv/video-downloads` (PHP-only block) | Block |
| Sidebar: producer credit | `wptv/producer` (PHP-only block) | Block |
| CC license widget, Resources (Links manager) | CC license in the single-video details; Resources dropped (see [decisions](decisions.md)) | Core |
| `comments.php` + `list_comments()` | Comment blocks | Core |
| `pagination.php` | Query Pagination blocks | Core |
| Random footer word ("An Automattic Production / Joint / …") | Dropped; owner-specified footer | Drop |

### Endpoints, forms and admin

| `wptv2` | `wptv-27` | Type |
|---|---|---|
| `/oembed/` | Same endpoint, cleaned up | Keep |
| `/api/` | Same endpoint and payload | Keep |
| `/unisubs/` and `?guid=` redirect | Same (Phase 2 candidate for removal) | Keep |
| `?flush_rules=1` | `inc/maintenance.php`, site administrators (`edit_theme_options`); rules are also flushed on theme activation | Keep |
| Anonymous upload page template | `templates/page-submit-video.html` + `wptv/submission-form` | Block |
| Upload handler, admin review box | Same handler, cleaned up | Keep |
| Subtitle upload page template | Not ported; next delivery ([backlog.md](backlog.md)) | Pending |
| Subtitle handler, AJAX nonce, moderation | Not ported; next delivery | Pending |
| `wporg_username_exists()` | Same, prefixed | Keep |

## WordPress.com-only functions used

`video_get_info_by_guid`, `video_get_highest_resolution_image_url`, `video_url_by_format`, `video_highest_resolution_ogg`, `video_get_single_response`, `find_all_videopress_shortcodes`, `VideoPress_Subtitles`, `VideoPress_Subtitles_Track`, `bump_stats_extras`, `jetpack_is_mobile`, `mrss_*` filters. See [wpcom-constraints.md](wpcom-constraints.md).
