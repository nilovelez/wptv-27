# Design handoff: WordPress.org visual language

Implementation instructions for applying [design.md](design.md) to wptv-27. Read [AGENTS.md](../AGENTS.md), [handoff.md](handoff.md) and [design.md](design.md) first. The site-root `DESIGN.md` (Studio) is the working summary of the same direction.

## WordCampTV follow-up (local 0.4.1)

The owner approved a replacement rather than restoration of the old featured/tag/menu landing page. See [wordcamptv.md](wordcamptv.md) for the implemented product/data/cache contract and [design.md](design.md#wordcamptv-owner-approved-replacement-local-041) for its layout. It is local and not yet published. The extra category template and three core-block patterns are explicit exceptions to the older inventory constraints below. Generic category/event spotlights and all inherited query/page-size/route contracts remain unchanged.

## Summary

wptv-27 moves from the interim wptv3 look to the visual language of the current WordPress.org sites: the `wporg-parent-2021` palette and type scale (same preset slugs), 680px/1160px widths with 80px edge spacing, Inter text with EB Garamond headings, 1px grey rules and 2px radii, cards grids for every video and term listing, and a white local navigation bar with the WordPress W mark. The single-video player sits on a full-width `charcoal-1` band. The front page moves recent videos up and ends with a "Get involved" band.

This is the project owner's approved direction, based on research of public WordPress.org code and guidelines. It is **not** an official WordPress.org Design team approval.

### Owner-approved implementation checkpoint: 0.4.0

**Owner review follow-up:** homepage category cards exclude `year` and `location` by slug while other cards retain native ordering; archive routes remain intact. Single titles use the full introduction width (no 22ch cap). The footer site name is Inter, retaining its previous size/weight. The scoped core-renderer supplement is in `inc/block-queries.php`; no block markup changed. After these refinements, 173 PHP checks and 196 browser assertions pass. The milestone counts below are historical; no additional full route run was needed for this follow-up.

The owner has approved the completed implementation and requested Git publication of all theme changes; site upload/deployment remains a separate owner action. The live card/header/footer refinements in [design.md](design.md) supersede the older mockup details and implementation checklist below. See [handoff.md](handoff.md) for current evidence and limits. The initial implementation passed 165 existing tests and 188 browser assertions; subsequent scoped checks are recorded in the handoff. Changed templates/evaluated patterns pass editor validation. The 145-URL `design-0.4.0` run records zero field changes from `phase-1c-integrations`, with no PHP notices; the four existing unaccepted feed hashes and four expected API hashes remain. This is not a green regression or a complete accessibility audit. Unchecked criteria below remain the broader acceptance checklist, not a claim that each item was exhaustively verified.

### Limits

- Preserve inherited queries, `posts_per_page`, pagination, routes, feeds and the editorial workflow. Retain the generic 0.3.4 category/event spotlight split (1 + 5 + rest on page one with more than six results). The owner's WordCampTV replacement above explicitly adds a category-specific template with the native 1 + 5 introduction and independent, unpaginated event sections. The owner corrected the earlier 22-card landing assumption; no Recent videos grid/heading/pagination belongs on the landing.
- Approved presentation changes include the three front-page changes (section order, Get involved band and two hero buttons), the revised card fields/interactions, removal of About from main navigation, and the three-column footer/subfooter. They do not change inherited post queries or archive partitions.
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
- Hero: two columns 5:7 from 1080px; at 782–1079px use 1:1 columns and a fluid 50–59px heading, as confirmed by the owner during implementation; below 782px stack as before and retain 52px below 600px. Keep WordPress.tv intact. On large screens h1 `heading-1`; intro `large`; buttons "Browse WordCamp talks" (fill, `/category/wordcamptv/`) and "Submit a video" (outline). The latest-video query and its Post Content player stay as they are; replace the dark caption frame with a `small` caption on white.
- Featured: `charcoal-2` band, h2 `heading-4` with an "All featured videos →" link to the `featured` tag archive, Post Template with the cards-grid style on dark.
- Recent videos: unchanged inherited query, cards grid, date added to each card, pagination. Moving the section must not change `queryId`, `inherit` or page size.
- Categories: `light-grey-2` band, compact term cards in four columns with counts.
- Events: white, term cards in three columns with counts and an "All WordCamp Videos →" link to `/category/wordcamptv/`. The owner confirmed this temporary label and destination; they may change later.
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
| `inc/setup.php` | Block style registration (parent names); retire `video-frame`, `event-tile`; scoped decorative card-thumbnail rendering and frontend script enqueue |
| `assets/js/navigation.js` | Mirror core Navigation dialog state into disclosure attributes; no custom menu/focus implementation |
| `tests/blocks/design-browser.cjs` | Read-only browser checks using an existing Playwright installation; no theme build/dependency |
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

- [x] All 165 existing PHP block checks pass unchanged; 188 new browser assertions also pass.
- [x] Full route regression at the end: no new differences beyond the four known feed hashes already documented (`design-0.4.0`, 145 URLs, no PHP notices, zero field differences from the previous full run). Still not green against the baseline.

## Decisions needing owner confirmation

The owner answered the five design confirmation questions after reviewing this handoff. They are resolved for implementation:

1. **W mark:** use the official artwork in `charcoal-1` before the site title. Owner approval does not establish separate trademark permission or official Design team approval; follow the [trademark policy](https://wordpressfoundation.org/trademark-policy/).
2. **Input borders:** use `charcoal-4` borders on ordinary form fields, as proposed.
3. **Search:** keep the proposed borderless search field on `light-grey-2`. Still verify focus visibility and accessibility in the rendered implementation; owner preference is not a conformance test.
4. **Front-page links:** "All featured videos →" links to the `featured` tag archive. The Recent events link must read **"All WordCamp Videos →"** and link to `/category/wordcamptv/`. The events label and destination are temporary and may change later. Hide decorative arrows from assistive technology.
5. **Design team review:** no review requested for now. Continue under owner approval without claiming official endorsement.
6. **Tablet hero (confirmed during implementation):** reduce the title and use equal columns at 782–1079px; keep 5:7 and 70px on large screens. The implementation uses `clamp(50px, 5.5vw, 59px)` for the tablet title. Do not break the site name to preserve 70px.

Remaining scope notes, not unanswered design confirmation questions:

- **Title bands:** keep the recolored, left-aligned 0.3.4 title bands. The white two-column .org archive header remains outside this version's scope.
- **WordCampTV landing page and forms/admin:** no layout is defined here. Their unfinished functionality remains tracked separately; use these tokens and components when implemented.

The static mockups predate the follow-up confirmations. If their events link differs, the exact label and destination above take precedence.
