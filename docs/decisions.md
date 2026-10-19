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
- **Status:** proposed (pending confirmation, see open question 20)
