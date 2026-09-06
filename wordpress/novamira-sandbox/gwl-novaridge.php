<?php
/**
 * Gateway Nova Ridge : builds the novaridge.gatewaylodgegroup.com landing page
 * as an Elementor document, from the same content as /novaridge/index.html.
 *
 * Lives in wp-content/novamira-sandbox/ on the Nova Ridge install. Defines
 * functions only; nothing runs until gwl_nr_build() is called.
 */

require_once __DIR__ . '/gwl-pages.php';

/* -------------------------------------------------------------------------
 * Primitives this page needs on top of gwl-builder.php
 * ---------------------------------------------------------------------- */

/** Full-height hero with a looping video background and a still fallback. */
function gwl_nr_video_hero( $video_url, $poster_key, $kids ) {
	$poster = gwl_media( $poster_key );
	return gwl_container(
		array( gwl_container( $kids, array( 'width' => 'boxed', 'gap' => 14, 'align' => 'flex-start' ) ) ),
		array(
			'width'   => 'full',
			'justify' => 'flex-end',
			'minh'    => 100,
			'pad'     => gwl_pad( 140, 0, 84, 0 ),
			'cls'     => 'gwl-hero',
			'extra'   => array(
				'min_height_mobile'          => array( 'unit' => 'vh', 'size' => 88 ),
				'padding_mobile'             => gwl_pad( 120, 0, 56, 0 ),
				'background_background'      => 'video',
				'background_video_link'      => $video_url,
				'background_play_on_mobile'  => 'yes',
				'background_video_fallback'  => array( 'url' => $poster['url'], 'id' => $poster['id'], 'size' => '' ),
				// Two stacked scrims: one from the left so the copy holds, one from
				// the foot so the stat bar does not butt against a bright frame.
				'background_overlay_background' => 'gradient',
				'background_overlay_color'      => 'rgba(28,11,13,0.82)',
				'background_overlay_color_b'    => 'rgba(28,11,13,0.18)',
				'background_overlay_gradient_type' => 'linear',
				'background_overlay_gradient_angle' => array( 'unit' => 'deg', 'size' => 90 ),
				'background_overlay_color_stop'   => array( 'unit' => '%', 'size' => 0 ),
				'background_overlay_color_b_stop' => array( 'unit' => '%', 'size' => 78 ),
			),
		)
	);
}

/** The maroon figures band under the hero. */
function gwl_nr_stat_bar( $stats ) {
	$cells = array();
	foreach ( $stats as $s ) {
		$cells[] = gwl_container(
			array(
				gwl_heading( $s[0], array( 'tag' => 'div', 'align' => 'center', 'size' => 40, 'size_m' => 30, 'color' => '#ECC989', 'lh' => 1.1 ) ),
				gwl_heading( $s[1], array( 'tag' => 'div', 'align' => 'center', 'family' => 'Jost', 'weight' => '600', 'size' => 11, 'ls' => 0.2, 'tt' => 'uppercase', 'color' => 'rgba(255,255,255,0.82)', 'lh' => 1.5 ) ),
			),
			array( 'width' => 'full', 'gap' => 2, 'basis' => 25, 'grow' => 1, 'pad' => gwl_pad( 34, 16, 34, 16 ) )
		);
	}
	return gwl_container(
		array( gwl_container( $cells, array( 'width' => 'boxed', 'dir' => 'row', 'gap' => 0, 'align' => 'stretch', 'extra' => array( 'flex_wrap' => 'wrap' ) ) ) ),
		array( 'width' => 'full', 'bg' => '#5C1620', 'gap' => 0, 'pad' => gwl_pad( 0, 0, 0, 0 ) )
	);
}

/** One gold-ruled positioning statement. */
function gwl_nr_pillar( $title, $body ) {
	return gwl_container(
		array(
			gwl_heading( $title, array( 'tag' => 'h3', 'align' => 'left', 'size' => 22, 'lh' => 1.25 ) ),
			gwl_text( '<p>' . $body . '</p>', array( 'align' => 'left', 'size' => 16 ) ),
		),
		array(
			'width' => 'full',
			'gap'   => 2,
			'inner' => true,
			'pad'   => gwl_pad( 0, 0, 0, 20 ),
			'extra' => array(
				'border_border' => 'solid',
				'border_width'  => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '2', 'isLinked' => '' ),
				'border_color'  => '#DBA845',
			),
		)
	);
}

/** Accommodation card: image, title, body. No button — the whole page books in one place. */
function gwl_nr_card( $imgkey, $title, $body ) {
	return gwl_container(
		array(
			gwl_image( $imgkey ),
			gwl_container(
				array(
					gwl_heading( $title, array( 'tag' => 'h3', 'align' => 'left', 'size' => 24 ) ),
					gwl_text( '<p>' . $body . '</p>', array( 'align' => 'left', 'size' => 15 ) ),
				),
				array( 'width' => 'full', 'gap' => 6, 'inner' => true, 'pad' => gwl_pad( 22, 22, 26, 22 ) )
			),
		),
		array( 'width' => 'full', 'gap' => 0, 'bg' => '#FFFFFF', 'border_color' => '#E6DDD3', 'basis' => 25, 'grow' => 1 )
	);
}

/* -------------------------------------------------------------------------
 * Content
 * ---------------------------------------------------------------------- */

function gwl_nr_content() {
	return array(
		'title'    => 'Gateway Nova Ridge',
		'locality' => 'Ridge, Accra',
		'address'  => '4 Drake Avenue area, Ridge, Accra, Ghana',
		'units'    => '1 Unit',
		'phone'    => '+233 24 000 0000',
		'whatsapp' => 'https://wa.me/233240000000',
		'email'    => 'reservations@gatewaylodgegroup.com',
		'group'    => 'https://www.gatewaylodgegroup.com',

		'tagline'  => 'A private high-rise residence in Accra&rsquo;s most established address, kept to the Gateway Lodge standard and yours alone for the length of your stay.',

		'stats' => array(
			array( '1', 'Private unit' ),
			array( '2', 'Bedrooms' ),
			array( '24/7', 'Guest support' ),
			array( '15 min', 'To the airport' ),
		),

		'about_head' => 'One apartment. No lobby, no queue, no neighbours in the corridor.',
		'about_body' => 'Nova Ridge is a single serviced apartment on an upper floor of a residential tower in Ridge &mdash; the quiet, tree-lined district that sits between central Accra&rsquo;s business addresses and the embassies. You are not checking into a hotel floor. You arrive to an apartment that has been prepared for you and stays that way.</p><p>Inside: an open living room that runs into a full kitchen, a dining table, two bedrooms dressed in hotel linen, two bathrooms, and a balcony that looks out over the green of Ridge towards the city. It suits the guest who is in Accra for a fortnight as easily as the one here for two nights.',
		'pillars' => array(
			array( 'Yours alone', 'A single unit, so the apartment is never shared, split or reassigned mid-stay.' ),
			array( 'Built for longer stays', 'A real kitchen, laundry and a desk &mdash; the things that matter after night three.' ),
			array( 'A settled address', 'Ridge puts the ministries, the CBD and Kotoka within an easy drive.' ),
		),

		'rooms' => array(
			array( 'bedroom-king', 'Principal Bedroom', 'A king bed, blackout curtains, fitted wardrobes and an en-suite bathroom.' ),
			array( 'bedroom-second', 'Second Bedroom', 'A second double room with its own wardrobe and mirror, for family or a colleague.' ),
			array( 'lounge-tv-wall', 'Living Room', 'Deep seating for six, a smart television and light on two sides through the day.' ),
			array( 'kitchen-wide', 'Kitchen &amp; Dining', 'A full fitted kitchen with oven, hob, microwave and a dining table for four.' ),
		),

		'facilities_head' => 'Everything the apartment needs, already in it',
		'facilities_body' => 'Nothing here is an upgrade or an extra line on the folio. The apartment arrives complete, and the team keeps it that way for as long as you stay.',
		'facilities' => array(
			array( 'fas fa-wifi', 'High-Speed Wi-Fi', 'Fibre throughout the apartment, steady enough for calls and uploads.' ),
			array( 'fas fa-utensils', 'Full Kitchen', 'Oven, hob, microwave, fridge-freezer, kettle and a stocked utensil drawer.' ),
			array( 'fas fa-snowflake', 'Air Conditioning', 'Individually controlled in every bedroom and the living room.' ),
			array( 'fas fa-bolt', 'Backup Power', 'The building runs on standby power, so the apartment stays on.' ),
			array( 'fas fa-tshirt', 'Laundry', 'In-apartment washing, with a pressing and laundry service on request.' ),
			array( 'fas fa-laptop', 'Desk &amp; Workspace', 'A proper surface to work from, not a chair pulled up to the dining table.' ),
			array( 'fas fa-tree', 'Private Balcony', 'Table and chairs, and a long view over the Ridge treeline.' ),
			array( 'fas fa-car', 'Secure Parking', 'Gated, monitored parking within the building for one vehicle.' ),
			array( 'fas fa-shield-alt', '24-Hour Security', 'Manned entry and CCTV across the building&rsquo;s common areas.' ),
			array( 'fas fa-concierge-bell', 'Guest Support', 'One number, answered at any hour, for anything the stay needs.' ),
			array( 'fas fa-elevator', 'Lift Access', 'Serviced lifts to the apartment floor.' ),
			array( 'fas fa-broom', 'Housekeeping', 'Scheduled servicing and fresh linen, arranged around you.' ),
		),

		'gallery' => array(
			'hero-living', 'lounge-tv-wall', 'kitchen-dining', 'bedroom-suite', 'balcony-skyline',
			'lounge-daylight', 'bedroom-window', 'dining-nook', 'bathroom', 'hallway',
			'lounge-sofas', 'balcony',
		),

		'location_points' => array(
			'Walking distance to Ridge&rsquo;s embassies and government offices',
			'About 15 minutes by car to Kotoka International Airport',
			'Ten minutes to Osu, Airport Residential and the CBD',
			'Ridge Hospital and Accra&rsquo;s main clinics close by',
		),
	);
}

/* -------------------------------------------------------------------------
 * Sections
 * ---------------------------------------------------------------------- */

function gwl_nr_sections( $c, $form_id ) {
	$media = gwl_media( 'tour' );
	$s = array();

	/* Hero ---------------------------------------------------------------- */
	$s[] = gwl_nr_video_hero(
		$media['url'],
		'hero-living',
		array(
			gwl_eyebrow( 'Gateway Lodge Group', 'left' ),
			gwl_heading( $c['title'], array( 'tag' => 'h1', 'align' => 'left', 'size' => 74, 'size_m' => 40, 'color' => '#FFFFFF', 'lh' => 1.04 ) ),
			gwl_heading(
				$c['locality'] . '&nbsp;&nbsp;&middot;&nbsp;&nbsp;' . $c['units'],
				array( 'tag' => 'div', 'align' => 'left', 'family' => 'Jost', 'weight' => '600', 'size' => 12, 'ls' => 0.2, 'tt' => 'uppercase', 'color' => '#ECC989', 'lh' => 1.5 )
			),
			gwl_text( '<p>' . $c['tagline'] . '</p>', array( 'align' => 'left', 'color' => 'rgba(255,255,255,0.9)', 'size' => 18, 'maxw' => 560 ) ),
			gwl_container(
				array(
					gwl_button( 'Book Now', '#book', 'gold', 'left' ),
					gwl_button( 'Explore the property', '#about', 'outline_light', 'left' ),
				),
				array( 'width' => 'full', 'dir' => 'row', 'gap' => 14, 'inner' => true, 'extra' => array( 'flex_wrap' => 'wrap' ) )
			),
		)
	);

	/* Figures ------------------------------------------------------------- */
	$s[] = gwl_nr_stat_bar( $c['stats'] );

	/* About --------------------------------------------------------------- */
	$about_text = array(
		gwl_eyebrow( 'The Residence', 'left' ),
		gwl_slash( 'left' ),
		gwl_heading( $c['about_head'], array( 'tag' => 'h2', 'align' => 'left', 'size' => 44 ) ),
		gwl_text( '<p>' . $c['about_body'] . '</p>', array( 'align' => 'left', 'size' => 17 ) ),
	);
	foreach ( $c['pillars'] as $p ) { $about_text[] = gwl_nr_pillar( $p[0], $p[1] ); }
	$s[] = gwl_container(
		array(
			gwl_container( array( gwl_image( 'lounge-open-plan' ) ), array( 'width' => 'full', 'gap' => 0, 'basis' => 50, 'grow' => 1 ) ),
			gwl_container( $about_text, array( 'width' => 'full', 'gap' => 14, 'basis' => 50, 'grow' => 1, 'justify' => 'center' ) ),
		),
		array( 'width' => 'boxed', 'dir' => 'row', 'gap' => 52, 'align' => 'center', 'pad' => gwl_xl(), 'cls' => 'gwl-anchor-about', 'extra' => array( 'padding_mobile' => gwl_xlm(), 'flex_wrap' => 'wrap', '_element_id' => 'about' ) )
	);

	/* Accommodation ------------------------------------------------------- */
	$cards = array();
	foreach ( $c['rooms'] as $r ) { $cards[] = gwl_nr_card( $r[0], $r[1], $r[2] ); }
	$s[] = gwl_container(
		array(
			gwl_container(
				array(
					gwl_section_head( 'Accommodation', 'Where you will stay', 'Rates, availability and the full description of each category are confirmed when you book.' ),
					gwl_container( $cards, array( 'width' => 'full', 'dir' => 'row', 'gap' => 22, 'align' => 'stretch', 'extra' => array( 'flex_wrap' => 'wrap' ) ) ),
				),
				array( 'width' => 'boxed', 'gap' => 0 )
			),
		),
		array( 'width' => 'full', 'bg' => '#F7F3EE', 'pad' => gwl_xl(), 'extra' => array( 'padding_mobile' => gwl_xlm() ) )
	);

	/* Facilities ---------------------------------------------------------- */
	$s[] = gwl_container(
		array(
			gwl_container(
				array(
					gwl_eyebrow( 'Facilities &amp; Amenities', 'left' ),
					gwl_slash( 'left' ),
					gwl_heading( $c['facilities_head'], array( 'tag' => 'h2', 'align' => 'left', 'size' => 44 ) ),
					gwl_text( '<p>' . $c['facilities_body'] . '</p>', array( 'align' => 'left', 'size' => 17 ) ),
				),
				array( 'width' => 'full', 'gap' => 12, 'basis' => 50, 'grow' => 1, 'justify' => 'center' )
			),
			gwl_container( array( gwl_image( 'kitchen' ) ), array( 'width' => 'full', 'gap' => 0, 'basis' => 50, 'grow' => 1 ) ),
		),
		array( 'width' => 'boxed', 'dir' => 'row', 'gap' => 52, 'align' => 'center', 'pad' => gwl_pad( 104, 0, 44, 0 ), 'extra' => array( 'padding_mobile' => gwl_pad( 60, 0, 30, 0 ), 'flex_wrap' => 'wrap', '_element_id' => 'facilities' ) )
	);

	$features = array();
	foreach ( $c['facilities'] as $f ) { $features[] = gwl_feature( $f[0], $f[1], $f[2] ); }
	$s[] = gwl_container(
		array( gwl_container( $features, array( 'width' => 'full', 'dir' => 'row', 'gap' => 22, 'align' => 'stretch', 'extra' => array( 'flex_wrap' => 'wrap' ) ) ) ),
		array( 'width' => 'boxed', 'gap' => 0, 'pad' => gwl_pad( 0, 0, 104, 0 ), 'extra' => array( 'padding_mobile' => gwl_pad( 0, 0, 60, 0 ) ) )
	);

	/* Gallery ------------------------------------------------------------- */
	$gallery = array();
	foreach ( $c['gallery'] as $key ) {
		$m = gwl_media( $key );
		if ( $m['id'] ) { $gallery[] = array( 'id' => $m['id'], 'url' => $m['url'] ); }
	}
	$s[] = gwl_container(
		array(
			gwl_container(
				array(
					gwl_section_head( 'Gallery', 'A closer look' ),
					gwl_widget( 'image-gallery', array(
						'wp_gallery'     => $gallery,
						'gallery_columns' => 3,
						'gallery_link'   => 'file',
						'open_lightbox'  => 'yes',
						'thumbnail_size' => 'large',
						'gallery_rand'   => '',
						'image_spacing_custom' => array( 'unit' => 'px', 'size' => 12 ),
					) ),
				),
				array( 'width' => 'boxed', 'gap' => 0 )
			),
		),
		array( 'width' => 'full', 'bg' => '#F7F3EE', 'pad' => gwl_xl(), 'extra' => array( 'padding_mobile' => gwl_xlm(), '_element_id' => 'gallery' ) )
	);

	/* Location & contact -------------------------------------------------- */
	$details = '<p style="margin:0 0 18px"><span style="display:block;font-size:12px;letter-spacing:.18em;text-transform:uppercase;color:#8A8078">Address</span>'
		. $c['address'] . '</p>'
		. '<p style="margin:0 0 18px"><span style="display:block;font-size:12px;letter-spacing:.18em;text-transform:uppercase;color:#8A8078">Reservations</span>'
		. '<a href="tel:' . str_replace( ' ', '', $c['phone'] ) . '">' . $c['phone'] . '</a> &middot; <a href="' . $c['whatsapp'] . '">WhatsApp</a></p>'
		. '<p style="margin:0"><span style="display:block;font-size:12px;letter-spacing:.18em;text-transform:uppercase;color:#8A8078">Email</span>'
		. '<a href="mailto:' . $c['email'] . '">' . $c['email'] . '</a></p>';

	$left = array(
		gwl_widget( 'google_maps', array(
			'address' => '4 Drake Avenue, Ridge, Accra, Ghana',
			'zoom'    => array( 'size' => 15 ),
			'height'  => array( 'unit' => 'px', 'size' => 380 ),
		) ),
		gwl_text( $details, array( 'align' => 'left', 'size' => 17 ) ),
		gwl_icon_list( $c['location_points'] ),
	);

	$right = array(
		gwl_heading( 'Send an enquiry', array( 'tag' => 'h3', 'align' => 'left', 'size' => 28 ) ),
		gwl_text( '<p>Tell us the dates and how many of you there are, and reservations will come back with availability and a rate.</p>', array( 'align' => 'left', 'size' => 16 ) ),
	);
	if ( $form_id ) {
		$right[] = gwl_widget( 'shortcode', array( 'shortcode' => '[wpforms id="' . $form_id . '"]' ) );
	}

	$s[] = gwl_container(
		array(
			gwl_section_head( 'Location &amp; Contact', 'Find us, or just ask', '', 'left' ),
			gwl_container(
				array(
					gwl_container( $left, array( 'width' => 'full', 'gap' => 22, 'basis' => 52, 'grow' => 1 ) ),
					gwl_container( $right, array( 'width' => 'full', 'gap' => 12, 'basis' => 48, 'grow' => 1 ) ),
				),
				array( 'width' => 'full', 'dir' => 'row', 'gap' => 52, 'align' => 'flex-start', 'extra' => array( 'flex_wrap' => 'wrap' ) )
			),
		),
		array( 'width' => 'boxed', 'gap' => 0, 'pad' => gwl_xl(), 'extra' => array( 'padding_mobile' => gwl_xlm(), '_element_id' => 'contact' ) )
	);

	/* Book ---------------------------------------------------------------- */
	$s[] = gwl_container(
		array(
			gwl_container(
				array(
					gwl_eyebrow( 'Ready when you are' ),
					gwl_slash(),
					gwl_heading( 'Book Gateway Nova Ridge', array( 'tag' => 'h2', 'size' => 44, 'color' => '#FFFFFF' ) ),
					gwl_text(
						'<p>Book direct for the best available rate. Reservations answer by phone and WhatsApp every day, or send the enquiry form and we will come back to you.</p>',
						array( 'size' => 17, 'color' => 'rgba(255,255,255,0.8)', 'maxw' => 640 )
					),
					// STAAH booking engine: paste the property's booking widget embed
					// into this container to take reservations on the page itself.
					gwl_container(
						array(
							gwl_button( 'Book on WhatsApp', $c['whatsapp'], 'gold' ),
							gwl_button( 'Call reservations', 'tel:' . str_replace( ' ', '', $c['phone'] ), 'outline_light' ),
							gwl_button( 'Send an enquiry', '#contact', 'outline_light' ),
						),
						array( 'width' => 'full', 'dir' => 'row', 'gap' => 14, 'inner' => true, 'justify' => 'center', 'extra' => array( 'flex_wrap' => 'wrap' ) )
					),
				),
				array( 'width' => 'boxed', 'gap' => 10, 'align' => 'center' )
			),
		),
		array( 'width' => 'full', 'bg' => '#4A1119', 'pad' => gwl_xl(), 'extra' => array( 'padding_mobile' => gwl_xlm(), '_element_id' => 'book' ) )
	);

	return $s;
}

/* -------------------------------------------------------------------------
 * Build
 * ---------------------------------------------------------------------- */

/** Creates the Nova Ridge enquiry form once and returns its id. */
function gwl_nr_form() {
	$existing = get_option( 'gwl_nr_form_id' );
	if ( $existing && get_post( $existing ) ) { return (int) $existing; }
	if ( ! post_type_exists( 'wpforms' ) ) { return 0; }

	$fields = array(
		'1' => array( 'id' => '1', 'type' => 'name', 'label' => 'Full Name', 'format' => 'simple', 'required' => '1', 'size' => 'large' ),
		'2' => array( 'id' => '2', 'type' => 'email', 'label' => 'Email', 'required' => '1', 'size' => 'large' ),
		'3' => array( 'id' => '3', 'type' => 'text', 'label' => 'Phone', 'size' => 'large' ),
		'4' => array( 'id' => '4', 'type' => 'date-time', 'label' => 'Check-in', 'format' => 'date', 'size' => 'large' ),
		'5' => array( 'id' => '5', 'type' => 'date-time', 'label' => 'Check-out', 'format' => 'date', 'size' => 'large' ),
		'6' => array( 'id' => '6', 'type' => 'number', 'label' => 'Guests', 'size' => 'large' ),
		'7' => array(
			'id' => '7', 'type' => 'select', 'label' => 'Enquiry Type', 'required' => '1', 'size' => 'large',
			'choices' => array(
				'1' => array( 'label' => 'Reservation' ),
				'2' => array( 'label' => 'Long stay' ),
				'3' => array( 'label' => 'Corporate booking' ),
				'4' => array( 'label' => 'Event or group' ),
				'5' => array( 'label' => 'General enquiry' ),
			),
		),
		'8' => array( 'id' => '8', 'type' => 'textarea', 'label' => 'Message', 'size' => 'medium' ),
	);

	$post_id = wp_insert_post( array(
		'post_type'   => 'wpforms',
		'post_title'  => 'Nova Ridge Enquiry',
		'post_status' => 'publish',
		'post_author' => 1,
	) );
	if ( ! $post_id || is_wp_error( $post_id ) ) { return 0; }

	$form = array(
		'id'       => (string) $post_id,
		'field_id' => 9,
		'fields'   => $fields,
		'settings' => array(
			'form_title'          => 'Nova Ridge Enquiry',
			'submit_text'         => 'Send Enquiry',
			'submit_text_processing' => 'Sending...',
			'confirmations'       => array(
				1 => array(
					'type'    => 'message',
					'message' => '<p>Thank you. Reservations will come back to you shortly. For anything urgent, message us on WhatsApp.</p>',
				),
			),
			'notifications'       => array(
				1 => array(
					'notification_name' => 'Nova Ridge enquiry',
					'email'             => 'reservations@gatewaylodgegroup.com',
					'subject'           => 'Nova Ridge enquiry from {field_id="1"}',
					'sender_name'       => 'Gateway Nova Ridge',
					'sender_address'    => '{admin_email}',
					'replyto'           => '{field_id="2"}',
					'message'           => '{all_fields}',
				),
			),
		),
	);

	wp_update_post( array( 'ID' => $post_id, 'post_content' => wp_slash( wp_json_encode( $form ) ) ) );
	update_option( 'gwl_nr_form_id', $post_id );
	return (int) $post_id;
}

/** Builds the page, publishes it and sets it as the site front page. */
function gwl_nr_build() {
	$c       = gwl_nr_content();
	$form_id = gwl_nr_form();

	$page = get_page_by_path( 'nova-ridge', OBJECT, 'page' );
	if ( $page ) {
		$page_id = $page->ID;
		wp_update_post( array( 'ID' => $page_id, 'post_title' => $c['title'], 'post_status' => 'publish' ) );
	} else {
		$page_id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_name'    => 'nova-ridge',
			'post_title'   => $c['title'],
			'post_status'  => 'publish',
			'post_author'  => 1,
			'post_content' => '',
		) );
	}
	if ( ! $page_id || is_wp_error( $page_id ) ) { return 'page failed'; }

	gwl_save_elementor( $page_id, gwl_nr_sections( $c, $form_id ) );

	// Full-bleed: Astra's 1240px container would otherwise gutter the hero.
	update_post_meta( $page_id, '_wp_page_template', 'elementor_canvas' );
	update_post_meta( $page_id, 'site-content-layout', 'page-builder' );
	update_post_meta( $page_id, 'ast-site-content-layout', 'full-width-container' );
	update_post_meta( $page_id, 'site-sidebar-layout', 'no-sidebar' );
	update_post_meta( $page_id, 'ast-title-bar-display', 'disabled' );
	update_post_meta( $page_id, 'ast-featured-img', 'disabled' );

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $page_id );
	update_option( 'blogname', 'Gateway Nova Ridge' );
	update_option( 'blogdescription', 'Serviced apartment in Ridge, Accra — Gateway Lodge Group' );

	if ( class_exists( '\\Elementor\\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	return array( 'page_id' => $page_id, 'form_id' => $form_id, 'url' => get_permalink( $page_id ) );
}
