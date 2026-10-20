---
name: WordPress.TV
description: Community-curated WordPress videos, in the WordPress.org visual language.
colors:
  charcoal-1: "#1e1e1e"
  charcoal-2: "#23282d"
  charcoal-3: "#40464d"
  charcoal-4: "#656a71"
  charcoal-5: "#979aa1"
  light-grey-1: "#d9d9d9"
  light-grey-2: "#f6f6f6"
  white: "#ffffff"
  white-opacity-15: "#ffffff26"
  deep-blueberry: "#213fd4"
  blueberry-1: "#3858e9"
  blueberry-2: "#9fb1ff"
  blueberry-4: "#eff2ff"
  purple-1: "#5300be"
  pomegrade-1: "#e26f56"
typography:
  heading-1: {fontFamily: EB Garamond, fontSize: 70px, fontWeight: 400, lineHeight: 1.05}
  heading-2: {fontFamily: EB Garamond, fontSize: 50px, fontWeight: 400, lineHeight: 1.2}
  heading-4: {fontFamily: EB Garamond, fontSize: 30px, fontWeight: 400, lineHeight: 1.33}
  body: {fontFamily: Inter, fontSize: 16px, fontWeight: 400, lineHeight: 1.875}
  card-title: {fontFamily: Inter, fontSize: 16px, fontWeight: 600, lineHeight: 1.4}
  small: {fontFamily: Inter, fontSize: 14px, fontWeight: 400, lineHeight: 1.71}
rounded: {default: 2px, avatar: 50%}
spacing: {"10": 10px, "20": 20px, "30": 30px, "40": "clamp(30px, 5vw, 50px)", "50": "clamp(40px, calc(5vw + 10px), 60px)", "60": "clamp(20px, calc(10vw - 40px), 80px)", edge-space: "80px; 20px below 890px"}
layout: {contentSize: 680px, wideSize: 1160px, blockGap: 20px}
components:
  button-primary:
    backgroundColor: "{colors.blueberry-1}"
    hoverBackgroundColor: "{colors.deep-blueberry}"
    textColor: "{colors.white}"
    rounded: "{rounded.default}"
    padding: 14px 32px
  button-small:
    padding: 7px 12px
    fontSize: 14px
  card:
    border: "1px solid {colors.light-grey-1}"
    rounded: "{rounded.default}"
    hoverBackgroundColor: "{colors.light-grey-2}"
imagery: {}
---

## Overview
WordPress.tv is a WordPress.org community site. Follow the visual language of the new WordPress.org sites (wporg-parent-2021 and wporg-main-2022): EB Garamond headings, Inter text, Charcoal on white, Blueberry as the only accent, 1px grey rules, 2px radii, no shadows. It does not copy any wordpress.org page or its global header; it uses the same language. The full specification, with sources and the migration plan, is the versioned theme document `docs/design.md`. Reference mockups: `docs/mockups/front-page.html` and `docs/mockups/single-video.html`. Design is maintained in theme code, not the Site Editor.

## Colors
Use the WordPress.org preset slugs exactly as listed (`charcoal-1`, `blueberry-1`, `light-grey-1`, and so on) so the generated CSS variables match the .org sites. White is the page background and Charcoal 1 the body text. Blueberry 1 marks links, primary buttons, focus and the closing "Get involved" band. Charcoal 2 is the featured-video band; Charcoal 1 is the single-video player band and the footer; Light Grey 2 is the categories band and card hover; Light Grey 1 draws every rule and card border. Charcoal 4 is for secondary text (dates, counts, labels). Purple 1 is only for visited links in post content; Pomegranate 1 only for errors. Never name a color slug `text`.

## Typography
Bundled local WOFF2 files only (Inter 100–900, EB Garamond 400–800); no font CDN. Headings use EB Garamond 400 with balanced wrapping; body uses Inter 16px/1.875 with pretty wrapping. Font size slugs follow the parent theme: `extra-small` 12, `small` 14, `normal` 16, `large` 20, `extra-large` 24, `huge` 32, `heading-6` 22 … `heading-1` 70, `heading-cta` 120 (px), with smaller values below 600px. Video titles on cards are Inter 600, not serif. Metadata, dates and terms use `small`.

## Layout
Content width 680px, wide width 1160px, block gap 20px, horizontal edge spacing 80px (20px below 890px). Breakpoints: 600, 782, 960, 1080px.

Header (every page): white local navigation bar with a 1px Light Grey 1 bottom rule, sticky. Left: the WordPress W mark (28px, current color) followed by "WordPress.tv", both linking home. Right: existing navigation links, a Light Grey 2 search field and a small "Submit a video" button. Below 1080px the links, search and button collapse into a menu.

Front page, in order:
- Hero: wide two columns, `h1` "Welcome to WordPress.tv" with a short intro and two buttons on the left, the latest video player on the right (on white). Stacks below 782px.
- Featured: Charcoal 2 full-width band, four featured videos as cards with 15% white borders.
- Recent videos: white, inherited main query, four-column cards, .org pagination (centered, middots). Eight posts per page are controlled by existing query logic.
- Video categories: Light Grey 2 band, compact cards in four columns with counts.
- Recent events: white, twelve event cards in three columns with video counts and a "See all →" link.
- Get involved: Blueberry 1 band, large Inter light "Get involved →" link to `/get-involved/` and one sentence presenting WordPress.tv as a volunteer project. Not a call for speakers.
- Footer.

Single video, in order:
- Header.
- Breadcrumbs (`small`, Charcoal 4), title `h1` at `heading-2`, linked speakers, all on white.
- Player on a full-width Charcoal 1 band that contains only the player (no band padding on phones).
- Jetpack Like and Sharing on white below the band, aligned with the player edges.
- Two-column display: metadata on the left (label/value rows with grey rules: published, event, speakers, tags, language, producer, downloads, subtitles, license), full description and slides link on the right at reading width. Stacks below 1080px with the description first.
- Comments: heading on the left, comments and form on the right.
- Previous/next links with a top rule.
- Footer.

Archives: two-column header (query title left, description and filters right), four-column video cards, pagination. Pages: reading column at 680px.

## Elevation & Depth
No shadows or gradients. Structure comes from 1px rules, card borders and full-width dark bands.

## Shapes
2px radius for buttons, inputs, cards and thumbnails; avatars are round. Full-width bands are square. Thumbnails are 16:9.

## Components
Recreate .org components with core blocks and same-named block styles: cards grid (`is-style-cards-grid`), two-column display (`is-style-two-column-display`), buttons (`fill`, `outline`, `fill-on-dark`, `outline-on-dark`, `text`, `toggle`), search, query pagination, post navigation and heading with arrow (`is-style-with-arrow`). WordPress.tv has no access to wporg-mu-plugins: do not use `wporg/*` blocks. Links are underlined and lose the underline on hover; title links work the other way round. Wrap arrows (→) in `aria-hidden` spans. Templates reference hidden PHP theme patterns. Resolve taxonomy slugs at render time.

## Do's and Don'ts
Do preserve existing routes, page sizes and content. Do keep logic in `inc/`. Do use the .org preset slugs. Do not copy the WordPress.org global header or footer. Do not edit the reference themes, hardcode IDs, invent event dates or missing video data, add RTL stylesheets (the site is English only), or use Additional CSS or global style database copies.

## Imagery
Real VideoPress posters and players only, never synthetic conference or speaker photos. The bundled placeholder is an explicit fallback. No cosmetic filters.

## Motion
No decorative animation. Hover changes color and underline only. Respect reduced-motion preferences.

## Voice
US English in code and interface. Community-focused, factual, concise; no promotional claims. Present WordPress.tv as a collaborative, volunteer-run project. Preserve editable institutional page content separately from the design.
