<?php
/**
 * Gateway Nova Ridge : site chrome for the subdomain.
 *
 * The landing page itself is an Elementor canvas document, so the theme renders
 * no header or footer. This file supplies both, plus the sticky-header
 * behaviour and the mobile drawer.
 *
 * Chrome lives here rather than in the Elementor document on purpose: it keeps
 * the document itself pure content, so the team can edit copy and images in
 * Elementor without stepping on the navigation.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

const GWL_NR_GROUP  = 'https://www.gatewaylodgegroup.com';
const GWL_NR_PHONE  = '+233 24 000 0000';
const GWL_NR_WA     = 'https://wa.me/233240000000';
const GWL_NR_EMAIL  = 'reservations@gatewaylodgegroup.com';

/** Only dress the landing page, not wp-admin or the Elementor editor. */
function gwl_nr_is_landing() {
	if ( is_admin() ) { return false; }
	if ( class_exists( '\\Elementor\\Plugin' ) && \Elementor\Plugin::$instance->preview->is_preview_mode() ) { return false; }
	return is_front_page();
}

function gwl_nr_logo_svg( $size = 34 ) {
	return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 100 100" fill="none" aria-hidden="true" focusable="false">'
		. '<path d="M50 6 L94 50 L50 94 L6 50 Z" fill="#dba845"/>'
		. '<path d="M50 6 L74 30 L38 30 Z" fill="#ffffff"/>'
		. '<path d="M50 22 C34 22 24 32 24 48 C24 62 34 72 48 72 L48 62 C38 62 34 56 34 48 C34 38 40 32 50 32 C60 32 66 38 66 46 L44 46 L44 56 L76 56 L76 46 C76 30 66 22 50 22 Z" fill="#5c1620"/>'
		. '</svg>';
}

function gwl_nr_nav_items() {
	return array(
		'#home'        => 'Home',
		'#about'       => 'About',
		'#facilities'  => 'Facilities',
		'#gallery'     => 'Gallery',
		'#contact'     => 'Contact',
		GWL_NR_GROUP . '/' => 'Gateway Lodge Group',
	);
}

/* ---------------------------------------------------------------- header -- */

add_action( 'wp_body_open', 'gwl_nr_header' );
function gwl_nr_header() {
	if ( ! gwl_nr_is_landing() ) { return; }
	$links = '';
	foreach ( gwl_nr_nav_items() as $href => $label ) {
		$links .= '<li><a href="' . esc_url( $href ) . '">' . esc_html( $label ) . '</a></li>';
	}
	?>
<a class="gwl-skip" href="#about">Skip to content</a>
<header class="gwl-nav" id="gwl-nav">
  <div class="gwl-nav-inner">
    <a class="gwl-brand" href="#home">
      <?php echo gwl_nr_logo_svg(); // phpcs:ignore ?>
      <span class="gwl-brand-lockup">
        <span class="gwl-brand-group">Gateway Lodge Group</span>
        <span class="gwl-brand-property">Nova Ridge</span>
      </span>
    </a>
    <nav class="gwl-menu" id="gwl-menu" aria-label="Primary">
      <ul><?php echo $links; // phpcs:ignore ?></ul>
    </nav>
    <div class="gwl-nav-actions">
      <a class="gwl-book" href="#book">Book Now</a>
      <button class="gwl-burger" id="gwl-burger" type="button" aria-label="Open menu"
        aria-expanded="false" aria-controls="gwl-menu"><span></span></button>
    </div>
  </div>
</header>
<div class="gwl-backdrop" id="gwl-backdrop" hidden></div>
	<?php
}

/* ---------------------------------------------------------------- footer -- */

add_action( 'wp_footer', 'gwl_nr_footer', 5 );
function gwl_nr_footer() {
	if ( ! gwl_nr_is_landing() ) { return; }
	$year = date( 'Y' );
	?>
<footer class="gwl-footer">
  <div class="gwl-footer-top">
    <div class="gwl-footer-brand">
      <a class="gwl-brand" href="#home">
        <?php echo gwl_nr_logo_svg(); // phpcs:ignore ?>
        <span class="gwl-brand-lockup">
          <span class="gwl-brand-group">Gateway Lodge Group</span>
          <span class="gwl-brand-property">Nova Ridge</span>
        </span>
      </a>
      <p>Gateway Nova Ridge is part of Gateway Lodge Group, a growing collection of hospitality
        destinations across Ghana.</p>
    </div>
    <div>
      <h2>This property</h2>
      <ul>
        <li><a href="#about">About</a></li>
        <li><a href="#facilities">Facilities</a></li>
        <li><a href="#gallery">Gallery</a></li>
        <li><a href="#contact">Location &amp; contact</a></li>
        <li><a href="#book">Book now</a></li>
      </ul>
    </div>
    <div>
      <h2>Gateway Lodge Group</h2>
      <ul>
        <li><a href="<?php echo esc_url( GWL_NR_GROUP ); ?>/">Group website</a></li>
        <li><a href="<?php echo esc_url( GWL_NR_GROUP ); ?>/properties.html">Our properties</a></li>
        <li><a href="<?php echo esc_url( GWL_NR_GROUP ); ?>/offers.html">Offers</a></li>
        <li><a href="<?php echo esc_url( GWL_NR_GROUP ); ?>/about.html">About us</a></li>
      </ul>
    </div>
    <div>
      <h2>Reservations</h2>
      <ul>
        <li><a href="tel:<?php echo esc_attr( str_replace( ' ', '', GWL_NR_PHONE ) ); ?>"><?php echo esc_html( GWL_NR_PHONE ); ?></a></li>
        <li><a href="<?php echo esc_url( GWL_NR_WA ); ?>">WhatsApp</a></li>
        <li><a href="mailto:<?php echo esc_attr( GWL_NR_EMAIL ); ?>"><?php echo esc_html( GWL_NR_EMAIL ); ?></a></li>
      </ul>
    </div>
  </div>
  <div class="gwl-footer-bottom">
    <p>&copy; <?php echo esc_html( $year ); ?> Gateway Lodge Group. All Rights Reserved.</p>
    <ul>
      <li><a href="<?php echo esc_url( GWL_NR_GROUP ); ?>/privacy-policy.html">Privacy Policy</a></li>
      <li><a href="<?php echo esc_url( GWL_NR_GROUP ); ?>/terms.html">Terms &amp; Conditions</a></li>
    </ul>
  </div>
</footer>
<div class="gwl-mobile-book"><a href="#book">Book Now</a></div>
	<?php
}

/* ------------------------------------------------------------------- css -- */

add_action( 'wp_head', 'gwl_nr_css', 99 );
function gwl_nr_css() {
	if ( ! gwl_nr_is_landing() ) { return; }
	?>
<style id="gwl-nr-chrome">
:root { --gwl-maroon:#5C1620; --gwl-dark:#4A1119; --gwl-gold:#DBA845; --gwl-gold-l:#ECC989;
  --gwl-ink:#201A17; --gwl-ink-6:#5A5049; --gwl-ink-4:#8A8078; --gwl-border:#E6DDD3;
  --gwl-nav-h:78px; }
html { scroll-behavior:smooth; scroll-padding-top:var(--gwl-nav-h); }
@media (prefers-reduced-motion: reduce) { html { scroll-behavior:auto; } }
body { margin:0; }

.gwl-skip { position:absolute; left:-9999px; top:0; z-index:1000; background:var(--gwl-maroon);
  color:#fff; padding:.7rem 1.1rem; font:600 13px/1 Jost,sans-serif; }
.gwl-skip:focus { left:0; }

.gwl-nav { position:fixed; top:0; left:0; right:0; z-index:900; background:transparent;
  transition:background .3s ease, box-shadow .3s ease; }
.gwl-nav.gwl-solid { background:#fff; box-shadow:0 1px 0 var(--gwl-border); }
.gwl-nav-inner { width:min(100% - 3rem,1240px); margin:0 auto; height:var(--gwl-nav-h);
  display:flex; align-items:center; gap:24px; }

.gwl-brand { display:flex; align-items:center; gap:.7rem; text-decoration:none; margin-right:auto; }
.gwl-brand-lockup { display:flex; flex-direction:column; line-height:1.15; }
.gwl-brand-group { font:600 10px/1.2 Jost,sans-serif; letter-spacing:.2em; text-transform:uppercase;
  color:rgba(255,255,255,.85); }
.gwl-brand-property { font:600 19px/1.15 "Cormorant Garamond",Georgia,serif; color:#fff; }
.gwl-solid .gwl-brand-group { color:var(--gwl-ink-4); }
.gwl-solid .gwl-brand-property { color:var(--gwl-maroon); }

.gwl-menu ul { display:flex; gap:26px; list-style:none; margin:0; padding:0; align-items:center; }
.gwl-menu a { font:600 11px/1 Jost,sans-serif; letter-spacing:.16em; text-transform:uppercase;
  color:#fff; text-decoration:none; padding:.5rem 0; border-bottom:1px solid transparent; }
.gwl-menu a:hover, .gwl-menu a:focus-visible { border-bottom-color:var(--gwl-gold); }
.gwl-solid .gwl-menu a { color:var(--gwl-ink); }

.gwl-nav-actions { display:flex; align-items:center; gap:14px; }
.gwl-book { display:inline-block; background:var(--gwl-gold); color:var(--gwl-dark);
  font:600 11px/1 Jost,sans-serif; letter-spacing:.16em; text-transform:uppercase;
  padding:15px 26px; text-decoration:none; transition:background .2s ease; }
.gwl-book:hover, .gwl-book:focus-visible { background:var(--gwl-gold-l); }

.gwl-burger { display:none; width:44px; height:44px; border:0; background:none; cursor:pointer;
  padding:0; position:relative; }
.gwl-burger span, .gwl-burger span::before, .gwl-burger span::after {
  display:block; width:22px; height:1.5px; background:#fff; content:''; position:absolute;
  left:11px; transition:background .3s ease, transform .3s ease, top .3s ease; }
.gwl-burger span { top:21px; }
.gwl-burger span::before { top:-7px; left:0; }
.gwl-burger span::after { top:7px; left:0; }
.gwl-solid .gwl-burger span, .gwl-solid .gwl-burger span::before, .gwl-solid .gwl-burger span::after
  { background:var(--gwl-ink); }
.gwl-open .gwl-burger span { background:transparent; }
.gwl-open .gwl-burger span::before { top:0; transform:rotate(45deg); background:var(--gwl-ink); }
.gwl-open .gwl-burger span::after { top:0; transform:rotate(-45deg); background:var(--gwl-ink); }

.gwl-backdrop { position:fixed; inset:0; background:rgba(24,12,14,.5); z-index:880;
  opacity:0; pointer-events:none; transition:opacity .3s ease; }
.gwl-open .gwl-backdrop { opacity:1; pointer-events:auto; }

/* The Elementor document renders inside the canvas wrapper. */
.gwl-footer { background:var(--gwl-dark); color:rgba(255,255,255,.8);
  font:400 15px/1.7 Jost,sans-serif; padding:72px 0 24px; }
.gwl-footer-top, .gwl-footer-bottom { width:min(100% - 3rem,1240px); margin:0 auto; }
.gwl-footer-top { display:grid; grid-template-columns:1.6fr 1fr 1fr 1fr; gap:40px; }
.gwl-footer h2 { color:#fff; font:600 11px/1 Jost,sans-serif; letter-spacing:.18em;
  text-transform:uppercase; margin:0 0 16px; }
.gwl-footer ul { list-style:none; margin:0; padding:0; }
.gwl-footer li { margin-bottom:8px; }
.gwl-footer a { color:inherit; text-decoration:none; }
.gwl-footer a:hover, .gwl-footer a:focus-visible { color:var(--gwl-gold-l); }
.gwl-footer p { color:rgba(255,255,255,.7); margin:16px 0 0; }
.gwl-footer .gwl-brand-group, .gwl-footer .gwl-brand-property { color:#fff; }
.gwl-footer-bottom { display:flex; flex-wrap:wrap; gap:16px; justify-content:space-between;
  border-top:1px solid rgba(255,255,255,.15); margin-top:48px; padding-top:22px; font-size:14px; }
.gwl-footer-bottom ul { display:flex; gap:18px; list-style:none; margin:0; padding:0; }
.gwl-footer-bottom p { margin:0; }

.gwl-mobile-book { display:none; position:fixed; left:0; right:0; bottom:0; z-index:870;
  background:var(--gwl-maroon); padding:10px 16px; }
.gwl-mobile-book a { display:block; text-align:center; background:var(--gwl-gold);
  color:var(--gwl-dark); font:600 12px/1 Jost,sans-serif; letter-spacing:.16em;
  text-transform:uppercase; padding:16px; text-decoration:none; }

@media (max-width:1024px) { .gwl-footer-top { grid-template-columns:1fr 1fr; } }

@media (max-width:900px) {
  .gwl-burger { display:block; }
  .gwl-nav-actions .gwl-book { display:none; }
  .gwl-menu { position:fixed; top:0; right:0; bottom:0; width:min(320px,84vw); background:#fff;
    padding:calc(var(--gwl-nav-h) + 16px) 24px 24px; transform:translateX(100%);
    transition:transform .3s ease; z-index:890; box-shadow:-20px 0 40px -30px rgba(0,0,0,.5);
    overflow-y:auto; }
  .gwl-open .gwl-menu { transform:translateX(0); }
  .gwl-menu ul { flex-direction:column; align-items:flex-start; gap:4px; }
  .gwl-menu a, .gwl-solid .gwl-menu a { color:var(--gwl-ink); font-size:13px; padding:12px 0; }
  .gwl-mobile-book { display:block; }
  body { padding-bottom:68px; }
}

@media (max-width:620px) {
  .gwl-nav-inner, .gwl-footer-top, .gwl-footer-bottom { width:min(100% - 2rem,1240px); }
  .gwl-footer-top { grid-template-columns:1fr; gap:32px; }
  .gwl-brand-property { font-size:17px; }
}
</style>
	<?php
}

/* -------------------------------------------------------------------- js -- */

add_action( 'wp_footer', 'gwl_nr_js', 99 );
function gwl_nr_js() {
	if ( ! gwl_nr_is_landing() ) { return; }
	?>
<script id="gwl-nr-chrome-js">
(function () {
  var nav = document.getElementById('gwl-nav');
  var burger = document.getElementById('gwl-burger');
  var backdrop = document.getElementById('gwl-backdrop');
  var menu = document.getElementById('gwl-menu');
  if (!nav) { return; }

  // The hero is the first Elementor section; the bar goes solid once past it.
  var hero = document.querySelector('.gwl-hero') ||
             document.querySelector('.elementor-section, .e-con');
  var ticking = false;

  function paint() {
    var limit = hero ? hero.offsetHeight - 120 : 80;
    nav.classList.toggle('gwl-solid', window.scrollY > limit || document.body.classList.contains('gwl-open'));
    ticking = false;
  }
  window.addEventListener('scroll', function () {
    if (!ticking) { ticking = true; requestAnimationFrame(paint); }
  }, { passive: true });
  window.addEventListener('resize', paint);
  paint();

  function setOpen(open) {
    document.body.classList.toggle('gwl-open', open);
    burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    burger.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    backdrop.hidden = !open;
    paint();
  }
  burger.addEventListener('click', function () {
    setOpen(!document.body.classList.contains('gwl-open'));
  });
  backdrop.addEventListener('click', function () { setOpen(false); });
  menu.addEventListener('click', function (e) {
    if (e.target.tagName === 'A') { setOpen(false); }
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && document.body.classList.contains('gwl-open')) { setOpen(false); }
  });

  // #home has no element of its own — it is the top of the page.
  document.querySelectorAll('a[href="#home"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  });
})();
</script>
	<?php
}
