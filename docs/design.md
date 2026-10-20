# Design: WordPress.org visual language

WordPress.tv is a WordPress.org community site, so wptv-27 follows the visual language of the new WordPress.org sites (the 2021/2022 redesign). This document translates that language into rules for this theme.

> **Status: proposal.** Drafted on the `design/wporg-visual-language` branch. It replaces the interim wptv3 direction only after the owner approves it. Until then, the code on `trunk` keeps the wptv3-based tokens described in the `trunk` version of this file. Approval must be recorded in [decisions.md](decisions.md), [open-questions.md](open-questions.md) (question 26) and [plan.md](plan.md).

## Sources

Reference only. Do not copy code without review, and never add these repositories as dependencies.

| Repository | Role | Studied at |
|---|---|---|
| [WordPress/wporg-parent-2021](https://github.com/WordPress/wporg-parent-2021) | Parent block theme shared by every new WordPress.org site. Owns the tokens (`theme.json`), base element styles, block styles and generic templates. | `70f374a` (2026-09-04) |
| [WordPress/wporg-main-2022](https://github.com/WordPress/wporg-main-2022) | Child theme for wordpress.org itself. Shows how a site composes the parent tokens: global header/footer, local navigation bar, page patterns, extra display fonts. | `c275122` (2026-10-14) |

Key files in the parent theme: `theme.json`, `readme.md` (design token tables), `inc/block-styles.php`, `sass/base/_elements.scss`, `sass/block-styles.scss`, `sass/blocks/*.scss`.

## How wptv-27 adopts it

1. **Copy the tokens, do not inherit the theme.** `wporg-parent-2021` is not available on the WordPress.com multisite, and it depends on `wporg-mu-plugins` (global fonts, `wporg/global-header`, `wporg/global-footer`, `wporg/local-navigation-bar`), which only run on WordPress.org infrastructure. The owner confirmed that WordPress.tv has no access to them. wptv-27 stays a standalone block theme and reproduces the language with its own `theme.json`, core blocks and `style.css`.
2. **Follow the language, not a copy of a site.** WordPress.tv does not have to look identical to any wordpress.org page. It must use the same visual language: tokens, typography, components and the restraint of the .org sites.
3. **Use the same preset slugs as WordPress.org.** Colors, font sizes and spacing use the parent slugs (`charcoal-1`, `blueberry-1`, `heading-2`, `edge-space`, and so on). The generated CSS variables then match the other .org sites, and block markup can move between projects without translation.
4. **Keep the design lockdown.** The parent theme enables `appearanceTools` and custom spacing. wptv-27 keeps the current lockdown (no custom colors, sizes or spacing in the editor, no Site Editor). Only the token values change; editors still pick from presets.
5. **Recreate .org components with core blocks.** Where the parent uses a block style (cards grid, two-column display, button variants), register the same style name in `inc/setup.php` and style it in `style.css`. Where .org uses a `wporg/*` block, approximate it with core blocks (see [Global chrome](#global-chrome)).

## Color

Body text is Charcoal on white. Blueberry is the only accent: links, primary buttons, focus rings and the occasional full-width call-to-action band. Dark bands use Charcoal 1/2 with white text. Structure comes from 1px Light Grey 1 rules, not from tinted backgrounds.

### Palette

Register this subset of the parent palette. Slugs and values are identical to `wporg-parent-2021`.

| Slug | Name | Hex | Use |
|---|---|---|---|
| `charcoal-0` | Charcoal 0 | `#1a1919` | Reserved (equals the current `ink`) |
| `charcoal-1` | Charcoal | `#1e1e1e` | Body text, dark bands, active button state |
| `charcoal-2` | Charcoal 2 | `#23282d` | Local navigation bar, featured video band |
| `charcoal-3` | Charcoal 3 | `#40464d` | Search field on dark backgrounds |
| `charcoal-4` | Charcoal 4 | `#656a71` | Secondary text: dates, captions, counts, labels |
| `charcoal-5` | Charcoal 5 | `#979aa1` | Form field borders |
| `light-grey-1` | Light Grey | `#d9d9d9` | Rules, card borders, section dividers |
| `light-grey-2` | Light Grey 2 | `#f6f6f6` | Search field, card hover, subtle bands |
| `white` | White | `#ffffff` | Page background, text on dark |
| `white-opacity-15` | White 15% | `#ffffff26` | Rules on dark backgrounds |
| `black-opacity-15` | Black 15% | `#00000026` | Header bottom border on white |
| `dark-blueberry` | Dark Blueberry | `#1d35b4` | Reserved |
| `deep-blueberry` | Deep Blueberry | `#213fd4` | Button hover |
| `blueberry-1` | Blueberry | `#3858e9` | Links, primary buttons, focus, CTA bands |
| `blueberry-2` | Blueberry 2 | `#9fb1ff` | Links and hover on dark backgrounds |
| `blueberry-3` | Blueberry 3 | `#c7d1ff` | Reserved |
| `blueberry-4` | Blueberry 4 | `#eff2ff` | Text-button hover background |
| `purple-1` | Purple 1 | `#5300be` | Visited links inside post content |
| `pomegrade-1` | Pomegranate | `#e26f56` | Destructive or error accents only |

The rest of the parent palette (Acid Green, Lemon, other Pomegranate and Purple tints) is for marketing pages. Leave it out unless a design needs it. Keep the parent slug spelling `pomegrade-*` even though the name is "Pomegranate".

Never register a color slug named `text` (see the `ink` decision in [decisions.md](decisions.md)). No parent slug collides with core utility classes.

### Mapping from the current tokens

| Current slug | Hex | New slug | Change |
|---|---|---|---|
| `ink` | `#1a1919` | `charcoal-1` (body text) | Text becomes `#1e1e1e`, as on .org |
| `coal` | `#1e1e1e` | `charcoal-1` | Same value |
| `coal-2` | `#23282d` | `charcoal-2` | Same value |
| `blue` | `#3858e9` | `blueberry-1` | Same value |
| `gray` | `#edeff0` | `light-grey-2` | Lighter: `#f6f6f6` |
| `orange` | `#d54e21` | none | Dropped. Not part of the .org palette |
| `white` | `#ffffff` | `white` | Same value |

wptv3 was already derived from the .org palette, so most values stay the same. The migration is mainly a slug rename.

## Typography

| Family | Slug | Use |
|---|---|---|
| Inter | `inter` | Body, UI, metadata, buttons, video titles in grids |
| EB Garamond | `eb-garamond` | Headings h1–h6, page titles, large display text |
| IBM Plex Mono | `monospace` | `code` and `pre` only (optional) |

- Headings: EB Garamond, weight 400, `text-wrap: balance`. Body: `text-wrap: pretty`.
- Body text is 16px with line height 1.875. Short UI text (cards, captions, lists) uses 1.625 (the parent `is-style-short-text`).
- When a heading is set in Inter (for example a video title on a card), use weight 600 and line height 1.3–1.4.
- WordPress.org loads fonts through its global fonts mu-plugin. wptv-27 uses local fonts only (confirmed by the owner): it keeps bundling the variable WOFF2 files it already has (Inter 100–900, EB Garamond 400–800) and declares them in `theme.json`. No font CDN. Add IBM Plex Mono only if code blocks appear in content.
- Speaker names, titles and descriptions come in many scripts. EB Garamond covers Latin, Cyrillic and Greek. Other scripts fall back to the system serif, which is acceptable for headings.

### Font sizes

Same slugs and values as the parent. Below 600px, the larger sizes switch to the small-screen value.

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

`h1`–`h6` elements map to `heading-1`–`heading-6`. Post terms, post date, comment dates and captions use `small`.

Current slugs map as follows: `small` (0.9rem) → `small` (14px); `medium` (1.05rem) → `normal`; `large` → `heading-5`; `x-large` → `heading-3`; `xx-large` → `heading-1`. Pattern attributes that use `medium`, `x-large` or `xx-large` must be updated when the tokens change.

## Layout and spacing

| Setting | Value |
|---|---|
| Content width | 680px (reading width: descriptions, comments, page text) |
| Wide width | 1160px (player, grids, section headings) |
| Edge spacing (`edge-space`) | 80px; 20px below 890px. Horizontal padding of full-width sections |
| Block gap | 20px |

Current values change from 960px / 1280px to 680px / 1160px.

### Spacing scale

Same slugs and values as the parent.

| Slug | Name | Value |
|---|---|---|
| `10` | 3X-Small | 10px |
| `20` | 2X-Small | 20px |
| `30` | X-Small | 30px |
| `40` | Small | clamp(30px, 5vw, 50px) |
| `50` | Medium | clamp(40px, calc(5vw + 10px), 60px) |
| `60` | Large | clamp(20px, calc(10vw - 40px), 80px) |
| `70` | X-Large | 100px |
| `80` | 2X-Large | clamp(80px, calc(6.67vw + 40px), 120px) |
| `90` | 3X-Large | clamp(80px, 13.33vw, 160px) |
| `edge-space` | Edge Spacing | 80px; 20px below 890px |

Typical use: `60` for vertical padding of full-width sections, `40` before an `h2`, `30` between an `h2` and an `h3`, `20` inside cards.

### Breakpoints

The parent uses the `@wordpress/base-styles` breakpoints: 600px (small), 782px (medium), 960px (large), 1080px (xlarge). It adds 890px for edge spacing and the sticky local bar. Use the same values in `style.css`.

## Global chrome

Every new .org site has two layers of chrome:

1. **Global header** (`wporg/global-header`): the WordPress.org bar with the W logo, the .org menu, search and the "Get WordPress" button. Dark by default; white with a `black-opacity-15` bottom border on some pages. The **global footer** (`wporg/global-footer`) closes the page, with "white-on-black" and "black-on-white" styles.
2. **Local navigation bar** (`wporg/local-navigation-bar`): a 57px bar under the global header. The site title is on the left, in `small`, and links to the site home. The local menu is on the right. It is sticky from 890px, either `charcoal-2` with white text or white with a `light-grey-1` bottom border. On mobile, the menu collapses into a dropdown (`is-style-dropdown-on-mobile`).

These blocks come from `wporg-mu-plugins`, which WordPress.tv cannot use. wptv-27 replicates the chrome with core blocks:

- **Header part:** a local navigation bar built with core blocks. Site title "WordPress.tv" on the left, Navigation on the right (existing wordpress.tv links), a Search block (`is-style-on-dark` style), and "Submit a video" as a small `fill-on-dark` button. `charcoal-2` background, sticky from 890px, dropdown menu below 890px.
- **Global header:** do not copy the WordPress.org global header (logo, .org menu, "Get WordPress"). The local navigation bar is the only header. It follows the .org look without pretending to be wordpress.org.
- **Footer part:** a `charcoal-1` band in the global-footer layout (columns of links, small Inter text, `white-opacity-15` rules), plus the CC BY-SA notice.

## Components

### Links

- Blueberry 1, underlined (1px, offset 0.15em). Hover removes the underline.
- Title links (post titles, comment authors, pagination, previous/next) are not underlined; hover adds the underline.
- Inside post content, visited links are Purple 1.
- On dark backgrounds, links are white or Blueberry 2.
- Wrap arrow characters (→ ↗) in `<span aria-hidden="true">`, as the parent does. Use "See all →" style links to move from a section to its archive.

### Buttons

| Property | Value |
|---|---|
| Font | Inter 600, `normal` (16px), line height 1.3 |
| Padding | 14px 32px; small buttons 7px 12px with `small` text |
| Border | 1px solid, radius 2px |
| Fill | Blueberry 1 background, white text; hover Deep Blueberry; active Charcoal 1 |
| Focus | inset 3px white shadow plus 1.5px Blueberry 1 outline |

Block styles to register, with the same names as the parent: `outline`, `fill-on-dark`, `outline-on-dark`, `text`, `toggle`. The `toggle` style suits filter chips (for example, language or category filters).

### Forms and search

- Inputs: white, 1px Charcoal 5 border, 2px radius, `small` text, padding 15px × 8px.
- Search: Light Grey 2 field without a border, icon button inside. Use the `on-dark` style (Charcoal 3 field, light text) in the dark navigation bar.
- These rules also apply to the submit-video and subtitle forms.

### Cards grid (video and term grids)

The parent's `is-style-cards-grid` on Post Template or a grid Group is the basis for every video, event and category grid:

- Each item: 1px Light Grey 1 border, 2px radius, padding `20`, Charcoal 1 text.
- Equal row heights. One column below 600px.
- Linked cards: the heading is Blueberry 1, and hover sets a Light Grey 2 background.
- Video card: a 16:9 thumbnail flush with the top edge, then the title (Inter 600, `normal` or `large`), then speakers and date in `small` Charcoal 4.
- Columns: 4 on wide screens, 2 on tablets, 1 below 600px.

This replaces the current `video-frame` and `event-tile` block styles (8px frames and tinted tiles).

### Two-column display ("Shifted content")

The parent's `is-style-two-column-display` (Group, Columns, Post Content): from 1080px, a narrow left column (wide width minus content width, 480px) holds labels or metadata, and the right column holds content at reading width. Below 1080px, it stacks. Use it for:

- archive headers: query title on the left, description and filters on the right;
- single-video details: metadata on the left, description on the right (see below).

### Separators and rules

1px `currentColor`; Light Grey 1 for page rules. Full-width rules mark section boundaries (`entry-header` pattern: 70px space, rule, 70px space).

### Pagination

Centered. Middots between Previous, numbers and Next from 600px. The current page is underlined. Stacked vertically on mobile. Links are not underlined.

### Previous/next post

A 1px Light Grey 1 top border with edge-space padding. Label and title, with a 2px Blueberry dot between them from 960px.

### Comments

Avatar 40px, round. Author name Inter 600, not underlined. Date `small`, Charcoal 4. Reply and edit links in `small`. Comments, pagination and form are in the reading column.

### Optional .org flourishes

Use these sparingly, and only for calls to action:

- **Heading with arrow** (`is-style-with-arrow`): a large Inter light heading link with a drawn arrow, on a Blueberry 1 band (for example "Submit a video").
- **Dots spacer** (`is-style-dots-background`): a decorative dotted band between sections.
- **Brush stroke** (`is-style-brush-stroke`): a painted bottom edge on a Blueberry subnav bar.

## Video

Neither repository has a video-library design, so these rules extend the language.

- **Player:** wide width (1160px), 16:9, no border, no radius (the VideoPress iframe cannot be styled). On the single-video page, the preferred direction is a full-width `charcoal-1` band behind the player (the owner likes it; it still needs a design study, see below). The front-page hero keeps the player on white.
- **Thumbnails:** 16:9, radius 2px (matching cards), no frame. Real VideoPress posters only, with the bundled placeholder as the explicit fallback.
- **Metadata:** compact `small` Inter text, labels in Charcoal 4, values as links. Use label/value rows inside the left column of a two-column display.
- **Duration, counts:** `extra-small` or `small`, Charcoal 4. Never invent data that is missing.
- The `has-16px-radius` and custom-controls video styles in wporg-main-2022 are for self-hosted `<video>` elements on marketing pages. They do not apply to VideoPress embeds.

## Page maps

The section order and query behavior approved for Phase 1b stay the same (see [decisions.md](decisions.md)). Only the presentation changes.

### Front page

1. **Header:** local navigation bar (`charcoal-2`).
2. **Hero:** wide two columns. On the left, an `h1` in `heading-1` and a short intro (`is-style-short-text`). On the right, the latest video player. Stacks below 782px.
3. **Featured:** full-width `charcoal-2` band, white `h2`, four videos as cards (white borders at 15% on dark, or white cards).
4. **Recent events:** white section, `h2` plus a "See all →" link, cards grid in three columns, no invented artwork or dates.
5. **Video categories:** `light-grey-2` band, cards grid in four columns, counts in Charcoal 4.
6. **Recent videos:** white section, inherited main query, cards grid in four columns, .org pagination.
7. **Footer.**

### Single video

1. **Header:** local navigation bar (white style, sticky).
2. **Title area:** breadcrumbs in `small`, post title as `h1` (`heading-2` on video pages, so the player stays visible above the fold), speakers as links.
3. **Player:** wide, preferably on a full-width `charcoal-1` band (pending study).
4. **Reactions:** Jetpack Like and Sharing, aligned with the player edges.
5. **Details:** a two-column display. The left column holds published date, event, speakers, tags, language, producer, downloads and subtitles, and the license (label/value rows). The right column holds the full description and the slides link at reading width.
6. **Comments:** in the reading column.
7. **Previous/next:** the .org post-navigation pattern, full width with a top rule.
8. **Footer.**

### Archives (category, event, speakers, language, tag, search)

A two-column display header: query title (`heading-2`) on the left, term description and filters on the right. Then a cards grid in four columns and pagination. The `posts_per_page` values in [routes.md](routes.md) do not change.

### Pages and 404

Pages: `h1` in the reading column, content at 680px, .org element styles. 404: follow the parent `404-page` pattern (large heading, short text, search).

## Elevation, shape and motion

- No shadows or gradients. Depth comes from rules, borders and dark bands.
- Radius 2px for buttons, inputs, cards and thumbnails. Avatars are round. Full-width bands are square.
- No decorative animation. Hover changes color and underline only. Respect `prefers-reduced-motion`.

## Voice

US English. Factual, community-focused, concise. Headings are short. Interface strings use the `wptv` text domain.

## Migration impact (when approved)

This is a design-only change. Routes, queries and content do not change.

| Area | Change |
|---|---|
| `theme.json` | New palette, font sizes, spacing scale, layout widths, element and block styles. Keep the lockdown settings. |
| `style.css` | Port the parent rules that `theme.json` cannot express: cards grid, two-column display, button variants, search, pagination, post navigation, link details, small-screen font sizes. |
| `inc/setup.php` | Register the parent block style names. Retire `video-frame` and `event-tile`. |
| `patterns/*.php`, `parts/` | Rename slugs in block attributes (`coal-2` → `charcoal-2`, `gray` → `light-grey-2`, `x-large` → `heading-3`, and so on). Rebuild the header and footer as described above. Bump the theme version, because the pattern inventory changes. |
| `tests/blocks/` | Update checks that assert class names or slugs. |
| Site `DESIGN.md` (Studio) | Rewrite to match this document once approved, so the Studio agent follows the new direction. |

## Open questions

1. **Single-video player band:** the owner prefers a full-width dark band behind the player. Study it with a single-video mockup before adopting it: band color (`charcoal-1` or `charcoal-2`), where the title and reactions sit (on the band or below it), how the band meets the sticky navigation bar, and how it behaves on mobile.

## Answered

- **wporg-mu-plugins:** WordPress.tv has no access to them. Replicate the design with core blocks and theme code.
- **Global header:** the site does not have to be identical to a wordpress.org site, but it must follow the same visual language. Do not copy the .org global header; use the local navigation bar.
- **Font loading:** local fonts. Keep the bundled WOFF2 files; no CDN.
- **RTL:** no RTL support for now. WordPress.tv is English only. Do not ship RTL stylesheets; using logical CSS properties is still welcome but not required.
