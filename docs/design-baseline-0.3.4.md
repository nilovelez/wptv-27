---
name: WordPress.TV
description: Community-curated WordPress videos.
colors:
  blue: "#3858e9"
  coal: "#1e1e1e"
  coal-2: "#23282d"
  orange: "#d54e21"
  ink: "#1a1919"
  gray: "#edeff0"
  white: "#ffffff"
typography:
  display: {fontFamily: EB Garamond, fontSize: 3.27rem, fontWeight: 400, lineHeight: 1.15}
  headline: {fontFamily: EB Garamond, fontSize: 2.5rem, fontWeight: 400, lineHeight: 1.2}
  body: {fontFamily: Inter, fontSize: 1.05rem, fontWeight: 400, lineHeight: 1.6}
  label: {fontFamily: Inter, fontSize: 0.9rem, fontWeight: 600, lineHeight: 1.5}
rounded: {sm: 0.33rem, frame: 8px}
spacing: {sm: 1rem, md: 2.5rem, lg: 4rem, xl: 6.5rem}
components:
  button-primary:
    backgroundColor: "{colors.blue}"
    textColor: "{colors.white}"
    rounded: "{rounded.sm}"
    padding: 0.6rem 1rem
imagery: {}
---

## Overview
Follow the existing wptv3 design, not a new catalog direction. Preserve its community video-library character: EB Garamond section headings, Inter metadata, restrained blue links, framed video imagery and alternating full-width sections. Design is maintained in theme code, not the Site Editor.

## Current approval
The owner confirmed that the existing wptv3-based design stays in place while consulting the WordPress.org Design team. Do not adopt a new WordPress.org visual direction, tokens or shared components without fresh approval. See the versioned theme documents docs/design.md and docs/handoff.md for the decision and resume point.

## Colors
White is the default ground; gray separates categories and frames images. Coal-2 identifies the featured-video band, coal the footer. Blue identifies links and the submission action. All values come from theme.json presets. The body-color slug is ink: never use text, whose generated class collides with core's generic has-text-color class.

## Typography
Bundle the existing Inter and EB Garamond WOFF2 files. Use serif display headings and sans-serif video titles and metadata. Video titles remain compact, not marketing-sized.

## Layout
Content width 960px; wide width 1280px. Retain root-aware gutters. Homepage layout map, in order:
- Header: existing wide title/navigation/search/submission bar; wraps on narrow screens.
- Welcome: wide two-column composition, introductory text on the left and the latest video/player on the right; stack on mobile.
- Featured: coal-2 full-width band, four videos tagged featured on the wide grid; no fixed term IDs or offset.
- Recent events: white band, twelve nonempty event terms by descending term ID, three columns; not a popularity ranking.
- Video categories: gray band, all nonempty top-level categories with counts, four columns.
- Recent videos: white band, inherited main query, four columns and core pagination; eight posts per page controlled by existing query logic.
- Footer: existing coal contribution/about/community columns and license.
At narrow widths, grids reduce to two columns and then one. All bands remain square at the viewport edge. Do not add Popular videos or invent directory routes.

Single-video layout map (functional wptv3):
- Header: reuse the shared header unchanged.
- Introduction: wide core breadcrumbs, one h1 post title, linked speaker terms underneath.
- Player: wide post content, rendering the stored shortcode or VideoPress block without rewriting it; no second player.
- Reactions: native Jetpack Like and Sharing blocks underneath the player; no replacement controls.
- Description/details: wide two-column row, description on the left, metadata and Creative Commons license on the right. Use the complete stored excerpt, preserving the presentation slides link. Stack on mobile.
- Discussion: reading-width core comments, pagination and reply form; honor existing comment settings.
- Navigation: previous/next post links at reading width, wrapping on mobile.
- Footer: reuse the shared footer unchanged.
Metadata and downloads use compact Inter text in aligned label/value rows, following wptv3: 1:2 columns, a 1rem column gap, 0.5rem between rows, and regular-weight labels. Keep multiple term links inline in the value column and allow long values to wrap there, including on mobile. The page stays white; thin gray rules separate details and navigation. Unavailable multisite data is omitted rather than fabricated.

Remaining-template layout map (functional wptv3):
- Shared header and footer remain unchanged on every view.
- General archives: full-width gray title band, then a wide four-column video grid with the inherited main query and native pagination; keep existing page sizes. Two columns on tablet and one on narrow mobile.
- Ordinary pages: gray title band with one h1, followed by stored editable content at reading width; do not rewrite institutional content.
- Search: query title and native search form above the inherited video grid; show an explicit empty-results message without changing the query.
- 404: reading-width heading, concise explanation and native search form; preserve the 404 status.
- Category/event archives: shared header; gray title band with one unprefixed h1 and native breadcrumbs; categories also retain their stored term description. On the first page only, when more than six videos exist, a white wide two-column spotlight presents the first player with a compact caption on the left and the next five linked thumbnails/titles on the right. Stack below 782px. Below it, the four-column grid shows the remaining main-query results and native pagination. Later pages and archives with six or fewer results omit the spotlight and show every queried video in the grid. Preserve page size, order and pagination boundaries; never offset a database query to create the spotlight. Category spotlight captions retain linked event terms; event spotlights do not repeat the event on each secondary video.
- The special WordCampTV featured/event-menu landing page remains separate parity work; the category fallback is not a declaration that this legacy feature is complete.

## Elevation & Depth
No shadows or gradients. Thin rules and contrasting frames provide structure.

## Shapes
Modest rounded video frames and buttons only. Full-width bands stay square.

## Components
Use core Query, Post Template, Terms Query and Term Template blocks. Templates reference hidden PHP theme patterns. The latest-video query is independent; recent videos inherit the main query unchanged. Resolve theme-owned taxonomy slugs at render time.

## Do's and Don'ts
Do preserve existing routes, page sizes and content. Do keep logic in inc/. Do not edit reference themes, hardcode IDs, invent event dates, or use Additional CSS/global style database copies.

## Imagery
Use actual VideoPress imagery and players, never synthetic conference or speaker photos. The bundled legacy placeholder is an explicit local fallback, not a replacement for production thumbnails. No cosmetic filters.

## Motion
No new animation. Respect reduced-motion preferences.

## Voice
US English in code and interface. Community-focused, factual, concise; no promotional claims. Preserve editable institutional page content separately from the design.
