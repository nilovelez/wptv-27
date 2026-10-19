# AGENTS.md

Instructions for AI coding agents (and humans) working on **wptv-27**, the new block theme for [WordPress.tv](https://wordpress.tv/).

Read this file first, then the current [session handoff](docs/handoff.md) and the documents in [`docs/`](docs/) that are relevant to your task. The owner confirmed Atomic thumbnails, but the last route regression is not green; do not mistake that confirmation for completed Phase 1c or production readiness. Keep the existing wptv3-based design pending guidance from the WordPress.org Design team.

## Project in one paragraph

WordPress.tv publishes community videos (WordCamps, meetups, workshops). It runs on the **WordPress.com multisite** with the legacy classic theme `wptv2`. The goal is to replace that theme with a new block theme, `wptv-27`, **without changing anything else**: same content, same data model, same URLs. Videos are regular posts with many taxonomies and custom fields. The videos themselves are hosted on VideoPress and embedded with the `[wpvideo]` shortcode.

## Hard rules

1. **Do not change any route in Phase 1.** Every URL, rewrite rule, query var, endpoint, redirect, feed and pagination boundary that works with `wptv2` must behave identically with `wptv-27`. This includes `posts_per_page`, which decides which `/page/N/` URLs exist. See [`docs/routes.md`](docs/routes.md).
2. **US English (en_US) only.** Code, function names, comments, translatable strings, commit messages and documentation. The project owner may talk to you in Spanish, but nothing in the repository may be in Spanish.
3. **No plugins.** Only plugins that already exist on the WordPress.com multisite can be used. Every site-specific feature must live in the theme. See [`docs/wpcom-constraints.md`](docs/wpcom-constraints.md).
4. **Do not edit `wptv2` or `wptv3`.** They are reference material only. `wptv2` is the production theme. `wptv3` is an earlier proof of concept, useful as a design reference.
5. **Prefer core over custom code.** Before writing PHP, check whether WordPress core, a core block, Block Bindings, a block variation, `theme.json` or Jetpack/VideoPress already solves the problem. Custom code must be small, single-purpose and justified in [`docs/decisions.md`](docs/decisions.md).
6. **No logic in templates.** Templates and parts are block HTML. PHP goes in `inc/` (hooks and helpers) or in dynamic blocks under `blocks/`.
7. **Guard WordPress.com-only functions.** VideoPress internals such as `video_get_info_by_guid()` or `bump_stats_extras()` only exist on WordPress.com. Always wrap them in `function_exists()` / `class_exists()` checks and degrade gracefully.
8. **Local data is not a clone of production.** The local site was built from an import, so term IDs, post IDs and menus differ from production. Never hardcode IDs. Use slugs.
9. **Record decisions.** When you choose an approach, keep or drop a legacy feature, or answer an open question, update [`docs/decisions.md`](docs/decisions.md), [`docs/open-questions.md`](docs/open-questions.md) and [`docs/plan.md`](docs/plan.md) in the same change.
10. **No secrets.** Do not commit credentials, tokens, personal data or private URLs.

## Coding standards

- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/) for PHP, JS, CSS and HTML.
- Text domain: `wptv`. This is the same as in `wptv2`, so existing translations keep working.
- Prefix every global function, hook, option, constant and handle with `wptv_` / `WPTV_`, or use the `WordPressTV` namespace.
- Escape late, sanitize early, verify nonces and capabilities.
- Block theme only. No classic PHP templates.
- Styles: `theme.json` first, then block style variations, then `style.css` for what `theme.json` cannot express.
- Animations must respect `prefers-reduced-motion`.

## Documentation index

| File | Contents |
|---|---|
| [`docs/handoff.md`](docs/handoff.md) | Current resume point, owner confirmations, environment cleanup, verification limits and next steps |
| [`docs/plan.md`](docs/plan.md) | Phases, tasks and current status |
| [`docs/backlog.md`](docs/backlog.md) | Agreed work deferred until after Phase 1 (route-changing fixes and removals) |
| [`docs/routes.md`](docs/routes.md) | Inventory of every route that must be preserved, plus the regression test URL list |
| [`docs/legacy-wptv2.md`](docs/legacy-wptv2.md) | Analysis of the legacy theme and the `wptv2` to `wptv-27` replacement map |
| [`docs/legacy-wptv3.md`](docs/legacy-wptv3.md) | Review of the `wptv3` proof of concept: what to reuse, what to avoid, its route test results |
| [`tests/routes/README.md`](tests/routes/README.md) | Route regression test: how to run it against the `wptv2` baseline |
| [`docs/wpcom-constraints.md`](docs/wpcom-constraints.md) | WordPress.com platform limits, wpcom-only functions, available plugins |
| [`docs/decisions.md`](docs/decisions.md) | Decision log (what was decided, when and why) |
| [`docs/open-questions.md`](docs/open-questions.md) | Questions waiting for an answer, and answered questions |

## Environment

- Local development uses WordPress Studio (SQLite database, so `wp db` commands do not work. Use WP-CLI object commands instead).
- The `wptv2` and `wptv3` themes are installed next to this one for reference.
- Leave `wptv-27` active after development and visual checks so the project owner can review changes. Do not switch back to `wptv3` unless explicitly asked. If a test temporarily changes themes, restore `wptv-27` afterwards.
- The full route regression test is slow and costly. Run it only at milestones and after routing-code changes, never after every minor change. See "When to run" in [`tests/routes/README.md`](tests/routes/README.md).
- Production deploys go through a WordPress.com employee. There is no direct access to production code. See [`docs/wpcom-constraints.md`](docs/wpcom-constraints.md).

## Repository

`docs/`, `tests/` and this file are excluded from theme zips through `export-ignore` in `.gitattributes`. See the README for how to build a clean zip with `git archive`.
