# Session handoff

## Current state

- Theme: `wptv-27`, version **0.3.2**, branch `trunk`. Leave it active locally. No further theme/runtime changes were requested for this session closeout.
- Phase 1a foundation is complete. Phase 1b homepage and single-video templates are complete; remaining templates are pending. Phase 1c GUID/thumbnail work has started, but full block integration is not complete.
- The owner synchronized the site to the Atomic test environment and **confirmed that thumbnails work there**. This is owner-reported verification, not an independent remote audit or proof of multisite production compatibility.
- Keep the current **wptv3-based design**. The owner will consult the WordPress.org Design team. Do not adopt a new WordPress.org visual system, change tokens, install a parent theme or begin a redesign without new direction.
- The owner is reviewing the transition from Classic Editor to the block editor and will supply specific changes. No editor redesign or new field/moderation workflow has been approved or implemented yet.

## Implemented and published code

- `cfc9aa0`: single-video pattern, guarded producer/download blocks and full description.
- `f141c72`: CSS-only 1:2 label/value metadata rows following wptv3.
- `c1dbb9c`: initial local MU-plugin environment guards.
- `501f508`: shortcode/block GUID extraction, real standalone VideoPress posters and opt-in route-test doubles; version 0.3.2.
- Today's closeout only updates documentation. The final documentation commit follows these code commits on `trunk`.

## Thumbnail behavior and verification

- Legacy shortcodes retain precedence and last-shortcode behavior. Otherwise the last valid nested `videopress/video` or earlier `jetpack/videopress` block supplies the GUID.
- Keep the production internal thumbnail API first; fall through to the callable standalone plugin poster resolver. Preserve plugin privacy/cache handling, existing featured images and the bundled fallback. No invented CDN paths, content rewrites or automatic featured-image assignments.
- Local post **126193** was manually converted by the owner to `videopress/video`, GUID `ob0EdJL3`. Post **133522** is the existing block-only fixture, GUID `HVQl3ZYz`.
- Last code verification: 27 GUID/image checks, 8 simulated multisite image checks, 11 homepage checks, 18 single checks and 12 simulated download/subtitle checks passed. A plugins-skipped smoke check covered absent APIs. All 12 real homepage poster URLs returned HTTP 200/image/jpeg. Atomic thumbnail success was subsequently confirmed by the owner.
- Built-in multisite serialization, actual production thumbnail APIs and other production-only integrations still require verification.

## Regression is still blocked

The last full run, **before the subsequent media cleanup**, covered 145 URLs and reported **27 unaccepted field differences across 11 URLs**, plus the same four expected API differences; no PHP notices.

- oEmbed JSON/XML/maxwidth and unisubs by URL still require literal shortcode content and return 501 for the converted fixture.
- Unisubs GUID lookup and GUID redirects still search shortcode text and do not find that block-only post.
- Four feed body hashes differ after the conversion, including build timestamps, player serialization and renderer markup/lazy attributes.
- The already-expected API body differences also contain missing shortcode-only video data for the converted post.

Do not describe this as a passing regression, change the baseline or broaden expected-difference allowances to hide it. Media cleanup may produce additional fixture-related differences on the next run. Audit the remaining GUID consumers in Phase 1c: oEmbed, unisubs/GUID redirects, API, Open Graph, JSON-LD, MRSS and downloads/subtitles. Keep route names, query parameters, redirects and pagination contracts unchanged.

## Environment cleanup and sync size

- The owner deleted attachment **133523** locally and on the test environment. Its local MP4 (`uploads/2026/05/video-bd9a03908b.mp4`, 297,547,310 bytes) initially remained on disk with access denied. It disappeared when the local site was stopped; after restarting, its absence was confirmed. This is consistent with a pending deletion/open handle, not a proven ACL diagnosis. Remote physical-file removal was not independently checked.
- Preserve attachment **133382**, the remote VideoPress reference for `HVQl3ZYz`. It is not another large local MP4. The plugin's attachment deletion hook can request remote video deletion, so do not delete it merely to reduce sync size.
- The disk audit found approximately 418 MiB under `wp-content`, dominated by the inactive Gutenberg plugin (230 MiB), Jetpack (96 MiB), themes (28 MiB), SQLite data (23 MiB), VideoPress (17 MiB) and translations (12 MiB). Uploads were only 0.21 MiB after MP4 cleanup. These are a historical measurement, not a current inventory.
- The owner removed Gutenberg and reported Studio's sync estimate falling to **about 183 MB**, then completed synchronization. Core's block editor remains available.
- The owner also reported disabling **Gravatar Enhanced, Layout Grid and Page Optimize**. Treat this as environment configuration, not a theme dependency change or evidence about production plugins. Existing saved content that uses plugin blocks may need separate review; wptv-27's designed templates do not depend on Layout Grid.

## Local MU-plugin caveat

- `wptv-route-test.php` requires an actual loaded Studio runtime, the local environment and `X-WPTV-Route-Test: 1`. `run.sh` sends that header. Ordinary local browsing/CLI must use real VideoPress APIs, not deterministic doubles.
- Those test requests suppress real thumbnail lookup through `wptv_pre_video_image_url` to keep snapshots deterministic. Runtime/header guards survive Studio loader regeneration.
- **The guard patched into Studio-managed `99-studio-loader.php` is not durable.** Studio was observed regenerating that file during development/CLI operations, removing the guard. Recheck immediately before syncing MU-plugins; do not promise that a previous patch still protects the current generated loader. Its machine-specific contents are not versioned in this theme repository.

## Next session

1. Start from this handoff and the [plan](plan.md); keep `wptv-27` active and the current design unchanged.
2. Receive the owner's specific editor changes; separate editor presentation from changes to fields/moderation, which must preserve Release 1 workflow.
3. Agree on the order of remaining Phase 1c integration fixes and Phase 1b templates; do not silently expand scope.
4. Re-run route regression at the next relevant routing/integration milestone and review fixture changes explicitly. Do not run it for this documentation-only closeout.
5. Production rollout remains blocked on unfinished templates/forms, block integrations, regression and production-only verification. Retain wptv2 for rollback.
