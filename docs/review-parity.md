# External review: feature parity with wptv2 and core replacements

Reviewer: external review session (Claude Code), October 22, 2026. Audit only: no code was changed.

**Method.** Every hook, template, plugin and helper in the `wptv2` SVN checkout (`functions.php`, 16 templates and partials, 7 bundled plugins, `inc/wpcom.php`) was listed and traced to `wptv-27` (`trunk` at `738a4a7`). For each feature the target code was read, and the result was checked against the decision log, backlog, open questions and handoff.

**Limits.** This is a code-reading audit. No WordPress.com, Atomic or imported-data behavior was observed: the reviewer's site is empty and has no Jetpack or VideoPress. Rendering and data differences were already covered by the route regression and focused suites recorded in [handoff.md](handoff.md); this report does not repeat or replace them. "Verify" below means the answer depends on production data or platform behavior.

## Summary

| Status | Count | Section |
|---|---|---|
| Migrated, complete | 39 | [1](#1-migrated-and-complete) |
| Migrated with a difference to verify | 6 | [2](#2-migrated-with-a-difference-to-verify) |
| Not migrated, by decision | 17 | [3.1](#31-by-decision) |
| Not migrated, pending | 5 | [3.2](#32-pending-tracked) |
| Not migrated, apparently overlooked | 4 (all resolved by the owner: not ported) | [3.3](#33-apparently-overlooked) |
| Core replacement opportunities | 8 | [4](#4-core-replacement-opportunities) |
| Documentation inconsistencies | 4 | [5](#5-documentation-inconsistencies) |

The six differences to verify ([2](#2-migrated-with-a-difference-to-verify)) need an owner decision or a developer task. The four overlooked items ([3.3](#33-apparently-overlooked)) are resolved. Everything else is either complete or already tracked.

## 1. Migrated and complete

The behavior matches `wptv2`. Any difference was owner-approved and is listed in the notes.

| Feature (`wptv2` source) | `wptv-27` | Notes |
|---|---|---|
| `wptv_setup_theme` switch / `setup_always()` (`functions.php`) | `functions.php` | Same split: setup, lockdown, video, head and attachments always load. |
| Taxonomies `producer`, `producer-username`, `speakers`, `flavor`, `language`, `event` | `inc/taxonomies.php` | Arguments copied verbatim (route contract); REST exposure added in `inc/editor.php`. |
| `posts_per_page()` per view | `inc/query.php` | Same values: 8 / 22 / 22 / 10 / 22. |
| `document_title_parts()` | `inc/content.php` | Same strings, guarded against non-term objects. |
| `rel_canonical()` for front page and term archives | `inc/head.php` | Same output. |
| `archive_link_rel_prev_next()` | `inc/head.php` | Same output; open question 19 decides whether to keep it. |
| `json_ld()` VideoObject | `inc/head.php` | Same fields, block GUID fallback. |
| `redirects()` (`social-learning`, `/upload`) | `inc/redirects.php` | Same. |
| `publish_post` → `wptv_post_views = 0` | `inc/publishing.php` | Same. |
| `transition_post_status` → `bump_stats_extras()` | `inc/publishing.php` | Guarded instead of a global stub. |
| `blog_upload_space` filter | `inc/publishing.php` | Same. |
| MRSS: `mrss_avatar_user`, `mrss_media` original file | `inc/content.php` | Same. |
| `wptv_excerpt_slides()` | `inc/content.php` | Same, including the untranslated "Presentation Slides" string. |
| `wptv_update_attachment_titles()` | `inc/attachments.php` | Same. |
| `locale_to_wp_locale()` | `inc/video.php` (`language_to_locale()`) | Same map. |
| GUID, details, image, file URLs, attachment helpers | `inc/video.php` | Shortcode first, block fallback. |
| Slides URL meta box + save | `inc/editor.php` (`register_post_meta`) + editor panel | Sanitized for http/https; `edit_post` required. |
| `wporg_username_exists()` | `inc/submissions.php` | Same endpoint and rules. |
| Open Graph / Twitter player plugin | `inc/open-graph.php` | Same tags, guarded. |
| `/oembed/` plugin and discovery links | `inc/endpoints/oembed.php` | Same contract (see R2 in [review-security.md](review-security.md)). |
| `/api/` plugin | `inc/endpoints/api.php` | Same payload; slug-based `year`/`location` (documented API allowance). |
| `/unisubs/` plugin and `?guid=` redirect | `inc/endpoints/unisubs.php` | Same, including the B2 permalink bug kept on purpose. |
| `rewrite.php` (`?flush_rules=1`) | `inc/maintenance.php` | Super admins only; the hardcoded user exception was dropped (decision). |
| Anonymous upload handler | `inc/submissions.php` | Same validation and storage; block content instead of shortcode (decision). |
| Anonymous upload page template and inline JS | `templates/page-submit-video.html`, `inc/submission-form.php`, `assets/js/submission-form.js` | See [submission-form.md](submission-form.md). |
| Submission review box (`dbx_post_sidebar`) with Approve buttons | `inc/submissions.php` + `assets/js/editor.js` | Moved to the block editor; shown regardless of author (decision). |
| Front page: lead video (newest `featured`, restricted to stickies) | `patterns/front-page.php` + `inc/block-queries.php` | Same selection. |
| Front page: latest videos (main query, 8) | `patterns/front-page.php` | Core enhanced pagination. |
| Category and event archives: 1 + 5 hero on page one when more than six results | `templates/category.html`, `taxonomy-event.html`, `inc/archives.php` | Same split and boundaries. |
| Single: date, excerpt, event, speakers, tags, language | `patterns/single.php` | Core Post Date / Excerpt / Terms. |
| Single: downloads (Low/Med/High MP4, HLS exclusion, Original, OGG) and TTML subtitles | `wptv/video-downloads` (`inc/video-blocks.php`) | Same rules, including the `/subtitle/?video=` link. |
| Single: producer credit | `wptv/producer` (`inc/video-blocks.php`) | Same link rules. |
| Single: CC BY-SA license | `patterns/single.php` | Kept in video details only (decision). |
| Comments on videos (`comments.php`, `list_comments()`) | Core Comments blocks in `patterns/single.php` | See difference D5. |
| Search, archive, index fallback, 404 | `templates/search.html`, `archive.html`, `index.html`, `404.html` | Core blocks; page size from `inc/query.php`. |
| Pagination partial | Core Query Pagination | Same page boundaries. |
| Breadcrumbs (`the_category()` priority + first event) | `core/breadcrumbs` + `inc/setup.php` | Owner policy adds `education`; no trail on taxonomy archives (decision `3f0eb78`). |
| Header search form (`name="s"`, action `/`) | Core Search block | Same request. |
| `title-tag`, comment-reply script, feed links | Core block-theme defaults | Site and comments feeds are automatic; see O2 for the blog feed. |

## 2. Migrated with a difference to verify

### D1. Shortcode player size and HD default

`wptv2`'s `the_video()` re-rendered each `[wpvideo]` with `w="940"` on singles and `w="575"` elsewhere, and set `hd` from the `video_player_high_quality` option when the shortcode lacked it. `wptv-27` renders the stored shortcode through Post Content without those overrides. On WordPress.com, the player width then follows the shortcode and `content_width`, and HD follows the platform default.

- **Check:** compare a shortcode video on Atomic or production under both themes, at single and archive spotlight widths.
- **Owner:** theme developers, if the width or quality differs.

### D2. Text around the shortcode in legacy posts

`the_video()` printed only the video, and the `remove_shortcodes` filter hid everything else in the content. Descriptions were shown from the excerpt. `wptv-27` prints the whole `post_content` in the player band. Legacy posts with text before or after the shortcode would show that text inside the dark player band.

- This is open question 14. Its answer decides whether a render filter is needed for shortcode posts.

### D3. Archive list items

`wptv2` listed each archive and search result with event links, speakers, date and excerpt. `wptv-27` uses the approved video card: thumbnail, speakers, title and date. The event names and excerpts are no longer shown.

- This is a design change. It is consistent with the approved cards, but it is not recorded as a decision for search and generic archives. **Check:** owner confirmation.

### D4. Archive headings

`wptv2` showed headings such as "'Tag' Videos", "Archive for {date}", "Author Archive" and "Taxonomy: term". `wptv-27`'s generic `archive.html` uses core Query Title with its default prefixes ("Tag: …", "Year: …", "Author: …"). Category and event templates hide the prefix. Document titles (`<title>`) are unchanged.

- **Check:** owner confirmation of the visible wording.

### D5. Pingbacks in comment lists

`wptv2`'s `list_comments()` skipped pingbacks. Core Comment Template lists every approved comment type.

- **Check:** whether imported videos contain approved pingbacks. If they do, add a decision or a small `comments_template_query_args` filter.

### D6. Header navigation targets

The `primary` menu became fixed Navigation links in `patterns/header.php`: WordCamp.tv, How-To, Learn WordPress and Get Involved. The production menu contents were never available (open question 13).

- `/category/learn-wordpress/` may not match the legacy redirect, which renames `social-learning` to `learn-wordpress-online-workshops`.
- **Check:** confirm that all four targets exist in production and return 200.

## 3. Not migrated

### 3.1. By decision

| `wptv2` feature | Decision |
|---|---|
| Front page "Popular videos" (`wptv_post_views`, 180 days) | Dropped; [decisions](decisions.md) "Drop Popular videos". Views meta still initialized. |
| Front page WordCampTV featured strip (`wordcamptv` + `featured`, 4) | Replaced by the five-section front page (Phase 1b, follows `wptv3`). |
| WordCampTV landing: featured query, `featured_wordcamps` menu, `WordCampTV_Walker_Nav_Menu` | Replaced by the automatic landing ([wordcamptv.md](wordcamptv.md)). |
| Widget areas (`widgets_init`, `sidebar-event.php`, `sidebar-wordcamptv.php`) | Dropped (legacy map; WordCampTV decision). |
| `footer` menu and random "An Automattic {word}" credit | Replaced by the owner-specified footer (answered "Footer structure"). |
| Typekit, IE conditional markup, `ie6.css`, `profile`/`pingback` links | Dropped (legacy map). |
| `videopress_flash_params()` | Dropped (Flash). |
| `wptv_widont()` | Replaced by CSS `text-wrap`. |
| 404 animated GIF and credit | Replaced by the approved 404 design. |
| Term counts beside single-video terms | Dropped by the approved details design. |
| Breadcrumbs on event archives | Removed (`3f0eb78`). |
| Classic "Video Info" meta box | Replaced by the block-editor panel (decision). |
| Classic editor and shortcode authoring | Retired (decision). |
| Review box limited to the drafts author | Shown on any post with submission data (decision). |
| `rewrite.php` user 4637740 exception | Dropped (decision). |
| `inc/wpcom.php` VIP widget loader | Dead code in `wptv2`; not ported. |
| Commented "improved search" in `search.php` | Dead code in `wptv2`; not ported. |

### 3.2. Pending (tracked)

| `wptv2` feature | Where it is tracked |
|---|---|
| Subtitle upload page (`upload-subtitles-template.php`, `/subtitle/`) and handler (`admin_post_wptv_video_upload_subtitles`) | Next delivery ([backlog](backlog.md)). |
| Subtitle AJAX nonce (`wp_ajax(_nopriv)_wptv_get_subtitles_nonce`), password check, script | Next delivery. |
| Subtitle moderation: `attachment_fields_to_save`, attachment edit box (`edit_form_after_title`), pending notice (`all_admin_notices`), Media Library view link (`views_upload`) and TTML MIME filter (`post_mime_types`) | Next delivery ("incoming-subtitle management"). |
| Google Tag Manager `GTM-P24PF4B` (head script and `noscript` iframe) | Open question 11. Not in `wptv-27`; if it is still needed, it must be added before launch, or analytics stop. |
| blog.wordpress.tv support: `wptv_home_url` filter used for the logo and search form | Resolved: the blog will never use `wptv-27`, so neither the filter nor the `wptv_setup_theme` switch is needed (see [decisions](decisions.md)). Removing the switch from `functions.php` is pending authorization. |

### 3.3. Apparently overlooked

These features exist in `wptv2`, appeared in the legacy replacement map as **Keep** or **Core**, and had no implementation, decision or backlog entry. The owner reviewed all four on October 22, 2026 and decided not to port them.

#### O1. Event term meta form

`wptv2` (`plugins/wordpresstv-event-meta/`) adds four fields to the event add/edit screens: YouTube playlist ID, hashtag, owners (WordPress.org usernames) and owners (Slack). They are stored as options `term_meta_{term_id}_{key}`. `wptv-27` still **reads** two of them in `/api/events.json`, but editors can no longer **edit** any of them.

- **Resolved (owner, October 22, 2026): not ported.** The form was never used: `wp option list --search="term_meta_%" --format=count` returned 0 on the imported data. The API keeps both keys, always empty. The owner recorded a new idea instead: backlog B14, connect a WordCamp's video album to its website. See [decisions](decisions.md).

#### O2. Blog feed link in the head

`wptv2`'s header prints two feed links: `wordpress.tv/feed/` and `blog.wordpress.tv/feed/`. Core's automatic feed links cover the first one only. The legacy map planned "`automatic-feed-links` plus the blog feed link".

- **Resolved (owner, October 22, 2026): not ported.** blog.wordpress.tv is unused and will be closed and migrated to the team blog on Make WordPress. Core already prints the site and comments feed links (checked on the review site). See [decisions](decisions.md).

#### O3. "Resources" links

`wptv2`'s sidebar (`sidebar.php`, used by the front page and archives) lists the Links Manager bookmarks (`wp_list_bookmarks()`, 10 bookmarks at the time of the analysis). The legacy map lists "Pattern / template part". No `wptv-27` template shows them, and no decision drops them.

- **Resolved (owner, October 22, 2026): not ported.** The five production links are outdated or already in the footer. See [decisions](decisions.md).

#### O4. Comments and page breaks on pages

`wptv2`'s `page.php` shows `wp_link_pages()` and `comments_template()`. `wptv-27`'s `page.html` has neither:

- Pages with open comments, or with existing ones, show no comments or reply form.
- Pages split with `<!--nextpage-->` show only their first part.
- **Resolved (owner, October 22, 2026): not ported.** Pages do not have, and will not have, comments or page breaks. See [decisions](decisions.md).

## 4. Core replacement opportunities

The theme already relies on core in most places. It uses no custom sitemap, which leaves core sitemaps or Jetpack in charge, and uses core breadcrumbs, Query Loops with enhanced pagination, Comments blocks, Query Title, Terms Query, Search and Navigation. These remaining pieces could use core more:

| ID | Current code | Core alternative | When | Notes |
|---|---|---|---|---|
| C1 | `assets/js/cards.js`: JavaScript expands the clickable area of each card to its title link and observes enhanced-pagination swaps | A CSS "stretched link": `position: relative` on the card and an absolutely positioned `::after` on the title link | Any time | No script, no MutationObserver; works after pagination swaps and without JavaScript. Keep the player and other interactive elements above the overlay (`z-index`). |
| C2 | `inc/single.php`: regex-replaces the rendered Post Excerpt HTML to show the full excerpt | A Block Bindings source (`register_block_bindings_source()`, core since 6.5) bound to a Paragraph | Any time | Avoids parsing rendered HTML. The slides link needs the same source, because core's `core/post-meta` source rejects protected (`_`-prefixed) keys. |
| C3 | `inc/content.php`: slides link appended through `get_the_excerpt` everywhere | Render the link only on the single (C2) | Phase 2 | Today the HTML link also enters JSON-LD `description`, feeds and the API, as in `wptv2`. Changing it alters feed/API output (route regression). |
| C4 | `inc/endpoints/oembed.php`: hand-rolled `/oembed/` provider | Core oEmbed provider (`/wp-json/oembed/1.0/embed`) with the `oembed_response_data` filter for the VideoPress HTML; keep `/oembed/` as a thin alias | Phase 2, after open question 8 | Core already prints its own discovery links on singles, so every video currently advertises two providers, as in `wptv2`. |
| C5 | `inc/endpoints/api.php`: `/api/*.json` with `query_posts()` | Core REST (`/wp/v2/posts`, taxonomy routes) plus `register_rest_field()` for video data | Phase 2, after open question 7 | Also removes the unbounded page size (B13). |
| C6 | Event fields as options `term_meta_{id}_{key}` (read in `api.php`) | Core term meta (`register_term_meta()` with `show_in_rest`) | With B14 | No stored values exist (O1), so B14 can start on term meta without migrating data. |
| C7 | `inc/wordcamps.php`: recent events found with `LIKE` scans of serialized `_wptv_submitted_video` | A separate indexed meta such as `_wptv_recorded` (Y-m-d), written on approval, queried with `meta_query`/`orderby` | Phase 2 | Removes the custom SQL and its ignores; needs a backfill of existing posts (data change). |
| C8 | `inc/maintenance.php`: `?flush_rules=1` | Flush on `after_switch_theme`, or rely on the platform flushing rules on theme switch | After open question 5 | Removes a GET-triggered admin action. |

Considered and kept, because core has no equivalent:
- Canonical and prev/next links on archives: core's `rel_canonical()` only covers singular views.
- JSON-LD.
- The locale map.
- `posts_per_page` (route contract).
- Rule-based legacy redirects.
- The design lockdown filters.
- `navigation.js`: core Navigation does not expose `aria-expanded` on its overlay button.
- The 1 + 5 archive split: a CSS-only split would render a player in every card.

## 5. Documentation inconsistencies

All four were corrected on October 22, 2026: question 18 moved to the answered footer entry, question 13 now covers only the `primary` menu, the legacy replacement map was refreshed, and `plan.md` lists the event meta form as pending.

1. [open-questions.md](open-questions.md) question 18 (random footer word) is still listed as open. The answered "Footer structure" entry settles it.
2. Question 13 still asks about `featured_wordcamps`, which the automatic WordCampTV landing replaced. Only `primary` and `footer` remain relevant, and D6 depends on `primary`.
3. The [legacy-wptv2.md](legacy-wptv2.md) replacement map is outdated in several rows:
   - The "Keep" rows for the event meta form and the blog feed link were never implemented (O1, O2).
   - "Resources" is listed as a pattern that does not exist (O3).
   - The planned block names `wptv/video-upload-form` and `wptv/subtitle-upload-form` became `wptv/submission-form` and a pending task.
   - Slides "Block Bindings" became an excerpt filter.
   - The random footer row is still "To decide".

   It should point to this report or be refreshed.
4. [plan.md](plan.md) line 139 ("Event term meta remains subject to its existing checks") reads as done, but no event meta form exists (O1).

## Not covered

- Visual or pixel parity: the approved design deliberately differs.
- The `style.css` of `wptv2`.
- Behavior on WordPress.com: the platform's `[wpvideo]` rendering, MRSS output, Jetpack Open Graph and real VideoPress responses.
- Formatting and docblock standards (see [review-security.md](review-security.md)).
