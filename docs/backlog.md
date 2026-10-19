# Backlog

Work that is agreed but deferred until after Phase 1. Nothing here may be done during Phase 1, because every item changes observable route behavior. The Phase 1 regression test must still pass against the unchanged `wptv2` baseline.

When an item is picked up:

1. Record the decision in [decisions.md](decisions.md).
2. Update [routes.md](routes.md) with the new behavior.
3. Capture a new route baseline, or document the expected differences in the item.
4. Move the item to **Done** below, with the commit or PR.

Status legend: ⏳ pending · 🔄 in progress · ✅ done

## Pending

### B2. `/unisubs/` returns the wrong `permalink` ⏳

- **Found by:** Phase 0 route baseline.
- **Current behavior:** with `?guid=`, the `permalink` field points to the most recent post instead of the requested video. `post_id`, `guid` and `title` are correct. With `?url=` the permalink is correct. Cause: `wptv2` calls `get_permalink( $post_id )`, but `$post_id` stays `0` on the `?guid=` path, so it falls back to the global post.
- **Fix:** build `permalink` from the resolved post (`get_permalink( $post )`).
- **Expected route test differences:** body of `unisubs: by guid` only.
- **Risk:** low. It may become moot if `/unisubs/` is retired (see B4).

### B3. Retire the `producer` and `flavor` taxonomies, with 301 redirects ⏳

- **Decision:** both taxonomies are removed. Their URLs must not start returning 404: every retired URL gets a **301** to a meaningful replacement.
- **Current routes** (see [routes.md](routes.md)): `/producer/<slug>/` (18 terms) and `/flavor/<slug>/` (10 terms), each with `/page/N/` pagination and `/feed/`. Neither taxonomy is exposed in the REST API.
- **Scope:**
  - Decide the redirect target per term. Candidates: `producer` → the matching `producer-username` archive, an `event` or category archive, or the home page; `flavor` → a category or the home page. A static, versioned map in the theme (slug → target path), with a fallback target for unmapped slugs.
  - Redirect every variant: the term archive, `/page/N/` (to the first page of the target, or to the same page when the target paginates identically), `/feed/` (to the target's feed), and the taxonomy query vars (`?producer=`, `?flavor=`).
  - Keep the rewrite rules (or an equivalent early `template_redirect`) only to serve the redirects. Unregister the taxonomies from the editor and admin UI.
  - Remove all theme usage: the sidebar producer credit (`wptv/producer` block in the Phase 1 plan), `/api/` filters, admin fields and any template references.
  - Term data: decide whether the terms and relationships are kept (harmless, invisible) or deleted. Deleting them on production needs a WordPress.com employee.
- **Expected route test differences:** every `producer:` and `flavor:` URL becomes a 301 to its mapped target.
- **Open points:** the redirect map (needs the production term list and, ideally, traffic data), `producer-username` stays (it becomes the validated producer field of the release 2 form), so it is a valid redirect target for `producer`.

### B5. Move Location and Year out of categories ⏳

- **Decision:** the children of category `location` move to their own taxonomy. Year (children of category `year`) also leaves the category tree: either its own taxonomy or derived from the recorded date (to be decided). Part of release 2; see [submission-form.md](submission-form.md).
- **Current routes:** `/category/location/<slug>/` and `/category/year/<slug>/`, with `/page/N/` and `/feed/`. The location archives are not linked from anywhere today, but they exist and may be indexed.
- **Scope:**
  - Register the new taxonomy (or taxonomies) and migrate the terms and relationships with a batched, dry-run-capable script, run on the test site first.
  - 301 every old URL variant (archive, pagination, feed) to its new equivalent.
  - Update the submission form, the event name suggestion, `inc/query.php` (Year/Location per-page rules) and the front page category list exclusions.
  - Decide whether the old category terms are deleted after migration (needs a WP.com employee on production).
- **Expected route test differences:** every `location`/`year` category URL becomes a 301; new baseline for the new taxonomy archives.
- **Open points:** new taxonomy slugs and URL bases; Year as taxonomy or derived from the date.

## Ideas (not yet agreed)

### B4. Retire `/unisubs/` if it is confirmed unused ⏳

- Listed as a Phase 2 candidate in [plan.md](plan.md). It depends on the open question about unisubs usage.

## Done

_Nothing yet._

## Withdrawn

### B1. `/api/videos.*` sends JSON as `text/html`

- **Withdrawn:** not a `wptv2` bug. It was an artifact of the first local test setup: the `video_get_single_response()` stub returned an empty array, PHP printed an "Undefined array key" warning before the headers, and that forced `text/html`. On production the real function returns the key and `display_errors` is off. The route test now stubs the full structure and logs notices instead of printing them. With the corrected baseline, `/api/videos.json` sends `application/json`.
