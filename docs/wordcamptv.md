# Automatic WordCampTV landing

## Scope and owner decisions

Local implementation for 0.4.1, following the owner's approval of the product and transient strategy. This replaces the legacy manual `featured_wordcamps` menu loop, not its stored menu data. The old Featured categories / Browse by year / Browse by language sidebar is deliberately dropped. Language filters and year navigation are separate backlog items B7/B8; the year-grouped directory is not a year filter.

The new `templates/category-wordcamptv.html` is an approved exception to the generic category template/spotlight inventory. Existing categories and event archives retain the 1 + 5 + rest layout. No rewrite, endpoint, redirect, feed, page size, main-query ordering or editorial workflow changes.

## Page structure

1. Shared category title and description, without breadcrumbs. The owner's subsequent breadcrumb decision removes them from every taxonomy archive; see the Breadcrumbs section in [design.md](design.md).
2. Landing video introduction: the native category spotlight, one latest video player plus the next five small rows. No Recent videos heading, 22-video grid or video pagination on the landing. The underlying 22-post main query is unchanged; existing later-page URLs keep their inherited grids.
3. First page only: Latest WordCamps, selected from recorded dates in the past twelve months, with an event heading, four videos and a View all link to the existing event archive.
4. First page only: All WordCamp videos, grouped under descending year headings, with three-column event lists (two below 960px, one below 600px).
5. Shared header/footer are unchanged. Page two and later preserve their existing video list/pagination without repeating the spotlight or the two event sections. For a small nonempty WordCampTV archive, the spotlight renders one lead and up to five secondary videos; other categories keep their existing more-than-six threshold.

## Pagination contract

The owner explicitly confirmed that neither automatic event section is paginated. Show ALL qualifying recent WordCamps and the COMPLETE year-grouped directory together on the first landing page, without an event-count cap, Load more control or separate pagination. The four videos per recent event are a preview, not pagination of the event list. The landing's upper video preview has no pagination either. Existing later video URLs are preserved separately and do not repeat the two complete event sections. The implementation already follows this contract.

## Sources and ordering

All derived lists consider published `post` entries in category slug `wordcamptv`, including child categories, matching native category-query semantics. Resolve IDs locally; never persist production term IDs in templates.

### Recent events

- Read `_wptv_submitted_video['recorded']`. The legacy form stores this in a serialized metadata array.
- Accept only a valid, exact `YYYY-MM-DD` calendar date. Missing, malformed, impossible or future dates do not qualify. Never substitute publication/modified dates or guess from an event name.
- Window: today minus twelve calendar months through today, inclusive, in the site's timezone. It is not a fixed 365-day interval or a publication-date filter.
- Each associated event must contain the standalone word WordCamp, case-insensitively. A year in its name is not required for this section.
- Deduplicate by event term ID; the greatest qualifying recorded date determines the base order, newest first. Equal dates use ascending term ID as a deterministic tie-breaker. For display, place qualifying flagship events (WordCamp Europe, WordCamp US, WordCamp Asia and WordCamp India) before all other events, preserving that date/ID order within each group. Recognize their names case-insensitively, optionally followed by an edition year, never by environment-specific term IDs. Flagships must meet the same eligibility/date window as other events; never add an ineligible edition. This stable partition runs in `recent_markup()` through `prioritize_flagships()`; the date-ordered transient, four-video queries and year directory are unchanged.
- For each event, an independent core Query retrieves at most four published category videos, ordered by stored publication date descending, then post ID descending. These four need not fall within the recording window. The filter requires both category membership AND the event; a missing event ID must return no videos.
- Publication dates are the available ordering proxy, not evidence of the actual upload/category-assignment time. Moderators may have backdated them. This implementation does not reconstruct an unavailable history or rewrite dates.

### Complete directory

- Fetch distinct events attached to published category videos. Do not use all nonempty event terms globally.
- Require standalone WordCamp in the event name, case-insensitively.
- Extract standalone years matching `20[0-9]{2}` from the event name, not the video title or slug.
- Accept exactly one distinct year. Repeated occurrences of the same year are allowed; no year, years outside 2000–2099 and multiple distinct years are omitted rather than guessed.
- Group by extracted year, descending; list event names alphabetically within each year. This section does not read recorded/publication dates.
- This is a naming heuristic, not proof of event identity. Misclassified non-WordCamp events are excluded from the derived lists; no videos are deleted or recategorized. They can still appear in the unchanged recent-video query.

## Transients and cold-query costs

Use normal site-local `get_transient()` / `set_transient()` APIs, not network-wide site transients. WordPress chooses its options-table or persistent-object-cache backend. Both indexes cache arrays only, including empty arrays; `false` is a miss. There is no HTML, post object, image/player URL or submission metadata in either payload.

| Key | Payload | Lifetime |
| --- | --- | --- |
| `wptv_wc_recent_v1` | Event ID => greatest qualifying `YYYY-MM-DD`, in descending date order | At most one hour, capped at the next site-local midnight |
| `wptv_wc_directory_v1` | Year => array of event IDs | 24 hours |

A transient may disappear early; recompute on a miss. No cron, custom table, persistent derived metadata or prebuilt HTML cache is introduced. Four-video queries and term rendering use native WordPress caches, not another theme transient. Change the key version if payload/selection semantics change in a later release.

**Cold directory:** one portable SQL join/EXISTS query selects distinct associated event IDs; native `get_terms()` loads their public names for grouping. It does not load every category video or its metadata.

**Cold recent index:** recorded is serialized, so it cannot provide an indexed date-range query. SQL prefilters native serialized recorded strings to the one/two candidate years. Candidate metadata is fetched in batches of at most 500 using a primary-key cursor, validated/unserialized in PHP, and associated event terms are fetched once per qualifying batch. Unrelated years/full post objects are not materialized in PHP. The leading-wildcard LIKE can still scan eligible metadata in the database; the transient reduces repeated work, not that fundamental cost. Test cold-cache behavior against the real catalogue before production rollout. If it is too expensive, the next step is a normalized/indexed recorded field or controlled background index, not silently serving an incomplete/capped list.

The midnight cap prevents the rolling calendar window staying on yesterday's boundary without an edit. There is no rebuild lock initially: concurrent cold requests can duplicate work. Database/term lookup failures are not cached as a day-long empty directory. Empty states remain visible and no substitute event content is invented.

## Invalidation

`WordPressTV\WordCamps\invalidate()` deletes both theme indexes without flushing unrelated caches. Changes are conservatively invalidated for published posts, even when the post is not currently known to belong to WordCampTV; this is cheap and avoids missing departures.

| Hook/change | Action |
| --- | --- |
| `save_post_post`, published posts | Clear both, including publication-date edits |
| `transition_post_status`, old OR new status published | Clear both; covers publish, unpublish and trash |
| `before_delete_post`, published posts | Clear both |
| `added_term_relationship`, `deleted_term_relationships`, category/event | Clear both; covers direct relationship removals as well as assignment APIs |
| `created_term`, `edited_term`, `delete_term`, category/event | Clear both; names, year extraction, root slug and hierarchy may change |
| Added/updated/deleted `_wptv_submitted_video` on a published post | Clear recent only; directory is independent of recorded dates |
| `after_switch_theme` | Clear both |

Raw database writes bypass WordPress hooks. An external importer doing that must call the explicit invalidator after completing its work. For manual refresh with WP-CLI:

```sh
wp eval 'WordPressTV\WordCamps\invalidate();'
```

## Code and block composition

- `inc/wordcamps.php`: parsing, category scope, indexes and invalidation.
- `inc/wordcamp-blocks.php`: fill marked core Groups on the first category page only; guard recursion and preserve query globals.
- `patterns/wordcamps.php`: section wrappers and editable empty states.
- `patterns/wordcamp-event.php`: core heading/View all and four-video Query prototype.
- `patterns/wordcamp-year.php`: core year heading and included-ID Terms Query prototype.
- `inc/block-queries.php`: constrain only the marked independent event queries. Other namespaces and inherited queries retain their behavior.
- `inc/archives.php`: use native 1 + 5 spotlight partitioning on WordCampTV, including small nonempty archives; `inc/wordcamp-blocks.php` hides its remaining-video grid only on the first page.

Core blocks cannot perform serialized-date aggregation or year extraction, so a small server-side data layer is justified. There are no new plugins, custom editor blocks, build dependencies or frontend scripts. PHP fills escaped term names/URLs, numeric year labels and integer query IDs in validated core-block prototypes; presentation remains in theme patterns/CSS. Existing card semantics and progressive pointer activation are reused.

## Diagnostics and verification

List category-associated events omitted by the naming/year rule without scanning all video IDs or changing terms:

```sh
wp eval '$terms=WordPressTV\WordCamps\directory_terms(); if(is_wp_error($terms)){WP_CLI::error($terms->get_error_message());} foreach($terms as $term){if(!WordPressTV\WordCamps\event_year($term->name)){echo $term->term_id."\t".$term->name.PHP_EOL;}}'
```

Focused PHP checks: `wp eval-file wp-content/themes/wptv-27/tests/blocks/wordcamps.php`. They test parser boundaries, cold recent selection with CLI-only synthetic metadata, cache invalidation, query constraints and global restoration. Only the two disposable transients are written and rebuilt; no posts, metadata, terms or menus are changed. Browser checks cover the real first/second pages and a clearly isolated populated-section preview using existing event videos, not a fabricated public recording.

The local dataset currently yields 58 events in five year groups and no qualifying recordings for 2025-10-20 through 2026-10-20. The public recent section therefore correctly shows its empty state. These counts are a dated local observation, not an assertion about production. Real multisite behavior, production cold-cache cost and data completeness remain unverified. The existing four feed-regression differences are not resolved by this feature; no baseline/allowance changes are authorized.
