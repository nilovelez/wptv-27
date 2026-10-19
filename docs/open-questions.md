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

## Answered

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
