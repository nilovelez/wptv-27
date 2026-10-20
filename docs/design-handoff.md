# Design handoff: WordPress.org visual language

Implementation instructions for applying [design.md](design.md) to wptv-27. Read [AGENTS.md](../AGENTS.md), [handoff.md](handoff.md) and [design.md](design.md) first. The site-root `DESIGN.md` (Studio) is the working summary of the same direction.

## Summary

wptv-27 moves from the interim wptv3 look to the visual language of the current WordPress.org sites: the `wporg-parent-2021` palette and type scale (same preset slugs), 680px/1160px widths with 80px edge spacing, Inter text with EB Garamond headings, 1px grey rules and 2px radii, cards grids for every video and term listing, and a white local navigation bar with the WordPress W mark. The single-video player sits on a full-width `charcoal-1` band. The front page moves recent videos up and ends with a "Get involved" band.

This is the project owner's approved direction, based on research of public WordPress.org code and guidelines. It is **not** an official WordPress.org Design team approval.

### Limits

- Presentation only. Keep the 0.3.4 template inventory, inherited queries, the category/event spotlight split (1 + 5 + rest on page one with more than six results), `posts_per_page`, pagination, routes, feeds and the editorial workflow.
- The only structural changes are the three owner-requested front-page changes: section order, the "Get involved" band and the two hero buttons.
- Do not edit `wptv2`, `wptv3` or [design-baseline-0.3.4.md](design-baseline-0.3.4.md).
- No plugins, no `wporg/*` blocks, no build step, no font CDN, no RTL stylesheet.
- Keep the design lockdown (`inc/lockdown.php`, `theme.json` custom controls disabled).
- Keep every class name that PHP or tests depend on: `wptv-taxonomy__spotlight`, `wptv-taxonomy__lead`, `wptv-taxonomy__secondary`, `wptv-taxonomy__remaining`, `wptv-taxonomy__category-events` (`inc/archives.php`) and `wptv-single__excerpt` (`inc/single.php`). Add new presentation classes next to them; do not rename them.
- US English everywhere in the repository.

## Reference mockups

Static, self-contained HTML. They show the target look with real local content; they are not theme code.

- [mockups/front-page.html](mockups/front-page.html)
- [mockups/single-video.html](mockups/single-video.html)

Open them from the repository folder (they load the bundled fonts through relative paths) and compare at 1440px, 1024px, 782px and 375px.

## Recommended implementation order

Each step should leave the site usable. Re-run the existing block checks after each step; run the full route regression only at the end, since no routing code changes.

1. **Tokens (`theme.json`).** Replace the palette, font sizes, spacing scale, layout widths and element styles with the values in design.md. Keep the lockdown settings. Do not remove the old slugs until step 3 is finished: either keep them temporarily as aliases with the new values, or do steps 1–3 in one change, so no block markup points at a missing preset.
2. **Base CSS (`style.css`).** Port the parent rules that `theme.json` cannot express: link underline details, small-screen font sizes, form fields, focus styles, `scroll-padding-top`, buttons and their variants, cards grid, two-column display, pagination, post navigation, breadcrumbs, comments, metadata rows. Remove the wptv3-specific rules they replace.
3. **Slug migration in markup.** Update preset references in patterns and templates (table below).
4. **Header and footer** (`patterns/header.php`, `patterns/footer.php`).
5. **Front page** (`patterns/front-page.php`).
6. **Single video** (`patterns/single.php`).
7. **Listings:** `patterns/video-grid.php`, `patterns/taxonomy-spotlight.php`, `templates/archive.html`, `templates/category.html`, `templates/taxonomy-event.html`.
8. **Search, page, 404:** `patterns/search.php`, `templates/page.html`, `patterns/404.php`.
9. **Block styles** (`inc/setup.php`): register the parent style names and retire `video-frame` and `event-tile` once no markup uses them.
10. **Version and docs:** bump the theme version in `style.css` (the pattern inventory changes if any pattern file is added or removed); record the change in [decisions.md](decisions.md), [plan.md](plan.md) and [handoff.md](handoff.md); update tests.

## Slug migration

| 0.3.4 | New | Where |
|---|---|---|
| `coal-2` | `charcoal-2` | `patterns/front-page.php` (featured band, lead frame), `patterns/taxonomy-spotlight.php` |
| `coal` | `charcoal-1` | `patterns/footer.php` |
| `gray` | `light-grey-2` | `patterns/front-page.php`, `patterns/footer.php` (text color: replace with `white`/`charcoal-5`), title bands in `templates/archive.html`, `category.html`, `taxonomy-event.html`, `page.html` |
| `ink` | `charcoal-1` | `theme.json`, `style.css` (`--ink` references) |
| `blue` | `blueberry-1` | `theme.json`, `style.css` |
| `orange` | none | Remove |
| `medium` (font size) | `normal` | Patterns using `"fontSize":"medium"` |
| `large` | `heading-5` or `large` (check the use) | Patterns |
| `x-large` | `heading-3` (or `heading-2` for page h1s) | Patterns, `theme.json` |
| `xx-large` | `heading-1` | `theme.json` |
| `is-style-video-frame` | cards grid / plain image | `patterns/front-page.php`, `patterns/taxonomy-spotlight.php`, `patterns/video-grid.php` |
| `is-style-event-tile` | cards grid term card | `patterns/front-page.php` |

Search the whole theme for each old slug before removing it: `grep -rn "coal-2\|\"gray\"\|video-frame\|event-tile\|x-large\|\"medium\"" patterns templates parts inc style.css theme.json`.

## Changes by component and template

### Header (`patterns/header.php`, `style.css`)

- White background, 1px `light-grey-1` bottom rule, min height 57px, edge-space padding, sticky from 890px (`position: sticky; top: var(--wp-admin--admin-bar--height, 0)`), `scroll-padding-top` on `html`.
- Before the Site Title block, add the WordPress W mark: the core `w-logo` artwork as inline SVG (or a bundled SVG file), 28px, `fill: currentColor`, `aria-hidden="true"`, inside the same home link as the title or immediately before it. Do not use the Site Logo block with a media upload: the design must live in code.
- Navigation links in `small`, not underlined, Charcoal 1. Search block with the light style. "Submit a video" as a small fill button.
- Below 1080px: core Navigation overlay or a collapsed menu, toggled by a button with `aria-expanded`. Check that the search field and button remain reachable.

### Footer (`patterns/footer.php`)

- `charcoal-1` background, white text in `small`, four link columns with Inter 600 headings, a `white-opacity-15` rule, bottom row with the site name and the CC BY-SA notice. Keep the existing links and license text.

### Front page (`patterns/front-page.php`)

- New order: hero, featured, **recent videos**, categories, **events**, **Get involved**.
- Hero: two columns 5:7 on the wide grid; h1 `heading-1`; intro `large`; buttons "Browse WordCamp talks" (fill, `/category/wordcamptv/`) and "Submit a video" (outline). The latest-video query and its Post Content player stay as they are; replace the dark caption frame with a `small` caption on white.
- Featured: `charcoal-2` band, h2 `heading-4` with an "All featured videos →" link to the `featured` tag archive, Post Template with the cards-grid style on dark.
- Recent videos: unchanged inherited query, cards grid, date added to each card, pagination. Moving the section must not change `queryId`, `inherit` or page size.
- Categories: `light-grey-2` band, compact term cards in four columns with counts.
- Events: white, term cards in three columns with counts and a "See all →" link (target: the WordCamp.tv category, as in the mockup, until a better archive exists).
- Get involved: Blueberry 1 band, heading-with-arrow link to `/get-involved/`, one sentence. All strings translatable with the `wptv` text domain.

### Single video (`patterns/single.php`)

- Introduction group on white: breadcrumbs, h1 `heading-2`, speakers `large`.
- Wrap the Post Content player in a full-width `charcoal-1` group (padding `30`, none below 600px) containing only the player at wide width. Keep `wptv-single__player` and the existing Post Content approach.
- Move the Jetpack Like and Sharing group out of the band, below it, on white.
- Details: two-column display; metadata rows (Published, Event, Speakers, Tags, Language, `wptv/producer`, `wptv/video-downloads`) as label/value rows with rules; license below. Description (`wptv-single__excerpt`, keep the class) on the right at reading width, `large`.
- Comments in a two-column display (title left, comments and form right).
- Previous/next: .org post-navigation layout with a top rule.
- Missing producer/download/subtitle data keeps rendering nothing.

### Listings (`patterns/video-grid.php`, `patterns/taxonomy-spotlight.php`, archive templates)

- Title band: `light-grey-2`, left-aligned on the wide grid, h1 `heading-2`, breadcrumbs `small` (category/event), category description `large` at reading width.
- Video grid: cards-grid style, four columns, speakers above the title, pagination with middots.
- Spotlight: keep both inherited queries and all marker classes. Lead: player plus a `charcoal-1` caption block (Inter 600 `large` title in white, event terms in Blueberry 2), no 8px frame. Secondary: five rows of thumbnail (about 40%) and title, separated by `light-grey-1` rules. Stack below 782px.

### Search, page and 404

- Search: title band with the query title and a full-width light search field; grid; no-results message.
- Page: title band with the h1 left-aligned at reading width; content at 680px.
- 404: reading-width h1, explanation and search; status stays 404.

## Expected file changes

| File | Change |
|---|---|
| `theme.json` | Palette, font sizes, spacing, layout widths, element and block styles |
| `style.css` | Base and component CSS; remove replaced wptv3 rules; version bump |
| `inc/setup.php` | Block style registration (parent names); retire `video-frame`, `event-tile` |
| `patterns/header.php`, `patterns/footer.php` | New chrome |
| `patterns/front-page.php` | Order, hero buttons, cards, Get involved band |
| `patterns/single.php` | Player band, reactions, two-column details and comments |
| `patterns/video-grid.php`, `patterns/taxonomy-spotlight.php`, `patterns/search.php`, `patterns/404.php` | Cards and listing styles |
| `templates/archive.html`, `category.html`, `taxonomy-event.html`, `page.html` | Title band color and alignment |
| `assets/images/` (optional) | W mark SVG, if not inlined |
| `tests/blocks/*.php` | Update only assertions about presentation; logic assertions must keep passing unchanged |
| `docs/decisions.md`, `docs/plan.md`, `docs/handoff.md` | Record the implementation |

Not expected to change: anything under `inc/endpoints/`, `inc/query.php`, `inc/taxonomies.php`, `inc/redirects.php`, `inc/archives.php` logic, `inc/lockdown.php`, routes and tests under `tests/routes/`.

## Verification criteria

Check each template at **1440px** and **375px**, and the breakpoints 600, 782, 890, 960 and 1080px where layouts change. Use the front page, a shortcode single, a block single (posts 126193 and 133522), a category and an event with the spotlight, their page two, a tag archive, search with and without results, a page and the 404.

### All views

- [ ] No horizontal scroll at 320px and 375px.
- [ ] One h1 per view; header, main and footer landmarks; labeled navigation regions.
- [ ] Header: white, grey bottom rule, W mark before "WordPress.tv", both linking home; sticky from 890px; menu collapsed below 1080px with a working toggle (`aria-expanded` changes) and reachable search and button.
- [ ] Fonts load from the theme (no external font requests).
- [ ] Only the approved palette appears in computed styles (no `#d54e21`, no `#edeff0`, body text `#1e1e1e`).
- [ ] Wide content is 1160px max, reading content 680px max; edge spacing 80px above 890px, 20px below.
- [ ] Every interactive element shows a visible focus indicator with the keyboard; tab order follows the visual order; nothing hides under the sticky header when focused.
- [ ] Interactive targets are at least 24×24px.
- [ ] Text contrast at least 4.5:1, component boundaries at least 3:1 (inputs use `charcoal-4` borders).
- [ ] No animation beyond color and underline changes; `prefers-reduced-motion` respected.
- [ ] Editor validation passes for every changed template and pattern; no Site Editor access; presets only.

### Front page

- [ ] Section order: hero, featured, recent videos, categories, events, Get involved, footer.
- [ ] `/` shows 8 recent videos; `/page/2/` and the last page keep the same posts as before; the page after the last returns 404.
- [ ] Desktop: hero in two columns, featured in four, recent videos in four, categories in four, events in three. 375px: everything in one column, hero text before the player.

### Single video

- [ ] Player band is full width, `charcoal-1`, contains only the player; no padding below 600px.
- [ ] Title and speakers above the band on white; Like and Sharing below the band on white.
- [ ] Details: metadata left and description right from 1080px; stacked below with the description first; missing downloads/subtitles render nothing.
- [ ] Shortcode and block posts render one player each.

### Listings

- [ ] Category and event page one with more than six results: 1 lead + 5 secondary + remaining grid, same posts and order as 0.3.4; page two and small archives show only the grid.
- [ ] Category page size 22; tag, speaker and search page size 10.
- [ ] Spotlight stacks below 782px; grids go to two columns below 960px and one below 600px.
- [ ] `tests/blocks/archives.php` passes unchanged.

### Regression

- [ ] All `tests/blocks/` checks pass.
- [ ] Full route regression at the end: no new differences beyond the four known feed hashes already documented.

## Decisions needing owner confirmation

1. **W mark usage.** WordPress.tv is an official WordPress project site, but the [trademark policy](https://wordpressfoundation.org/trademark-policy/) asks for permission for uses of the logo in project branding. Confirm that the W mark in the header is acceptable (and in which color: the mockups use `charcoal-1`, close to the official black version).
2. **Input borders.** The .org parent uses `charcoal-5` borders (2.8:1). This design uses `charcoal-4` (5.5:1) to meet WCAG 2.2 AA. Confirm the deviation.
3. **Borderless search field.** The .org light search field has no border (its boundary relies on the `light-grey-2` fill). Confirm, or add a 1px `charcoal-4` border for a stricter reading of WCAG 1.4.11.
4. **Featured link target.** "All featured videos →" points to the `featured` tag archive; "See all →" under events points to the WordCamp.tv category. Confirm both, or name better destinations (no new routes).
5. **Design team review.** Whether and when to ask the WordPress.org Design team to review this direction before release.
6. **Title band.** The 0.3.4 grey title bands are kept (recolored, left-aligned). The .org parent archives use a white two-column header instead; switching would be a structural change and is not part of this version.
7. **WordCampTV landing page and forms/admin.** Not designed here; they use these tokens and components when implemented.
