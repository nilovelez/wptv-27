# Production checklist: Release 1

The checks for the activation window on WordPress.tv, run with the WordPress.com contact. Their result decides whether `wptv-27` stays active or `wptv2` comes back. It implements step 3 of phase 1e in [plan.md](plan.md); the local evidence it builds on is in [regression-meta-review-checkpoint.md](regression-meta-review-checkpoint.md).

Mark each check ✅ (pass), ❌ (fail) or ⚠️ (works with a difference to note), with the URL tested. Any ❌ in a **blocking** check means rollback.

## 1. Before the window

- [ ] `wptv2` is still installed, so rolling back is a theme switch.
- [ ] Someone with access can read the PHP error log during the window.
- [ ] Note the current `wptv2` menu assignments (Appearance > Menus > Manage Locations), including **Featured WordCamps**, and the Jetpack sharing settings.
- [ ] Pick the test content and write down its URLs:
  - a recent video with a VideoPress **block**;
  - an older video with a `[wpvideo]` **shortcode**;
  - a video with slides, one with downloads, one with subtitles;
  - an event, a speaker, a language, a producer username, a category with subcategories (`year`, `location`);
  - a WordCamp with recordings.
- [ ] Screenshots of the front page, one video and one archive under `wptv2`, to compare.
- [ ] Agree on a recognizable title for every test record created during the window (for example `WPTV-27 TEST – delete`), so cleanup only touches those.

## 2. Activation

- [ ] Activate `wptv-27`.
- [ ] Flush rewrite rules: as an administrator, open `/?flush_rules=1` and follow the confirmation link.
- [ ] Assign the main menu to **Primary Menu**. `wptv-27` has no footer menu location: the footer is the WordPress.org global footer.
- [ ] PHP error log: no new fatal errors or warnings from `wptv-27`.

## 3. Pages (blocking)

- [ ] Front page: recent videos, categories, events, Get involved band. Page 2 (`/page/2/`) works.
- [ ] Header: logo, menu, search; mobile menu opens and closes.
- [ ] Footer: the WordPress.org global footer appears. If it is empty, the server can't reach `wordpress.org/wp-json/global-header-footer/v1/footer`: note it, not blocking on its own.
- [ ] Pages: `/about/`, `/get-involved/`, `/submit-video/`.
- [ ] 404 page and search (`/?s=wordcamp`), with pagination.

## 4. Single video (blocking)

For the block video and the shortcode video:

- [ ] The player loads and plays.
- [ ] Title, speakers, event, language, categories, description, slides link, producer.
- [ ] Downloads list (where the video has them).
- [ ] Exactly **one** row of share buttons.
- [ ] Comments and previous/next navigation.
- [ ] Shortcode player: compare size and default quality with `wptv2` (parity difference D1; ⚠️ if smaller or lower quality, not blocking).
- [ ] Page source: Open Graph tags, JSON-LD with video data, oEmbed discovery `<link>`.

## 5. Archives (blocking)

Same posts, order and page sizes as `wptv2`:

- [ ] Category (22 per page) and a subcategory (`/category/year/2024/`, `/category/location/<city>/`).
- [ ] `/category/wordcamptv/` landing page.
- [ ] Event, speaker, language and producer-username archives.
- [ ] Tag, date and author archives (10 per page).

## 6. Feeds and endpoints (blocking)

- [ ] `/feed/` and one archive feed: valid RSS with MediaRSS (video and thumbnail).
- [ ] `/api/videos.json`, `/api/videos.json?event=<slug>`, `/api/events.json`, `/api/speakers.json`.
- [ ] `/oembed/?url=<video permalink>&format=json` and `&format=xml`.
- [ ] `/unisubs/?guid=<guid>` and `/unisubs/?url=<permalink>`.
- [ ] `/<anything>?guid=<guid>` redirects to the video.
- [ ] Redirects: `/upload` → `/submit-video/`; `/category/social-learning/` → `/category/learn-wordpress-online-workshops/` (301).

## 7. Submission and moderation (blocking)

With a short test video and the agreed test title:

- [ ] Logged out, submit through `/submit-video/`: progress, success message, the post is saved as **pending** with the submitted data.
- [ ] Validation: an empty required field and an invalid email show the error and keep the other values (including line breaks in the description and apostrophes).
- [ ] VideoPress processes the upload and the post gets its GUID and poster.
- [ ] As a moderator: the Submitted video box shows the data; **Approve** fills the fields; publish; the video plays on the front end.
- [ ] Cancel a submission in progress (if possible): nothing is left half saved, or note what is (backlog B15).

## 8. Editor and lockdown (blocking)

- [ ] Open the **test** shortcode post in the editor: it converts to a VideoPress block (save it only if it's a test record or agreed beforehand).
- [ ] The video editor only allows the one VideoPress block; Video details panel works.
- [ ] Site Editor is blocked for administrators; Appearance > Menus still works.

## 9. Known differences (not failures)

- Subtitles: the `/subtitle/` upload form and passing subtitle tracks to the player are deferred to the next release.
- Footer: comes from WordPress.org and isn't editable from WordPress.tv.
- Archive headings (D4) and the items left for the next release (B15–B17 in [backlog.md](backlog.md)).

## 10. Decision

- **Keep** `wptv-27` if every blocking check passes. Note the ⚠️ items in [backlog.md](backlog.md) and agree an observation period (plan.md, exit criterion).
- **Roll back** if any blocking check fails and it can't be fixed within the window.

## 11. Rollback

1. Activate `wptv2`.
2. Flush rewrite rules (`/?flush_rules=1` under `wptv2`).
3. Check the menu assignments against the notes from step 1, especially **Featured WordCamps** on `/category/wordcamptv/`.
4. Posts saved in the editor during the window now hold a VideoPress block instead of the shortcode: restore the shortcode from the post's revisions (or rebuild it from the attached video).
5. Load the front page, a video and an archive; check the PHP error log.

## 12. Cleanup

- [ ] Delete only the records with the agreed test title (posts and their attachments).
- [ ] Write the results, with the URLs and ⚠️ notes, into [handoff.md](handoff.md).
