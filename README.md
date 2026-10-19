# WordPress.tv Theme (wptv-27)

Block theme for [WordPress.tv](https://wordpress.tv/), the WordPress community site that publishes videos from WordCamps, meetups and other community events.

wptv-27 replaces the legacy classic theme (`wptv2`). It is built with Full Site Editing (block templates, template parts, patterns and `theme.json`), and relies on WordPress core features instead of hand-written code wherever possible.

> **Status:** in development. The theme files are not in place yet. The repository currently holds the project documentation only.

## Constraints

WordPress.tv runs on the WordPress.com multisite. That means:

- No custom plugins can be installed. Only the plugins available on the multisite can be used, so every site-specific feature lives in this theme.
- Videos are hosted on VideoPress.
- Several VideoPress helpers only exist on WordPress.com. They are always called behind `function_exists()` checks, so the theme also runs in local environments.

See [`docs/wpcom-constraints.md`](docs/wpcom-constraints.md) for details.

## Repository layout

| Path | Purpose | In theme zip |
|---|---|---|
| `AGENTS.md` | Entry point for AI coding agents: project rules and documentation index | No |
| `docs/` | Project documentation: plan, route inventory, legacy analysis, decisions | No |
| Everything else | The theme itself | Yes |

## Working on the theme

Clone the full repository into the `wp-content/themes/` directory of a local WordPress site (for example, a [WordPress Studio](https://developer.wordpress.com/studio/) site):

```bash
cd wp-content/themes
git clone <repository-url> wptv-27
```

Read [`AGENTS.md`](AGENTS.md) and the [`docs/`](docs/) folder before making changes. The most important rule: **existing URLs must not change**. See [`docs/routes.md`](docs/routes.md).

## Building a clean theme zip

Development-only files (`docs/`, `AGENTS.md`, `.gitattributes`, `.gitignore`, `.github/`) are marked `export-ignore` in [`.gitattributes`](.gitattributes), so `git archive` leaves them out.

From the repository root:

```bash
git archive --format=zip --prefix=wptv-27/ --output=wptv-27.zip HEAD
```

- `--prefix=wptv-27/` puts the files inside a `wptv-27/` folder, which is what WordPress expects when you upload a theme zip.
- Replace `HEAD` with a tag or branch name to export a specific version, for example `v1.0.0`.
- `git archive` only exports **committed** files and reads `export-ignore` rules from the committed `.gitattributes`. Commit your changes before you build the zip.

To check what the zip will contain without creating it:

```bash
git archive --format=tar HEAD | tar -t
```

You can also download a clean zip straight from GitHub (**Code → Download ZIP**, or the source archive of any release), because GitHub honors `export-ignore` too. The folder inside that zip is named after the repository and branch, so rename it to `wptv-27` before you upload it.

## Language

All project content is written in **US English (en_US)**, following the WordPress standard: code, function names, comments, translatable strings, commit messages and documentation.

## License

GNU General Public License v2 or later.
