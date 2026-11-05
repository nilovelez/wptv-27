# External review: security and database findings

Reviewer: external review session (Claude Code), October 22, 2026, on branch `chore/review-tooling`.
Scope: the `WordPress.Security.*`, `WordPress.DB.*`, `GlobalVariablesOverride`, `PrefixAllGlobals` and `DiscouragedPHPFunctions` findings of the first `composer lint` (WPCS 3.4.1), plus a manual read of every request handler, public endpoint, write path and `phpcs:ignore` in `functions.php`, `inc/` and `patterns/`. Legacy behavior was compared against the `wptv2` SVN checkout. Formatting and docblock findings are out of scope here.

Evidence comes from code reading and from an empty Studio review site (WordPress 7.1.3, no Jetpack/VideoPress, no WordPress.tv data). Nothing was checked on WordPress.com or the Atomic test site.

## Summary

| ID | Area | Severity | Inherited from wptv2 | Owner |
|---|---|---|---|---|
| R1 | Submission nonce skipped when `wptv_uploaded_by` is filled | Low–medium | Yes | Fixed by the reviewer |
| R2 | oEmbed and unisubs `?url=` resolve unpublished posts | Medium | Yes | Fixed by the reviewer |
| R3 | `/api/videos.json?posts_per_page=` is unbounded | Medium (availability) | Yes | Fixed (S3, November 6) |
| R4 | Submission length check measures field names, not values | Low | Yes | Fixed by the reviewer |
| R5 | REST design lockdown bypassed with uppercase routes | Low | No (new code) | Done by the reviewer |
| R6 | Single-block cleanup drops non-empty content too | — | No | Intended; decision log corrected |
| D1–D2 | Unslash/sanitize and documented false positives | None (standards) | — | Done by the reviewer |
| D3 | Nonce warnings in `validate()`/`save()` | — | — | Documented with R1 |

No SQL injection, output-escaping or stored-XSS defect was found. The submission review panel renders through React elements (text is escaped), the slides URL is sanitized with `esc_url_raw()` for http/https and escaped on output, and REST meta writes require `edit_post`.

## Findings for the theme developers

### R1. Submission nonce is skipped when "Uploaded by" is filled

`inc/submissions.php:55` only verifies the nonce when `wptv_uploaded_by` is empty. That field is sent by anonymous users, but nothing stops a forged request from sending it for a logged-in user. A cross-site POST from a page the logged-in user visits then creates a pending submission whose `submitted_by`/`submitted_email` are the logged-in user's display name and email (`insert_submission()` uses `wp_get_current_user()`).

- Same condition in `wptv2` (`plugins/wordpresstv-anon-upload/anon-upload.php:21-26`), presumably so cached form pages with expired nonces keep working for anonymous visitors.
- Impact is limited: the post is pending and owned by `anonvideoupload`, but it is attributed to a real user and accepts an arbitrary upload.
- Suggested change: always verify the nonce when `is_user_logged_in()`; keep the anonymous exception only if page caching requires it, and record that reason in `docs/decisions.md`.
- Check after the change: logged-in submission with a valid nonce succeeds; logged-in submission with a filled `wptv_uploaded_by` and an invalid nonce returns `?error=15`; anonymous submissions behave as before.
- **Fixed (owner-authorized, October 22, 2026).** `handle()` now verifies the nonce for every logged-in user. Anonymous visitors keep the `wptv2` exception when they fill in their name, because a cached form page may carry an expired nonce for them, and their submission is not attributed to any account. Checked over HTTP on the review site: logged-in + name + invalid nonce returned `error=15` (the previous code let it through to the file check, `error=1`); logged-in + valid nonce passed the check; anonymous + name + invalid nonce behaved as before (`error=11`, missing email); anonymous without a name or with an empty nonce returned `error=15`.

### R2. oEmbed and unisubs resolve unpublished posts

`inc/endpoints/oembed.php` and `inc/endpoints/unisubs.php` (`?url=` branch) resolve the URL with `url_to_postid()` and use the post without checking its status or password. `url_to_postid()` returns the ID of a `?p=<ID>` URL without any status check: on the review site, an anonymous `url_to_postid( home_url( '/?p=5' ) )` returned the ID of a pending post.

- Anyone can enumerate IDs and get the title, VideoPress player HTML, GUID, description and dimensions of pending submissions, drafts, private and password-protected posts. Pending anonymous submissions all carry a VideoPress block.
- The unisubs `?guid=` branch is not affected: it already queries `post_status => publish`.
- Same code path in `wptv2` (`plugins/wordpresstv-oembed/wordpresstv-oembed.php:63`, `plugins/wordpresstv-unisubs/wordpresstv-unisubs.php:97`).
- Suggested change: after resolving the post, return the existing 404 unless `is_post_publicly_viewable( $post )` and `! post_password_required( $post )`.
- This changes responses for non-public posts only. Route regression: published fixtures must stay identical.
- **Fixed (owner-authorized, October 22, 2026).** Both endpoints return their existing 404 unless `is_post_publicly_viewable( $post )` and the post has no password. In unisubs the check runs after both lookups, so `?guid=` is covered too for logged-in visitors (core search already hides password-protected posts from anonymous ones). Checked on the review site with posts in each status: before, pending, draft, private and password-protected posts went through to the VideoPress lookup (HTTP 500 locally, without VideoPress; their data in production); now they return 404, while the published post follows the same path as before. Published route fixtures are expected to stay identical; the developers verify that with the route regression.

### R3. The videos API accepts any page size

`inc/endpoints/api.php:129-132` re-runs the main query with `posts_per_page` from the request. `-1` or a very large value builds one response for every published video (about 18,700), with per-post term and VideoPress lookups. It is an unauthenticated, uncached way to load the server.

- Inherited unchanged from `wptv2` (`plugins/wordpresstv-rest/wordpresstv-rest.php:64-65`) and part of the API contract, so Phase 1 rule 1 applies.
- The owner recorded it as [backlog B13](backlog.md); no change for now.
- **Fixed on November 6, 2026** with the second review below (S3).

### R4. Submission text fields have no server-side length limit

`validate()` (`inc/submissions.php:127-131`) measures the field *names*, which are never longer than 150 characters, so the check never fails. The comment says this was kept from `wptv2` on purpose; `wptv2` measures `(string) $field` in the same loop.

- Only the browser limits lengths (`assets/js/submission-form.js`, slides URL only). Direct POSTs can store arbitrarily long titles, speakers and descriptions in post meta.
- Suggested change: measure `$_POST[ $field ]` values. Owner decision because it changes which submissions are accepted.
- **Fixed (owner decision, October 22, 2026).** `Submissions\field_lengths()` sets the limits in characters: 100 for every text field (submitter name, email, title, speakers, language, event, producer username), 200 for the slides URL and 1,500 for the description. `validate()` checks them right after the honeypot, before the WordPress.org username request, and returns the existing error 6. Speakers and title are limited separately, before they are joined into the post title. Line breaks count as one character, as in the browser, which sends them as `\r\n`; non-string values are rejected. The form's JavaScript adds `maxlength="1500"` to the description; the other limits already existed in the browser. Checked on the review site: 15 boundary checks (exact limits, one over, multibyte text, CRLF descriptions, array values, honeypot precedence), the existing `submission-validation.php` suite (14 checks) and the rendered `maxlength` attributes.

## Resolved by the owner

### R6. The single-block cleanup removes all other content

`keep_only_video_block()` (`inc/editor.php`) keeps only the video block whenever a `post` contains exactly one `videopress/video` block, removing any other block, including paragraphs with text, on every save. The decision log only mentioned "stray empty paragraphs". The owner confirmed this is intended: a video post's content may only hold its single VideoPress block. `docs/decisions.md` now says so. No code change.

## Reviewer changes

### R5. REST design lockdown was case-sensitive (done)

`block_design_writes()` (`inc/lockdown.php`) matched `#^/wp/v2/(templates|template-parts|global-styles)(/|$)#`, but core matches REST routes case-insensitively (`class-wp-rest-server.php`, `'@^' . $route . '$@i'`). On the review site, as an administrator, `POST /wp/v2/templates` returned `403 wptv_design_locked`, while `POST /wp/v2/TEMPLATES` reached the templates controller and stored a published `wp_template` (the response was a 400, but the post was created; it was deleted afterwards).

This is a policy guard, not a privilege boundary: only users who can already edit theme options can write there. The pattern now has the `i` modifier. Check on the review site: `POST` to `/wp/v2/templates`, `/wp/v2/TEMPLATES` and `/wp/v2/Global-Styles/1` all return `403 wptv_design_locked`; `GET /wp/v2/TEMPLATES` still returns 200 and `POST /wp/v2/posts` is not affected.

### D1. Unslash and sanitize request values (done)

- `inc/submissions.php`: the nonce, producer username and `REMOTE_ADDR` are unslashed and sanitized; the email is unslashed and passed through `sanitize_email()` before `is_email()`; the uploaded file name is sanitized before reading its extension and building the attachment title.
- `inc/submission-form.php`: prefilled values are sanitized with `sanitize_text_field()` (`sanitize_textarea_field()` for the description, which keeps its line breaks) before the existing escaping. The stored submission is sanitized the same way, so a redisplayed value matches what would be saved.
- One visible effect: an email with an apostrophe (`o'brien@example.com`) was rejected with error 12, because `is_email()` received the slashed value (`o\'brien`). It is now accepted. `wptv2` had the same bug.
- Checks on the review site: the existing `tests/blocks/submission-validation.php` passes (14 checks), plus 8 focused checks (apostrophe email accepted, invalid email still error 12, nonce round trip, title/description/slides prefills, missing prefill, no raw `<script>` in the rendered form). PHP syntax passes.

### D2. Document false positives with targeted `phpcs:ignore` comments (done)

| Location | Sniff | Why it is safe |
|---|---|---|
| `inc/wordcamps.php:103-107`, `160-165` | `PreparedSQL.InterpolatedNotPrepared`, `DirectDatabaseQuery` | `$scope` is built from `wp_parse_id_list()` IDs; `$likes` contains only `%s` placeholders passed to `prepare()`. Results are cached in transients. |
| `inc/archives.php:89-94` | `GlobalVariablesOverride.Prohibited` | Temporary isolated query, restored in `finally`. |
| `inc/single.php:33` | `NonPrefixedHooknameFound` | Applies the core `the_excerpt` filter. |
| `inc/block-queries.php:76` | `SlowDBQuery.slow_db_query_tax_query` | Documented query; same as the existing route contract. |
| `inc/endpoints/oembed.php:158` | `urlencode_urlencode` | Byte-for-byte parity with `wptv2` discovery links, as already done in `api.php`. |
| `inc/submission-form.php` (`message()`, `prefill()`, `render()`) | `NonceVerification.Recommended` | Read-only display of redirect codes and prefills, escaped on output. |

### D3. Remaining nonce warnings in `validate()`/`save()`

Resolved with R1: `validate()`, `save()` and `handle_upload()` only run after `handle()`'s nonce check, and the remaining anonymous exception is intentional. A `phpcs:disable` block documents it, and `composer lint` is now clean.

## Second review: Meta pull request (November 6, 2026)

Sources: CodeRabbit's automatic review of the WordPress.org Meta pull request that adds the theme ([WordPress/wordpress.org#1186](https://github.com/WordPress/wordpress.org/pull/1186)), and a security analysis sent privately to the owner. The owner approved every fix below. Each finding was confirmed against the code and against `wptv2` before it was fixed.

| ID | Finding | Severity | Inherited from wptv2 | Fix |
|---|---|---|---|---|
| S1 | Submission XHR response: unnormalized kept fields, no content type | Hardening | Yes | Done |
| S2 | Anonymous `?guid=` lookup scans the whole catalog (also CodeRabbit) | Medium | No (new code) | Done |
| S3 | Unbounded `posts_per_page` in `/api/videos.json` (R3, also CodeRabbit) | Low | Yes | Done (owner decision) |
| S4 | Producer username sent unencoded to `grav-redirect.php` (also CodeRabbit) | Low | Yes | Done |
| S5 | `?flush_rules=1` can be triggered by a link, with no nonce | Low | Partly | Done (owner decision) |
| S6 | Submitter email stored as typed, not as validated | Info | Yes | Done |
| S7 | JSON-LD encoded without `JSON_HEX_TAG` | Info | Yes | Done |
| C1 | `/api/videos.json` exposes file URLs of password-protected videos (CodeRabbit) | Low | Yes | Done |
| C2 | `publish-video` stat counts every post type (CodeRabbit) | Low | Yes | Done |
| C3 | README links to development files that the Meta copy does not contain (CodeRabbit) | — | No | Done |

- **S1.** `handle()` copies the kept fields of a failed submission into the redirect URL. Now `post_category` keeps only non-zero integer IDs, other kept fields only plain strings, and the XHR response is `text/plain` with `X-Content-Type-Options: nosniff` and `esc_url_raw()`. `submission-form.js` already parses the response with `new URL()`.
- **S2.** The block-GUID fallback paged through every post whose text contained the GUID, 100 at a time, and any 8-character word passes the GUID format check. It now runs one exact search for the serialized attribute (`"guid":"<GUID>"`, `sentence`, 10 posts, no found rows or cache priming) and still confirms each candidate with `Video\get_guid()`.
- **S3.** `MAX_PER_PAGE` (100): negative values and values above it get 100. Other values behave as before. Closes [B13](backlog.md).
- **S5.** A plain `?flush_rules=1` now shows a confirmation link with a nonce; the flush itself needs the nonce. Who can run it is unchanged. CodeRabbit's second pass also found that the handler never flushed: it ran on `init` and exited, but core defers any flush requested before `wp_loaded`, so the rules stayed unchanged while the page said "Rewrite rules flushed." The handler now runs on `wp_loaded`. Checked through a full WordPress load with a sentinel rewrite rule: on `init` the sentinel survived, on `wp_loaded` the rules were regenerated. The earlier CLI check had missed it because `wp_loaded` had already run.
- **Not changed:** CodeRabbit's second pass also suggested capping `posts_per_page=0`. WordPress then uses the site's own `posts_per_page` option, which only administrators set, so it is the normal page size, not a request-controlled value.
- **C1.** `get_video_item()` returns the metadata of password-protected videos without the original, MP4/Ogg and subtitle URLs, consistent with R2.

Checks on the review site, with disposable fixtures deleted afterwards: the XHR response keeps only normalized fields and a normal failed submission keeps its fields as before; a block GUID, a legacy shortcode GUID and a common word each take exactly two searches; `posts_per_page` -1, 5 and 1000 return 100, 5 and 100 videos; a protected video keeps its metadata without file URLs; publishing a page bumps no stat; the JSON-LD stays valid with no raw `<`, `>` or `&`; `flush_rules` without a nonce does not flush, with a nonce it does, and anonymous visitors are unaffected. `submission-validation.php` (16 checks, username encoding added) and `video-integrations.php` (23 checks, updated for the bounded lookup) pass. `composer lint` and the Meta repository's `phpcs.xml.dist` report no issues. CLI checks cannot read response headers; the two header calls were confirmed to run. No full route regression was run.

### Full CodeRabbit review of the final export (November 6, 2026)

After the fixes above, CodeRabbit reviewed the whole theme again. The owner approved five fixes and deferred three findings to the next release.

| ID | Finding | Inherited from wptv2 | Fix |
|---|---|---|---|
| C4 | JSON-LD of password-protected videos lists their file URLs in `contentUrl` | Yes | Done: no JSON-LD while the post needs a password |
| C5 | Open Graph of password-protected videos adds the player and MP4 URLs | Yes | Done: existing tags only |
| C6 | `?guid[]=x` reaches the legacy search as an array ("Array to string conversion" and a wasted query) | Yes | Done: non-strings are rejected first |
| C7 | The design lock rejects read-only `HEAD` and `OPTIONS` requests | No | Done: GET, HEAD and OPTIONS pass |
| C8 | JSON-LD prints `"thumbnailUrl": false` when there is no image | Yes | Done: the key is omitted |
| — | Failed submissions leave a pending post, and on error 4 an attachment | Yes | Deferred: [B15](backlog.md) |
| — | "Post content to clean" treats `[WPVIDEO]` in capitals as clean | No | Deferred: [B16](backlog.md) |
| — | JSON-LD `description` contains the slides link HTML (unverified by CodeRabbit) | Yes | Deferred: [B17](backlog.md) |

Checks on the review site, with disposable fixtures deleted afterwards: a public video keeps its JSON-LD and player card; a password-protected one prints no JSON-LD and keeps only the existing Open Graph tags; without an image the JSON-LD has no `thumbnailUrl`, with one it keeps it; `?guid[]` returns no post with no query and no warning; as an administrator, GET, HEAD and OPTIONS on `/wp/v2/templates` return 200 while POST and DELETE return `403 wptv_design_locked`. The GUID lookup checks and `video-integrations.php` (23 checks) still pass, and `composer lint` is clean.

## Not covered yet

- Formatting and docblock findings: **fixed** on October 22, 2026. `phpcbf` handled 140 (a token comparison showed no code change), and the rest were fixed by hand with equivalent code. `composer lint` is clean since R1 was fixed.
- JavaScript review beyond the HTML sinks searched here.
- Feature parity with `wptv2`.
