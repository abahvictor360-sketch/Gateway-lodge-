<?php
/**
 * Gateway Nova Ridge : the multi-page Elementor build for
 * novaridge.gatewaylodgegroup.com.
 *
 * Four pages (Home, About, Facilities, Contact), a nav menu, an XPRO Theme
 * Builder header and footer saved to the XPRO library, and the WPForms enquiry
 * form on the Contact page.
 *
 * Rules this file holds to:
 *   - Native Elementor containers only. No sections, no inner sections, no
 *     atomic elements (e-div-block / e-flexbox / e-grid).
 *   - No HTML widgets. WPForms goes in through the core shortcode widget.
 *   - Header and footer use XPRO widgets (site logo, horizontal menu, social
 *     icon) rather than Elementor Pro's.
 *
 * Defines functions only; nothing runs until gwl_nrp_build() is called.
 */

require_once __DIR__ . '/gwl-pages.php';

/* =========================================================================
 * Shared primitives
 * ====================================================================== */

/** Full-height opening hero with a looping video background. */
function gwl_nrp_video_hero( $kids ) {
	$video  = gwl_media( 'tour' );
	$poster = gwl_media( 'hero-living' );
	return gwl_container(
		array( gwl_container( $kids, array( 'width' => 'boxed', 'gap' => 14, 'align' => 'flex-start' ) ) ),
		array(
			'width'   => 'full',
			'justify' => 'flex-end',
			'minh'    => 100,
			'pad'     => gwl_pad( 150, 0, 90, 0 ),
			'extra'   => array(
				'min_height_mobile'             => array( 'unit' => 'vh', 'size' => 86 ),
				'padding_mobile'                => gwl_pad( 120, 0, 60, 0 ),
				'background_background'         => 'video',
				'background_video_link'         => $video['url'],
				'background_play_on_mobile'     => 'yes',
				'background_video_fallback'     => array( 'url' => $poster['url'], 'id' => $poster['id'], 'size' => '' ),
				'background_overlay_background' => 'gradient',
				'background_overlay_color'      => 'rgba(28,11,13,0.82)',
				'background_overlay_color_b'    => 'rgba(28,11,13,0.20)',
				'background_overlay_gradient_type'  => 'linear',
				'background_overlay_gradient_angle' => array( 'unit' => 'deg', 'size' => 90 ),
				'background_overlay_color_stop'     => array( 'unit' => '%', 'size' => 0 ),
				'background_overlay_color_b_stop'   => array( 'unit' => '%', 'size' => 78 ),
			),
		)
	);
}

/** Inner-page banner: a photograph with the page title over it. */
function gwl_nrp_banner( $imgkey, $eyebrow, $title, $sub = '' ) {
	$kids = array( gwl_eyebrow( $eyebrow, 'left' ) );
	$kids[] = gwl_heading( $title, array( 'tag' => 'h1', 'align' => 'left', 'size' => 56, 'size_m' => 34, 'color' => '#FFFFFF', 'lh' => 1.08 ) );
	if ( $sub ) {
		$kids[] = gwl_text( '<p>' . $sub . '</p>', array( 'align' => 'left', 'color' => 'rgba(255,255,255,0.9)', 'size' => 18, 'maxw' => 620 ) );
	}
	return gwl_container(
		array( gwl_container( $kids, array( 'width' => 'boxed', 'gap' => 12, 'align' => 'flex-start' ) ) ),
		array(
			'width'   => 'full',
			'bgimg'   => $imgkey,
			'justify' => 'center',
			'minh'    => 58,
			'pad'     => gwl_pad( 150, 0, 90, 0 ),
			'extra'   => array(
				'min_height_mobile'             => array( 'unit' => 'vh', 'size' => 52 ),
				'padding_mobile'                => gwl_pad( 118, 0, 60, 0 ),
				'background_overlay_background' => 'gradient',
				'background_overlay_color'      => 'rgba(28,11,13,0.80)',
				'background_overlay_color_b'    => 'rgba(28,11,13,0.28)',
				'background_overlay_gradient_type'  => 'linear',
				'background_overlay_gradient_angle' => array( 'unit' => 'deg', 'size' => 90 ),
				'background_overlay_color_stop'     => array( 'unit' => '%', 'size' => 0 ),
				'background_overlay_color_b_stop'   => array( 'unit' => '%', 'size' => 82 ),
			),
		)
	);
}

/** The maroon figures band. */
function gwl_nrp_stats( $stats ) {
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

/** A gold-ruled positioning statement. */
function gwl_nrp_pillar( $title, $body ) {
	return gwl_container(
		array(
			gwl_heading( $title, array( 'tag' => 'h3', 'align' => 'left', 'size' => 22, 'lh' => 1.25 ) ),
			gwl_text( '<p>' . $body . '</p>', array( 'align' => 'left', 'size' => 16 ) ),
		),
		array(
			'width' => 'full',
			'gap'   => 2,
			'pad'   => gwl_pad( 0, 0, 0, 20 ),
			'extra' => array(
				'border_border' => 'solid',
				'border_width'  => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '2', 'isLinked' => '' ),
				'border_color'  => '#DBA845',
			),
		)
	);
}

/** Accommodation card: image over copy, no button. */
function gwl_nrp_card( $imgkey, $title, $body, $basis = 25 ) {
	return gwl_container(
		array(
			gwl_image( $imgkey ),
			gwl_container(
				array(
					gwl_heading( $title, array( 'tag' => 'h3', 'align' => 'left', 'size' => 24 ) ),
					gwl_text( '<p>' . $body . '</p>', array( 'align' => 'left', 'size' => 15 ) ),
				),
				array( 'width' => 'full', 'gap' => 6, 'pad' => gwl_pad( 22, 22, 26, 22 ) )
			),
		),
		array( 'width' => 'full', 'gap' => 0, 'bg' => '#FFFFFF', 'border_color' => '#E6DDD3', 'basis' => $basis, 'grow' => 1 )
	);
}

/** XPRO Simple Gallery, with its lightbox on. */
function gwl_nrp_gallery( $keys, $per_row = '3', $height = 340 ) {
	$items = array();
	foreach ( $keys as $k ) {
		$m = gwl_media( $k );
		if ( $m['id'] ) { $items[] = array( 'id' => $m['id'], 'url' => $m['url'] ); }
	}
	return gwl_widget( 'xpro-simple-gallery', array(
		'gallery'             => $items,
		'thumbnail_size'      => 'large',
		'item_per_row'        => $per_row,
		'item_per_row_tablet' => '2',
		'item_per_row_mobile' => '1',
		'item_height'         => array( 'unit' => 'px', 'size' => $height ),
		'item_height_mobile'  => array( 'unit' => 'px', 'size' => 260 ),
		'margin'              => array( 'unit' => 'px', 'size' => 12 ),
		'popup'               => 'yes',
		'caption'             => '',
		'description'         => '',
		'hover_effect'        => 'zoom-in',
		'hover_color_background' => 'classic',
		'hover_color_color'   => 'rgba(74,17,25,0.42)',
	) );
}

/** Section wrapper with the standard rhythm. */
function gwl_nrp_section( $kids, $bg = null, $id = '' ) {
	$o = array( 'width' => 'boxed', 'gap' => 0, 'pad' => gwl_xl(), 'extra' => array( 'padding_mobile' => gwl_xlm() ) );
	if ( $id ) { $o['extra']['_element_id'] = $id; }
	if ( $bg ) {
		return gwl_container(
			array( gwl_container( $kids, array( 'width' => 'boxed', 'gap' => 0 ) ) ),
			array( 'width' => 'full', 'bg' => $bg, 'pad' => gwl_xl(), 'extra' => array( 'padding_mobile' => gwl_xlm() ) + ( $id ? array( '_element_id' => $id ) : array() ) )
		);
	}
	return gwl_container( $kids, $o );
}

/** Image + copy split. */
function gwl_nrp_split( $imgkey, $eyebrow, $title, $paras, $extra_kids = array(), $side = 'left', $bg = null ) {
	$text = array();
	if ( $eyebrow ) { $text[] = gwl_eyebrow( $eyebrow, 'left' ); }
	$text[] = gwl_slash( 'left' );
	$text[] = gwl_heading( $title, array( 'tag' => 'h2', 'align' => 'left', 'size' => 44 ) );
	foreach ( (array) $paras as $p ) { $text[] = gwl_text( '<p>' . $p . '</p>', array( 'align' => 'left', 'size' => 17 ) ); }
	foreach ( $extra_kids as $k ) { $text[] = $k; }

	$media = gwl_container( array( gwl_image( $imgkey ) ), array( 'width' => 'full', 'gap' => 0, 'basis' => 50, 'grow' => 1 ) );
	$body  = gwl_container( $text, array( 'width' => 'full', 'gap' => 14, 'basis' => 50, 'grow' => 1, 'justify' => 'center' ) );
	$kids  = ( 'left' === $side ) ? array( $media, $body ) : array( $body, $media );

	$row = array( 'dir' => 'row', 'gap' => 52, 'align' => 'center', 'extra' => array( 'flex_wrap' => 'wrap' ) );
	if ( $bg ) {
		return gwl_container(
			array( gwl_container( $kids, array_merge( array( 'width' => 'boxed' ), $row ) ) ),
			array( 'width' => 'full', 'bg' => $bg, 'pad' => gwl_xl(), 'extra' => array( 'padding_mobile' => gwl_xlm() ) )
		);
	}
	return gwl_container( $kids, array_merge( array( 'width' => 'boxed', 'pad' => gwl_xl() ), $row, array( 'extra' => array( 'flex_wrap' => 'wrap', 'padding_mobile' => gwl_xlm() ) ) ) );
}

/** Closing booking band. */
function gwl_nrp_cta( $contact_url ) {
	$c = gwl_nrp_content();
	return gwl_container(
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
					gwl_container(
						array(
							gwl_button( 'Book on WhatsApp', $c['whatsapp'], 'gold' ),
							gwl_button( 'Call reservations', 'tel:' . str_replace( ' ', '', $c['phone'] ), 'outline_light' ),
							gwl_button( 'Send an enquiry', $contact_url, 'outline_light' ),
						),
						array( 'width' => 'full', 'dir' => 'row', 'gap' => 14, 'justify' => 'center', 'extra' => array( 'flex_wrap' => 'wrap' ) )
					),
					// STAAH booking engine: drop the property's booking widget into this
					// container to take reservations on the page itself.
				),
				array( 'width' => 'boxed', 'gap' => 10, 'align' => 'center' )
			),
		),
		array( 'width' => 'full', 'bg' => '#4A1119', 'pad' => gwl_xl(), 'extra' => array( 'padding_mobile' => gwl_xlm(), '_element_id' => 'book' ) )
	);
}

/* =========================================================================
 * Content
 * ====================================================================== */

function gwl_nrp_content() {
	static $c = null;
	if ( null !== $c ) { return $c; }
	$c = array(
		'property' => 'Gateway Nova Ridge',
		'short'    => 'Nova Ridge',
		'locality' => 'Ridge, Accra',
		'address'  => '4 Drake Avenue area, Ridge, Accra, Ghana',
		'units'    => '1 Unit',
		'phone'    => '+233 24 000 0000',
		'whatsapp' => 'https://wa.me/233240000000',
		'email'    => 'reservations@gatewaylodgegroup.com',
		'group'    => 'https://www.gatewaylodgegroup.com',
		'tagline'  => 'A private high-rise residence in Accra&rsquo;s most established address, kept to the Gateway Lodge standard and yours alone for the length of your stay.',
		'stats'    => array(
			array( '1', 'Private unit' ),
			array( '2', 'Bedrooms' ),
			array( '24/7', 'Guest support' ),
			array( '15 min', 'To the airport' ),
		),
		'pillars' => array(
			array( 'Yours alone', 'A single unit, so the apartment is never shared, split or reassigned mid-stay.' ),
			array( 'Built for longer stays', 'A real kitchen, laundry and a desk: the things that matter after night three.' ),
			array( 'A settled address', 'Ridge puts the ministries, the CBD and Kotoka within an easy drive.' ),
		),
		'rooms' => array(
			array( 'bedroom-king', 'Principal Bedroom', 'A king bed, blackout curtains, fitted wardrobes and an en-suite bathroom.' ),
			array( 'bedroom-second', 'Second Bedroom', 'A second double room with its own wardrobe and mirror, for family or a colleague.' ),
			array( 'lounge-tv-wall', 'Living Room', 'Deep seating for six, a smart television and light on two sides through the day.' ),
			array( 'kitchen-wide', 'Kitchen &amp; Dining', 'A full fitted kitchen with oven, hob, microwave and a dining table for four.' ),
		),
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
		'location_points' => array(
			'Walking distance to Ridge&rsquo;s embassies and government offices',
			'About 15 minutes by car to Kotoka International Airport',
			'Ten minutes to Osu, Airport Residential and the CBD',
			'Ridge Hospital and Accra&rsquo;s main clinics close by',
		),
		'faqs' => array(
			array( 'Is the whole apartment mine?', 'Yes. Nova Ridge is a single unit, so it is never shared or split between parties.' ),
			array( 'Do you take long stays?', 'We do. The kitchen, laundry and workspace are built for stays measured in weeks rather than nights. Ask reservations about the long-stay rate.' ),
			array( 'Is parking included?', 'Yes, one gated parking space within the building comes with the apartment.' ),
			array( 'What happens in a power cut?', 'The building runs on standby power, so the apartment stays lit and cooled.' ),
		),
	);
	return $c;
}

/* =========================================================================
 * Pages
 * ====================================================================== */

function gwl_nrp_home( $urls ) {
	$c = gwl_nrp_content();

	$cards = array();
	foreach ( $c['rooms'] as $r ) { $cards[] = gwl_nrp_card( $r[0], $r[1], $r[2] ); }

	$features = array();
	foreach ( array_slice( $c['facilities'], 0, 6 ) as $f ) { $features[] = gwl_feature( $f[0], $f[1], $f[2] ); }

	$pillars = array();
	foreach ( $c['pillars'] as $p ) { $pillars[] = gwl_nrp_pillar( $p[0], $p[1] ); }

	return array(
		gwl_nrp_video_hero( array(
			gwl_eyebrow( 'Gateway Lodge Group', 'left' ),
			gwl_heading( $c['property'], array( 'tag' => 'h1', 'align' => 'left', 'size' => 74, 'size_m' => 40, 'color' => '#FFFFFF', 'lh' => 1.04 ) ),
			gwl_heading(
				$c['locality'] . '&nbsp;&nbsp;&middot;&nbsp;&nbsp;' . $c['units'],
				array( 'tag' => 'div', 'align' => 'left', 'family' => 'Jost', 'weight' => '600', 'size' => 12, 'ls' => 0.2, 'tt' => 'uppercase', 'color' => '#ECC989', 'lh' => 1.5 )
			),
			gwl_text( '<p>' . $c['tagline'] . '</p>', array( 'align' => 'left', 'color' => 'rgba(255,255,255,0.9)', 'size' => 18, 'maxw' => 560 ) ),
			gwl_container(
				array(
					gwl_button( 'Book Now', $urls['contact'], 'gold', 'left' ),
					gwl_button( 'Explore the property', $urls['about'], 'outline_light', 'left' ),
				),
				array( 'width' => 'full', 'dir' => 'row', 'gap' => 14, 'extra' => array( 'flex_wrap' => 'wrap' ) )
			),
		) ),

		gwl_nrp_stats( $c['stats'] ),

		gwl_nrp_split(
			'lounge-open-plan',
			'The Residence',
			'One apartment. No lobby, no queue, no neighbours in the corridor.',
			array(
				'Nova Ridge is a single serviced apartment on an upper floor of a residential tower in Ridge, the quiet tree-lined district that sits between central Accra&rsquo;s business addresses and the embassies. You arrive to an apartment that has been prepared for you, and it stays that way.',
			),
			array_merge( $pillars, array( gwl_button( 'More about the residence', $urls['about'], 'outline_dark', 'left' ) ) ),
			'left'
		),

		gwl_nrp_section(
			array(
				gwl_section_head( 'Accommodation', 'Where you will stay', 'Rates and availability are confirmed when you book.' ),
				gwl_container( $cards, array( 'width' => 'full', 'dir' => 'row', 'gap' => 22, 'align' => 'stretch', 'extra' => array( 'flex_wrap' => 'wrap' ) ) ),
			),
			'#F7F3EE'
		),

		gwl_nrp_section( array(
			gwl_section_head( 'Facilities', 'Everything the apartment needs, already in it', 'Nothing here is an upgrade or an extra line on the folio.' ),
			gwl_container( $features, array( 'width' => 'full', 'dir' => 'row', 'gap' => 22, 'align' => 'stretch', 'extra' => array( 'flex_wrap' => 'wrap' ) ) ),
			gwl_container(
				array( gwl_button( 'See all facilities', $urls['facilities'], 'outline_dark' ) ),
				array( 'width' => 'full', 'align' => 'center', 'pad' => gwl_pad( 34, 0, 0, 0 ) )
			),
		) ),

		gwl_nrp_section(
			array(
				gwl_section_head( 'Gallery', 'A closer look' ),
				gwl_nrp_gallery( array( 'hero-living', 'lounge-tv-wall', 'kitchen-dining', 'bedroom-suite', 'balcony-skyline', 'dining-nook' ), '3', 340 ),
			),
			'#F7F3EE'
		),

		gwl_nrp_cta( $urls['contact'] ),
	);
}

function gwl_nrp_about( $urls ) {
	$c = gwl_nrp_content();
	$pillars = array();
	foreach ( $c['pillars'] as $p ) { $pillars[] = gwl_nrp_pillar( $p[0], $p[1] ); }

	return array(
		gwl_nrp_banner( 'lounge-daylight', 'About', 'The Residence', 'A single serviced apartment in Ridge, run to the Gateway Lodge standard.' ),

		gwl_nrp_split(
			'lounge-open-plan',
			'Who we are',
			'A hotel standard, in a home that is only yours.',
			array(
				'Gateway Nova Ridge is the smallest and most private of the three Gateway Lodge properties: one apartment, on an upper floor of a residential tower in Ridge. There is no reception floor and no corridor of other guests. You let yourself in, and for as long as you stay the apartment belongs to you.',
				'Inside there is an open living room that runs into a full kitchen, a dining table, two bedrooms dressed in hotel linen, two bathrooms, and a balcony looking over the green of Ridge towards the city.',
			),
			$pillars,
			'left'
		),

		gwl_nrp_split(
			'balcony-skyline',
			'The address',
			'Ridge, where Accra keeps its quiet.',
			array(
				'Ridge is the district of embassies, ministries and old trees, a few minutes from the central business district but a world away from its noise. It is the address people choose when they want to be close to everything and hear none of it.',
			),
			array( gwl_icon_list( $c['location_points'] ) ),
			'right',
			'#F7F3EE'
		),

		gwl_nrp_split(
			'kitchen-dining',
			'How we run it',
			'Serviced, not staffed over.',
			array(
				'Housekeeping comes on a rhythm that suits your stay rather than a fixed hotel schedule. Laundry, pressing, airport transfers and grocery runs are arranged on request. One number reaches the team at any hour.',
			),
			array( gwl_button( 'Talk to reservations', $urls['contact'], 'outline_dark', 'left' ) ),
			'left'
		),

		gwl_nrp_cta( $urls['contact'] ),
	);
}

function gwl_nrp_facilities( $urls ) {
	$c = gwl_nrp_content();
	$features = array();
	foreach ( $c['facilities'] as $f ) { $features[] = gwl_feature( $f[0], $f[1], $f[2] ); }

	return array(
		gwl_nrp_banner( 'kitchen', 'Facilities', 'Facilities &amp; Amenities', 'Everything an extended stay asks for is already fitted.' ),

		gwl_nrp_split(
			'kitchen-wide',
			'The apartment',
			'Complete on arrival, and kept that way.',
			array(
				'Nothing on this page is an upgrade or an extra line on the folio. The kitchen is a real kitchen, the laundry is in the apartment, the desk is a desk. The team keeps it all in order for as long as you stay.',
			),
			array(),
			'right'
		),

		gwl_nrp_section( array(
			gwl_container( $features, array( 'width' => 'full', 'dir' => 'row', 'gap' => 22, 'align' => 'stretch', 'extra' => array( 'flex_wrap' => 'wrap' ) ) ),
		) ),

		gwl_nrp_section(
			array(
				gwl_section_head( 'Gallery', 'Room by room' ),
				gwl_nrp_gallery(
					array( 'bedroom-king', 'bedroom-suite', 'bathroom', 'lounge-sofas', 'hallway', 'balcony', 'kitchen-dining', 'bedroom-window', 'lounge-tv-wall' ),
					'3',
					320
				),
			),
			'#F7F3EE'
		),

		gwl_faq_section( 'Good to know', 'Frequently asked questions', $c['faqs'] ),

		gwl_nrp_cta( $urls['contact'] ),
	);
}

function gwl_nrp_contact( $urls, $form_id ) {
	$c = gwl_nrp_content();

	$details = gwl_widget( 'icon-list', array(
		'icon_list' => array(
			array( '_id' => gwl_id(), 'text' => $c['address'], 'selected_icon' => array( 'value' => 'fas fa-map-marker-alt', 'library' => 'fa-solid' ) ),
			array( '_id' => gwl_id(), 'text' => $c['phone'], 'selected_icon' => array( 'value' => 'fas fa-phone', 'library' => 'fa-solid' ), 'link' => array( 'url' => 'tel:' . str_replace( ' ', '', $c['phone'] ), 'is_external' => '', 'nofollow' => '' ) ),
			array( '_id' => gwl_id(), 'text' => 'WhatsApp reservations', 'selected_icon' => array( 'value' => 'fab fa-whatsapp', 'library' => 'fa-brands' ), 'link' => array( 'url' => $c['whatsapp'], 'is_external' => 'on', 'nofollow' => '' ) ),
			array( '_id' => gwl_id(), 'text' => $c['email'], 'selected_icon' => array( 'value' => 'fas fa-envelope', 'library' => 'fa-solid' ), 'link' => array( 'url' => 'mailto:' . $c['email'], 'is_external' => '', 'nofollow' => '' ) ),
		),
		'view'          => 'traditional',
		'icon_color'    => '#DBA845',
		'icon_size'     => array( 'unit' => 'px', 'size' => 16 ),
		'text_color'    => '#5A5049',
		'icon_typography_typography' => 'custom',
		'icon_typography_font_family' => 'Jost',
		'icon_typography_font_size'  => array( 'unit' => 'px', 'size' => 17 ),
		'space_between' => array( 'unit' => 'px', 'size' => 16 ),
	) );

	$map = gwl_widget( 'google_maps', array(
		'address' => '4 Drake Avenue, Ridge, Accra, Ghana',
		'zoom'    => array( 'size' => 15 ),
		'height'  => array( 'unit' => 'px', 'size' => 420 ),
	) );

	$form_kids = array(
		gwl_eyebrow( 'Enquiries', 'left' ),
		gwl_slash( 'left' ),
		gwl_heading( 'Send us the dates', array( 'tag' => 'h2', 'align' => 'left', 'size' => 40 ) ),
		gwl_text( '<p>Tell us when you are arriving and how many of you there are, and reservations will come back with availability and a rate.</p>', array( 'align' => 'left', 'size' => 17 ) ),
	);
	if ( $form_id ) {
		// WPForms goes in through Elementor's own shortcode widget, not an HTML widget.
		$form_kids[] = gwl_widget( 'shortcode', array( 'shortcode' => '[wpforms id="' . $form_id . '"]' ) );
	}

	return array(
		gwl_nrp_banner( 'hallway', 'Contact', 'Contact Nova Ridge', 'Reservations answer by phone and WhatsApp every day.' ),

		gwl_container(
			array(
				gwl_container(
					array(
						gwl_eyebrow( 'Find us', 'left' ),
						gwl_slash( 'left' ),
						gwl_heading( 'Where we are', array( 'tag' => 'h2', 'align' => 'left', 'size' => 40 ) ),
						$details,
						gwl_icon_list( $c['location_points'] ),
					),
					array( 'width' => 'full', 'gap' => 14, 'basis' => 45, 'grow' => 1, 'justify' => 'center' )
				),
				gwl_container( array( $map ), array( 'width' => 'full', 'gap' => 0, 'basis' => 55, 'grow' => 1 ) ),
			),
			array( 'width' => 'boxed', 'dir' => 'row', 'gap' => 52, 'align' => 'center', 'pad' => gwl_xl(), 'extra' => array( 'flex_wrap' => 'wrap', 'padding_mobile' => gwl_xlm() ) )
		),

		gwl_container(
			array(
				gwl_container(
					array( gwl_container( $form_kids, array( 'width' => 'full', 'gap' => 12, 'maxw' => 780 ) ) ),
					array( 'width' => 'boxed', 'gap' => 0 )
				),
			),
			array( 'width' => 'full', 'bg' => '#F7F3EE', 'pad' => gwl_xl(), 'extra' => array( 'padding_mobile' => gwl_xlm() ) )
		),

		gwl_nrp_cta( $urls['contact'] ),
	);
}

/* =========================================================================
 * XPRO header and footer
 * ====================================================================== */

function gwl_nrp_header_template( $menu_slug, $contact_url ) {
	$c = gwl_nrp_content();

	$brand = gwl_container(
		array(
			gwl_widget( 'xpro-site-logo', array(
				'logo_type'      => 'default',
				'thumbnail_size' => 'thumbnail',
				'link_type'      => 'default',
				'align'          => 'left',
				'width'          => array( 'unit' => 'px', 'size' => 40 ),
			) ),
			gwl_heading( $c['short'], array(
				'tag' => 'div', 'align' => 'left', 'size' => 21, 'size_m' => 16,
				'lh' => 1, 'ls' => 0.11, 'tt' => 'uppercase',
				'link' => home_url( '/' ),
			) ),
		),
		array(
			'width' => 'full', 'dir' => 'row', 'gap' => 11, 'align' => 'center',
			'extra' => array( 'flex_wrap' => 'nowrap', '_flex_size' => 'none', '_flex_grow' => 0, '_flex_shrink' => 0 ),
		)
	);

	$nav = gwl_container(
		array(
			gwl_widget( 'xpro-horizontal-menu', array(
				'nav_menu'                    => $menu_slug,
				'responsive_show'             => 'tablet',
				'hamburger_entrance_animation' => 'left',
				'hamburger_icon'              => array( 'value' => 'fas fa-bars', 'library' => 'fa-solid' ),
				'hamburger_close_icon'        => array( 'value' => 'fas fa-times', 'library' => 'fa-solid' ),
				'align'                       => 'right',
				'menu_style'                  => 'fade',
				'menu_typography_typography'  => 'custom',
				'menu_typography_font_family' => 'Jost',
				'menu_typography_font_weight' => '600',
				'menu_typography_font_size'   => array( 'unit' => 'px', 'size' => 13 ),
				'menu_typography_text_transform' => 'uppercase',
				'menu_typography_letter_spacing' => array( 'unit' => 'em', 'size' => 0.14 ),
				'menu_text_color'             => '#5C1620',
				'menu_hover_text_color'       => '#DBA845',
				'submenu_typography_typography' => 'custom',
				'submenu_typography_font_family' => 'Jost',
				'submenu_typography_font_size' => array( 'unit' => 'px', 'size' => 14 ),
				'submenu_text_color'          => '#5A5049',
				'submenu_hover_text_color'    => '#5C1620',
				'responsive_bg_color'         => '#FFFFFF',
				'responsive_overlay_color'    => 'rgba(20,12,10,0.5)',
				'responsive_menu_width'       => array( 'unit' => 'px', 'size' => 420 ),
				'responsive_menu_width_mobile' => array( 'unit' => 'px', 'size' => 320 ),
				'responsive_menu_text_color'  => '#5C1620',
				'responsive_menu_hover_text_color' => '#DBA845',
				'toggle_color'                => '#201A17',
				'toggle_size'                 => array( 'unit' => 'px', 'size' => 22 ),
				'close_color'                 => '#5C1620',
				'close_size'                  => array( 'unit' => 'px', 'size' => 22 ),
			) ),
			gwl_button( 'Book Now', $contact_url, 'gold', 'right' ),
		),
		array(
			'width' => 'full', 'dir' => 'row', 'gap' => 18, 'align' => 'center', 'justify' => 'flex-end',
			'extra' => array( 'flex_wrap' => 'nowrap', '_flex_size' => 'none', '_flex_grow' => 0, '_flex_shrink' => 0 ),
		)
	);

	return array(
		gwl_container(
			array( $brand, $nav ),
			array(
				'width' => 'boxed', 'dir' => 'row', 'gap' => 16, 'align' => 'center', 'justify' => 'space-between',
				'bg' => '#FBF9F6',
				'pad' => gwl_pad( 14, 0, 14, 0 ),
				'extra' => array(
					'flex_wrap'     => 'nowrap',
					'border_border' => 'solid',
					'border_width'  => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '1', 'left' => '0', 'isLinked' => '' ),
					'border_color'  => '#E6DDD3',
				),
			)
		),
	);
}

function gwl_nrp_footer_template( $urls ) {
	$c = gwl_nrp_content();

	$link_list = function ( $items ) {
		$li = array();
		foreach ( $items as $text => $url ) {
			$li[] = array(
				'_id'  => gwl_id(),
				'text' => $text,
				'link' => array( 'url' => $url, 'is_external' => '', 'nofollow' => '' ),
				'selected_icon' => array( 'value' => '', 'library' => '' ),
			);
		}
		return gwl_widget( 'icon-list', array(
			'icon_list'    => $li,
			'view'         => 'traditional',
			'text_color'   => 'rgba(255,255,255,0.8)',
			'text_color_hover' => '#ECC989',
			'icon_typography_typography' => 'custom',
			'icon_typography_font_family' => 'Jost',
			'icon_typography_font_size'  => array( 'unit' => 'px', 'size' => 15 ),
			'space_between' => array( 'unit' => 'px', 'size' => 10 ),
		) );
	};

	$col_head = function ( $t ) {
		return gwl_heading( $t, array(
			'tag' => 'h3', 'align' => 'left', 'color' => '#FFFFFF', 'family' => 'Jost',
			'weight' => '600', 'size' => 11, 'ls' => 0.18, 'tt' => 'uppercase', 'lh' => 1.4,
		) );
	};

	$brand_col = gwl_container(
		array(
			gwl_container(
				array(
					gwl_widget( 'xpro-site-logo', array( 'logo_type' => 'default', 'thumbnail_size' => 'thumbnail', 'link_type' => 'default', 'align' => 'left', 'width' => array( 'unit' => 'px', 'size' => 38 ) ) ),
					gwl_heading( $c['short'], array( 'tag' => 'div', 'align' => 'left', 'size' => 20, 'lh' => 1, 'ls' => 0.11, 'tt' => 'uppercase', 'color' => '#FFFFFF' ) ),
				),
				array( 'width' => 'full', 'dir' => 'row', 'gap' => 10, 'align' => 'center', 'extra' => array( 'flex_wrap' => 'nowrap' ) )
			),
			gwl_text(
				'<p>Gateway Nova Ridge is part of Gateway Lodge Group, a growing collection of hospitality destinations across Ghana.</p>',
				array( 'align' => 'left', 'size' => 15, 'color' => 'rgba(255,255,255,0.7)' )
			),
			gwl_widget( 'xpro-social-icon', array(
				'item' => array(
					array( '_id' => gwl_id(), 'social_icon' => array( 'value' => 'fab fa-facebook-f', 'library' => 'fa-brands' ), 'social_link' => array( 'url' => 'https://facebook.com/gatewaylodgegroup', 'is_external' => 'on', 'nofollow' => '' ) ),
					array( '_id' => gwl_id(), 'social_icon' => array( 'value' => 'fab fa-instagram', 'library' => 'fa-brands' ), 'social_link' => array( 'url' => 'https://instagram.com/gatewaylodgegroup', 'is_external' => 'on', 'nofollow' => '' ) ),
					array( '_id' => gwl_id(), 'social_icon' => array( 'value' => 'fab fa-linkedin-in', 'library' => 'fa-brands' ), 'social_link' => array( 'url' => 'https://linkedin.com/company/gatewaylodgegroup', 'is_external' => 'on', 'nofollow' => '' ) ),
					array( '_id' => gwl_id(), 'social_icon' => array( 'value' => 'fab fa-whatsapp', 'library' => 'fa-brands' ), 'social_link' => array( 'url' => $c['whatsapp'], 'is_external' => 'on', 'nofollow' => '' ) ),
				),
				'social_align_horizontal' => 'left',
				'social_icon_color'       => '#FFFFFF',
				'social_icon_hover_color' => '#ECC989',
				'social_icon_bg_color'    => 'rgba(0,0,0,0)',
				'social_icon_hover_bg_color' => 'rgba(0,0,0,0)',
				'social_icon_border_border' => 'solid',
				'social_icon_border_width' => array( 'unit' => 'px', 'top' => '1', 'right' => '1', 'bottom' => '1', 'left' => '1', 'isLinked' => '1' ),
				'social_icon_border_color' => 'rgba(255,255,255,0.25)',
				'social_icon_border_radius' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => '1' ),
				'social_icon_padding'     => gwl_pad( 10, 10, 10, 10 ),
				'icon_size'               => array( 'unit' => 'px', 'size' => 15 ),
				'social_item_space_between' => array( 'unit' => 'px', 'size' => 10 ),
			) ),
		),
		array( 'width' => 'full', 'gap' => 16, 'basis' => 34, 'grow' => 1 )
	);

	$cols = array(
		$brand_col,
		gwl_container(
			array( $col_head( 'This property' ), $link_list( array(
				'Home'       => $urls['home'],
				'About'      => $urls['about'],
				'Facilities' => $urls['facilities'],
				'Contact'    => $urls['contact'],
			) ) ),
			array( 'width' => 'full', 'gap' => 8, 'basis' => 22, 'grow' => 1 )
		),
		gwl_container(
			array( $col_head( 'Gateway Lodge Group' ), $link_list( array(
				'Group website'  => $c['group'] . '/',
				'Our properties' => $c['group'] . '/properties.html',
				'Offers'         => $c['group'] . '/offers.html',
				'About us'       => $c['group'] . '/about.html',
			) ) ),
			array( 'width' => 'full', 'gap' => 8, 'basis' => 22, 'grow' => 1 )
		),
		gwl_container(
			array( $col_head( 'Reservations' ), $link_list( array(
				$c['phone'] => 'tel:' . str_replace( ' ', '', $c['phone'] ),
				'WhatsApp'  => $c['whatsapp'],
				$c['email'] => 'mailto:' . $c['email'],
			) ) ),
			array( 'width' => 'full', 'gap' => 8, 'basis' => 22, 'grow' => 1 )
		),
	);

	$bottom = gwl_container(
		array(
			gwl_text( '<p>&copy; ' . date( 'Y' ) . ' Gateway Lodge Group. All Rights Reserved.</p>', array( 'align' => 'left', 'size' => 14, 'color' => 'rgba(255,255,255,0.65)' ) ),
			gwl_text(
				'<p><a href="' . esc_url( $c['group'] ) . '/privacy-policy.html">Privacy Policy</a> &nbsp;&nbsp; <a href="' . esc_url( $c['group'] ) . '/terms.html">Terms &amp; Conditions</a></p>',
				array( 'align' => 'right', 'size' => 14, 'color' => 'rgba(255,255,255,0.65)' )
			),
		),
		array(
			'width' => 'boxed', 'dir' => 'row', 'gap' => 16, 'align' => 'center', 'justify' => 'space-between',
			'pad' => gwl_pad( 22, 0, 0, 0 ),
			'extra' => array(
				'flex_wrap'     => 'wrap',
				'border_border' => 'solid',
				'border_width'  => array( 'unit' => 'px', 'top' => '1', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => '' ),
				'border_color'  => 'rgba(255,255,255,0.15)',
			),
		)
	);

	return array(
		gwl_container(
			array(
				gwl_container( $cols, array( 'width' => 'boxed', 'dir' => 'row', 'gap' => 40, 'align' => 'flex-start', 'extra' => array( 'flex_wrap' => 'wrap' ) ) ),
				$bottom,
			),
			array(
				'width' => 'full', 'bg' => '#4A1119', 'gap' => 44,
				'pad' => gwl_pad( 72, 0, 26, 0 ),
				'extra' => array( 'padding_mobile' => gwl_pad( 52, 0, 22, 0 ) ),
			)
		),
	);
}

/** Creates or updates an XPRO Theme Builder template and shows it site-wide. */
function gwl_nrp_themer( $slug, $title, $type, $elements ) {
	$existing = get_page_by_path( $slug, OBJECT, 'xpro-themer' );
	if ( $existing ) {
		$id = $existing->ID;
		wp_update_post( array( 'ID' => $id, 'post_title' => $title, 'post_status' => 'publish' ) );
	} else {
		$id = wp_insert_post( array(
			'post_type'   => 'xpro-themer',
			'post_name'   => $slug,
			'post_title'  => $title,
			'post_status' => 'publish',
			'post_author' => 1,
		) );
	}
	if ( ! $id || is_wp_error( $id ) ) { return 0; }

	gwl_save_elementor( $id, $elements, 'xpro-themer' );

	// XPRO resolves templates through get_settings( 'type_header' ), which compares
	// this meta against that same string, so the value is 'type_header', not 'header'.
	update_post_meta( $id, 'xpro_theme_builder_template_type', $type );
	update_post_meta( $id, 'xpro_theme_builder_target_include_locations', array( 'rule' => array( 'basic-global' ) ) );
	// Leave the exclusion and user-role rules absent: an empty rule array reads as
	// "exclude everywhere" and the template never renders.
	delete_post_meta( $id, 'xpro_theme_builder_target_exclude_locations' );
	delete_post_meta( $id, 'xpro_theme_builder_target_user_roles' );

	return $id;
}

/* =========================================================================
 * Build
 * ====================================================================== */

/** The Nova Ridge enquiry form, in WPForms. */
function gwl_nrp_form() {
	$existing = get_option( 'gwl_nr_form_id' );
	if ( $existing && get_post( $existing ) ) { return (int) $existing; }
	if ( ! post_type_exists( 'wpforms' ) ) { return 0; }

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
		'fields'   => array(
			'1' => array( 'id' => '1', 'type' => 'name', 'label' => 'Full Name', 'format' => 'simple', 'required' => '1', 'size' => 'large' ),
			'2' => array( 'id' => '2', 'type' => 'email', 'label' => 'Email', 'required' => '1', 'size' => 'large' ),
			'3' => array( 'id' => '3', 'type' => 'text', 'label' => 'Phone', 'size' => 'large' ),
			'4' => array( 'id' => '4', 'type' => 'text', 'label' => 'Check-in', 'size' => 'large', 'placeholder' => 'e.g. 12 March 2027' ),
			'5' => array( 'id' => '5', 'type' => 'text', 'label' => 'Check-out', 'size' => 'large', 'placeholder' => 'e.g. 19 March 2027' ),
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
		),
		'settings' => array(
			'form_title'             => 'Nova Ridge Enquiry',
			'submit_text'            => 'Send Enquiry',
			'submit_text_processing' => 'Sending...',
			'confirmations'          => array(
				1 => array(
					'type'    => 'message',
					'message' => '<p>Thank you. Reservations will come back to you shortly. For anything urgent, message us on WhatsApp.</p>',
				),
			),
			'notifications'          => array(
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

/** Creates or updates a page and returns its id. */
function gwl_nrp_page( $slug, $title ) {
	$p = get_page_by_path( $slug, OBJECT, 'page' );
	if ( $p ) {
		wp_update_post( array( 'ID' => $p->ID, 'post_title' => $title, 'post_status' => 'publish' ) );
		return $p->ID;
	}
	return wp_insert_post( array(
		'post_type'   => 'page',
		'post_name'   => $slug,
		'post_title'  => $title,
		'post_status' => 'publish',
		'post_author' => 1,
		'post_content' => '',
	) );
}

/** Astra has to stand back so the containers run full width. */
function gwl_nrp_page_meta( $page_id ) {
	update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );
	update_post_meta( $page_id, 'site-content-layout', 'page-builder' );
	update_post_meta( $page_id, 'ast-site-content-layout', 'full-width-container' );
	update_post_meta( $page_id, 'site-sidebar-layout', 'no-sidebar' );
	update_post_meta( $page_id, 'ast-title-bar-display', 'disabled' );
	update_post_meta( $page_id, 'ast-featured-img', 'disabled' );
}

function gwl_nrp_build() {
	$report = array();

	// 1. Pages -------------------------------------------------------------
	$ids = array(
		'home'       => gwl_nrp_page( 'home', 'Home' ),
		'about'      => gwl_nrp_page( 'about', 'About' ),
		'facilities' => gwl_nrp_page( 'facilities', 'Facilities' ),
		'contact'    => gwl_nrp_page( 'contact', 'Contact' ),
	);
	foreach ( $ids as $k => $id ) {
		if ( ! $id || is_wp_error( $id ) ) { return 'page failed: ' . $k; }
	}
	$urls = array();
	foreach ( $ids as $k => $id ) { $urls[ $k ] = get_permalink( $id ); }

	// 2. Front page --------------------------------------------------------
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $ids['home'] );
	update_option( 'blogname', 'Gateway Nova Ridge' );
	update_option( 'blogdescription', 'Serviced apartment in Ridge, Accra. Part of Gateway Lodge Group.' );
	$urls['home'] = home_url( '/' );

	// 3. Form --------------------------------------------------------------
	$form_id = gwl_nrp_form();

	// 4. Page content ------------------------------------------------------
	gwl_save_elementor( $ids['home'], gwl_nrp_home( $urls ) );
	gwl_save_elementor( $ids['about'], gwl_nrp_about( $urls ) );
	gwl_save_elementor( $ids['facilities'], gwl_nrp_facilities( $urls ) );
	gwl_save_elementor( $ids['contact'], gwl_nrp_contact( $urls, $form_id ) );
	foreach ( $ids as $id ) { gwl_nrp_page_meta( $id ); }

	// 5. Menu --------------------------------------------------------------
	$menu_slug = 'novaridge-main';
	$menu = wp_get_nav_menu_object( $menu_slug );
	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( 'Nova Ridge Main' );
		$menu    = wp_get_nav_menu_object( $menu_id );
	} else {
		$menu_id = $menu->term_id;
		foreach ( wp_get_nav_menu_items( $menu_id ) as $item ) { wp_delete_post( $item->ID, true ); }
	}
	if ( is_wp_error( $menu_id ) || ! $menu_id ) { return 'menu failed'; }
	// wp_create_nav_menu derives the slug from the name; make sure it is ours.
	wp_update_term( $menu_id, 'nav_menu', array( 'slug' => $menu_slug ) );

	$order = 1;
	foreach ( array( 'home' => 'Home', 'about' => 'About', 'facilities' => 'Facilities', 'contact' => 'Contact' ) as $key => $label ) {
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'     => $label,
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $ids[ $key ],
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
			'menu-item-position'  => $order++,
		) );
	}
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	foreach ( array_keys( get_registered_nav_menus() ) as $loc ) { $locations[ $loc ] = $menu_id; }
	set_theme_mod( 'nav_menu_locations', $locations );

	// 6. XPRO header and footer -------------------------------------------
	$header_id = gwl_nrp_themer( 'novaridge-header', 'Nova Ridge Header', 'type_header', gwl_nrp_header_template( $menu_slug, $urls['contact'] ) );
	$footer_id = gwl_nrp_themer( 'novaridge-footer', 'Nova Ridge Footer', 'type_footer', gwl_nrp_footer_template( $urls ) );

	// 7. Clear caches ------------------------------------------------------
	if ( class_exists( '\\Elementor\\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	$report = array(
		'pages'     => $ids,
		'urls'      => $urls,
		'form_id'   => $form_id,
		'menu_id'   => $menu_id,
		'menu_slug' => $menu_slug,
		'header_id' => $header_id,
		'footer_id' => $footer_id,
	);
	update_option( 'gwl_nrp_report', $report );
	return $report;
}
