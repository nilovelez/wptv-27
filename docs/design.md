# Design: WordPress.org visual language

WordPress.tv is a WordPress.org community site. wptv-27 adopts the visual language of the current WordPress.org sites. This document records the approved tokens, rules and decisions needed to rebuild the design. The site-root `DESIGN.md` (Studio, outside this repository) is the working summary for the Studio agent and must stay consistent with this file. Implementation steps are in [design-handoff.md](design-handoff.md).

## Status

Owner follow-up in 0.4.1: the homepage lead caption combines all speakers, a trailing colon and the video title inside one native link to the video single. Category descriptions retain the 680px reading width but align with the left edge of the wide breadcrumbs/title, including on large screens. The five secondary category/event spotlight titles use small (14px), down from normal (16px), with 2px rather than 8px between speakers and title. These rules supersede conflicting descriptions below. Focused checks passed at 1440px and 390px, with event title sizing checked separately; both changed patterns passed block validation. No new broad regression was run for these presentation corrections.

Owner follow-up after the local 0.4.0 review: exclude `year` and `location` by slug only from the homepage category cards; retain all other cards and their native ordering. Keep the categories and archives themselves unchanged. Single-video titles use the full introduction width, and the footer site name uses Inter like the header, retaining its existing size and weight. These refinements supersede conflicting earlier mockup details.

- **Owner-approved direction.** The project owner reviewed the proposal and the mockups in [`mockups/`](mockups/) and approved them, including the header, player band and front-page order decisions below.
- **Not official Design team approval.** This is research by the project, based on public WordPress.org code and guidelines. The WordPress.org Design team has not reviewed or endorsed it.
- **Implemented and owner-approved in 0.4.0.** Theme tokens, chrome, card behavior and page layouts follow this direction, including the owner's subsequent header/footer refinements. The owner requested Git publication of all theme changes. Implementation and verification are recorded in [handoff.md](handoff.md); Git publication is not site synchronization or deployment. The pre-design site-root `DESIGN.md` is preserved in [design-baseline-0.3.4.md](design-baseline-0.3.4.md) as a historical snapshot; do not edit it or restore it over this direction.
- **Structure is kept.** The functional structure accepted for this version (templates, queries, the category/event spotlight split, page sizes, pagination, routes and editorial flow) does not change. The redesign changes presentation, plus three owner-requested front-page changes listed in [What changes](#what-stays-and-what-changes).

## Sources

Three kinds of sources inform this document. Keep them apart: only the first kind is a requirement from the WordPress project.

### Official guidelines

| Source | What it requires here |
|---|---|
| [WordPress Accessibility Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/accessibility/) | Code must conform to WCAG 2.2 at level AA. See [Accessibility and interaction](#accessibility-and-interaction). |
| [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/) (CSS, HTML, PHP) | Markup and stylesheet conventions (already required by [AGENTS.md](../AGENTS.md)). |
| [WordPress trademark policy](https://wordpressfoundation.org/trademark-policy/) and [logos page](https://wordpress.org/about/logos/) | Use the official WordPress marks as supplied, according to the policy. Relevant to the "W" mark in the header. |

### Patterns observed on WordPress.org sites

Public code of the WordPress.org themes. These are implementation choices of those sites, not published guidelines.

| Repository | Role | Studied at |
|---|---|---|
| [WordPress/wporg-parent-2021](https://github.com/WordPress/wporg-parent-2021) | Parent block theme of the new WordPress.org sites: `theme.json` tokens, base element styles, block styles, generic templates. Key files: `theme.json`, `readme.md`, `inc/block-styles.php`, `sass/base/_elements.scss`, `sass/block-styles.scss`, `sass/blocks/*.scss`. | `70f374a` (2026-09-04) |
| [WordPress/wporg-main-2022](https://github.com/WordPress/wporg-main-2022) | Child theme of wordpress.org: global header/footer and local navigation bar usage, page patterns. | `c275122` (2026-10-14) |

Observed patterns adopted: the palette, font families, font size and spacing scales, layout widths, link and button treatment, form fields, cards grid, two-column display, pagination, post navigation, comments, separators, the local navigation bar and the heading-with-arrow call to action.

### Adaptations for WordPress.TV

Decisions of this project, not found in either source. Each one is marked **(WPTV)** where it appears below.

- No global header or footer: WordPress.tv has no access to `wporg-mu-plugins`. The local navigation bar is the only header, white, with the W mark before the site title.
- Video-specific components: video cards, the single-video player band, metadata rows, the category/event spotlight.
- Local fonts only, English only (no RTL), and the design lockdown.
- Accessibility adjustments where the observed .org values fall short of WCAG 2.2 AA (input borders).
- Front-page section order and the closing "Get involved" band.

## Principles

1. **Copy the tokens, do not inherit the theme.** `wporg-parent-2021` is not available on the WordPress.com multisite and depends on `wporg-mu-plugins`. wptv-27 stays a standalone block theme and reproduces the language with its own `theme.json`, core blocks and `style.css`.
2. **Follow the language, not a copy of a site.** WordPress.tv does not have to look identical to any wordpress.org page, but uses the same tokens, typography, components and restraint.
3. **Same preset slugs as WordPress.org.** Colors, font sizes and spacing use the parent slugs, so CSS variables match the .org sites and markup can move between projects.
4. **Keep the design lockdown.** No custom colors, sizes or spacing in the editor, no Site Editor. Editors pick from presets.
5. **Core blocks first.** Register .org block styles under the same names in `inc/setup.php` and style them in `style.css`. Never use `wporg/*` blocks.

## Color

Body text is Charcoal 1 on white. Blueberry 1 is the only accent: links, primary buttons, focus and the closing call-to-action band. Dark bands use Charcoal 1 or 2 with white text. Structure comes from 1px Light Grey 1 rules, not tinted backgrounds.

### Palette

Register this subset of the parent palette. Slugs and values are identical to `wporg-parent-2021`.

| Slug | Name | Hex | Use |
|---|---|---|---|
| `charcoal-1` | Charcoal | `#1e1e1e` | Body text, headings, single-video player band, footer, active button |
| `charcoal-2` | Charcoal 2 | `#23282d` | Featured-video band |
| `charcoal-3` | Charcoal 3 | `#40464d` | Search field on dark bands (if ever needed) |
| `charcoal-4` | Charcoal 4 | `#656a71` | Secondary text (dates, counts, labels, breadcrumbs); form field borders **(WPTV)** |
| `charcoal-5` | Charcoal 5 | `#979aa1` | Secondary text on dark bands |
| `light-grey-1` | Light Grey | `#d9d9d9` | Rules, card borders, header bottom rule |
| `light-grey-2` | Light Grey 2 | `#f6f6f6` | Categories band, title bands, search field, card hover |
| `white` | White | `#ffffff` | Page background, text on dark |
| `white-opacity-15` | White 15% | `#ffffff26` | Rules and card borders on dark bands |
| `deep-blueberry` | Deep Blueberry | `#213fd4` | Button hover |
| `blueberry-1` | Blueberry | `#3858e9` | Links, primary buttons, focus, "Get involved" band |
| `blueberry-2` | Blueberry 2 | `#9fb1ff` | Links and hover on dark bands |
| `blueberry-4` | Blueberry 4 | `#eff2ff` | Text-button hover background |
| `purple-1` | Purple 1 | `#5300be` | Visited links inside post content |
| `pomegrade-1` | Pomegranate | `#e26f56` | Error and destructive accents only |

Keep the parent slug spelling `pomegrade-*`. Leave out the rest of the parent palette (Acid Green, Lemon, other tints) unless a design needs it. Never register a color slug named `text`: its generated `.has-text-color` class collides with core's generic marker class.

### Mapping from the 0.3.4 tokens

| 0.3.4 slug | Hex | New slug | Change |
|---|---|---|---|
| `ink` | `#1a1919` | `charcoal-1` | Body text becomes `#1e1e1e` |
| `coal` | `#1e1e1e` | `charcoal-1` | Same value |
| `coal-2` | `#23282d` | `charcoal-2` | Same value |
| `blue` | `#3858e9` | `blueberry-1` | Same value |
| `gray` | `#edeff0` | `light-grey-2` | Lighter: `#f6f6f6` |
| `orange` | `#d54e21` | none | Dropped |
| `white` | `#ffffff` | `white` | Same value |

## Typography

| Family | Slug | Use |
|---|---|---|
| Inter | `inter` | Body, UI, metadata, buttons, video titles on cards |
| EB Garamond | `eb-garamond` | Headings h1–h6, page and term titles, section headings |

- Local fonts only: keep the bundled variable WOFF2 files (Inter 100–900, EB Garamond 400–800) declared in `theme.json`. No font CDN. **(WPTV;** .org loads fonts through its global fonts mu-plugin.**)**
- Headings: EB Garamond 400, `text-wrap: balance`. Body: Inter, `text-wrap: pretty`.
- Body: 16px, line height 1.875. Short UI text (cards, captions, lists): line height 1.625.
- Headings set in Inter (video titles on cards, footer headings): weight 600, line height 1.3–1.4.
- Speaker names and titles appear in many scripts. EB Garamond covers Latin, Cyrillic and Greek; other scripts fall back to the system font.

### Font sizes

Same slugs and values as the parent. Below 600px, the sizes in the last column apply.

| Slug | Size / line height | Below 600px |
|---|---|---|
| `extra-small` | 12px / 1.67 | — |
| `small` | 14px / 1.71 | — |
| `normal` | 16px / 1.875 | — |
| `large` | 20px / 1.7 | — |
| `extra-large` | 24px / 1.58 | 20px / 1.5 |
| `huge` | 32px / 1.5 | — |
| `heading-6` | 22px / 1.27 | 18px / 1.22 |
| `heading-5` | 26px / 1.3 | 20px / 1.2 |
| `heading-4` | 30px / 1.33 | 22px / 1.09 |
| `heading-3` | 36px / 1.28 | 26px / 1.15 |
| `heading-2` | 50px / 1.2 | 30px / 1.07 |
| `heading-1` | 70px / 1.05 | 52px / 1.08 |
| `heading-cta` | 120px / 1 | 52px / 1.08 |

Hierarchy on WordPress.tv:

| Element | Size |
|---|---|
| Front-page h1 | `heading-1` |
| Single-video, term, archive, search and page h1 | `heading-2` |
| Section headings (h2) on the front page and comments | `heading-4` |
| Video titles on cards | `normal`, Inter 600 |
| Spotlight lead title | `large`, Inter 600 |
| Metadata, terms, dates, breadcrumbs | `small` |
| Counts, card dates | `extra-small` or `small` |

0.3.4 slugs map as: `small` (0.9rem) → `small`; `medium` (1.05rem) → `normal`; `large` → `heading-5`; `x-large` → `heading-3`; `xx-large` → `heading-1`.

## Layout and spacing

| Setting | Value | 0.3.4 value |
|---|---|---|
| Content width (reading) | 680px | 960px |
| Wide width | 1160px | 1280px |
| Edge spacing (`edge-space`) | 80px; 20px below 890px | root padding |
| Block gap | 20px | 1.25rem |

### Spacing scale

| Slug | Value |
|---|---|
| `10` | 10px |
| `20` | 20px |
| `30` | 30px |
| `40` | clamp(30px, 5vw, 50px) |
| `50` | clamp(40px, calc(5vw + 10px), 60px) |
| `60` | clamp(20px, calc(10vw - 40px), 80px) |
| `70` | 100px |
| `80` | clamp(80px, calc(6.67vw + 40px), 120px) |
| `90` | clamp(80px, 13.33vw, 160px) |
| `edge-space` | 80px; 20px below 890px |

Typical use: `60` for vertical padding of full-width sections, `40` before an h2, `30` between an h2 and its content, `20` inside cards.

### Responsive behavior

Breakpoints (the `@wordpress/base-styles` values used by the parent, plus its 890px edge-spacing step): 600px, 782px, 890px, 960px, 1080px.

- Video and term grids: 4 columns (3 for events) on wide screens, 2 below 960px, 1 below 600px.
- Two-column layouts (hero, spotlight, single details): stacked below 782px (hero, spotlight) or 1080px (two-column display). **(WPTV, owner-confirmed during implementation):** at 782–1079px, the hero uses equal columns and a fluid 50–59px title; from 1080px it retains 5:7 columns and 70px. Below 600px the title remains 52px. Do not split the WordPress.tv name to preserve the desktop size.
- Header links collapse into a menu below 1080px.
- No horizontal scrolling at 320px width.

## Global chrome

WordPress.org sites have a global header (`wporg/global-header`) and footer (`wporg/global-footer`) from `wporg-mu-plugins`, with a local navigation bar (`wporg/local-navigation-bar`) under the global header. WordPress.tv cannot use those blocks. Replicate with core blocks:

### Header **(WPTV adaptation of the local navigation bar)**

- Same on every page. White background, 1px `light-grey-1` bottom rule, `charcoal-1` text, min height 57px, horizontal padding `edge-space`, sticky from 890px.
- Left: the WordPress "W" mark (28px, `currentColor`, the core `w-logo` artwork, `aria-hidden`) followed by the site title "WordPress.tv" in `small` weight 600. Both link to the home page. With no global header above, the mark ties the site to WordPress.org.
- Right: Navigation (existing links: WordCamp.tv, How-To, Learn WordPress, Get Involved) in `small`, a Search block with the light style, and "Submit a video" as a small fill button.
- Below 1080px: the title stays on the left with a "Menu" toggle on the right; the menu opens below with links as rows separated by `light-grey-1` rules, a full-width search field and the button.
- Do not copy the WordPress.org global header (its menu, "Get WordPress" button or layout).

### Footer

- `charcoal-1` full-width band, white text in `small`, padding `50` top and `30` bottom.
- Three columns in a 2:1:1 ratio (50%/25%/25% after gutters): text-only WordPress.TV Site Title (Inter 600 `large`) and the existing community description; Contribute (Submit a video, Get involved, Make WordPress); About (About WordPress.tv, Powered by VideoPress linking to https://videopress.com/, Privacy). Link-column headings remain Inter 600 `small`. No Watch section or separate WordPress column. Add decorative aria-hidden ↗ indicators to Make WordPress, Powered by VideoPress and Privacy without changing their destinations or targets.
- Below a `white-opacity-15` rule, show the owner's supplied WordPress.org wordmark linking to https://wordpress.org/, followed by "The WordPress® trademark is the intellectual property of the WordPress Foundation." in `small` `charcoal-5` text. The local white SVG is an editable Image block with WordPress.org alt text, 27px high with proportional automatic width. The paragraphs directly following the footer Site Title and the subfooter wordmark have zero top margin; other spacing is unchanged. This owner-requested subfooter is a scoped exception to not copying the global footer, not an official design or trademark approval. No Creative Commons badge or license notice in the footer; retain them in each video's details.
- Below 782px, place the brand/description across the full width, followed by the two link columns.

## Components

### Links

- Blueberry 1, underlined (1px, offset 0.15em); hover removes the underline.
- Title links (post titles, comment authors, pagination, previous/next, header and footer links) are not underlined; hover adds the underline.
- Visited links inside post content are Purple 1.
- On dark bands, links are white or Blueberry 2.
- Wrap arrow characters (→ ↗) in `<span aria-hidden="true">`, as the parent does. Use "See all →" links from a section to its archive.

### Buttons

| Property | Value |
|---|---|
| Font | Inter 600, `normal` (16px), line height 1.3 |
| Padding | 14px 32px; small buttons 7px 12px with `small` text |
| Border | 1px solid, radius 2px |
| Fill | Blueberry 1 background, white text; hover Deep Blueberry; active Charcoal 1 |
| Outline | transparent background, Blueberry 1 text and border; hover Deep Blueberry fill, white text |
| Focus | inset 3px white shadow plus 1.5px Blueberry 1 outline |

Register the parent style names: `outline`, `fill-on-dark`, `outline-on-dark`, `text`, `toggle`.

### Forms and search

- Inputs and textareas: white, 1px `charcoal-4` border **(WPTV:** the parent uses `charcoal-5`, which gives 2.8:1 against white, below the 3:1 required for component boundaries**)**, 2px radius, `small` text, padding 8px 15px.
- Search: `light-grey-2` field without a border and an icon button inside, as on .org. Focus shows a 1.5px Blueberry 1 ring.
- The same rules apply to the submit-video and subtitle forms when they are styled.

### Cards grid

Based on the parent's `is-style-cards-grid` (Post Template or grid Group):

- Each card: 1px `light-grey-1` border, 2px radius, Charcoal 1 text; equal row heights; one column below 600px.
- Linked cards: category/event term cards retain their native stretched title link. Video cards have exactly one native link, on the title; frontend JavaScript expands pointer activation to the card using that URL, without overlays, extra roles or tab stops. Preserve text selection, controls, modified/middle clicks and keyboard use of the native title. Without JavaScript, the title still navigates. Hover retains the existing card background.
- Video card **(WPTV)**: unlinked decorative 16:9 thumbnail flush with the top edge, then all speakers (`small`, Charcoal 4, plain text separated by commas with one trailing colon), linked title (Inter 600 `normal`), and unlinked date (`extra-small`, Charcoal 4), aligned at the bottom of each row. Omit absent speakers without an author fallback. Featured retains its existing undated presentation. Padding `20` below the image.
- Term card **(WPTV)**: title in EB Garamond 22px (events) or Inter 600 `normal` (compact category cards), count in `small` Charcoal 4.
- On dark bands: transparent background, `white-opacity-15` borders, white titles, Charcoal 5 secondary text, Charcoal 1 hover.
- Replaces the 0.3.4 `video-frame` and `event-tile` styles (8px frames, tinted tiles).

### Two-column display

The parent's `is-style-two-column-display`: from 1080px, a left column of 460px (wide minus content width, minus the gap) for labels or metadata and a right column at reading width; stacked below 1080px. Used for single-video details and comments.

### Breadcrumbs

Core Breadcrumbs on video singles only: `small`, Charcoal 4, "/" separators, links not underlined (hover: Blueberry 1, underlined). The owner explicitly removed breadcrumbs from every taxonomy archive, including categories, WordCampTV, events, tags and custom taxonomies. This supersedes archive breadcrumb references elsewhere in the design history.

The owner-approved single-video selection replaces the generic core hierarchy and deliberately differs from the legacy `wptv2` helpers:

| Assigned navigation terms | Trail after Home |
|---|---|
| Event and `wordcamptv` | WordCampTV, then the first event |
| Event, without `wordcamptv` | First event only; omit all categories |
| No event | One category, in priority order `education`, `how-to`, `wordcamptv` |
| No event or recognized category | Nothing; retain Home only |

Use only directly assigned terms, not category ancestors. Never introduce Year, Location, their children, arbitrary categories, the video title or pagination items. Preserve native term order when several events are assigned. Resolve terms from the queried video, not a nested loop's current post. Missing/error term results are treated as absent, and invalid term links are omitted.

Implementation: `WordPressTV\Setup\breadcrumb_terms()` holds the selection policy; `filter_breadcrumb_items()` supplies structured items through `block_core_breadcrumbs_items`. Core keeps ownership of markup, label/URL escaping and accessibility. The three taxonomy templates no longer contain the block; the filter also returns an empty trail for any taxonomy archive. No routes, queries, stored content or relationships change. Unlike `wptv2`, archive trails no longer depend on the first video in an event listing.

Focused, read-only policy checks: `wp eval-file wp-content/themes/wptv-27/tests/blocks/breadcrumbs.php` (use the Studio WP-CLI tool). The 18 cases cover category precedence, structural-category exclusion, event suppression/WordCampTV exception, first-event order and unchanged input terms.

### Separators and rules

1px; `light-grey-1` on white, `white-opacity-15` on dark.

### Pagination

Centered; middots between Previous, numbers and Next from 600px; current page underlined; stacked below 600px; links not underlined.

### Previous/next post

Full-width row with a 1px `light-grey-1` top rule. Each side: label (`small`, Charcoal 4, with an arrow) above the title (Blueberry 1). Next aligned to the end.

### Comments

Avatar 40px, round. Author Inter 600, Charcoal 1, not underlined. Date `small`, Charcoal 4. Reply/edit links `small`. Form fields as in [Forms](#forms-and-search); submit as a fill button.

### Call to action (heading with arrow)

Parent `is-style-with-arrow` on a Blueberry 1 full-width band: a large Inter 300 heading link (`heading-2`) with an arrow, plus one sentence in `large`. Used once, for "Get involved" at the end of the front page.

## Video, imagery and metadata

- **Player:** stored post content (shortcode or VideoPress block) rendered by core Post Content, never rewritten. 16:9, no border or radius (the VideoPress iframe cannot be styled).
- **Single-video player band (WPTV):** a full-width `charcoal-1` band that contains only the player, padding `30` above and below; on phones (below 600px) no padding, so the video runs edge to edge. Title above and reactions below stay on white.
- **Thumbnails:** 16:9, 2px radius (top corners inside cards), no frame. Real VideoPress posters only; the bundled placeholder is the explicit fallback. Lazy loading stays.
- **Metadata rows (WPTV):** a definition list in `small`: label column (Charcoal 4) and value column in 1:2 proportion, a `light-grey-1` rule between rows. Multiple term links stay inline and wrap inside the value column.
- **Missing data:** downloads, subtitles and other multisite-only data are omitted when unavailable, never fabricated or shown as placeholders.
- No synthetic speaker or conference images, no cosmetic filters.

## Accessibility and interaction

Requirement: WCAG 2.2 level AA (WordPress Accessibility Coding Standards).

### Contrast of approved pairs

| Pair | Ratio | Use |
|---|---|---|
| `charcoal-1` on `white` | 16.7:1 | Body text |
| `charcoal-4` on `white` | 5.5:1 | Secondary text, input borders |
| `charcoal-4` on `light-grey-2` | 5.0:1 | Counts on the categories band |
| `blueberry-1` on `white` | 5.6:1 | Links |
| `blueberry-1` on `light-grey-2` | 5.2:1 | Links on grey bands |
| `white` on `blueberry-1` | 5.6:1 | Buttons, CTA band |
| `white` on `deep-blueberry` | 7.7:1 | Button hover |
| `white` on `charcoal-2` | 14.9:1 | Featured band |
| `charcoal-5` on `charcoal-2` | 5.3:1 | Secondary text on dark |
| `blueberry-2` on `charcoal-2` | 7.2:1 | Links on dark |
| `purple-1` on `white` | 10.0:1 | Visited links |

Not allowed for text or required component boundaries: `charcoal-5` on white (2.8:1), `light-grey-1` on white (1.4:1; decorative card borders only, since card content carries its own contrast).

### States

- **Focus:** every interactive element shows a visible focus indicator: 1.5px Blueberry 1 outline (2px offset for links and cards; inset with a 3px white shadow for buttons). Never remove outlines without a replacement. On dark bands use Blueberry 2 or white.
- **Hover:** color and underline changes only; no motion.
- **Active:** buttons go to Charcoal 1.
- **Current page:** navigation and pagination mark the current item with `aria-current` and an underline.
- **Target size:** interactive targets at least 24×24px (WCAG 2.2, 2.5.8), including pagination numbers and the menu toggle.
- **Sticky header:** set `scroll-padding-top` to the header height so focused or anchored elements are not hidden under it (WCAG 2.2, 2.4.11).

### Structure

- One h1 per view. Landmarks: header, main, footer, `nav` with labels for main, breadcrumbs, pagination and previous/next.
- The mobile menu toggle is a button with `aria-expanded` and `aria-controls`.
- Decorative images and the W mark have empty `alt` or `aria-hidden`; thumbnails inside linked cards have empty `alt`, since the title names the link.
- Respect `prefers-reduced-motion`; no autoplay.
- English only; no RTL stylesheet for now.

## Page layouts

### Front page

Order changed by the owner (recent videos before categories and events; "Get involved" added).

1. Header.
2. **Hero:** white, wide two columns (5:7 from 1080px; 1:1 with a smaller 50–59px heading at 782–1079px, as confirmed by the owner). Left: h1 "Welcome to WordPress.tv" (`heading-1`), intro in `large`, two buttons: fill "Browse WordCamp talks" (`/category/wordcamptv/`) and outline "Submit a video". Right: the latest video player on white with a `small` caption (label, linked title, speakers). Stacks below 782px.
3. **Featured videos:** `charcoal-2` band, h2 with "All featured videos →" (tag archive), four featured cards on dark.
4. **Recent videos:** white, h2, inherited main query, four-column video cards with dates, pagination. Eight posts per page, controlled by existing query logic.
5. **Video categories:** `light-grey-2` band, h2, compact term cards in four columns with counts.
6. **Recent events:** white, h2 with "All WordCamp Videos →" linking to `/category/wordcamptv/`, twelve event cards in three columns with video counts. The owner confirmed this temporary label and destination; they may change later.
7. **Get involved:** Blueberry 1 call-to-action band linking to `/get-involved/`, one sentence presenting WordPress.tv as a volunteer, collaborative project. Not a call for speakers.
8. Footer.

Mockup: [`mockups/front-page.html`](mockups/front-page.html).

### Single video

1. Header.
2. **Introduction (white):** breadcrumbs, h1 (`heading-2`, using the full available wide-grid introduction width without a 22ch cap), linked speakers in `large`.
3. **Player band:** full-width `charcoal-1`, player only, wide width.
4. **Reactions (white):** Jetpack Like and Sharing below the band, aligned with the player edges.
5. **Details:** two-column display. Left: metadata rows (published, event, speakers, tags, language, producer, downloads, subtitles) and the CC BY-SA license. Right: full description with the slides link, `large` text. Stacks below 1080px with the description first.
6. **Comments:** two-column display; left: "N responses" h2 (`heading-4`); right: comments, pagination and form.
7. **Previous/next.**
8. Footer.

Mockup: [`mockups/single-video.html`](mockups/single-video.html) (downloads and subtitles appear as labeled placeholders only in the mockup).

### WordCampTV (owner-approved replacement, local 0.4.1)

See [wordcamptv.md](wordcamptv.md) for data/date rules, transients and invalidation. Reuse the shared title band, header and footer. The landing uses the native category spotlight: one latest video player plus the next five small rows, without a Recent videos heading, remaining-video grid or video pagination. The underlying 22-post query and existing later-page URLs are preserved. On page one only, append a white Latest WordCamps band: h2, then an h3 per event with View all alongside, followed by four dated cards with h4 titles. Append a light-grey-2 All WordCamp videos band: h2, descending h3 year headings, three-column linked event lists in Inter normal (two columns below 960px, one below 600px). Retain 1160px wide alignment, normal edge gutters and spacing-50 band padding. Use explicit empty states rather than invented dates or events. No manual event menu or legacy left sidebar; language filters and year navigation remain deferred.

### Category and event archives (0.3.4 structure kept except WordCampTV)

1. Header.
2. **Title band:** full-width `light-grey-2` band with the term title h1 (`heading-2`), left-aligned on the wide grid, without breadcrumbs. Categories show the term description in `large` below the title at reading width; events keep omitting it.
3. **Spotlight** (page one, more than six results; unchanged rules): white section, two columns (larger left) stacked below 782px. Lead: the interactive player with a `charcoal-1` caption block below it (plain speakers in `small` Blueberry 2, then linked title in white Inter 600 `large`), 2px radius on the caption, no 8px frame. Only the caption participates in expanded card navigation, never the player. Secondary list: five rows, each an unlinked 16:9 thumbnail (about 40% width), plain speakers in `small` Charcoal 4, then the linked title (Inter 600 `normal`), separated by `light-grey-1` rules. Both caption sizes show all speakers separated by commas followed by a colon, omit absent speakers and never display event terms. No dates in the spotlight.
4. **Remaining videos:** cards grid in four columns, pagination.
5. Footer.

### General archives (tags, speakers, languages, producers, dates)

Title band as above without breadcrumbs or description (0.3.4 centers the title; left-align it on the wide grid), then the video cards grid and pagination.

### Search

Title band with the query title h1 and a search field (`light-grey-2`, full reading width), then the video cards grid, pagination and the editable no-results message.

### Page

Title band with the h1 left-aligned at reading width, then content at 680px with .org element styles.

### 404

h1 (`heading-2`) at reading width, one short explanation and the search field. Keep the 404 status.

### Not covered

The forms/admin screens remain unimplemented. WordCampTV is now defined and implemented as the owner-approved replacement above, not the legacy featured/event-menu page.

## What stays and what changes

| Area | Stays | Changes |
|---|---|---|
| Routes, main queries, page sizes, pagination, editorial flow | All | Nothing |
| Templates and template structure | Existing templates, generic spotlight split and inherited queries | Presentation plus the explicitly approved WordCampTV category override and independent event sections |
| Front page | Hero, featured, categories, events, recent videos, queries | Recent videos move up; "Get involved" band added; hero gets two buttons **(owner)** |
| Header | Links, search, "Submit a video" | White .org-style bar with the W mark; collapses below 1080px |
| Footer | Owner-approved current links and trademark notice | Three columns and the supplied WordPress.org subfooter mark on `charcoal-1`; no footer CC notice |
| Palette | Values of blue, coal, coal-2, white | .org slugs; text `#1e1e1e`; gray becomes `#f6f6f6`; orange removed; new greys and accents |
| Typography | Inter and EB Garamond, bundled | .org size scale; body 16px/1.875 |
| Layout | Full-width bands, wide grids | 680px / 1160px widths, edge spacing, new breakpoints |
| Video frames | 16:9 real posters, placeholder | No 8px frames; cards with 1px borders and 2px radius |
| Single video | Order of sections | Player on a full-width dark band; details in a two-column display; comments in two columns |
| Lockdown | Site Editor blocked, presets only | Nothing |

## Ownership

Templates reference hidden PHP theme patterns. Design is maintained in code and cannot be changed through the Site Editor, user global styles or Additional CSS. Institutional page content stays editable. Keep PHP logic in `inc/`. Bump the theme version when adding or removing pattern files so core refreshes its cached pattern inventory.

## Owner follow-up confirmations

The owner confirmed the official W mark in `charcoal-1`, `charcoal-4` borders on ordinary inputs, and borderless search fields on `light-grey-2`. Keep visible focus and verify accessibility rather than treating preference as proof of conformance. "All featured videos →" links to the `featured` tag archive; Recent events uses "All WordCamp Videos →" linking to `/category/wordcamptv/` for now. Decorative arrows remain hidden from assistive technology.

No Design team review is requested for now. Owner approval is not official endorsement or evidence of separate trademark permission. Keep the existing title-band structure. WordCampTV now follows the explicitly approved replacement above; forms/admin remain outside this design handoff's defined layouts. See [design-handoff.md](design-handoff.md#decisions-needing-owner-confirmation). These follow-up decisions take precedence over differing mockup labels.
