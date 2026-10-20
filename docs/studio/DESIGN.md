---
name: WordPress.TV
description: Community-curated WordPress videos.
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
  display: {fontFamily: EB Garamond, fontSize: 70px, fontWeight: 400, lineHeight: 1.05}
  headline: {fontFamily: EB Garamond, fontSize: 50px, fontWeight: 400, lineHeight: 1.2}
  body: {fontFamily: Inter, fontSize: 16px, fontWeight: 400, lineHeight: 1.875}
  label: {fontFamily: Inter, fontSize: 14px, fontWeight: 400, lineHeight: 1.71}
rounded: {sm: 2px, frame: 2px}
spacing: {sm: 20px, md: 50px, lg: 80px, xl: 120px}
components:
  button-primary:
    backgroundColor: "{colors.blueberry-1}"
    textColor: "{colors.white}"
    rounded: "{rounded.sm}"
    padding: 14px 32px
imagery: {}
---

## Overview
Follow the WordPress.org visual language, not the interim wptv3 look. WordPress.tv is a WordPress.org community site, so it uses the language of the new .org sites (wporg-parent-2021, wporg-main-2022): EB Garamond headings, Inter text, Charcoal on white, Blueberry as the only accent, thin grey rules and 2px radii. It does not copy any wordpress.org page or its global header. Design is maintained in theme code, not the Site Editor.

## Current approval
The owner approved the WordPress.org visual language on the `design/wporg-visual-language` branch; it replaces the wptv3-based design. The versioned theme document docs/design.md holds the full specification, sources and migration plan. Reference mockups: docs/mockups/front-page.html and docs/mockups/single-video.html. WordPress.tv has no access to wporg-mu-plugins, so every .org component is replicated with core blocks and theme code.

## Colors
Use the WordPress.org preset slugs exactly (charcoal-1, blueberry-1, light-grey-1…), so CSS variables match the .org sites. White is the default ground and charcoal-1 the body text. Blueberry-1 identifies links, primary buttons, focus and the closing Get involved band; deep-blueberry is button hover; blueberry-2 is links on dark. Charcoal-2 identifies the featured-video band; charcoal-1 the single-video player band and the footer; light-grey-2 the categories band, search fields and card hover. Light-grey-1 draws every rule and card border. Charcoal-4 is secondary text (dates, counts, labels); charcoal-5 form borders. Purple-1 only for visited links in post content; pomegrade-1 only for errors. All values come from theme.json presets. The current slugs map as coal → charcoal-1, coal-2 → charcoal-2, blue → blueberry-1, gray → light-grey-2, ink → charcoal-1; orange is dropped. Never use text as a color slug: its generated class collides with core's generic has-text-color class.

## Typography
Bundle the existing Inter and EB Garamond WOFF2 files; local fonts only, no CDN. Serif headings (EB Garamond 400, balanced wrapping); sans-serif body, video titles and metadata (Inter, pretty wrapping). Body 16px with line height 1.875. Use the parent font size slugs: extra-small 12px, small 14px, normal 16px, large 20px, extra-large 24px, huge 32px, heading-6 22px, heading-5 26px, heading-4 30px, heading-3 36px, heading-2 50px, heading-1 70px, heading-cta 120px; below 600px heading-1 is 52px, heading-2 30px, heading-3 26px, heading-4 22px. Video titles on cards are Inter 600 at normal size, not serif and not marketing-sized. Metadata, dates and terms use small.

## Layout
Content width 680px; wide width 1160px; block gap 20px. Edge spacing (edge-space) 80px, 20px below 890px. Spacing slugs follow the parent: 10 = 10px, 20 = 20px, 30 = 30px, 40 = clamp(30px, 5vw, 50px), 50 = clamp(40px, calc(5vw + 10px), 60px), 60 = clamp(20px, calc(10vw - 40px), 80px), 70 = 100px, 80 = clamp(80px, calc(6.67vw + 40px), 120px). Breakpoints: 600, 782, 960, 1080px. Homepage layout map, in order:
- Header: white local navigation bar with a 1px light-grey-1 bottom rule, sticky. Left: the WordPress W mark (28px, current color) then "WordPress.tv", both linking home. Right: existing navigation links, a light-grey-2 search field and a small Submit a video button. Collapses into a menu below 1080px.
- Hero: white wide two-column composition; display h1 "Welcome to WordPress.tv", short intro and two buttons (fill and outline) on the left, the latest video player and a small caption on the right. Stacks below 782px.
- Featured: charcoal-2 full-width band, white heading with an "All featured videos →" link, four videos tagged featured as cards on the wide grid; no fixed term IDs or offset.
- Recent videos: white band, inherited main query, four-column video cards and .org pagination; eight posts per page controlled by existing query logic.
- Video categories: light-grey-2 band, all nonempty top-level categories as compact cards with counts, four columns.
- Recent events: white band, twelve nonempty event terms by descending term ID as cards with video counts, three columns; not a popularity ranking.
- Get involved: blueberry-1 full-width band, large Inter light "Get involved →" link to /get-involved/ and one sentence presenting WordPress.tv as a volunteer, collaborative project. Not a call for speakers.
- Footer: charcoal-1 band, four columns of links, white-opacity-15 rule, brand and CC BY-SA notice.
At narrow widths, grids reduce to two columns below 960px and one below 600px. All bands remain square at the viewport edge. Do not add Popular videos or invent directory routes.

Single-video layout map:
- Header: reuse the shared header unchanged.
- Introduction: on white, small core breadcrumbs in charcoal-4, one h1 post title at heading-2, linked speaker terms underneath at large size.
- Player: wide post content on a full-width charcoal-1 band that contains only the player, rendering the stored shortcode or VideoPress block without rewriting it; no second player. No band padding on phones, so the video runs edge to edge.
- Reactions: native Jetpack Like and Sharing blocks on white below the band, aligned with the player edges; no replacement controls.
- Description/details: two-column display from 1080px. Metadata and Creative Commons license in the left column, complete stored excerpt with the presentation slides link in the right column at reading width. Stacks below 1080px with the description first.
- Discussion: comments heading in the left column; core comments, pagination and reply form in the right column. Honor existing comment settings.
- Navigation: previous/next post links with a top light-grey-1 rule, label in small charcoal-4 and title in blueberry-1.
- Footer: reuse the shared footer unchanged.
Metadata and downloads use small Inter text in label/value rows: 1:2 columns, labels in charcoal-4, a light-grey-1 rule between rows. Keep multiple term links inline in the value column and allow long values to wrap there, including on mobile. Unavailable multisite data (downloads, subtitles) is omitted rather than fabricated.

Archives: two-column header (query title at heading-2 on the left, description and filters on the right), four-column video cards and pagination. Pages: h1 and content in the 680px reading column.

## Elevation & Depth
No shadows or gradients. Thin rules, card borders and full-width dark bands provide structure.

## Shapes
2px radius for buttons, inputs, cards and video thumbnails; avatars are round. Full-width bands stay square. Thumbnails are 16:9 and flush with the top of their card.

## Components
Use core Query, Post Template, Terms Query and Term Template blocks. Templates reference hidden PHP theme patterns. The latest-video query is independent; recent videos inherit the main query unchanged. Resolve theme-owned taxonomy slugs at render time. Register .org block styles under the same names: cards grid (1px light-grey-1 border, 2px radius, 20px padding, light-grey-2 hover, linked title in blueberry-1), two-column display, buttons (fill, outline, fill-on-dark, outline-on-dark, text, toggle; Inter 600, 14px 32px padding, small 7px 12px), search, pagination (centered, middots between previous, numbers and next), post navigation and heading with arrow. Retire the wptv3 video-frame and event-tile styles. Do not use wporg/* blocks. Links are underlined and lose the underline on hover; title links work the other way round. Wrap arrows (→) in aria-hidden spans.

## Do's and Don'ts
Do preserve existing routes, page sizes and content. Do keep logic in inc/. Do use the .org preset slugs. Do not copy the WordPress.org global header or footer. Do not edit reference themes, hardcode IDs, invent event dates or missing video data, add RTL stylesheets (the site is English only), or use Additional CSS/global style database copies.

## Imagery
Use actual VideoPress imagery and players, never synthetic conference or speaker photos. The bundled legacy placeholder is an explicit local fallback, not a replacement for production thumbnails. No cosmetic filters.

## Motion
No new animation. Hover changes color and underline only. Respect reduced-motion preferences.

## Voice
US English in code and interface. Community-focused, factual, concise; no promotional claims. Present WordPress.tv as a collaborative, volunteer-run project. Preserve editable institutional page content separately from the design.
