# Decision log

Record every significant decision here, newest first. Each entry says what was decided, why, and what it affects. Do not delete entries. If a decision is reversed, add a new entry that references the old one.

Template:

```
## YYYY-MM-DD: Short title

- **Decision:** what was decided.
- **Reason:** why.
- **Affects:** files, features or routes involved.
- **Status:** accepted | superseded by <entry>
```

---

## Phase 0: Project documentation lives in the theme repository

- **Decision:** project documentation lives in `docs/` and `AGENTS.md` inside the theme repository. Both are excluded from theme zips with `export-ignore` in `.gitattributes`. Clean zips are built with `git archive` (see README).
- **Reason:** the documentation travels with the code, is versioned with it, and is read automatically by AI coding agents. Contributors clone the full repo, while deploys only get the theme files.
- **Affects:** `.gitattributes`, `README.md`, `AGENTS.md`, `docs/`.
- **Status:** accepted

## Phase 0: US English for everything in the repository

- **Decision:** code, function names, comments, translatable strings, commit messages and documentation are written in US English (en_US).
- **Reason:** WordPress project standard. The project owner may communicate in Spanish, but the repository is English-only.
- **Affects:** whole repository.
- **Status:** accepted

## Phase 0: New block theme from scratch, `wptv-27`

- **Decision:** build a new block theme named `wptv-27` from scratch, instead of evolving `wptv2` or `wptv3`.
- **Reason:** `wptv2` is an old classic theme with logic embedded in its templates. `wptv3` is a proof of concept that is only used as a design and structure reference.
- **Affects:** whole project.
- **Status:** accepted

## Phase 0: Phase 1 must not change any route

- **Decision:** in Phase 1, every route served by `wptv2` must behave identically with `wptv-27`, verified by a regression test against a `wptv2` baseline.
- **Reason:** WordPress.tv URLs are linked from WordCamp sites, embeds, feeds and external consumers of `/api/` and `/oembed/`. Breaking them has a high cost and is hard to detect.
- **Affects:** taxonomies, `pre_get_posts`, rewrite rules, endpoints, redirects. See [routes.md](routes.md).
- **Status:** accepted

## Phase 0: Use `wptv3` as the interim design reference

- **Decision:** the visual design starts from `wptv3`. It will later be aligned with the WordPress.org visual language, once the project owner provides the documentation.
- **Reason:** `wptv3` already explores a block-based design for the site.
- **Affects:** `theme.json`, templates, patterns.
- **Status:** accepted

## Phase 0: Keep the `wptv` text domain

- **Decision:** the text domain stays `wptv`, as in `wptv2`.
- **Reason:** existing translations keep working.
- **Affects:** all translatable strings.
- **Status:** accepted

## Phase 0: Theme-agnostic route regression test

- **Decision:** routes are verified by `tests/routes/`: a fixed URL list generated from real data, a curl runner that does not follow redirects, and a compare script. Main query data comes from an `X-WPTV-Route` header added by a local-only mu-plugin, never from theme markup. The `wptv2` baseline is committed.
- **Reason:** both themes render different markup, so counting posts in HTML would not compare. The header exposes exactly what decides a route (404 flag, found posts, pages, per page, queried object). Not following redirects makes redirect targets exact.
- **Affects:** `tests/routes/` (export-ignored, never deployed).
- **Status:** accepted

## Phase 0: Deterministic stubs for WordPress.com-only VideoPress helpers

- **Decision:** the local-only mu-plugin defines missing VideoPress helpers. `video_get_info_by_guid()` returns fake details derived from the GUID only, `videopress_2015_player_get_html()` returns fixed markup, and file, thumbnail and subtitle helpers return no data. The mu-plugin does nothing on WordPress.com (`IS_WPCOM`).
- **Reason:** without stubs, `wptv2` singles and endpoints fail locally. Stubs that return "no data" only exercise error paths. Deterministic fake data exercises the full code paths and keeps runs identical.
- **Affects:** `tests/routes/mu-plugin/wptv-route-test.php`. Real video output must be checked on the WordPress.com test site.
- **Status:** accepted

## Phase 0: Legacy quirks are part of the route contract

- **Decision:** Phase 1 reproduces observable `wptv2` behavior even when it is a bug (for example, `/api/videos.*` sending JSON as `text/html`), unless the project owner decides otherwise. Each quirk is tracked in [backlog.md](backlog.md) and fixed after Phase 1.
- **Reason:** external consumers may depend on the current behavior, and Phase 1 must not change routes.
- **Affects:** endpoints, see [routes.md](routes.md).
- **Status:** accepted

## Phase 0: Retire the `producer` and `flavor` taxonomies after Phase 1

- **Decision:** both taxonomies will be removed after Phase 1. Every existing URL (term archives, pagination, feeds, query vars) gets a 301 to a mapped replacement, never a 404. Phase 1 still registers them with the exact `wptv2` arguments.
- **Reason:** decided by the project owner. Removing them in Phase 1 would break the no-route-changes rule.
- **Affects:** taxonomy registration, `/producer/*`, `/flavor/*`, the producer credit, `/api/`. See B3 in [backlog.md](backlog.md).
- **Status:** accepted (deferred)
