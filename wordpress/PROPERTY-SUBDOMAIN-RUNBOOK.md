# Property subdomain runbook

How Gateway Nova Ridge was built on `novaridge.gatewaylodgegroup.com`, and what
to repeat for **Lakeside** and **Tamale**. Written after the Nova Ridge build so
the next two do not have to rediscover the same traps: every "Gotcha" below cost
a rebuild cycle.

Read this before touching the other two subdomains.

---

## 1. What each property gets

Four native Elementor pages, a menu, an XPRO header and footer, and a WPForms
enquiry form.

| Piece | Detail |
| --- | --- |
| Pages | Home, About, Facilities, Contact |
| Menu | `<slug>-main`, four items, assigned to every registered theme location |
| Front page | Home (`show_on_front` = page) |
| Header | XPRO Theme Builder template, type `type_header`, shown site-wide, sticky |
| Footer | XPRO template, derived from the group site's own footer |
| Form | WPForms, on Contact, routed to `reservations@gatewaylodgegroup.com` |
| Page template | `elementor_header_footer` + Astra stretched layout |

Constraints the client set, all verifiable in the rendered HTML:

- Native Elementor **containers** only. No sections, no inner sections.
- **No atomic elements** (`e-div-block`, `e-flexbox`, `e-grid`) — the atomic
  editor is off.
- **No HTML widgets.** WPForms goes in through the core `shortcode` widget.
- Header and footer use **XPRO widgets**, not Elementor Pro.

Check with: `substr_count($html, 'elementor-section')`, `'e-div-block'`,
`'elementor-widget-html'` — all must be `0`.

---

## 2. Prerequisites per subdomain

Neither Lakeside nor Tamale had a reachable install when Nova Ridge was built —
this is the actual blocker, not the code.

1. WordPress on the subdomain, with: **Astra**, **Elementor**, **XPRO Elementor
   Addons**, **XPRO Theme Builder**, **WPForms Lite**, **AIOSEO**, **Novamira**.
2. A **Novamira connector** added for that site in the Claude connector
   settings. One connector reaches exactly one install.
3. The property's photography in the repo under `<slug>/media/`.

Confirm the target before writing anything:

```php
return [ 'home' => home_url(), 'abspath' => ABSPATH, 'theme' => get_stylesheet() ];
```

---

## 3. Build order

Each step depends on the one before it.

1. **Media** — sideload images with `media_sideload_image( $url, 0, $alt, 'id' )`,
   video with `download_url()` + `media_handle_sideload()`. Store the id map in
   the `gwl_media_map` option.

   **Key every entry by the repo filename without its extension**
   (`hero-living`, `novaridge-tour`), because that is what
   `build-wp-property-content.py` derives from `<slug>/media/`. Nova Ridge
   originally stored the video under `tour`, the config asked for
   `novaridge-tour`, the lookup returned nothing and the hero silently fell back
   to the still. If a key is missing the build does not fail — it just quietly
   loses that image.
2. **Site logo** — `set_theme_mod( 'custom_logo', $id )`. `xpro-site-logo` reads
   `custom_logo`; without it the header shows nothing. Use
   `novaridge/media/gateway-logo.png` (rendered from the SVG; WordPress will not
   take the SVG without extra filters).
3. **Elementor kit** — merge `wordpress/templates/elementor-kit.json` into the
   active kit's `_elementor_page_settings`. Gives brand colours, Cormorant
   Garamond / Jost and the 1240px container.
4. **Pages, menu, front page, form, header, footer** — one call to the builder.
   The header comes out sticky; see section 5.
5. **Customizer CSS** — the builder writes it; see Gotcha 1.
6. **AIOSEO** — `\AIOSEO\Plugin\Common\Models\Post::savePost( $id, $data )`.
   The `aioseo-posts/seo-data-update` ability fails with "Post not found" until
   AIOSEO has a row for the post; the model call creates one.
7. **Disable the build tooling** — `touch` a `.<file>.disabled` marker beside
   each of `gwl-builder.php`, `gwl-pages.php`, the property builder. The runtime
   chrome, if any, stays enabled.

---

## 4. Gotchas, each of which cost a cycle

### 1. The page renders at half width

Astra ships `.site-content .ast-container{display:flex}` for its content and
sidebar columns. A page-builder template has no `#primary` to fill it, so the
Elementor wrapper becomes a lone flex item and shrinks to fit.

- Set `ast-site-content-layout` = **`full-width-layout`** (Stretched). Not
  `full-width-container`, which leaves Astra's container sizing the content.
- Plus the CSS the builder writes to Additional CSS:
  `.ast-page-builder-template .site-content > .ast-container { display: block; }`
  and `… > .elementor { width: 100%; }`.

### 2. The XPRO header and footer do not render

`Xpro_Theme_Builder_Main::get_settings( 'type_header' )` passes that same string
to `get_template_id()`, which compares it against the post meta. So:

- `xpro_theme_builder_template_type` must be **`type_header`** / **`type_footer`**,
  not `header` / `footer`. With the obvious value the display rules match but the
  type filter drops it, and Astra keeps rendering its own header.
- `xpro_theme_builder_target_include_locations` = `[ 'rule' => [ 'basic-global' ] ]`.
- **Delete** `xpro_theme_builder_target_exclude_locations` and
  `…_target_user_roles`. An empty rule array reads as "exclude everywhere".

### 3. A container row overflows to twice its width

Elementor's `e-con-full` carries `--width: 100%`. Two such children in a row each
claim the whole row. Worse, `_flex_size: 'none'` compiles to `flex: 0 0 auto`,
which **ignores** the separate `_flex_grow` / `_flex_shrink` controls.

- Give row children explicit widths (the header uses 50% each) with
  `_flex_size: 'custom'`, `_flex_grow: 1`, `_flex_shrink: 1`.

### 4. Widgets inside a row container split its width between them

That squeezed the header menu until CONTACT wrapped and broke the button to
"BOOK / NOW". Pinned in the Customizer CSS rather than an Elementor width
control, because the control did not survive a rebuild:

```css
.xpro-theme-builder-header .elementor-widget-xpro-horizontal-menu,
.xpro-theme-builder-header .elementor-widget-button { width: auto; max-width: none; flex: 0 0 auto; }
```

### 5. Small labels balloon on phones

`gwl_heading()` defaults mobile type to `max( 26px, 55% of desktop )`. Fine for
display type; it turns a 12px eyebrow or an 11px figure caption into 26px, which
wraps meta lines and pushes text off the edge.

- The responsive pass clamps mobile type so a heading is **never larger on mobile
  than on desktop**.

### 6. Tablet inherits the desktop width

`gwl_container()` copies the desktop share onto `width_tablet`. Between 768px and
1024px that leaves cards four-across and splits side by side.

- `gwl_nrp_make_responsive()` walks the finished tree: narrow columns (<30%) go
  48% on tablet, halves go 100%, pixel widths go 100%, display type steps to ~74%,
  section padding to ~70%. Run it on every page before saving.

### 7. Rebuilds silently use a stale file

`raw.githubusercontent.com` served an old copy for minutes, through query-string
cache busting and `Cache-Control: no-cache`. Two rebuilds no-opped and looked
identical, which wasted a lot of time chasing the wrong cause.

- Fetch through the **GitHub API** instead:
  `https://api.github.com/repos/<owner>/<repo>/contents/<path>?ref=<branch>`
  with `Accept: application/vnd.github.raw` and a `User-Agent`.
- After any build, assert a marker string from the new code is present in the
  file on disk before trusting the result.
- **Write the file in one call and run the build in the next.** PHP's opcode
  cache can still serve the previously compiled version of a file overwritten
  earlier in the same request, so a build that fetches and then immediately
  requires a builder file runs the *old* code. The Lakeside build silently kept
  the previous Customizer CSS this way, while the fetch itself reported success.
  Assert on an effect (a rule present in `wp_get_custom_css()`), not just on the
  bytes on disk.

### 8. WPForms Lite has no date field

`type: 'date-time'` is silently dropped on save. Check-in / check-out are plain
`text` fields with a date placeholder.

### 9. Elementor caches

`gwl_save_elementor()` deletes `_elementor_element_cache` and
`_elementor_page_assets` and regenerates the post CSS. The build then calls
`files_manager->clear_cache()`, which wipes the generated CSS — it regenerates on
the next front-end request, so **request each page once** before inspecting
`wp-content/uploads/elementor/css/post-<id>.css`.

### 10. A missing media key fails silently

`gwl_media()` returns an empty url for an unknown key, and the page still builds.
After a build, assert the hero video appears in the served HTML rather than
assuming it does.

### 11. XPRO widget controls that look like they should just work

Three of these were reported as visual bugs on Nova Ridge after launch. Read the
widget's own control schema before assuming a shape:

```php
$w = \Elementor\Plugin::$instance->widgets_manager->get_widget_types( 'xpro-simple-gallery' );
$c = $w->get_controls();   // ['gallery']['type'], ['gallery']['fields'], selectors, defaults
```

- **`xpro-simple-gallery`'s `gallery` is a repeater of *filter groups*, not a
  media gallery.** Each row is `[ '_id', 'filter', 'is_default_filter',
  'images' ]`, and `images` is the actual gallery of `[ 'id', 'url' ]`. Passing
  image rows straight into `gallery` renders the widget with **no pictures at
  all** and no error. Emit one row (`is_default_filter => 'yes'`) and set
  `show_filter => ''` so the filter bar stays hidden.
- **`xpro-social-icon` lays its icons out in a CSS grid**, and
  `social_icon_column_grid` defaults to `3` — six icons break onto two rows. Set
  it to the number of icons. The gaps are `social_icon_item_space_vertical`
  (column gap) and `social_item_space_between` (row gap); `social_icon_spacing`
  is not a control and is ignored.
- **`xpro-site-logo` renders the chosen thumbnail at its natural size.** With
  `thumbnail_size => 'thumbnail'` that is 150x150, which makes the footer brand
  lockup three times taller than the link columns beside it. Set `width`,
  `height` and `object-fit => 'contain'`.
- A **nested row container** carries Elementor's default 10px padding, which
  insets the logo from its column's left edge and drops it below the headings in
  the columns beside it. Zero the padding on the lockup row.
- An **explicit `padding` replaces a boxed container's responsive default**, so
  the footer's copyright bar (padding `20 0 0 0`) lost the 10px mobile gutter and
  sat flush against the screen edge. Set `padding_mobile` too whenever a boxed
  container carries an explicit desktop padding.

### 12. A hero video that never plays

The film was fine; its container was not. An MP4 keeps its index in a `moov`
atom, and if that atom sits after the media data the browser has to fetch the
whole file before it can read a single frame. Lakeside's 18MB original had its
`moov` at byte 18,364,882, so the hero showed the fallback still indefinitely.
Elementor also sizes a hosted background video only once metadata arrives, so
until then the `<video>` stayed at its intrinsic 300x150 inside a 900px hero.

Every server-side check passed while this was broken: the attachment existed,
the URL returned 200 with `Accept-Ranges: bytes`, and `background_video_link`
was correct in `_elementor_data`.

Check any film before it goes up, and re-encode if the atom is at the back:

```
python3 -c "d=open('f.mp4','rb').read(); print(d.find(b'moov'), d.find(b'mdat'))"
ffmpeg -i in.mp4 -vf "hqdn3d=2:1.5:3:3,scale=1024:576:flags=lanczos" \
  -c:v libx264 -profile:v high -preset slower -crf 29 -pix_fmt yuv420p \
  -g 60 -an -movflags +faststart out.mp4
```

`-an` drops the audio track, which a muted background video never plays.
The proof it worked: `ffprobe` reads full metadata from the first 100KB
(`head -c 102400 out.mp4 > prefix.mp4 && ffprobe prefix.mp4`), where the same
test on the original gives "moov atom not found" - exactly what the browser saw.

Nova Ridge's and Tamale's films were already faststart; only a file handed over
straight from a phone or a camera is likely to need this.

`tools/build-landings.py` now checks every property's hero film on each run and
prints a `WARNING` naming the file if its index is at the back, so a future
shoot cannot reach a subdomain the way this one did.

### 13. A container's CSS classes use a different key from a widget's

`gwl_container( ..., array( 'cls' => 'x' ) )` wrote `_css_classes`, the widget
control. A container's is `css_classes`, with no underscore. Elementor saved
the widget key without complaint and rendered no class at all, so a stylesheet
rule written against it matched nothing. Confirm against the live install:

```php
\Elementor\Plugin::$instance->elements_manager->get_element_types('container')->get_controls()
```

### 14. A background overlay disappears under a background video

A container paints its overlay as a `::before` with no z-index of its own,
while the background video is a real child element at z-index 0, so the video
covers the overlay completely and hero copy ends up on raw footage. Lifting the
overlay works; lowering the video does not, because at `z-index: -1` it falls
behind the container's own background image and the still shows instead:

```css
.gwl-video-hero::before { z-index: 1; }
.gwl-video-hero > .e-con,
.gwl-video-hero > .elementor-element { position: relative; z-index: 2; }
```

Also set `background_overlay_opacity` to 1 where the gradient already carries
its own alphas, or Elementor's 0.5 default halves them.

Only visible once the film plays, which is why it survived the first build.

### 15. Other

- `xpro-contact-form` is XPRO's own form builder, **not** WPForms.
- XPRO renders Font Awesome icons as inline SVG, so grepping for `fab fa-` finds
  nothing even when icons are present.
- The em dash is banned in customer-facing copy by the saved design direction.

---

## 5. The sticky header

XPRO Theme Builder pins the header itself, so none of our own CSS positions it.
`gwl_nrp_themer()` sets the meta as part of the normal build; there is nothing
extra to do per property.

```php
update_post_meta( $header_id, 'xpro_theme_builder_sticky', 'enable' );
```

That puts `xtb-header-sticky` on the `<header>`. XPRO's own script then adds
`xtb-appear` once the page has scrolled past **220px**, at which point its CSS
fixes `.xpro-theme-builder-header-nav` to the top with a fade-down animation and
a drop shadow. The script also sets the header's `min-height` to the tallest nav
it has measured, so the page does not jump when the bar leaves the flow.

Three things to know:

- **Write the meta on the footer template too, as `''`.**
  `xpro_theme_builder_render_header()` reads it as `$sticky[0]` with no `isset`
  guard, so a missing row notices.
- **Do not use XPRO's `xpro_header_sticky_padding` on a boxed container.** The
  control's selector is the container, but a boxed container carries its padding
  on `.e-con-inner`. Setting it *added* 16px to the pinned bar instead of
  trimming it. The Customizer CSS targets the inner element instead:
  `.xtb-appear .xpro-theme-builder-header-nav .e-con-inner { padding: 9px 0 }`,
  with a `transition` on the resting rule. Nova Ridge's bar is 105px at rest and
  95px pinned, and the reserved `min-height` stays at 105px, so nothing shifts.
- **220px is hard-coded in XPRO's script**, not a setting. On the home page the
  bar therefore appears while the visitor is still inside the video hero.
  Changing that means overriding the scroll handler with our own JS.

The header background must stay opaque (`#FBF9F6`), or the pinned bar shows the
page through it.

---

## 6. Verifying without being able to load the site

This environment cannot reach `*.gatewaylodgegroup.com` or the Vercel preview.
Server-side checks confirm that rules exist; they cannot show how they compose.
**A doubled-width header passed every server-side check.** Mirror and render:

1. On the server, collect the page HTML and every same-origin stylesheet, then
   `base64_encode( gzencode( json_encode( $bundle ), 9 ) )` into an option.
2. Return it from `execute-php` with ~20k characters of padding so the result
   exceeds the inline limit and the harness writes it to a local file — that
   keeps the payload out of the conversation.
3. Locally: decode, write the CSS under `mirror/wp-content/...`, rewrite
   `https://<site>` to relative paths, strip `srcset`/`sizes` and the Google
   Fonts link, drop WordPress's `-WxH` image suffixes, and copy the repo's own
   media into `mirror/wp-content/uploads/<year>/<month>/`.
4. Serve the folder and screenshot with Playwright
   (`executablePath=/opt/pw-browsers/chromium`) at 1440 / 900 / 390.

The measurement that matters:

```js
window.scrollTo(9999, 0);
({ sw: document.body.scrollWidth, cw: document.documentElement.clientWidth, maxScrollX: window.scrollX })
```

`maxScrollX === 0` at all three widths is the pass. A `scrollWidth` slightly over
`clientWidth` can be the closed off-canvas drawer, which Astra clips with
`body { overflow-x: hidden }` — check `maxScrollX`, not `scrollWidth` alone.

Mirror the **scripts** too when the thing you are checking is JS-driven. The
XPRO gallery is a Cube Portfolio grid: without its script the section renders as
a bare spinner, so a CSS-only mirror cannot tell an empty gallery from a working
one. Collect same-origin `<script src>` the same way as the stylesheets. Without
them, `jQuery is not defined` is expected and static layout still measures
correctly, but the menu and the gallery will not.

Three things that quietly break the mirror:

- **Collect assets with both quote styles.** WordPress prints stylesheet links
  with single quotes and scripts with double, so a regex that only sees `"`
  returns every script and not one stylesheet. The mirror then renders
  unstyled while reporting 23 assets collected, and every measurement is wrong.
- **Rewrite the escaped form of the site URL as well as the plain one.**
  Elementor's webpack `publicPath` reaches the page as `https:\/\/site` inside
  JSON, so replacing only `https://site` leaves the runtime fetching absolute
  URLs. Its handler chunks then fail to load, and anything they drive — the
  background video among them — silently does nothing. Also copy
  `elementor/assets/js/*.bundle.min.js` in, since nothing links them from the
  HTML.
- **This Chromium cannot decode H.264.** `canPlayType('video/mp4; codecs="avc1.42E01E"')`
  returns `''`, so a perfectly good MP4 reports `error.code 4` and the element
  stays at 300x150. It proves nothing about the live site. To exercise the
  video path, transcode a few seconds to VP9 and serve it under the same name:

  ```
  ffmpeg -t 8 -i tour.mp4 -c:v libvpx-vp9 -b:v 500k -cpu-used 5 -an tour.webm
  ```

  Then `!video.paused && video.currentTime > 0` is a real pass, and the codec
  itself is settled separately with `ffprobe`.

---

## 7. Building a property

Everything is driven by slug. Once the install and connector exist:

```php
require_once WP_CONTENT_DIR . '/novamira-sandbox/gwl-nr-pages.php';
gwl_nrp_build( 'lakeside' );   // or 'tamale'
```

That creates the four pages, the menu, the front page, the WPForms form, the XPRO
header and footer, the SEO and the Customizer CSS. The content comes from
`gwl-property-content.php` and the footer from `gwl-property-footers.php`, both
generated:

```bash
python3 tools/build-wp-property-content.py
python3 tools/build-wp-footers.py
```

Media and the site logo still have to be sideloaded first (step 1 and 2 above),
and the Elementor kit applied (step 3).

## 8. What changes per property

Everything else is shared. Per property you need:

- Slug, name, short name, locality, address, unit count, tagline.
- Four figures for the band, three positioning statements, four accommodation
  cards, twelve facilities, FAQ pairs, location points.
- Media keys for: hero video + poster, About banner, Facilities banner, Contact
  banner, the split images, and two gallery sets.
- The Google Maps address string.
- Per-page AIOSEO title and description.

All of this already exists for all three properties in `tools/build-landings.py`
(`PROPERTIES`), which is the single source of truth for the copy — the static
pages and the WordPress build should not drift apart.

**Lakeside has now been photographed** (12 Sep 2026): 30 stills and a
walkthrough film of the real block, replacing the stock resort imagery and the
interiors borrowed from the other two. Every picture on the site is its own.

**Re-shooting a property is not only a media swap.** Lakeside's copy had been
written around the placeholders and claimed a lakefront — "the drive home ends
beside the water", walkable grounds, a *Lakeside Grounds* facility with "seating
out by the water", gallery captions naming a pool and a lake at sunset. The real
photographs show a gated terraced block with no water anywhere. Read the copy
against the new pictures before shipping them: the tagline, stats band, pillars,
facilities list and every alt string had to change too.

**Resize before committing.** The shoot arrived at 4096x3072, 17MB for 30 files.
At 1600px wide and quality 80 that is 5.3MB, which is the right trade for a hero
and gallery. Name each file for what it shows, not what the camera called it.

**Check borrowed photographs by eye.** Two of Tamale's filenames do not describe
their contents: `bedroom-suite.jpg` is a street view of the block and
`bedroom-tv.jpg` is a bathroom. Trusting the names put an exterior on the
Lakeside Suite card.

---

## 9. Still open on Nova Ridge

- Phone is the site-wide placeholder `+233 24 000 0000`.
- STAAH booking engine needs credentials via Ace Management Consult. A comment in
  the booking band marks where the widget goes.
- Only the home page has been rendered and inspected visually; the other three
  were checked structurally.
- The booking button reads **Book Now** (it still links to WhatsApp until the
  STAAH engine is wired up).
