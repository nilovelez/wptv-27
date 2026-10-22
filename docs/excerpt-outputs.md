# Excerpt outputs: what the video description looks like everywhere

The excerpt is the video description. Other tools read it through feeds, the APIs, structured data and social tags, so its shape is part of the integration contract. This page describes what `wptv-27` outputs, and where it differs from `wptv2`.

Checked on October 22, 2026 by reading the code of both themes and running three sample posts on an empty review site (WordPress 7.1.3). Jetpack and WordPress.com behavior was not observed; those rows are marked.

## 1. How the excerpt is built

`get_the_excerpt()` returns:

```
excerpt = manual excerpt                      (post_excerpt, as typed)
          OR, if empty, automatic excerpt     (see below)
        + slides link, if _wptv_slides_url    (inc/content.php, get_the_excerpt filter)
```

- **Manual excerpt:** returned as typed, untrimmed, without paragraphs added.
- **Automatic excerpt** (core `wp_trim_excerpt()`, only when `post_excerpt` is empty): the post content with registered shortcodes removed (`[wpvideo]` is registered on WordPress.com), non-text blocks removed, `the_content` filters applied, tags stripped, then trimmed to 55 words plus ` [&hellip;]`.
- **Slides link:** `<p><a href="{slides URL}">Presentation Slides &raquo;</a></p>`, appended as **HTML** to every excerpt read through `get_the_excerpt()`. Same filter and markup as `wptv2`.

Consequences:

| Post | Excerpt |
|---|---|
| Manual excerpt + slides | `Manual description.<p><a href="…">Presentation Slides &raquo;</a></p>` |
| No excerpt, text typed into the content next to the shortcode | `Text typed into the content by a moderator.` (automatic) |
| No excerpt, clean content (shortcode or VideoPress block only) | Empty, or only the slides link |

After the pre-migration cleanup moves descriptions from the content to the excerpt, every description comes from the manual excerpt. A clean post without a manual excerpt has **no description** in any output below.

## 2. Where it goes

| Output | Source | What it contains | Same as `wptv2` |
|---|---|---|---|
| Single video page, description column | `inc/single.php` (Post Excerpt block, marked `wptv-single__excerpt`) | Full excerpt through `the_excerpt` filters: paragraphs added, slides link included, never trimmed | Yes (`wptv2` used `the_excerpt()` in the sidebar) |
| RSS 2.0 `<description>` / Atom `<summary>` | Core `the_excerpt_rss()` | Excerpt as above, slides link included as HTML, entities converted | Yes (core behavior) |
| RSS 2.0 `<content:encoded>` | Core `the_content` | Full post content, including the video shortcode or block, **not** the excerpt | Yes |
| `/api/videos.json` → `description` | `inc/endpoints/api.php` | `get_the_excerpt()` raw: plain manual text, slides link appended as HTML. The URL is also in a separate `slides` field | Yes |
| JSON-LD `VideoObject.description` | `inc/head.php` | `get_the_excerpt()` raw, so the slides link HTML ends up inside the JSON string | Yes |
| Core REST `/wp/v2/posts` → `excerpt.rendered` | Core | Excerpt through `the_excerpt` filters: `<p>` paragraphs, slides link included | Yes (core behavior) |
| Open Graph `og:description` / Twitter description | Jetpack, not the theme | Per Jetpack's code: the manual excerpt stripped of tags, or the first 55 words of the content without shortcodes; **no** slides link (the filter is not applied). *Not verified on WordPress.com.* | Yes (Jetpack) |
| `/oembed/` | `inc/endpoints/oembed.php` | No description field | Yes |
| `/unisubs/` → `description` | `inc/endpoints/unisubs.php` | The **VideoPress** video description (`video_get_info_by_guid()`), not the WordPress excerpt; "No description given." when empty | Yes |
| Video cards (home, archives, search) | Patterns | Not shown | Changed by design |
| `templates/index.html` (fallback only) | Core Post Excerpt block | Trimmed by core, links removed | n/a |

## 3. Notes for integrations

- The description is **HTML-capable** wherever the slides link is appended: consumers of `/api/videos.json`, JSON-LD and feeds receive a trailing `<p><a …>Presentation Slides »</a></p>` when a video has slides. This is inherited from `wptv2` and unchanged (C3 in [review-parity.md](review-parity.md) proposes rendering the link only on the single page, which would change these outputs).
- Open Graph uses a different source (Jetpack) and can differ from the other outputs: no slides link, and its own trimming.
- `/unisubs/` is the only output that uses VideoPress's own description.
