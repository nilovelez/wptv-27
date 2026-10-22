# AGENTS.md

Instructions for AI coding agents (and humans) working on **wptv-27**, the new block theme for [WordPress.tv](https://wordpress.tv/).

Read this file first. At the start of a new or resumed session, consult the current [session handoff](docs/handoff.md); otherwise reuse the context already established. Read only the documents in [`docs/`](docs/) relevant to the task, and do not reread unchanged documents for every small adjustment. The owner confirmed Atomic thumbnails, but the last route regression is not green; do not mistake that confirmation for completed Phase 1c or production readiness. The owner accepted the current category/event structure for this version and prioritized design before further functional work. The WordPress.org visual language and follow-up card/header/footer refinements are implemented in 0.4.0 and approved by the project owner for Git publication. Site upload/deployment is a separate owner action; the remaining functional and verification gaps are not closed by visual approval. Preserve that work and follow the site-root DESIGN.md, [docs/design.md](docs/design.md) and [docs/design-handoff.md](docs/design-handoff.md), including the owner-confirmed tablet adaptation; never restore the historical 0.3.4 snapshot. Do not claim official Design team approval has already been obtained.

This **0.4.1** checkpoint contains the owner-approved automatic WordCampTV replacement, the follow-up caption/archive refinements and the proportional-workflow rules. The owner authorized its Git publication after 0.4.0 (`14a9b88`); that authorization is not a site deployment or blanket permission to publish subsequent work. Follow [docs/wordcamptv.md](docs/wordcamptv.md): recorded-based recent events, name/year-based directory, two data-only transients with documented invalidation. This explicitly supersedes the old manual featured-event menu/sidebar requirement and adds the approved `category-wordcamptv.html` exception; do not restore legacy parity for that page. Language filters and year navigation stay in backlog B7/B8.

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
9. **Record decisions proportionally.** Record substantive product or technical decisions in the appropriate canonical document. Update [`docs/decisions.md`](docs/decisions.md), [`docs/open-questions.md`](docs/open-questions.md) or [`docs/plan.md`](docs/plan.md) only when their decision history, question status or project status actually changes; cross-reference rather than duplicate explanations. Minor presentation corrections do not require entries in every log. Keep the site-root DESIGN.md current when the look changes.
10. **No secrets.** Do not commit credentials, tokens, personal data or private URLs.

## Owner-required workflow: proportional small changes

This is a standing instruction, not a preference limited to the conversation in which it was agreed.

- Before implementing, trace the smallest complete execution path relevant to the request, including defaults, exclusion filters, writes and cleanup. Resolve those specific uncertainties first; do not discover them through successive implementation attempts. Do not read the entire codebase or reread established context.
- Reuse installed parsers, importers and APIs before writing replacements. For example, use the installed WXR parser rather than building a regex-based XML extractor. Choose the smallest extension point that satisfies the current request.
- A pilot proves only the requested sample. Do not build bulk processing, recovery frameworks, reusable tooling or future-feature support unless the current task actually needs them. State any remaining bulk-work limitation rather than implementing it preemptively.
- Group all ready changes to each file into one edit. Do not add unrelated cleanup, refactoring or review.
- Decide the concrete success checks before execution, using the actual requirements rather than accumulating tests afterward. Complete mandatory validation and check each required result once. Do not repeat equivalent evidence through several test suites, DOM inspections and screenshots. Required checks still apply; this rule removes redundant checks, not safety requirements.
- Assess expensive operations by their actual workload and failure mode, not the number of tool calls. Inspect input sizes, use a representative sample where needed, bound network waits and make long batches interruptible with recoverable progress. If duration or resource use is materially uncertain, explain that uncertainty before launching the expensive operation. Do not invent tool-call budgets or promise unmeasured completion times.
- Diagnose and fix a failed check, then repeat only the affected check, not the entire verification process.
- Do not run broad regressions or full-page audits for a typography, spacing or link correction. Broader checks belong to milestones and changes that affect routes, queries, data, shared behavior or publication risk.
- Document the verified result and necessary limitations in one canonical place after the work succeeds; avoid intermediate reports and duplicated explanations. Update other documents only when their meaning actually changes. More extensive documentation remains appropriate when the owner explicitly requests it, as for WordCampTV selection and cache behavior.
- Stop when the requested correction and its required checks are complete. If an unexpected issue requires broader work, explain the reason before expanding scope; ask the owner when it changes the agreed work.
- Report the result briefly and distinguish local changes from Git publication or deployment. Do not promise an exact duration that cannot be guaranteed.

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
| [`docs/wordcamptv.md`](docs/wordcamptv.md) | Automatic WordCampTV product rules, core-block composition, transient payloads/expiry/invalidation, query costs and diagnostics |
| [`docs/design.md`](docs/design.md) | Approved design direction: WordPress.org visual language, tokens, components, page layouts, sources |
| [`docs/design-handoff.md`](docs/design-handoff.md) | Design implementation order, file list, verification criteria and confirmed owner decisions |
| [`docs/mockups/`](docs/mockups/) | Static HTML reference mockups of the front page and single video |
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
