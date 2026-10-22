# WordPress.tv RSS feed

Structure of the main RSS 2.0 feed, element by element, as a basis for a visual schema.

**Sources (October 22, 2026):**

- **Production:** `https://wordpress.tv/feed/`, served today by `wptv2` on WordPress.com, downloaded and parsed: 50 items, all with the same structure.
- **`wptv-27`:** the same feed generated on an empty review site (WordPress 7.1.3, no Jetpack/VideoPress) with a sample video post.

Every element below is tagged with **who produces it**:

| Tag | Producer |
|---|---|
| `core` | WordPress core feed template (`feed-rss2.php`) |
| `wpcom` | WordPress.com platform additions (not present on a plain WordPress site) |
| `videopress` | VideoPress, through the `[wpvideo]` shortcode or its Media RSS data |
| `theme` | The theme (`wptv2`, ported unchanged to `wptv-27`) |

## Tree

```text
rss  @version="2.0"                                                     core
│   xmlns:content  xmlns:wfw  xmlns:dc  xmlns:atom  xmlns:sy  xmlns:slash  core
│   xmlns:georss  xmlns:geo  xmlns:media                                wpcom
│
└── channel  [1]
    ├── title                     "WordPress.tv"                         core
    ├── atom:link  @rel="self"    @href=feed URL  @type="application/rss+xml"   core
    ├── link                      "https://wordpress.tv"                 core
    ├── description               site tagline                           core
    ├── lastBuildDate             RFC 822 date of the newest change      core
    ├── language                  "en"                                   core
    ├── sy:updatePeriod           "hourly"                               core
    ├── sy:updateFrequency        "1"                                    core
    ├── generator                 "http://wordpress.com/"                core (value set by wpcom)
    ├── site  (xmlns="com-wordpress:feed-additions:1")   blog ID "5089392"   wpcom
    ├── cloud  @domain @port @path="/?rsscloud=notify" @registerProcedure @protocol="http-post"   wpcom
    ├── source:cloud  (xmlns:source)  "https://wordpress.tv/?rsscloud=notify"   wpcom
    ├── image                                                            wpcom
    │   ├── url                   "https://s2.wp.com/i/webclip.png"
    │   ├── title                 "WordPress.tv"
    │   └── link                  "https://wordpress.tv"
    ├── atom:link  @rel="search"  @href="https://wordpress.tv/osd.xml"  @type="application/opensearchdescription+xml"  @title   wpcom
    ├── atom:link  @rel="hub"     @href="https://wordpress.tv/?pushpress=hub"   wpcom
    │
    └── item  [0..posts_per_rss, newest first; 50 in production]
        ├── title                 post title ("Speakers: Title")         core
        ├── link                  permalink                              core
        ├── comments              permalink + "#respond" | "#comments"   core
        ├── dc:creator            author display name (CDATA)            core
        ├── pubDate               RFC 822 publication date               core
        ├── category  [0..n]      one per category, then one per tag (CDATA)   core
        ├── guid  @isPermaLink="false"   stored post GUID                core
        ├── description           excerpt (CDATA) + slides link          core + theme
        ├── content:encoded       full rendered content (CDATA)          core + videopress
        ├── wfw:commentRss        comments feed URL of the video         core
        ├── slash:comments        number of comments                     core
        ├── post-id  (xmlns="com-wordpress:feed-additions:1")   post ID   wpcom
        │
        ├── media:content  @width  @height        ── the VideoPress video   videopress
        │   ├── media:player     @url="https://videopress.com/v/{GUID}"
        │   ├── media:rating     @scheme="urn:mpaa"   "g" | "pg" | …
        │   ├── media:title      @type="plain"        video (attachment) title
        │   └── media:thumbnail  @url  @width  @height   VideoPress poster
        │
        └── media:content  @medium="video"  @isDefault="false"  @url   ── the original upload   theme
```

## Channel

| Element | Value in production | Producer | Notes |
|---|---|---|---|
| `title` | `WordPress.tv` | core | Site title |
| `atom:link rel="self"` | `https://wordpress.tv/feed/` | core | URL of the feed being read |
| `link` | `https://wordpress.tv` | core | Site address |
| `description` | "WordPress-related videos curated and moderated by the WordPress.org community" | core | Site tagline |
| `lastBuildDate` | e.g. `Thu, 22 Oct 2026 14:51:02 +0000` | core | Newest modification among the posts |
| `language` | `en` | core | `en-US` on a plain site |
| `sy:updatePeriod`, `sy:updateFrequency` | `hourly`, `1` | core | |
| `generator` | `http://wordpress.com/` | core | WordPress version URL on a plain site |
| `site` | `5089392` | wpcom | WordPress.com blog ID |
| `cloud`, `source:cloud` | rssCloud endpoint | wpcom | Push notifications for feed readers |
| `image` (`url`, `title`, `link`) | WordPress.com web clip icon | wpcom | |
| `atom:link rel="search"` | `https://wordpress.tv/osd.xml` | wpcom | OpenSearch description |
| `atom:link rel="hub"` | `https://wordpress.tv/?pushpress=hub` | wpcom | WebSub/PubSubHubbub hub |

The `georss` and `geo` namespaces are declared by WordPress.com but no element uses them.

## Item

| Element | Value | Producer | Notes |
|---|---|---|---|
| `title` | Post title | core | Usually "Speakers: Title" |
| `link` | Permalink | core | `https://wordpress.tv/YYYY/MM/DD/slug/` |
| `comments` | Permalink + `#respond` (no comments) or `#comments` | core | |
| `dc:creator` | Author display name | core | `WordPress.tv` in production (the anonymous upload user's display name) |
| `pubDate` | Publication date | core | |
| `category` | Categories first, then tags, one element each | core | Includes the year and location categories. Speakers, event, language and producer taxonomies are **not** listed. Up to 4 per item in the sample |
| `guid isPermaLink="false"` | Stored GUID | core | In production, the GUID created with the draft: `http://wordpress.tv/?p={ID}&preview=true&preview_id={ID}` (legacy quirk of the submission flow; stable, so readers can rely on it) |
| `description` | The excerpt | core + theme | Manual excerpt as typed (all 50 production items had one); if empty, the first 55 words of the content without shortcodes. When the video has slides, the theme appends `<p><a href="{slides URL}">Presentation Slides &#187;</a></p>` (40 of 50 items) |
| `content:encoded` | Rendered post content | core + videopress | See below |
| `wfw:commentRss` | `{permalink}feed/` | core | |
| `slash:comments` | Comment count | core | |
| `post-id` | Post ID | wpcom | |
| `media:content` (VideoPress) | The hosted video | videopress | See below |
| `media:content` (original) | The original uploaded file | theme | See below |

### `content:encoded`

For a clean video post (content = one `[wpvideo]` shortcode), WordPress.com renders the shortcode as the same three pieces in every production item:

```html
<iframe title='VideoPress Video Player' aria-label='VideoPress Video Player'
        width='400' height='233'
        src='https://video.wordpress.com/embed/{GUID}?hd=1&autoPlay=0&permalink=1&loop=0&preloadContent=metadata&muted=0&playsinline=0&controls=1&cover=1'
        frameborder='0' allowfullscreen allow='clipboard-write; presentation'></iframe>
<script src='https://v0.wordpress.com/js/next/videopress-iframe.js?m={version}'></script>
<div><a href="{permalink}"><img alt="{video title}" src="https://videos.files.wordpress.com/{GUID}/{file}_mp4_std.original.jpg" width="160" height="120" /></a></div>
```

The linked thumbnail is a fallback for readers that strip iframes. Any other text left in the post content (see the Post content to clean tool) follows these pieces.

### `media:content`: the VideoPress video

| Element / attribute | Value | Notes |
|---|---|---|
| `@width`, `@height` | Feed player size (`400` × `233` in the sample) | No `@url` or `@medium` on this element |
| `media:player @url` | `https://videopress.com/v/{GUID}` | |
| `media:rating @scheme="urn:mpaa"` | VideoPress rating, e.g. `g` | |
| `media:title @type="plain"` | Title of the **video attachment**, not of the post | The theme keeps it in sync with the post title when it was the default or the previous title (`inc/attachments.php`) |
| `media:thumbnail @url @width @height` | VideoPress poster, e.g. `…_mp4_hd_1080p.original.jpg`, `256` × `149` | |

### `media:content`: the original upload

| Attribute | Value | Notes |
|---|---|---|
| `@medium` | `video` | |
| `@isDefault` | `false` | |
| `@url` | Original uploaded file, e.g. `https://wordpress.tv/wp-content/uploads/YYYY/MM/video-{random}.mp4` | Added by the theme (`mrss_media` filter, `inc/content.php`) when the post has a video attachment |

The theme also removes the author avatar `media:content` that WordPress.com would add for each item (`mrss_avatar_user` filter), because every video belongs to the anonymous upload user.

## Other feeds

The same item structure is served by:

- `/category/{slug}/feed/`, `/tag/{slug}/feed/`, `/event/{slug}/feed/`, `/speakers/{slug}/feed/`, `/language/{slug}/feed/`, `/producer-username/{slug}/feed/` and search feeds (`/?s={query}&feed=rss2`). Only the channel `title`, `link` and `atom:link rel="self"` change.
- `/feed/atom/`: the same data in Atom (`entry`, `summary` for the excerpt, `content` for the rendered content).
- `/comments/feed/` and `{permalink}feed/`: comments, not videos.

## `wptv-27` compared with `wptv2`

- **Structure:** identical. The theme only touches the feed in three places, all ported unchanged:
  - the slides link appended to `description`;
  - the original file `media:content`;
  - the removal of the author avatar from Media RSS.
- **Everything tagged `wpcom` or `videopress`** comes from the platform and does not depend on the theme. On the empty review site, with no WordPress.com or VideoPress, the feed has only the `core` elements and the raw `[wpvideo]` shortcode in `content:encoded`.
- **Known difference:** for posts converted to the VideoPress block, `content:encoded` renders the block player instead of the shortcode iframe, and its `posterUrl` parameter carries the featured image (approved poster change). The latest route regression found exactly this difference in four feeds; see [regression-editor-checkpoint.md](regression-editor-checkpoint.md).
