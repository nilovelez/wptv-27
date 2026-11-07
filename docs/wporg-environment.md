# Meta repository environment

The WordPress.org Meta repository has a local environment for WordPress.tv: [`environments/`](https://github.com/WordPress/wordpress.org/tree/trunk/environments), added by the Meta committer in changeset 15509. It runs `wptv-27` from the Meta copy of the theme with [wp-env](https://www.npmjs.com/package/@wordpress/env), imports about 60 real videos from WordPress.tv's public API, and runs the theme's PHPUnit suite (`tests/*.php`, `phpunit.xml.dist`).

It complements the Studio site: Studio has the full imported dataset; this environment matches what Meta CI tests.

## Setup on the owner's Mac (bender)

wp-env needs Docker. The Mac uses [Colima](https://github.com/abiosoft/colima): free, command line only, no Docker Desktop.

1. Install Colima, the Docker CLI and Compose:

   ```bash
   brew install colima docker docker-compose
   ```

2. Register the Compose plugin in `~/.docker/config.json` (Homebrew installs it outside Docker's default plugin folder):

   ```json
   "cliPluginsExtraDirs": [ "/opt/homebrew/lib/docker/cli-plugins" ]
   ```

3. Start Colima sharing both the home folder and the data disk:

   ```bash
   colima start --cpu 4 --memory 6 --mount "$HOME:w" --mount /Volumes/Datos:w
   ```

   Without the mounts, Colima only shares `~`. The repositories and scratch live in `/Volumes/Datos`, so the containers would see their bind mounts empty (the mu-plugins fail to load and Jetpack is "not found").

The Meta clone is `/Volumes/Datos/repos/wordpress.org-wptv` (sparse checkout of `wordpress.tv` and `environments`). Install its dependencies once with `npm install` in `environments/` (Node 24 or later).

## Daily use

From `/Volumes/Datos/repos/wordpress.org-wptv/environments`, with wp-env's data kept in scratch:

```bash
colima start --mount "$HOME:w" --mount /Volumes/Datos:w
export WP_ENV_HOME=/Volumes/Datos/scratch/wptv-27/wp-env-home

npm run wptv:env start     # http://localhost:8888, admin / password
npm run wptv:env stop
```

The PHPUnit suite starts a second environment that also wants port 8888. Run it on other ports, with its own data folder:

```bash
WP_ENV_HOME=/Volumes/Datos/scratch/wptv-27/wp-env-home-test WP_ENV_PORT=8890 WP_ENV_MYSQL_PORT=32790 npm run wptv:test
```

When done, stop the environments and then Colima (`colima stop`). Colima does not start at login.

## Notes

- The site's address is `localhost:8888`, so it only works from bender itself (or through an SSH tunnel: `ssh -L 8888:localhost:8888 bender`).
- The import creates videos, terms and pages, but no menus. Create one in Appearance > Menus to test the header menu.
- WordPress.com internals (`video_get_info_by_guid()` and related) are not stubbed: downloads, subtitles, JSON-LD video data, file URLs, `/oembed/` and `/unisubs/` stay empty or answer with an error. See the environment's README.
- wp-env's experimental Playground runtime (`--runtime=playground`) starts WordPress without Docker, but does not support `wp-env run`, so the setup script and the PHPUnit suite fail. Docker is required.
