# External review: security and database findings

Reviewer: external review session (Claude Code), October 22, 2026, on branch `chore/review-tooling`.
Scope: the `WordPress.Security.*`, `WordPress.DB.*`, `GlobalVariablesOverride`, `PrefixAllGlobals` and `DiscouragedPHPFunctions` findings of the first `composer lint` (WPCS 3.4.1), plus a manual read of every request handler, public endpoint, write path and `phpcs:ignore` in `functions.php`, `inc/` and `patterns/`. Legacy behavior was compared against the `wptv2` SVN checkout. Formatting and docblock findings are out of scope here.

Evidence comes from code reading and from an empty Studio review site (WordPress 7.1.3, no Jetpack/VideoPress, no WordPress.tv data). Nothing was checked on WordPress.com or the Atomic test site.

## Summary

| ID | Area | Severity | Inherited from wptv2 | Owner |
|---|---|---|---|---|
| R1 | Submission nonce skipped when `wptv_uploaded_by` is filled | Low–medium | Yes | Theme developers (behavior) |
| R2 | oEmbed and unisubs `?url=` resolve unpublished posts | Medium | Yes | Theme developers (behavior, routes) |
| R3 | `/api/videos.json?posts_per_page=` is unbounded | Medium (availability) | Yes | Recorded as backlog B13 |
| R4 | Submission length check measures field names, not values | Low | Yes | Theme developers (behavior) |
| R5 | REST design lockdown bypassed with uppercase routes | Low | No (new code) | Reviewer, pending authorization |
| R6 | Single-block cleanup drops non-empty content too | — | No | Intended; decision log corrected |
| D1–D2 | Unslash/sanitize and documented false positives | None (standards) | — | Done by the reviewer |
| D3 | Nonce warnings in `validate()`/`save()` | — | — | Open until R1 is decided |

No SQL injection, output-escaping or stored-XSS defect was found. The submission review panel renders through React elements (text is escaped), the slides URL is sanitized with `esc_url_raw()` for http/https and escaped on output, and REST meta writes require `edit_post`.

## Findings for the theme developers

### R1. Submission nonce is skipped when "Uploaded by" is filled

`inc/submissions.php:55` only verifies the nonce when `wptv_uploaded_by` is empty. That field is sent by anonymous users, but nothing stops a forged request from sending it for a logged-in user. A cross-site POST from a page the logged-in user visits then creates a pending submission whose `submitted_by`/`submitted_email` are the logged-in user's display name and email (`insert_submission()` uses `wp_get_current_user()`).

- Same condition in `wptv2` (`plugins/wordpresstv-anon-upload/anon-upload.php:21-26`), presumably so cached form pages with expired nonces keep working for anonymous visitors.
- Impact is limited: the post is pending and owned by `anonvideoupload`, but it is attributed to a real user and accepts an arbitrary upload.
- Suggested change: always verify the nonce when `is_user_logged_in()`; keep the anonymous exception only if page caching requires it, and record that reason in `docs/decisions.md`.
- Check after the change: logged-in submission with a valid nonce succeeds; logged-in submission with a filled `wptv_uploaded_by` and an invalid nonce returns `?error=15`; anonymous submissions behave as before.

### R2. oEmbed and unisubs resolve unpublished posts

`inc/endpoints/oembed.php` and `inc/endpoints/unisubs.php` (`?url=` branch) resolve the URL with `url_to_postid()` and use the post without checking its status or password. `url_to_postid()` returns the ID of a `?p=<ID>` URL without any status check: on the review site, an anonymous `url_to_postid( home_url( '/?p=5' ) )` returned the ID of a pending post.

- Anyone can enumerate IDs and get the title, VideoPress player HTML, GUID, description and dimensions of pending submissions, drafts, private and password-protected posts. Pending anonymous submissions all carry a VideoPress block.
- The unisubs `?guid=` branch is not affected: it already queries `post_status => publish`.
- Same code path in `wptv2` (`plugins/wordpresstv-oembed/wordpresstv-oembed.php:63`, `plugins/wordpresstv-unisubs/wordpresstv-unisubs.php:97`).
- Suggested change: after resolving the post, return the existing 404 unless `is_post_publicly_viewable( $post )` and `! post_password_required( $post )`.
- This changes responses for non-public posts only. Route regression: published fixtures must stay identical.

### R3. The videos API accepts any page size

`inc/endpoints/api.php:129-132` re-runs the main query with `posts_per_page` from the request. `-1` or a very large value builds one response for every published video (about 18,700), with per-post term and VideoPress lookups. It is an unauthenticated, uncached way to load the server.

- Inherited unchanged from `wptv2` (`plugins/wordpresstv-rest/wordpresstv-rest.php:64-65`) and part of the API contract, so Phase 1 rule 1 applies.
- The owner recorded it as [backlog B13](backlog.md); no change for now.

### R4. Submission text fields have no server-side length limit

`validate()` (`inc/submissions.php:127-131`) measures the field *names*, which are never longer than 150 characters, so the check never fails. The comment says this was kept from `wptv2` on purpose; `wptv2` measures `(string) $field` in the same loop.

- Only the browser limits lengths (`assets/js/submission-form.js`, slides URL only). Direct POSTs can store arbitrarily long titles, speakers and descriptions in post meta.
- Suggested change: measure `$_POST[ $field ]` values. Owner decision because it changes which submissions are accepted.

## Resolved by the owner

### R6. The single-block cleanup removes all other content

`keep_only_video_block()` (`inc/editor.php`) keeps only the video block whenever a `post` contains exactly one `videopress/video` block, removing any other block, including paragraphs with text, on every save. The decision log only mentioned "stray empty paragraphs". The owner confirmed this is intended: a video post's content may only hold its single VideoPress block. `docs/decisions.md` now says so. No code change.

## Reviewer changes

### R5. Pending authorization

REST design lockdown is case-sensitive. `block_design_writes()` (`inc/lockdown.php:75-84`) matches `#^/wp/v2/(templates|template-parts|global-styles)(/|$)#`, but core matches REST routes case-insensitively (`class-wp-rest-server.php`, `'@^' . $route . '$@i'`). On the review site, as an administrator, `POST /wp/v2/templates` returned `403 wptv_design_locked` while `POST /wp/v2/TEMPLATES` reached the templates controller.

This is a policy guard, not a privilege boundary: only users who can already edit theme options can write there. Fix: add the `i` modifier to the pattern.

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

These are the real consequence of R1 and are left open until R1 is decided. They should not be silenced with a comment while the nonce is conditional.

## Not covered yet

- Formatting and docblock findings (about 200, 140 fixable with `phpcbf`); these touch many lines and should be scheduled with the developers to avoid conflicts.
- JavaScript review beyond the HTML sinks searched here.
- Feature parity with `wptv2`.
