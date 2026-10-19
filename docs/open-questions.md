# Open questions

Questions that need an answer from the project owner or a WordPress.com employee. When a question is answered, move it to **Answered**, write down the answer and the date, and log any resulting decision in [decisions.md](decisions.md).

## Open

### Platform

1. **blog.wordpress.tv:** does it use this theme too? `wptv2` has a `wptv_setup_theme` filter and a `setup_always()` method that suggest it does. If so, `wptv-27` must support it (or a separate theme is needed).
2. **Production versions:** which PHP and WordPress versions run in production?
3. **Plugins:** what is the full list of plugins available on the multisite? Can we assume Jetpack and VideoPress are always active?
4. **Blocks from the theme:** can a theme register custom blocks from `block.json` files on the WordPress.com multisite? Are there any restrictions on PHP files in `inc/`?
5. **Rewrite flush:** does switching themes on WordPress.com flush rewrite rules? This decides whether `?flush_rules=1` is still needed.
6. **Deploy process:** how will the theme reach production, and who reviews it?

### Routes and integrations

7. **`/api/`:** who consumes it (for example, mobile apps, WordCamp.org or third parties)?
8. **`/oembed/`:** is the custom provider still used, or has core oEmbed (`/wp-json/oembed/1.0/`) replaced it in practice?
9. **`/unisubs/` and `?guid=`:** is the Amara/Universal Subtitles integration still used?
10. **MediaRSS feed:** who consumes it?
11. **Google Tag Manager** (`GTM-P24PF4B`): is it still needed?

### Production data (not available locally)

12. **Page templates:** which `_wp_page_template` values do `/submit-video/` and `/subtitle/` have in production?
13. **Menus:** what do the `primary`, `footer` and `featured_wordcamps` menus contain? They are empty in the local copy.
14. **Video post content:** is the `[wpvideo]` shortcode always at the start of the content? Two or three real `post_content` samples would settle whether `the_video()` behavior needs to be reproduced.
15. **Hardcoded IDs:** confirm the production term IDs of the `year` (`91093`) and `location` (`6418`) categories, so the code can switch to slugs safely.

### Editorial workflow

16. **Editor:** do editors use the block editor or the classic editor? This decides between a block editor sidebar panel and a classic meta box for custom fields.
17. **Upload flows:** who publishes videos, and how? Are the anonymous upload (`/submit-video/`) and subtitle upload (`/subtitle/`) still in active use?
18. **Footer:** keep the random "An Automattic Production / Joint / Medley…" word?
19. **Prev/next links:** keep `<link rel="prev|next">` on archives? Search engines no longer use them, but removing them changes the `<head>` output.

### VideoPress blocks and release 2

20. **Block markup on WordPress.com:** what exact markup does the VideoPress block save on wordpress.tv (block name, `guid` attribute, poster/featured image settings)? Release 1 (1c) depends on it. **Status:** the owner converted local post 126193 to a saved `videopress/video` block with a `guid` attribute. The shared extractor and real local thumbnail lookup now support it and existing block post 133522. The first manually converted production post is still needed to verify built-in VideoPress serialization/API behavior; Atomic deployment verification is also pending. Remaining shortcode-specific consumers (oEmbed, unisubs, GUID redirects, API, Open Graph, downloads/subtitles) must still be audited in 1c.
21. **Upload API:** in release 2, can the form handler create the VideoPress block from the uploaded attachment server-side (GUID available right after upload, as with `video_get_info_by_blogpostid()` today), or is processing asynchronous? **Status:** expected to be available immediately. The test site is Atomic and cannot upload to VideoPress this way, so it can only be verified in production or on the multisite by a WP.com employee.
23. **Autocomplete endpoint:** `speakers`, `event` and `language` are already in the core REST API (`/wp/v2/speakers?search=`), so autocomplete can use core endpoints with no new routes. Confirm that anonymous lookups against them are acceptable on WordPress.com (rate limits, caching).
24. **Migration run:** who runs the shortcode-to-block migration on production, and in what form (WP-CLI command in the theme, or a one-off script)? **Status:** not known yet.
25. **Rollout window:** who switches the theme on the test site and production, when, and how long is the production observation period? **Status:** not known yet.

## Answered

- **Real thumbnails on Atomic/Studio:** preserve the internal production thumbnail API and fall through to the existing standalone VideoPress plugin's public poster resolver. Do not invent CDN paths, rewrite posts or set featured images automatically. Deterministic route-test doubles run only on explicit test requests, not during normal local browsing. See [decisions.md](decisions.md#phase-1c-first-step-guid-extraction-and-real-posters).

- **Local MU-plugins during test-site sync:** the owner confirmed that both must run only in Studio and remain inert outside it, rather than requiring selective syncs. Guards and eight isolated negative checks are in place. Studio was observed regenerating its managed loader during development/CLI operations and removing the guard; it was reapplied, but must be checked immediately before syncing. The route helper's independent guard survives regeneration. See [decisions.md](decisions.md#local-tooling-mu-plugins-fail-closed-outside-studio).

### Location and Year URLs (B5)

- **Answer:** the old `/category/location/...` and `/category/year/...` URLs return a 301 to the home page. The new taxonomies are public in the REST API but have no front-end URLs.

### Submission form fields (release 2)

- **Answer:** new speakers and events can be entered from the form; the terms are created when the moderator approves. Event uses autocomplete plus the composed name as a suggestion. Language becomes a selector of existing `language` terms. Location and Year both leave the category tree and become internal taxonomies with no public URLs; Year is not a form field and is assigned from Date recorded (B5). `producer-username` stays: optional, one value, validated against WordPress.org. Full spec in [submission-form.md](submission-form.md).

### Release 1 scope

- **Answer:** the form keeps the `[wpvideo]` shortcode in release 1. The VideoPress block is tested on a few existing posts converted by hand in production. There is a WordPress.com test site, used before production. Release 1 design level is functional `wptv3`. See [decisions.md](decisions.md).

### Phase 1b design ownership and front page

- **Answer:** nobody, including administrators, may access the Site Editor. Editors and administrators still edit institutional page content; templates and visual structure live in theme patterns. The owner approved proceeding with the five-section front page: latest video, Featured, recent events, top-level video categories and paginated recent videos. No Popular videos. See [decisions.md](decisions.md).

### Text domain (was question 20)

- **Answer:** keep `wptv`, as in `wptv2`.

### Legacy quirks found by the baseline (were questions 21 and 22)

- **Answer:** keep them in Phase 1 and fix them later. Tracked as B2 (`/unisubs/` permalink) in [backlog.md](backlog.md). B1 (`/api/videos.*` Content-Type) was withdrawn: it was an artifact of the local test setup, not a `wptv2` bug.

### `producer` and `flavor` taxonomies

- **Answer:** both will be retired after Phase 1, with 301 redirects for every existing URL. Phase 1 keeps them unchanged. Tracked as B3 in [backlog.md](backlog.md).

### Phase 0 tooling

- **Answer (stubs, tooling location, baseline):** approved. VideoPress helpers are stubbed by a local-only mu-plugin, the tooling lives in `tests/routes/` (export-ignored), and the `wptv2` baseline was captured by switching the local site to `wptv2` temporarily (`wptv3` was reactivated afterwards).

### GitHub repository for the theme

- **Answer:** the theme will live in a GitHub repository created by the project owner. Contributors clone the full repo. Clean theme zips are built with `git archive`, with `docs/` and `AGENTS.md` excluded through `.gitattributes`.

### Design reference

- **Answer:** use `wptv3` as the interim design reference. WordPress.org visual language documentation will follow.

### Project language

- **Answer:** US English (en_US) for everything in the repository.
