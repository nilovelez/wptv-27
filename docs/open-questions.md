# Open questions

Questions that need an answer from the project owner or a WordPress.com employee. When a question is answered, move it to **Answered**, write down the answer and the date, and log any resulting decision in [decisions.md](decisions.md).

## Open

### Platform

2. **Production versions:** which PHP and WordPress versions run in production?
3. **Plugins:** what is the full list of plugins available on the multisite? Can we assume Jetpack and VideoPress are always active?
4. **Blocks from the theme:** can a theme register custom blocks from `block.json` files on the WordPress.com multisite? Are there any restrictions on PHP files in `inc/`?
6. **Deploy process:** how will the theme reach production, and who reviews it?

### Routes and integrations

7. **`/api/`:** who consumes it (for example, mobile apps, WordCamp.org or third parties)?
8. **`/oembed/`:** is the custom provider still used, or has core oEmbed (`/wp-json/oembed/1.0/`) replaced it in practice?
9. **`/unisubs/` and `?guid=`:** is the Amara/Universal Subtitles integration still used?
10. **MediaRSS feed:** who consumes it?
11. **Google Tag Manager** (`GTM-P24PF4B`): is it still needed?

### Production data (not available locally)

12. **Page templates:** which `_wp_page_template` values do `/submit-video/` and `/subtitle/` have in production?
13. **Menus:** what does the production `primary` menu contain? It is empty in the local copy. The header shows the menu assigned to `primary` (editable in Appearance > Menus), so the production menu will appear as soon as `wptv-27` is active; its links will be replaced by hand once the new theme is kept (see D6 in [review-parity.md](review-parity.md)). The `footer` menu was replaced by the owner-specified footer, and `featured_wordcamps` by the automatic WordCampTV landing ([wordcamptv.md](wordcamptv.md)).
14. **Video post content:** is the `[wpvideo]` shortcode always at the start of the content? Two or three real `post_content` samples would settle whether `the_video()` behavior needs to be reproduced. The new category/event lead uses the same core Post Content approach as the homepage/single; unlike the legacy shortcode-only player extractor it can render accompanying stored text. Confirm real production content shape before declaring complete player parity. All local category descriptions are empty as well; review real production descriptions for wrapping and safe display through core Term Description.
15. **Hardcoded IDs:** confirm the production term IDs of the `year` (`91093`) and `location` (`6418`) categories, so the code can switch to slugs safely.

### Editorial workflow

16. **Editor:** resolved in part. The owner retired the classic editor and shortcode authoring, and a block-editor prototype exists ([decisions](decisions.md)). The owner accepted the current field order for now. `producer` and `flavor` stay out of the editor: `flavor` has no terms, and the owner will empty `producer` before the migration. Still open: Play inline, which the owner will test both ways before deciding.
17. **Upload flows:** who publishes videos, and how? Are the anonymous upload (`/submit-video/`) and subtitle upload (`/subtitle/`) still in active use?
19. **Prev/next links:** keep `<link rel="prev|next">` on archives? Search engines no longer use them, but removing them changes the `<head>` output.

26. **Edit Flow module management in production:** the owner reports that the Features tab is missing and `/wp-admin/admin.php?page=ef-settings` displays "There are no Edit Flow modules registered", although custom statuses work and the production export contains editorial comments and metadata. Which Edit Flow version or customized integration runs on WordPress.tv? Is module management intentionally hidden or centrally controlled, or is module registration failing? Which modules are actually loaded, and how can individual modules be safely enabled or disabled? The local plugin displays this message when its internal module counter is empty/zero, but production code has not been inspected; do not infer that all modules are disabled, assume a permissions issue, or reset production settings. Local 0.11.1 settings and defaults do not establish production configuration. Ask a WordPress.com employee with production code/configuration access before recommending production module changes.

27. **VideoPress Allow download:** the owner found it disabled on a 2016 production video and on a video he uploaded to the test host, so the effective default is disabled. It is a video-level VideoPress setting that only the owning account can change, not something the theme can enforce through block attributes. Can the upload script set it at upload time on WordPress.com multisite? Can a super admin enable it for all existing WordPress.tv videos, and for future uploads by default? Until both are confirmed, keep the theme's own download links.

### VideoPress blocks and release 2

20. **Block markup on WordPress.com:** what exact markup does the VideoPress block save on wordpress.tv (block name, `guid` attribute, poster/featured image settings)? Release 1 (1c) depends on it. **Status:** the owner converted local post 126193 to a saved `videopress/video` block with a `guid` attribute. The shared extractor and real local thumbnail lookup now support it and existing block post 133522. The first manually converted production post is still needed to verify built-in VideoPress serialization/API behavior; The owner synchronized the site and confirmed thumbnails on Atomic; broader block integration and rollout checks remain pending. The local theme-consumer audit is now implemented: oEmbed, unisubs/GUID redirects, API, Open Graph and downloads/subtitles have block fallback with legacy selection preserved; JSON-LD already uses shared helpers and the theme MRSS filter reads attachments. Twenty-two integration simulations pass. Real production APIs and platform MRSS/serialization still need verification; the latest full regression retains four unaccepted feed hashes and is not green.
21. **Upload API:** in release 2, can the form handler create the VideoPress block from the uploaded attachment server-side (GUID available right after upload, as with `video_get_info_by_blogpostid()` today), or is processing asynchronous? **Status:** expected to be available immediately. The test site is Atomic and cannot upload to VideoPress this way, so it can only be verified in production or on the multisite by a WP.com employee.
23. **Autocomplete endpoint:** `speakers`, `event` and `language` are already in the core REST API (`/wp/v2/speakers?search=`), so autocomplete can use core endpoints with no new routes. Confirm that anonymous lookups against them are acceptable on WordPress.com (rate limits, caching).
24. **Migration run:** who runs the shortcode-to-block migration on production, and in what form (WP-CLI command in the theme, or a one-off script)? **Status:** not known yet.
25. **Rollout window:** who switches the theme on the test site and production, when, and how long is the production observation period? **Status:** not known yet.

## Answered

- **Rewrite flush (was question 5, October 22, 2026):** core flushes rewrite rules on the first load after a theme switch (`check_theme_switched()` in `wp-includes/theme.php`), and `wptv2` and `wptv-27` register the same three routes. The theme also flushes on `after_switch_theme`, and keeps `?flush_rules=1` for later deploys that change its routes without a theme switch, now for site administrators (`edit_theme_options`). How WordPress.com itself switches themes was not observed. See [decisions](decisions.md).

- **blog.wordpress.tv (was question 1, October 22, 2026):** the blog will never use `wptv-27`. It is a standard blog kept for historical reasons that any block theme can reproduce, and it will be migrated to the team blog on Make WordPress. How it closes is still undecided and outside this theme's scope: keep `wptv2` there until it closes, switch it to a generic theme, or migrate it now and redirect the domain. `wptv-27` needs neither the `wptv_setup_theme` switch nor the `wptv_home_url` filter. See [decisions](decisions.md).

- **WordCampTV introduction correction:** the owner requires the native one-large/five-small category spotlight, not the 22-video Recent videos grid. The next heading is Latest WordCamps. Both event sections show all qualifying events without pagination. This overrides the earlier interpretation of retaining a full recent-video grid on the landing; existing later-page URLs remain preserved.

- **WordCampTV replacement (local 0.4.1):** the owner approved retaining recent category videos, dropping the entire legacy sidebar/manual `featured_wordcamps` dependency, and adding automatic recent event sections plus a complete year-grouped directory. Recent selection uses the problematic but best-available recorded field without guessed dates. The complete directory instead extracts one distinct 2000–2099 year from WordCamp event names. Two data-only transients and documented change-driven invalidation are approved. The implementation is local; see [wordcamptv.md](wordcamptv.md). Obtaining the old manual menu is no longer a prerequisite. Language filters/year navigation are backlog B7/B8. Production cold-cache cost and recorded coverage remain verification needs, not answered platform questions.

- **Visual review and publication:** the owner approved the final 0.4.0 presentation, including all card/header/footer refinements, and requested publication of all theme changes. This authorizes Git commit/push; the owner will handle the site upload. No remaining visual-review question blocks this checkpoint. Functional, feed-regression and production-verification questions remain open.

- **Footer structure:** the owner specified three columns (WordPress.TV/description, Contribute, About) and supplied the WordPress.org wordmark/trademark subfooter. Implemented with the exact requested links; no open question remains. See DESIGN.md and the newest footer decision. This also settles former question 18: the random "An Automattic Production / Joint / Medley…" credit is not kept.

- **Video-card fields and behavior:** confirmed all speakers, comma-separated with a trailing colon; omit absent speakers, with no author fallback. Apply progressive full-card pointer activation to Featured too, keeping only titles as native links. Both large and secondary spotlight captions show speakers followed by title, never event terms; keep the player interactive. Implemented locally; no remaining question for this change.

- **Minor visual-review changes (2026-10-20):** the owner requested excluding `year` and `location` only from the homepage category cards, allowing single-video titles more width, and using Inter for the footer site name. All three are implemented locally; no terms or archive routes were removed, and no post-query or editorial behavior changed.

- **Tablet hero (2026-10-20, implementation clarification):** the owner requested both a smaller hero title and equal 1:1 columns on tablets, retaining 5:7 on large screens. Implemented at 782–1079px with `clamp(50px, 5.5vw, 59px)`; 70px resumes at 1080px, with the existing mobile size/stack preserved. Ten viewport checks confirm the site name remains intact. No unanswered design confirmation blocks the local 0.4.0 implementation.

- **WordPress.org design guidance and follow-up confirmations (post-`d02cbfd` owner review):** the handoff has arrived and replaces the interim wptv3 direction. Follow `docs/design.md`, `docs/design-handoff.md` and the site-root `DESIGN.md`. The owner confirmed the official W mark in `charcoal-1`, `charcoal-4` borders on ordinary fields, borderless search fields on `light-grey-2`, and the featured link to the `featured` tag archive. Recent events must use "All WordCamp Videos →" linking to `/category/wordcamptv/` temporarily. No Design team review is requested for now. These preferences do not establish trademark permission, official endorsement or accessibility conformance. Existing title bands stay; WordCampTV/forms/admin remain unfinished. The approved presentation is implemented locally in 0.4.0 and awaits owner visual review; see the handoff for verification and release limits.

- **Version 0.3.4 presentation and next priority:** the owner accepted the current category/event structure for this version and deferred restructuring. Publish all pending theme changes to GitHub before collaboration, then work on design from Claude Code's forthcoming document/instructions rather than continuing WordCampTV/forms/admin immediately. This acceptance does not resolve the four feed hashes or production-verification gaps.

- **Atomic thumbnail verification:** after completing synchronization, the owner confirmed that thumbnails work on the test site. Production multisite verification and the broader rollout checklist remain open.
- **Sync size and optional plugins:** the owner removed inactive Gutenberg and reported a sync estimate of about 183 MB, and also reported disabling Gravatar Enhanced, Layout Grid and Page Optimize. The large local MP4 is physically absent after a site stop/start; preserve the remote VideoPress reference. See [handoff.md](handoff.md) for the evidence and limitations.

- **Real thumbnails on Atomic/Studio:** preserve the internal production thumbnail API and fall through to the existing standalone VideoPress plugin's public poster resolver. Do not invent CDN paths, rewrite posts or set featured images automatically. Deterministic route-test doubles run only on explicit test requests, not during normal local browsing. See [decisions.md](decisions.md#phase-1c-first-step-guid-extraction-and-real-posters).

- **Local MU-plugins during test-site sync:** the owner confirmed that both must run only in Studio and remain inert outside it, rather than requiring selective syncs. Guards and eight isolated negative checks are in place. Studio was observed regenerating its managed loader during development/CLI operations and removing the guard; it was reapplied, but must be checked immediately before syncing. The route helper's independent guard survives regeneration. See [decisions.md](decisions.md#local-tooling-mu-plugins-fail-closed-outside-studio).

### Location and Year URLs (B5)

- **Answer:** the old `/category/location/...` and `/category/year/...` URLs return a 301 to the home page. The new taxonomies are public in the REST API but have no front-end URLs.

### Submission form fields (release 2)

- **Answer:** new speakers and events can be entered from the form; the terms are created when the moderator approves. Event uses autocomplete plus the composed name as a suggestion. Language becomes a selector of existing `language` terms. Location and Year both leave the category tree and become internal taxonomies with no public URLs; Year is not a form field and is assigned from Date recorded (B5). `producer-username` stays: optional, one value, validated against WordPress.org. Full spec in [submission-form.md](submission-form.md).

### Release 1 scope

- **Answer:** the form keeps the `[wpvideo]` shortcode in release 1. The VideoPress block is tested on a few existing posts converted by hand in production. There is a WordPress.com test site, used before production. The original functional `wptv3` design level was subsequently replaced by the owner-approved WordPress.org visual checkpoint; the release's workflow and functional scope remain unchanged. See [decisions.md](decisions.md).

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

- **Historical answer, superseded for presentation:** `wptv3` was the interim reference. The approved WordPress.org documentation and the local 0.4.0 implementation now replace that look; keep the accepted functional structure.

### Project language

- **Answer:** US English (en_US) for everything in the repository.
