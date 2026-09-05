<?php
/**
 * Plugin Name: Gateway Lodge Site Support
 * Description: Two things the theme and page builder cannot do on their own:
 *              (1) makes the Xpro drawer submenus collapse on desktop, and
 *              (2) provides a booking slot shortcode that STAAH can be dropped
 *              into later from wp-admin, with no code editing.
 * Version:     1.1.0
 * Author:      Gateway Lodge Group
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GWL_BOOKING_OPTION', 'gwl_staah_settings' );

/* ==========================================================================
   1. Desktop submenu accordion for the Xpro header drawer
   --------------------------------------------------------------------------
   Xpro's own script binds the chevron only below its breakpoint:

       let p = ( 'tablet' === responsive_show ) ? 1025 : 768;
       chevron.on( 'click', function () { if ( $(window).width() < p ) { ... } } );

   and its resize handler force-shows every submenu above that width.

   Rather than raise Elementor's global tablet breakpoint, which would change
   responsive behaviour for every widget on the site, this supplies the missing
   half: the same toggle, gated to widths at or above the breakpoint, plus a
   correction for that resize handler. Below the breakpoint Xpro still owns the
   behaviour and this script stays out of the way.
   ========================================================================== */

function gwl_enqueue_menu_script() {
	$handle = 'gwl-desktop-submenu';
	wp_register_script( $handle, '', array( 'jquery' ), '1.1.0', true );
	wp_enqueue_script( $handle );

	$js = <<<'JS'
( function ( $ ) {
	'use strict';

	var BREAKPOINT = 1025; // matches Xpro's "Tablet and Mobile" setting
	var SCOPE      = '.xpro-theme-builder-header';
	var CHEVRON    = SCOPE + ' .xpro-elementor-horizontal-navbar-nav .dropdown > a > .xpro-dropdown-menu-toggle';
	var SUBMENU    = '.xpro-elementor-dropdown-menu';

	function isDesktop() {
		return $( window ).width() >= BREAKPOINT;
	}

	// Collapse every submenu that is not marked open. Xpro's resize handler
	// calls .show() on all of them above its breakpoint, so this runs after it.
	function collapse() {
		if ( ! isDesktop() ) {
			return;
		}
		$( SCOPE + ' ' + SUBMENU ).each( function () {
			var $sub = $( this );
			if ( ! $sub.prev( 'a' ).hasClass( 'active' ) ) {
				$sub.hide();
			}
		} );
	}

	function bind() {
		$( CHEVRON ).off( 'click.gwl' ).on( 'click.gwl', function ( event ) {
			if ( ! isDesktop() ) {
				return; // Xpro handles this range itself.
			}
			// Bound directly on the chevron so the click never reaches the
			// parent link, whose own handler would close the drawer.
			event.preventDefault();
			event.stopPropagation();

			var $link = $( this ).parent();
			$link.toggleClass( 'active' );
			$link.parent().toggleClass( 'active' );
			$link.next( SUBMENU ).stop( true, true ).slideToggle( 240 );
		} );
	}

	$( function () {
		bind();
		collapse();
	} );

	$( window ).on( 'load', function () {
		bind();
		collapse();
	} );

	// Re-assert after Xpro's own resize handler has run.
	var resizeTimer = null;
	$( window ).on( 'resize', function () {
		clearTimeout( resizeTimer );
		resizeTimer = setTimeout( collapse, 120 );
	} );

	// Elementor re-renders widgets in the editor preview.
	$( window ).on( 'elementor/frontend/init', function () {
		if ( window.elementorFrontend && elementorFrontend.hooks ) {
			elementorFrontend.hooks.addAction(
				'frontend/element_ready/xpro-horizontal-menu.default',
				function () {
					setTimeout( function () {
						bind();
						collapse();
					}, 50 );
				}
			);
		}
	} );
}( jQuery ) );
JS;

	wp_add_inline_script( $handle, $js );
}
add_action( 'wp_enqueue_scripts', 'gwl_enqueue_menu_script', 20 );

/* ==========================================================================
   2. Booking slot: [gateway_booking]
   --------------------------------------------------------------------------
   Drop this shortcode into any page with Elementor's native Shortcode widget.
   Until STAAH credentials exist it renders a branded booking bar that links to
   the enquiry page. When Ace Management Consult supplies the STAAH booking
   engine embed, paste it into Settings > Gateway Booking and every instance of
   the shortcode switches over. No page needs to be re-edited.
   ========================================================================== */

function gwl_booking_defaults() {
	return array(
		'mode'        => 'placeholder', // placeholder | embed | url
		'embed'       => '',
		'booking_url' => '',
		'heading'     => 'Check Availability Across Our Properties',
		'intro'       => 'Book on this site for our best available rate, with no third-party booking fees.',
		'cta'         => 'Check Availability',
	);
}

function gwl_booking_settings() {
	$saved = get_option( GWL_BOOKING_OPTION, array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	return wp_parse_args( $saved, gwl_booking_defaults() );
}

function gwl_booking_shortcode( $atts ) {
	$s    = gwl_booking_settings();
	$atts = shortcode_atts(
		array(
			'property' => '',   // pre-select a property, e.g. property="lakeside"
			'heading'  => $s['heading'],
			'intro'    => $s['intro'],
		),
		$atts,
		'gateway_booking'
	);

	// STAAH (or any provider) embed code, pasted by an administrator.
	if ( 'embed' === $s['mode'] && '' !== trim( (string) $s['embed'] ) ) {
		return '<div class="gwl-booking gwl-booking--embed" data-property="'
			. esc_attr( $atts['property'] ) . '">' . $s['embed'] . '</div>';
	}

	$action = ( 'url' === $s['mode'] && $s['booking_url'] )
		? $s['booking_url']
		: home_url( '/contact/' );

	$properties = array(
		''           => 'All Properties',
		'nova-ridge' => 'Gateway Nova Ridge, Accra',
		'lakeside'   => 'Gateway Lodge Lakeside, Accra',
		'tamale'     => 'Gateway Lodge Tamale, Northern Region',
	);

	ob_start();
	?>
	<div class="gwl-booking gwl-booking--placeholder">
		<span class="gwl-booking__eyebrow">Book Direct With Gateway Lodge</span>
		<span class="gwl-booking__slash" aria-hidden="true">/</span>
		<?php if ( $atts['heading'] ) : ?>
			<h2 class="gwl-booking__heading"><?php echo esc_html( $atts['heading'] ); ?></h2>
		<?php endif; ?>
		<?php if ( $atts['intro'] ) : ?>
			<p class="gwl-booking__intro"><?php echo esc_html( $atts['intro'] ); ?></p>
		<?php endif; ?>

		<form class="gwl-booking__form" action="<?php echo esc_url( $action ); ?>" method="get">
			<div class="gwl-booking__field">
				<label for="gwl-property">Property</label>
				<select id="gwl-property" name="property">
					<?php foreach ( $properties as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>"
							<?php selected( $atts['property'], $value ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="gwl-booking__field">
				<label for="gwl-checkin">Check-in</label>
				<input type="date" id="gwl-checkin" name="checkin">
			</div>
			<div class="gwl-booking__field">
				<label for="gwl-checkout">Check-out</label>
				<input type="date" id="gwl-checkout" name="checkout">
			</div>
			<div class="gwl-booking__field gwl-booking__field--narrow">
				<label for="gwl-guests">Guests</label>
				<select id="gwl-guests" name="guests">
					<option value="1">1 Guest</option>
					<option value="2" selected>2 Guests</option>
					<option value="3">3 Guests</option>
					<option value="4">4+ Guests</option>
				</select>
			</div>
			<div class="gwl-booking__field gwl-booking__field--submit">
				<button type="submit" class="gwl-booking__submit">
					<?php echo esc_html( $s['cta'] ); ?>
				</button>
			</div>
		</form>
		<p class="gwl-booking__note">Powered by the STAAH Booking Engine, search live availability and book direct for the best rate, no third-party fees.</p>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'gateway_booking', 'gwl_booking_shortcode' );

function gwl_booking_styles() {
	$css = '
	/* Matches the approved design: a white card that overlaps the hero. */
	.gwl-booking{background:#fff;border:1px solid #E6DDD3;padding:40px;
		margin-top:-40px;position:relative;z-index:5;text-align:left;}
	.gwl-booking__eyebrow{display:block;font-family:Jost,"Helvetica Neue",Arial,sans-serif;
		font-size:12px;font-weight:600;letter-spacing:.26em;text-transform:uppercase;
		color:#DBA845;line-height:1.4;}
	.gwl-booking__slash{display:block;font-family:"Cormorant Garamond",Georgia,serif;
		font-size:34px;font-weight:300;color:#DBA845;line-height:1;margin:6px 0;}
	.gwl-booking__heading{font-family:"Cormorant Garamond",Georgia,serif;font-weight:600;
		color:#5C1620;font-size:22px;line-height:1.2;margin:0 0 4px;text-align:left;}
	.gwl-booking__intro{font-family:Jost,"Helvetica Neue",Arial,sans-serif;color:#5A5049;
		font-size:16px;line-height:1.7;margin:0 0 20px;text-align:left;max-width:none;}
	.gwl-booking__note{font-family:Jost,"Helvetica Neue",Arial,sans-serif;font-size:13px;
		color:#8A8078;margin:16px 0 0;text-align:left;}
	.gwl-booking__form{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));
		gap:16px;align-items:end;margin-top:24px;}
	.gwl-booking__field{display:flex;flex-direction:column;gap:7px;min-width:0;}
	.gwl-booking__field--narrow{}
	.gwl-booking__field--submit{}
	.gwl-booking__field label{font-family:Jost,"Helvetica Neue",Arial,sans-serif;font-size:11px;
		font-weight:600;letter-spacing:.16em;text-transform:uppercase;color:#8A8078;}
	.gwl-booking__field input,.gwl-booking__field select{font-family:Jost,"Helvetica Neue",Arial,sans-serif;
		font-size:15px;color:#201A17;background:#fff;border:1px solid #E6DDD3;border-radius:0;
		padding:13px 14px;height:48px;width:100%;}
	.gwl-booking__field input:focus,.gwl-booking__field select:focus{outline:none;border-color:#DBA845;}
	.gwl-booking__submit{font-family:Jost,"Helvetica Neue",Arial,sans-serif;font-size:12px;font-weight:600;
		letter-spacing:.16em;text-transform:uppercase;color:#fff;background:#5C1620;border:1px solid #5C1620;
		border-radius:0;padding:0 32px;height:48px;cursor:pointer;transition:background .25s ease;}
	.gwl-booking__submit:hover{background:#4A1119;}
	@media(max-width:1024px){
		.gwl-booking__form{grid-template-columns:repeat(2,minmax(0,1fr));}
	}
	@media(max-width:780px){
		.gwl-booking{padding:30px 22px;margin-top:-24px;}
		.gwl-booking__form{grid-template-columns:minmax(0,1fr);}
		.gwl-booking__submit{width:100%;}
	}';
	wp_register_style( 'gwl-booking', false, array(), '1.1.0' );
	wp_enqueue_style( 'gwl-booking' );
	wp_add_inline_style( 'gwl-booking', $css );
}
add_action( 'wp_enqueue_scripts', 'gwl_booking_styles', 20 );

/* ---------- Settings screen: Settings > Gateway Booking ---------- */

function gwl_booking_menu() {
	add_options_page(
		'Gateway Booking',
		'Gateway Booking',
		'manage_options',
		'gateway-booking',
		'gwl_booking_settings_page'
	);
}
add_action( 'admin_menu', 'gwl_booking_menu' );

function gwl_booking_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['gwl_booking_nonce'] )
		&& wp_verify_nonce( sanitize_key( $_POST['gwl_booking_nonce'] ), 'gwl_booking_save' ) ) {

		// The embed field is deliberately stored unfiltered: a booking engine
		// snippet contains script or iframe markup. Only administrators can
		// reach this screen, and the nonce is checked above.
		$new = array(
			'mode'        => in_array( ( $_POST['mode'] ?? '' ), array( 'placeholder', 'embed', 'url' ), true )
				? sanitize_key( $_POST['mode'] ) : 'placeholder',
			'embed'       => isset( $_POST['embed'] ) ? trim( wp_unslash( $_POST['embed'] ) ) : '',
			'booking_url' => esc_url_raw( wp_unslash( $_POST['booking_url'] ?? '' ) ),
			'heading'     => sanitize_text_field( wp_unslash( $_POST['heading'] ?? '' ) ),
			'intro'       => sanitize_text_field( wp_unslash( $_POST['intro'] ?? '' ) ),
			'cta'         => sanitize_text_field( wp_unslash( $_POST['cta'] ?? '' ) ),
		);
		update_option( GWL_BOOKING_OPTION, $new );
		echo '<div class="notice notice-success is-dismissible"><p>Booking settings saved.</p></div>';
	}

	$s = gwl_booking_settings();
	?>
	<div class="wrap">
		<h1>Gateway Booking</h1>
		<p style="max-width:760px">
			Add the booking bar to any page with Elementor's <strong>Shortcode</strong> widget using
			<code>[gateway_booking]</code>. To pre-select a property use
			<code>[gateway_booking property="lakeside"]</code>.
			When the STAAH booking engine is ready, paste its embed code below and switch the mode
			to <em>Booking engine embed</em>. Every instance updates at once and no page needs re-editing.
		</p>

		<form method="post">
			<?php wp_nonce_field( 'gwl_booking_save', 'gwl_booking_nonce' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">Mode</th>
					<td>
						<label><input type="radio" name="mode" value="placeholder"
							<?php checked( $s['mode'], 'placeholder' ); ?>>
							Built-in booking bar (sends enquiries to the contact page)</label><br>
						<label><input type="radio" name="mode" value="url"
							<?php checked( $s['mode'], 'url' ); ?>>
							Built-in booking bar, submitting to an external booking URL</label><br>
						<label><input type="radio" name="mode" value="embed"
							<?php checked( $s['mode'], 'embed' ); ?>>
							Booking engine embed (STAAH)</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="embed">STAAH embed code</label></th>
					<td>
						<textarea name="embed" id="embed" rows="10" class="large-text code"
							placeholder="Paste the STAAH booking engine script or iframe here"><?php
							echo esc_textarea( $s['embed'] ); ?></textarea>
						<p class="description">
							Script and iframe markup is allowed here and is output exactly as pasted.
							Only administrators can edit this field.
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="booking_url">Booking URL</label></th>
					<td>
						<input type="url" name="booking_url" id="booking_url" class="regular-text"
							value="<?php echo esc_attr( $s['booking_url'] ); ?>"
							placeholder="https://book.gatewaylodgegroup.com/">
						<p class="description">Used when the mode above is set to an external booking URL.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="heading">Heading</label></th>
					<td><input type="text" name="heading" id="heading" class="regular-text"
						value="<?php echo esc_attr( $s['heading'] ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="intro">Intro text</label></th>
					<td><input type="text" name="intro" id="intro" class="large-text"
						value="<?php echo esc_attr( $s['intro'] ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="cta">Button label</label></th>
					<td><input type="text" name="cta" id="cta" class="regular-text"
						value="<?php echo esc_attr( $s['cta'] ); ?>"></td>
				</tr>
			</table>
			<?php submit_button( 'Save booking settings' ); ?>
		</form>
	</div>
	<?php
}
