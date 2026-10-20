# Interim design: wptv3

The reference is the installed `wptv3` theme, read-only. This is not a new visual direction. `theme.json` is the versioned token source; the local site's `DESIGN.md` describes the same direction for Studio.

## Current approval

The owner explicitly chose to continue with this design while consulting the WordPress.org Design team. No new WordPress.org visual direction, parent theme, palette, typography or shared header/footer implementation is approved. Treat previously discussed official sources as research only; wait for new guidance before changing the look. This does not prevent separately requested editor usability changes or functional fixes.

At the version 0.3.4 checkpoint, the owner accepted the current category/event structure for this version, deferred restructuring and prioritized design before further functional work. Claude Code is studying WordPress/WordPress.org guidance and will update the design document and supply instructions. Read that handoff before implementing the new look; do not assume official Design team approval has already arrived. The pre-handoff site-root Studio specification is archived verbatim in [design-baseline-0.3.4.md](design-baseline-0.3.4.md), outside the live design-instruction path.

## Tokens

- Blue `blue`: #3858e9; coal `coal`: #1e1e1e; coal-2 `coal-2`: #23282d; orange `orange`: #d54e21; body text `ink`: #1a1919; gray `gray`: #edeff0; white `white`: #ffffff.
- Never name a color preset `text`: its generated `.has-text-color` utility conflicts with WordPress's generic marker class.
- Bundled variable Inter (100–900) for body copy and metadata; EB Garamond (400–800) for display and section headings.
- Body 1.05rem, small labels 0.9rem, fluid display scale 1.39–3.27rem. Use theme presets, not scattered values.
- Content width 960px; wide width 1280px; root-padding-aware gutters.
- No shadows, gradients or decorative animation. Full-width bands remain square; video frames have 8px borders and radii. Buttons use a 0.33rem radius.

## Front-page layout map

1. **Header:** existing wide brand/navigation/search/submission bar, wrapping on small screens.
2. **Welcome:** white wide two-column layout; centered introductory text left, latest video player and dark caption right. Stacks on mobile.
3. **Featured:** coal-2 full-width band; white heading, four tagged videos on the wide grid. No category restriction or offset.
4. **Recent events:** white band; twelve nonempty terms in three columns, ordered by descending term ID. Gray inset tiles, not invented event artwork or dates.
5. **Video categories:** gray band; all nonempty top-level categories in four columns with their counts.
6. **Recent videos:** white band; inherited main query, four columns, eight posts per page, core pagination. Never override the main page size in markup.
7. **Footer:** coal band with brand, contribution/about/community links, CC BY-SA.

Video and term grids reduce to two columns below 900px and one below 480px. Preserve the section order and existing URLs. No Popular videos or fabricated directory links.

## Single-video layout map

1. Shared header, unchanged.
2. Wide breadcrumbs, one post-title h1, linked speakers.
3. Wide Post Content block renders the stored shortcode or VideoPress block, with no content rewrite or duplicated player.
4. Native Jetpack Like and Sharing blocks below the player.
5. Wide description/details columns, stacked on mobile. Full excerpt and presentation slides on the left; published date, event, speakers, tags, language, producer, available downloads/subtitles and CC BY-SA license on the right. Thin gray divider; compact Inter metadata in label/value rows (1:2 columns, 1rem gutter, 0.5rem row spacing, regular-weight labels), matching wptv3. Multiple term links stay inline and wrap within the value column, including on mobile.
6. Core comments, pagination and reply form at reading width; honor the existing discussion settings.
7. Core previous/next post links, wrapping on mobile.
8. Shared footer, unchanged.

Unavailable multisite data is omitted, never fabricated. Machete may independently inject sharing controls on the local/Atomic sites; do not change plugin configuration or add environment-specific theme patches to compensate.

## General templates (version 0.3.3)

- Archive: unchanged shared header, square full-width gray title band, inherited wide four-column video grid with real 16:9 VideoPress thumbnails, linked speakers, compact Inter titles and native pagination, unchanged footer.
- Page: gray band with one h1, stored content at the existing 960px reading width. No institutional copy or metadata is rewritten.
- Search: native query title and labeled search form above the inherited video grid; editable no-results message.
- 404: one reading-width h1, concise explanation and native search; retains its 404 status.
- Grids reduce to two columns below 900px and one below 480px. Root-aware gutters stay intact; sections own their vertical padding without an extra core block gap.
- Other taxonomy/date archives retain this general fallback. Category/event views now use the dedicated layout below. WordCampTV's featured/tag query and featured-event menu remain pending; do not treat its category fallback as completed special-landing-page parity.
- Desktop/mobile screenshots and rendered DOM checks reviewed these four general views. The existing palette, typography, header, footer, homepage and single layout are unchanged.

## Category/event layout (version 0.3.4)

1. Shared header, unchanged.
2. Full-width gray band: native breadcrumbs and one unprefixed term-title h1 on the wide grid. Categories retain their stored description through core Term Description; events do not add a description that the legacy template omitted. Local category descriptions are empty, so production copy still needs visual review.
3. First page only, with more than six results: white spotlight on the wide grid; first player and dark inset caption in the larger left column, next five linked thumbnails/titles in the right column. Category captions retain linked event terms; event secondary items do not repeat the event. Stack below 782px. Use the existing Post Content player approach and real images; no stored-content conversion.
4. Remaining main-query videos in the existing four-column grid, followed by native pagination. Later pages and archives with six or fewer results omit the spotlight and show every result in the grid. Preserve query order, total, page size and pagination boundaries; rendering uses isolated in-memory slices, not database offsets or extra queries.
5. Shared footer, unchanged.

The 8px frames, compact Inter titles, root gutters, palette and fonts are unchanged. Category/event desktop/mobile screenshots and rendered DOM checks completed; removed only the extra core margin before the already-padded spotlight. Offscreen lazy images remain lazy. The special WordCampTV featured/event-menu layout is still separate work.

## Content and imagery

Use factual US English interface copy. Video titles, speakers, categories and events come from existing content. Use real VideoPress imagery, not generated speaker/conference pictures. The bundled legacy placeholder is an explicit fallback when video data and attachment images are unavailable. The latest local test video uses a VideoPress block; legacy posts use `[wpvideo]`.

## Ownership

Templates reference hidden PHP theme patterns. Design is maintained in code and cannot be changed through the Site Editor, user global styles or Additional CSS. Institutional page content stays editable. Keep PHP logic in `inc/`. Bump the theme version when adding/removing pattern files so core refreshes its cached pattern inventory.
