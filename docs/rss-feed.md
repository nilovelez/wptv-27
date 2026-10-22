# WordPress.tv RSS feed (`wptv-27`)

Built from a real feed generated on an empty review site (WordPress 7.1.3) with a sample video post, and from what is known of production. Checked on October 22, 2026.

**Feeds:** `/feed/` (all videos), plus the same structure on category, tag, event, speaker, language and search feeds (`/event/<slug>/feed/`, `/?s=…&feed=rss2`…). `/feed/atom/` carries the same data in Atom.

**Items per feed:** the site's "Syndication feeds show the most recent" setting (`posts_per_rss`). WordPress applies it to every feed, overriding the theme's page sizes. Same as `wptv2`.

## Channel

| Element | Value |
|---|---|
| `title` | Site title |
| `link` | Site address |
| `description` | Site tagline |
| `lastBuildDate` | Date of the newest change in the feed |
| `language` | Site language (`en-US`) |
| `sy:updatePeriod` / `sy:updateFrequency` | `hourly` / `1` |
| `generator` | WordPress version |

## Item (one per video)

| Element | Value | Notes |
|---|---|---|
| `title` | Post title | Usually "Speakers: Title" |
| `link` | Video permalink | |
| `comments` | Permalink + `#respond` or `#comments` | |
| `dc:creator` | Post author display name | Anonymous submissions are owned by `anonvideoupload` |
| `pubDate` | Publication date (GMT) | |
| `category` | One per **category** and per **tag** | Speakers, event, language and producer are **not** included (core only lists categories and tags) |
| `guid` | Stored post GUID, `isPermaLink="false"` | |
| `description` | **Excerpt**, plus `<p><a href="…">Presentation Slides »</a></p>` when the video has slides | Manual excerpt as typed; if empty, the first 55 words of the content without shortcodes |
| `content:encoded` | **Full post content** | The `[wpvideo]` shortcode is rendered by WordPress.com into the player or its link; block posts render the VideoPress block |
| `wfw:commentRss` | Comments feed of the video | |
| `slash:comments` | Number of comments | |

## WordPress.com only (Media RSS)

On WordPress.com the platform adds the Media RSS namespace (`media:`) to each item. This is not visible locally:

| Element | Value |
|---|---|
| `media:content` | VideoPress media for the video, **plus** the original uploaded file (`medium="video"`, `isDefault="false"`), added by the theme (`mrss_media` filter) |
| Author avatar | Removed by the theme (`mrss_avatar_user`), because every video belongs to the anonymous upload user |

## Same as `wptv2`?

Yes, in structure and fields. The theme only touches the feed in three places, all inherited from `wptv2`:

- the slides link in `description`;
- the original file in Media RSS;
- removing the avatar from Media RSS.

The latest route regression found that four feeds differ from `wptv2` **only** in the `posterUrl` parameter of block-based players inside `content:encoded`. That comes from the approved poster change (featured image as the poster). See [regression-editor-checkpoint.md](regression-editor-checkpoint.md).
