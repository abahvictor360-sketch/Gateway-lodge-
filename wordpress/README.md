# WordPress side of Gateway Lodge

Backup of the code that turned the static HTML in this repo into the live
Elementor site at https://www.gatewaylodgegroup.com. Everything here lived only
on the server until now.

Stack: WordPress 7.1 / PHP 8.3, Astra 4.13, Elementor 4.2.4 (containers, no Pro),
Xpro Elementor Addons + Xpro Theme Builder, WPForms Lite, Novamira.

## novamira-sandbox/

Drops into `wp-content/novamira-sandbox/`, which auto-loads every `.php` in it.

| File | On the server | What it does |
| --- | --- | --- |
| `gateway-lodge-site.php` | enabled | Runtime plugin. `[gateway_booking]` shortcode (placeholder / url / embed modes), its styles, the Settings -> Gateway Booking admin page, the inline JS that drives the desktop submenu accordion in the header drawer, and the sticky-header scroll handler. |
| `gwl-builder.php` | disabled | Elementor primitives: containers, headings, eyebrows, text, buttons, images, cards, icon lists, and `gwl_save_elementor()`. |
| `gwl-pages.php` | disabled | Section-level helpers built on the above: hero, intro, features, split, cards, CTA, FAQ, publish. |
| `gwl-convert.php` | disabled | HTML -> Elementor conversion, part 1: DOM helpers, the 40-page slug map, keyword-to-icon map, buttons, icon lists, FAQ, cards. |
| `gwl-convert2.php` | disabled | HTML -> Elementor conversion, part 2: `gwl_c_blocks()`, `gwl_c_section()`, `gwl_c_convert()` - the page walker. |

The four disabled files are the build tooling. They only need to be enabled when
regenerating pages from the HTML; leaving them loaded on every request is dead
weight. Re-enable by moving them back into the loader glob.

### Things that will bite you

- Elementor containers have **no `_flex_basis` control**. Size a flex child with
  `content_width: 'full'` + `width` (%) + `_flex_size: 'custom'` + `_flex_grow` /
  `_flex_shrink`. Widgets use `_element_width: 'initial'` + `_element_custom_width`.
- A capped-width block also needs `_flex_align_self: 'center'`, or it sits hard
  left instead of centred.
- Writing `_elementor_data` directly does **not** invalidate
  `_elementor_element_cache`. Delete that meta (and `_elementor_page_assets`) on
  every save or the frontend serves old markup with old element ids while the CSS
  regenerates against new ones - the page renders unstyled.
- PHP opcache serves the pre-edit copy of a sandbox file within the same request
  cycle. Call `opcache_invalidate()` after editing one mid-session.
- Pages need Astra's `site-content-layout = page-builder` and
  `ast-site-content-layout = full-width-container`, or the theme's 1240px
  `.ast-container` puts white gutters either side of a full-bleed hero.
- `m` is a reserved WordPress query var - do not use `?m=1` as a cache buster, it
  404s.

## css/

| File | Where it lives |
| --- | --- |
| `header-drawer.css` | `wp-content/uploads/gwl-import/header-drawer.css`, mirrored into Customizer -> Additional CSS between `/* GWL-HEADER-START */` and `/* GWL-HEADER-END */`. |
| `wp-custom-css.css` | The full Customizer Additional CSS as served, i.e. the header block plus the `/* GWL-BASE-START */` base block. |

Covers the off-canvas drawer at all widths, the CSS-drawn hamburger and close X,
the submenu accordion, the inline social row, footer logo alignment, and the
`.e-con-inner { max-width: min(1240px, 100%) }` boxed-width rule.

Scoping note: the footer wrapper is `.elementor-11063`, **not**
`.xpro-theme-builder-footer` - that class does not exist. The header does use
`.xpro-theme-builder-header`.

### The sticky header (GWL-STICKY block)

The header is `position: fixed` on every page. It is transparent while it sits
over the hero media and turns solid white once the hero has scrolled past;
`gwl_enqueue_sticky_header()` adds `.gwl-header-solid` from a rAF-throttled
scroll handler, and `.gwl-drawer-open` from a MutationObserver on the drawer
panel. Pages without a media hero get `body.gwl-no-hero` and are padded down
instead of overlaid.

Four things this had to work around:

- **Do not put `filter` or `backdrop-filter` on the header element.** Either
  makes it a containing block and collapses the drawer's `position: fixed` -
  the same failure that made Xpro's own sticky option unusable. A plain
  `position: fixed` ancestor is fine; the drawer still measures to the viewport.
- The cream surface is painted by the header's Elementor container
  (`.elementor > .e-con`), not by the wrapper. It is forced transparent so the
  two states switch in one place.
- That container also carries a 1px bottom border, which reads as a stray
  hairline across the hero. It is hidden while transparent.
- The MENU toggler colour needs `!important`: Elementor's per-element rule
  (`.elementor-11062 .elementor-element-x .toggler`) out-specifies a two-class
  selector.

Header height is published as `--gwl-header-h`, measured on load and resize, and
used for the hero's top padding and `scroll-padding-top` for in-page anchors.

## templates/

Raw exports, for restoring or diffing. Not import files.

| File | Source |
| --- | --- |
| `header-11062.json` | `_elementor_data` of the Xpro header template (post 11062). |
| `footer-11063.json` | `_elementor_data` of the Xpro footer template (post 11063). |
| `wpforms-11057.json` | The contact form (`wpforms` post 11057). |
| `elementor-kit.json` | Global kit settings: colours, typography. |
| `menu-gateway-main.json` | The `gateway-main` menu, 37 items with parents and order. |

Both Xpro templates are `xpro-themer` posts with
`xpro_theme_builder_target_include_locations = [ 'rule' => 'basic-global' ]`.
Sticky is deliberately **off** on the header: a transformed ancestor creates a
containing block and breaks the drawer's `position: fixed`.

## Other IDs

Home page 10, logo attachment 11140, menu term 32, Elementor kit 8.

## Still open

- STAAH booking integration - needs credentials via Ace Management Consult. The
  `[gateway_booking]` shortcode has an `embed` mode waiting for the snippet.
- Footer social handles are unconfirmed; the brief says do not publish accounts
  that have not been verified.
- Phone number is still the placeholder `+233 XX XXX XXXX`.
- One empty image widget on the `journal` page.
