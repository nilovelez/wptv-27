# Video submission form

Specification of the `/submit-video/` form. The owner approved an **intermediate release (option A)**: preserve anonymous submissions and per-field moderation while retiring classic-editor and shortcode authoring. **Release 2** remains future work. See [plan.md](plan.md) (1d and 2a).

## Implemented intermediate release (local)

- `templates/page-submit-video.html` owns the fixed `wptv/submission-form` block on the left and `core/post-content` instructions on the right (2:1 columns above 1080px; stacked with form first at 1080px and below). The instructions column has `id="guidelines"`; only at stacked widths does the introductory prompt appear, with “review the guidelines” linked to `#guidelines`. All labels and controls share aligned columns above 600px, including Location, Category and Description; labels for large controls align to the top. Category groups the WordCamp checkbox with the category list under one accessible group heading. Instructions edited by the owner are left untouched. The page editor contains instructions only; the existing theme design lockdown protects the template. No upload shortcode is registered. Local page 133512 now contains only its original instruction blocks; production must recreate those instructions as page content. Its prior local content is backed up in `wp-content/wptv-work/submit-video-before.json`, outside the theme repository.
- `inc/submission-form.php` renders the form; `assets/js/submission-form.js` preserves legacy validation, event composition, speaker separators, progress/cancellation and redirect prefills. Network/malformed-server responses allow a retry instead of navigating to error output. Error codes are read from `$_REQUEST`, as in wptv2: core removes `$_GET['error']` during permalink parsing.
- `inc/submissions.php` handles `admin_post_wptv_video_upload`, also for logged-out users: creates a `pending` post titled `<speakers>: <title>`, uploads the file and embeds the resulting GUID as a `videopress/video` block, not a shortcode.
- Every field is stored in the `_wptv_submitted_video` post meta array. The excerpt is left empty.
- Only categories are assigned directly: WordCampTV by slug or the site's default category. User-selected categories and the recorded year stay in submitted metadata until approval.
- Moderators copy each value to its real field by pressing **Approve** in the Submitted video meta box below the editor. It appears on any editable post with submission metadata, regardless of author. The description ends up as the post excerpt.
- Producer username validation is ported into `WordPressTV\Submissions\wporg_username_exists()`: requests `https://wordpress.org/grav-redirect.php?user=…` without following redirects and checks Location for `d=mm`, exactly as wptv2. HTTP failures fail validation; a nonempty producer is never silently skipped.
- Real processing still requires `video_get_info_by_blogpostid()` or a compatible integration. A local media upload alone does not create a VideoPress GUID. The existing `wptv_pre_submission_video_guid` filter is only a test seam, not a production processing fallback.
- Checks: template 11/11 and instruction content 15/15 valid blocks; `tests/blocks/submission-validation.php` passes 14 mocked HTTP/validation assertions; `tests/blocks/submission-form-browser.cjs` checks anonymous required fields, event composition/manual override, speaker normalization, XHR failure/retry, malformed responses, simulated success redirects, error 14/prefill, mobile order and overflow. No videos were sent to VideoPress or new posts created by these checks. Real encoding and cancellation during a live upload remain environment checks.
- Client-side only: a 100-character `maxlength` on text inputs (200 on the slides URL), required checks and a honeypot (`wptv_honey`). The server does not enforce lengths.

## Target workflow (release 2)

The form writes each value straight to its real destination. The post is still created as `pending`. Moderators review, correct if needed and publish. The per-field Approve box is replaced by a read-only submission summary (submitter, email, IP, date). The video is embedded as a VideoPress block and its poster becomes the featured image.

Every rule below is validated **server-side** as well as in the browser. Hardcoded term IDs are replaced by slugs.

## Fields

Current anonymous form order (owner-approved): Uploaded by, Email address, Video title, Speakers, Language, Date recorded, Location, Category (including the WordCamp checkbox), Event, Producer WordPress.org Username, Description, Slides URL, Video file. The table below documents field behavior rather than display order.

| Field | Today | Release 2 |
|---|---|---|
| **This is a WordCamp video** | Checkbox. Assigns category `wordcamptv` (ID hardcoded) and changes how the event name is built. | Kept. Assigns `wordcamptv` by slug and drives the event suggestion. |
| **Uploaded by**, **Email** | Text, required for logged-out users only. Logged-in users use their account. | Kept. Email validated as an email address. Private, shown only to moderators. Later replaced by WordPress.org SSO (B6). |
| **Video title** | Free text, no server limit. Post title is `<speakers>: <title>`. | Required, **max 100 characters**. |
| **Language** | Free text. | **Selector of existing `language` terms** with search. No new terms: editors create them in the admin. |
| **Date recorded** | Native `date` input. Adds the matching Year child category. | Kept as a native date. The `year` term is assigned automatically from it; Year is not a form field. |
| **Location** | Checkboxes of the children of category `location`. Multiple. | **Moved to its own taxonomy** (see below). Multiple selection of existing terms; no new terms from the form. |
| **Category** | Checkboxes of top-level categories, excluding `location` and `year` trees. Multiple. | Same set and multiple selection. No new terms from the form. |
| **Producer WordPress.org username** | Free text, no limit. | Optional. If filled, **exactly one** value, validated as an existing WordPress.org username. Assigned to `producer-username`. |
| **Speakers** | Free text, comma-separated. Source of most inconsistencies. | **Autocomplete** against the `speakers` taxonomy, multiple values. **New speakers are allowed**: the term is created when the moderator approves the video. |
| **Event** | Text built in the browser from the other fields; editable. | **Autocomplete** against the `event` taxonomy, plus the composed name as a suggestion when no existing event matches. A new event is created on approval, like speakers. |
| **Description** | Free textarea. | Plain text, no formatting. Saved as the **post excerpt**. |
| **Slides URL** | Free text. | Optional. Validated as a URL (`http`/`https`). Saved to `_wptv_slides_url`. |
| **Video file** | File input, required. Upload with a progress bar and cancel link. | Kept. Uploaded to VideoPress and embedded as a block. |

## Event name composition (current logic, to be reviewed)

Built in the browser whenever the WordCamp checkbox, a Location, a Category or the date changes, unless the user has focused the Event field:

- `<location> <year>`, from the first checked Location and the year of the recorded date.
- WordCamp checked: `WordCamp <location> <year>`.
- Otherwise, if the BuddyCamp (`107686937`) or Global Translation Day (`467571547`) category is checked: `<category name> <location> <year>`.
- Otherwise: `WordPress Meetup <location> <year>`.
- The "World Wide Web" location (`2648`) is floated to the top of the list.

Known issues: hardcoded production IDs, only the first location is used, and any focus on the field blocks updates. Release 2 keeps the idea as a suggestion only; the review is part of 2a.

## Location and Year taxonomies (release 2)

- Location and Year stop being category trees and become two taxonomies, `location` and `year`.
- Location is chosen in the form. Year is **not** in the form: it is assigned automatically from Date recorded.
- **No public URLs:** both taxonomies are for internal queries and the public REST API only (not publicly queryable, no rewrite rules, no archives).
- The old category archives (`/category/location/<slug>/`, `/category/year/<slug>/`, feeds, pagination) return a 301 to the home page. Tracked as **B5** in [backlog.md](backlog.md) with a term migration.

## Autocomplete

`speakers`, `event` and `language` are already exposed in the core REST API (`/wp/v2/<taxonomy>?search=`), so lookups need no new routes. Pending: confirm that anonymous lookups are acceptable on WordPress.com (open question 23).
