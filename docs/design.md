# Interim design: wptv3

The reference is the installed `wptv3` theme, read-only. This is not a new visual direction. `theme.json` is the versioned token source; the local site's `DESIGN.md` describes the same direction for Studio.

## Current approval

The owner explicitly chose to continue with this design while consulting the WordPress.org Design team. No new WordPress.org visual direction, parent theme, palette, typography or shared header/footer implementation is approved. Treat previously discussed official sources as research only; wait for new guidance before changing the look. This does not prevent separately requested editor usability changes or functional fixes.

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

## Content and imagery

Use factual US English interface copy. Video titles, speakers, categories and events come from existing content. Use real VideoPress imagery, not generated speaker/conference pictures. The bundled legacy placeholder is an explicit fallback when video data and attachment images are unavailable. The latest local test video uses a VideoPress block; legacy posts use `[wpvideo]`.

## Ownership

Templates reference hidden PHP theme patterns. Design is maintained in code and cannot be changed through the Site Editor, user global styles or Additional CSS. Institutional page content stays editable. Keep PHP logic in `inc/`. Bump the theme version when adding/removing pattern files so core refreshes its cached pattern inventory.
