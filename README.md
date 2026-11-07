# WordPress.tv Theme (wptv-27): project documentation

Documentation, decisions and migration tooling for **wptv-27**, the block theme that replaces the classic `wptv2` theme on [WordPress.tv](https://wordpress.tv/).

## Where the theme lives

The theme's code is not in this repository. It lives in the WordPress.org Meta repository, next to `wptv2`:

| Where | Path |
|---|---|
| Meta SVN (canonical) | [`meta.svn.wordpress.org/sites/trunk/wordpress.tv/public_html/wp-content/themes/wptv-27`](https://meta.svn.wordpress.org/sites/trunk/wordpress.tv/public_html/wp-content/themes/wptv-27/) |
| Official GitHub mirror | [`WordPress/wordpress.org`, `trunk`](https://github.com/WordPress/wordpress.org/tree/trunk/wordpress.tv/public_html/wp-content/themes/wptv-27) |
| Development fork | [`nilovelez/wordpress.org`, branch `wptv-27-dev`](https://github.com/nilovelez/wordpress.org/tree/wptv-27-dev/wordpress.tv/public_html/wp-content/themes/wptv-27) |

Changes are made on the `wptv-27-dev` branch of the fork and proposed to the Meta repository as pull requests to `WordPress/wordpress.org`. A Meta committer commits them to SVN, which is mirrored back to GitHub. The theme was first committed in Meta changeset [15507](https://meta.trac.wordpress.org/changeset/15507).

Until November 7, 2026, this repository held the theme's code too. Its history still has it.

## Contents

| Path | Purpose |
|---|---|
| `AGENTS.md` | Entry point for AI coding agents and developers: project rules and documentation index |
| `docs/` | Handoff, plan, decisions, backlog, route inventory, legacy analysis, reviews, design and the production checklist |
| `docs/mockups/` | Static HTML design mockups, published to GitHub Pages |
| `tests/blocks/` | Block, API and integration checks for the migration, run with WP-CLI against a local site |
| `tests/routes/` | Route regression test comparing `wptv2` and `wptv-27`, with its URL inventory and committed baselines |
| `blueprint.json` | Empty Playground/Studio review site for standards, security and WP-CLI checks |

The Meta repository has its own WordPress.tv environment for integration tests (wp-env, PHPUnit); see [docs/wporg-environment.md](docs/wporg-environment.md).

## Language

All project content is written in **US English (en_US)**, following the WordPress standard.

## License

GNU General Public License v2 or later.
