<?php
/**
 * Plugin Name: Gateway Lodge booking widget
 * Description: Registers [gwl_booking], the STAAH quick-book embed, so the page
 *              builder can place it through Elementor's own shortcode widget
 *              rather than an HTML widget.
 *
 * This is a must-use plugin because a shortcode has to be registered on every
 * request, not only while a build runs. It is the only file of ours that loads
 * on a visitor request, and it registers one shortcode and nothing else.
 *
 * The ids are not written here. They come from gwl-property-content.php, which
 * tools/build-wp-property-content.py generates from PROPERTIES, so the widget
 * and the pages cannot end up pointing at different properties. That file is
 * data - a single `return array(...)` - so requiring it runs no logic.
 */

defined( 'ABSPATH' ) || exit;

/**
 * The STAAH embed for one property, or nothing at all.
 *
 * A property STAAH has not issued ids for renders nothing rather than an empty
 * panel: the phone, WhatsApp and enquiry routes on the page still work.
 */
function gwl_booking_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'slug' => '' ), $atts, 'gwl_booking' );
	$slug = sanitize_key( $atts['slug'] );
	if ( ! $slug ) { return ''; }

	$file = WP_CONTENT_DIR . '/novamira-sandbox/gwl-property-content.php';
	if ( ! is_readable( $file ) ) { return ''; }

	$all = require $file;
	if ( empty( $all[ $slug ]['booking_widget'] ) ) { return ''; }

	// The vendor's script finds its own tag by the id `propInfo` and reads the
	// query string off it, so the tag goes out as STAAH supplied it rather than
	// through wp_enqueue_script(), which would rename the id to a handle.
	// The id carries a trailing "=" that STAAH matches literally, so it is passed
	// through exactly as issued and only escaped for the attribute it sits in.
	$id = $all[ $slug ]['booking_widget'];

	return sprintf(
		'<div id="quickbook-widget-%1$s-%1$s" class="Configure-quickBook-Widget"></div>'
		. '<script src="https://www.swiftbook.io/cwplugin/displaywidget/preview/booking-service.min.js'
		. '?propertyId=%1$s&scriptId=%1$s" id="propInfo"></script>',
		esc_attr( $id )
	);
}
add_shortcode( 'gwl_booking', 'gwl_booking_shortcode' );
