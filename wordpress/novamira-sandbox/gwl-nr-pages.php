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
	$c      = gwl_nrp_content();
	$video  = $c['hero_video'] ? gwl_media( $c['hero_video'] ) : array( 'url' => '', 'id' => '' );
	$poster = gwl_media( $c['hero_poster'] );
	// Lakeside has no walkthrough film yet, so it opens on a still instead.
	$media  = $video['url']
		? array(
			'background_background'        => 'video',
			'background_video_link'        => $video['url'],
			'background_play_on_mobile'    => 'yes',
			'background_video_fallback'    => array( 'url' => $poster['url'], 'id' => $poster['id'], 'size' => '' ),
		)
		: array(
			'background_background' => 'classic',
			'background_image'      => array( 'url' => $poster['url'], 'id' => $poster['id'], 'size' => '' ),
			'background_position'   => 'center center',
			'background_size'       => 'cover',
			'background_repeat'     => 'no-repeat',
		);
	return gwl_container(
		array( gwl_container( $kids, array( 'width' => 'boxed', 'gap' => 14, 'align' => 'flex-start' ) ) ),
		array(
			'width'   => 'full',
			'justify' => 'flex-end',
			'minh'    => 100,
			'pad'     => gwl_pad( 150, 0, 90, 0 ),
			// A hook for the stacking fix in gwl_nrp_custom_css(): a container's
			// overlay is a ::before, which the background-video layer covers.
			'cls'     => 'gwl-video-hero',
			'extra'   => array(
				'min_height_mobile'             => array( 'unit' => 'vh', 'size' => 86 ),
				'padding_mobile'                => gwl_pad( 120, 0, 60, 0 ),
				// The gradient already carries its own alphas, so keep Elementor's
				// overlay opacity out of it rather than having them halved.
				'background_overlay_opacity'    => array( 'unit' => 'px', 'size' => 1 ),
				'background_overlay_background' => 'gradient',
				'background_overlay_color'      => 'rgba(28,11,13,0.82)',
				'background_overlay_color_b'    => 'rgba(28,11,13,0.20)',
				'background_overlay_gradient_type'  => 'linear',
				'background_overlay_gradient_angle' => array( 'unit' => 'deg', 'size' => 90 ),
				'background_overlay_color_stop'     => array( 'unit' => '%', 'size' => 0 ),
				'background_overlay_color_b_stop'   => array( 'unit' => '%', 'size' => 78 ),
			) + $media,
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
			array(
				'width' => 'full', 'gap' => 2, 'basis' => 25, 'grow' => 1,
				'pad'   => gwl_pad( 34, 16, 34, 16 ),
				// Four figures stacked is a lot of scrolling; pair them instead.
				'extra' => array( 'width_mobile' => array( 'unit' => '%', 'size' => 50 ) ),
			)
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
			// A class so the row of cards holds one image shape whatever each photo's
			// native ratio is; see the aspect rule in gwl_nrp_custom_css().
			gwl_image( $imgkey, array( 'cls' => 'gwl-card-image' ) ),
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
	// xpro-simple-gallery's `gallery` control is a repeater of filter groups, each
	// holding its own `images` gallery. Passing image rows directly renders nothing.
	$images = array();
	foreach ( $keys as $k ) {
		$m = gwl_media( $k );
		if ( $m['id'] ) { $images[] = array( 'id' => $m['id'], 'url' => $m['url'] ); }
	}
	if ( ! $images ) { return null; }
	$group = array(
		array(
			'_id'               => gwl_id(),
			'filter'            => 'Gallery',
			'is_default_filter' => 'yes',
			'images'            => $images,
		),
	);
	return gwl_widget( 'xpro-simple-gallery', array(
		'gallery'             => $group,
		'show_filter'         => '',
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
					gwl_heading( 'Book ' . $c['property'], array( 'tag' => 'h2', 'size' => 44, 'color' => '#FFFFFF' ) ),
					gwl_text(
						'<p>Book direct for the best available rate. Reservations answer by phone and WhatsApp every day, or send the enquiry form and we will come back to you.</p>',
						array( 'size' => 17, 'color' => 'rgba(255,255,255,0.8)', 'maxw' => 640 )
					),
					gwl_container(
						array(
							gwl_button( 'Book Now', $c['whatsapp'], 'gold' ),
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

/** The property currently being built. */
function gwl_nrp_slug( $set = null ) {
	static $slug = 'novaridge';
	if ( null !== $set ) { $slug = $set; }
	return $slug;
}

/**
 * Per-property content, generated from the same source as the static pages by
 * tools/build-wp-property-content.py so the two cannot drift apart.
 */
function gwl_nrp_content( $slug = null ) {
	static $all = null;
	if ( null === $all ) {
		$file = __DIR__ . '/gwl-property-content.php';
		$all  = file_exists( $file ) ? require $file : array();
	}
	$slug = $slug ? $slug : gwl_nrp_slug();
	return isset( $all[ $slug ] ) ? $all[ $slug ] : array();
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
			$c['about_image'],
			$c['about_kicker'],
			$c['about_head'],
			array_slice( $c['about_body'], 0, 1 ),
			array_merge( $pillars, array( gwl_button( 'More about the property', $urls['about'], 'outline_dark', 'left' ) ) ),
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
				gwl_nrp_gallery( $c['gallery_home'], '3', 340 ),
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
		gwl_nrp_banner( $c['banner_about'], 'About', $c['about_kicker'], $c['banner_subs']['about'] ),

		gwl_nrp_split(
			$c['about_image'],
			$c['about_sections'][0]['kicker'],
			$c['about_sections'][0]['head'],
			$c['about_sections'][0]['paras'],
			$pillars,
			'left'
		),

		gwl_nrp_split(
			$c['gallery_facilities'][0],
			$c['about_sections'][1]['kicker'],
			$c['about_sections'][1]['head'],
			$c['about_sections'][1]['paras'],
			array( gwl_icon_list( $c['location_points'] ) ),
			'right',
			'#F7F3EE'
		),

		gwl_nrp_split(
			$c['facilities_image'],
			$c['about_sections'][2]['kicker'],
			$c['about_sections'][2]['head'],
			$c['about_sections'][2]['paras'],
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
		gwl_nrp_banner( $c['banner_facilities'], 'Facilities', 'Facilities &amp; Amenities', $c['banner_subs']['facilities'] ),

		gwl_nrp_split(
			$c['facilities_image'],
			'The property',
			$c['facilities_head'],
			array( $c['facilities_body'] ),
			array(),
			'right'
		),

		gwl_nrp_section( array(
			gwl_container( $features, array( 'width' => 'full', 'dir' => 'row', 'gap' => 22, 'align' => 'stretch', 'extra' => array( 'flex_wrap' => 'wrap' ) ) ),
		) ),

		gwl_nrp_section(
			array(
				gwl_section_head( 'Gallery', 'Room by room' ),
				gwl_nrp_gallery( $c['gallery_facilities'], '3', 320 ),
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
		'address' => $c['map_address'],
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
		gwl_nrp_banner( $c['banner_contact'], 'Contact', 'Contact ' . $c['short'], $c['banner_subs']['contact'] ),

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
 * Responsive pass
 * ====================================================================== */

/**
 * Elementor sizes flex children through `width`, and gwl_container copies the
 * desktop share straight onto tablet. That leaves four-across cards and
 * side-by-side splits intact between 768px and 1024px, where they are far too
 * narrow. Fixed pixel widths (the capped section heads) also overflow a phone.
 *
 * This walks a finished element tree and fills in the tablet and mobile values
 * the builder does not, so every page gets the same treatment without each
 * call site having to remember.
 */
function gwl_nrp_make_responsive( &$elements ) {
	foreach ( $elements as &$el ) {
		if ( empty( $el['settings'] ) || ! is_array( $el['settings'] ) ) { $el['settings'] = array(); }
		$s    = &$el['settings'];
		$type = isset( $el['elType'] ) ? $el['elType'] : '';

		if ( 'container' === $type ) {
			if ( isset( $s['width']['unit'], $s['width']['size'] ) ) {
				$unit = $s['width']['unit'];
				$size = (float) $s['width']['size'];

				if ( '%' === $unit && $size > 0 ) {
					// Narrow columns (cards, figures) pair up on tablet; anything
					// already at about half the row goes full width.
					$s['width_tablet'] = ( $size < 30 )
						? array( 'unit' => '%', 'size' => 48 )
						: array( 'unit' => '%', 'size' => 100 );
					if ( ! isset( $s['width_mobile'] ) ) {
						$s['width_mobile'] = array( 'unit' => '%', 'size' => 100 );
					}
				} elseif ( 'px' === $unit ) {
					// A capped block must not be wider than the screen holding it.
					$s['width_tablet'] = array( 'unit' => '%', 'size' => 100 );
					$s['width_mobile'] = array( 'unit' => '%', 'size' => 100 );
				}
			}

			// Ease the section rhythm off before the mobile value takes over.
			if ( isset( $s['padding']['top'] ) && ! isset( $s['padding_tablet'] ) ) {
				$top    = (int) $s['padding']['top'];
				$bottom = (int) $s['padding']['bottom'];
				if ( $top >= 80 || $bottom >= 80 ) {
					$s['padding_tablet'] = gwl_pad(
						(int) round( $top * 0.7 ),
						(int) $s['padding']['right'],
						(int) round( $bottom * 0.7 ),
						(int) $s['padding']['left']
					);
				}
			}
		}

		if ( 'widget' === $type ) {
			// gwl_heading's mobile default is max( 26px, 55% of desktop ), which suits
			// display type but blows a 12px eyebrow or an 11px figure label up to
			// 26px on a phone. A heading should never be larger on mobile than on
			// desktop, so clamp it.
			if ( isset( $s['typography_font_size']['size'], $s['typography_font_size_mobile']['size'] ) ) {
				if ( (float) $s['typography_font_size_mobile']['size'] > (float) $s['typography_font_size']['size'] ) {
					$s['typography_font_size_mobile'] = $s['typography_font_size'];
				}
			}

			// Display type set for a 1440px screen is too loud on a tablet.
			if ( isset( $s['typography_font_size']['unit'], $s['typography_font_size']['size'] )
				&& 'px' === $s['typography_font_size']['unit']
				&& ! isset( $s['typography_font_size_tablet'] ) ) {
				$desktop = (float) $s['typography_font_size']['size'];
				if ( $desktop >= 30 ) {
					$mobile = isset( $s['typography_font_size_mobile']['size'] )
						? (float) $s['typography_font_size_mobile']['size']
						: $desktop * 0.55;
					$s['typography_font_size_tablet'] = array(
						'unit' => 'px',
						'size' => max( $mobile, round( $desktop * 0.74 ) ),
					);
				}
			}

			// Same story for widgets capped to a pixel measure.
			if ( isset( $s['_element_custom_width']['unit'] )
				&& 'px' === $s['_element_custom_width']['unit'] ) {
				$s['_element_width']                 = 'initial';
				$s['_element_custom_width_tablet']    = array( 'unit' => '%', 'size' => 100 );
				$s['_element_custom_width_mobile']    = array( 'unit' => '%', 'size' => 100 );
			}
		}

		if ( ! empty( $el['elements'] ) && is_array( $el['elements'] ) ) {
			gwl_nrp_make_responsive( $el['elements'] );
		}
	}
}

/** Builds a page's elements and saves them with the responsive pass applied. */
function gwl_nrp_save_page( $page_id, $elements ) {
	gwl_nrp_make_responsive( $elements );
	gwl_save_elementor( $page_id, $elements );
}

/* =========================================================================
 * XPRO header and footer
 * ====================================================================== */

/** Lets a widget size to its own content rather than filling its container. */
function gwl_nrp_auto_width( $widget ) {
	$widget['settings']['_element_width'] = 'auto';
	return $widget;
}

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
			'extra' => array(
				'flex_wrap' => 'nowrap',
				// e-con-full carries --width:100%, and _flex_size 'none' compiles to
				// flex:0 0 auto, which ignores the shrink control. Left that way both
				// children claim the whole row and the bar runs to twice its width.
				// Explicit halves keep the brand left and the nav right instead.
				'width'        => array( 'unit' => '%', 'size' => 50 ),
				'_flex_size'   => 'custom',
				'_flex_grow'   => 1,
				'_flex_shrink' => 1,
			),
		)
	);

	$nav = gwl_container(
		array(
			gwl_widget( 'xpro-horizontal-menu', array(
				// Widgets default to the full width of their container, which squeezed
				// the menu until CONTACT wrapped to a second line.
				'_element_width'              => 'auto',
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
			gwl_nrp_auto_width( gwl_button( 'Book Now', $contact_url, 'gold', 'right' ) ),
		),
		array(
			'width' => 'full', 'dir' => 'row', 'gap' => 18, 'align' => 'center', 'justify' => 'flex-end',
			'extra' => array(
				'flex_wrap'    => 'nowrap',
				'width'        => array( 'unit' => '%', 'size' => 50 ),
				'_flex_size'   => 'custom',
				'_flex_grow'   => 1,
				'_flex_shrink' => 1,
			),
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

/**
 * The footer is the group site's own footer, not a restyled copy of it.
 * tools/build-wp-footers.py takes wordpress/templates/footer-11063.json, keeps
 * every style setting and swaps the link columns and copy per property,
 * emitting gwl-property-footers.php beside this file.
 */
function gwl_nrp_footer_template( $urls ) {
	$file = __DIR__ . '/gwl-property-footers.php';
	if ( ! file_exists( $file ) ) { return array(); }
	$all  = require $file;
	$slug = gwl_nrp_slug();
	return ( is_array( $all ) && isset( $all[ $slug ] ) ) ? $all[ $slug ] : array();
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

	// XPRO's sticky header: this meta puts `xtb-header-sticky` on the <header>, and
	// its own script pins the nav once the page has scrolled past 220px. Always
	// written, because xpro_theme_builder_render_header() reads it as $sticky[0]
	// with no isset guard and notices when the row is missing entirely.
	update_post_meta( $id, 'xpro_theme_builder_sticky', 'type_header' === $type ? 'enable' : '' );

	return $id;
}

/* =========================================================================
 * Build
 * ====================================================================== */

/** The Nova Ridge enquiry form, in WPForms. */
function gwl_nrp_form() {
	$c        = gwl_nrp_content();
	$option   = 'gwl_form_id_' . $c['slug'];
	$existing = get_option( $option );
	if ( $existing && get_post( $existing ) ) { return (int) $existing; }
	if ( ! post_type_exists( 'wpforms' ) ) { return 0; }

	$post_id = wp_insert_post( array(
		'post_type'   => 'wpforms',
		'post_title'  => $c['short'] . ' Enquiry',
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
			'form_title'             => $c['short'] . ' Enquiry',
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
					'notification_name' => $c['short'] . ' enquiry',
					'email'             => $c['email'],
					'subject'           => $c['short'] . ' enquiry from {field_id="1"}',
					'sender_name'       => $c['property'],
					'sender_address'    => '{admin_email}',
					'replyto'           => '{field_id="2"}',
					'message'           => '{all_fields}',
				),
			),
		),
	);

	wp_update_post( array( 'ID' => $post_id, 'post_content' => wp_slash( wp_json_encode( $form ) ) ) );
	update_option( $option, $post_id );
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
	// Stretched, not "full width container": the latter still leaves Astra's
	// container in charge of the width.
	update_post_meta( $page_id, 'ast-site-content-layout', 'full-width-layout' );
	update_post_meta( $page_id, 'site-sidebar-layout', 'no-sidebar' );
	update_post_meta( $page_id, 'ast-title-bar-display', 'disabled' );
	update_post_meta( $page_id, 'ast-featured-img', 'disabled' );
}

/**
 * Astra makes .ast-container a flex row for its content and sidebar columns.
 * A page-builder template has no #primary column to fill it, so the Elementor
 * wrapper becomes a lone flex item and shrinks to fit, rendering the page at
 * roughly half width. This restores the full width its own column would have.
 *
 * Written to the Customizer's Additional CSS so it stays visible and editable.
 */
function gwl_nrp_custom_css() {
	$rule = "/* GWL-START */\n"
		. "/* Astra makes .ast-container a flex row for its content and sidebar columns.\n"
		. "   A page-builder template has no #primary column to fill it, so the Elementor\n"
		. "   wrapper shrinks to fit and the page renders half width. Give it the full\n"
		. "   width Astra's own column would have had. */\n"
		. ".ast-page-builder-template .site-content > .ast-container { display: block; }\n"
		. ".ast-page-builder-template .site-content > .ast-container > .elementor { width: 100%; }\n"
		. "\n"
		. "/* Widgets in a row container share its width between them, which squeezed\n"
		. "   the header menu until CONTACT wrapped and broke the button to two lines.\n"
		. "   Both should size to their own content. */\n"
		. ".xpro-theme-builder-header .elementor-widget-xpro-horizontal-menu,\n"
		. ".xpro-theme-builder-header .elementor-widget-button {\n"
		. "  width: auto; max-width: none; flex: 0 0 auto;\n"
		. "}\n"
		. ".xpro-theme-builder-header .elementor-widget-button .elementor-button { white-space: nowrap; }\n"
		. "@media (max-width: 767px) {\n"
		. "  .xpro-theme-builder-header .elementor-widget-button .elementor-button {\n"
		. "    padding: 12px 14px; font-size: 10px;\n"
		. "  }\n"
		. "}\n"
		. "\n"
		. "/* Sticky header. XPRO pins .xpro-theme-builder-header-nav past 220px and\n"
		. "   reserves the tallest height it has seen, so a shorter pinned bar leaves\n"
		. "   the page where it was. The padding sits on .e-con-inner, not on the\n"
		. "   container itself, which is why XPRO's own sticky padding control adds to\n"
		. "   the bar instead of tightening it. */\n"
		. ".xtb-appear .xpro-theme-builder-header-nav .e-con-inner {\n"
		. "  padding-top: 9px; padding-bottom: 9px;\n"
		. "}\n"
		. ".xpro-theme-builder-header-nav .e-con-inner { transition: padding 0.25s ease; }\n"
		. "\n"
		. "/* Accommodation cards sit in one row, so their photographs have to share a\n"
		. "   shape. Source images vary between 3:2 and 4:3, which left one card's\n"
		. "   picture visibly shorter than the rest. */\n"
		. ".gwl-card-image img { aspect-ratio: 3 / 2; object-fit: cover; width: 100%; height: auto; }\n"
		. "\n"
		. "/* A container paints its background overlay as a ::before carrying no\n"
		. "   z-index, while the background video is a real child at z-index 0. The\n"
		. "   video therefore covers the overlay and the hero copy sits on raw\n"
		. "   footage. Lift the overlay over the video, then the copy over both. */\n"
		. ".gwl-video-hero::before { z-index: 1; }\n"
		. ".gwl-video-hero > .e-con,\n"
		. ".gwl-video-hero > .elementor-element { position: relative; z-index: 2; }\n"
		. "/* GWL-END */";

	$existing = wp_get_custom_css();
	$cleaned  = trim( preg_replace( '~/\* GWL-START \*/.*?/\* GWL-END \*/~s', '', $existing ) );
	wp_update_custom_css_post( trim( $cleaned . "\n\n" . $rule ) );
}

/**
 * AIOSEO titles and descriptions. The aioseo-posts ability reports "Post not
 * found" until AIOSEO has a row for the post, so go through the model, which
 * creates one.
 */
function gwl_nrp_seo( $ids, $urls, $c ) {
	if ( ! class_exists( '\\AIOSEO\\Plugin\\Common\\Models\\Post' ) ) { return; }
	$hero = gwl_media( $c['hero_poster'] );
	$pages = array( 'home' => array( $c['property'] . ' | ' . $c['locality'], $c['meta_desc'] ) );
	foreach ( $c['seo'] as $key => $pair ) { $pages[ $key ] = $pair; }

	foreach ( $pages as $key => $pair ) {
		if ( empty( $ids[ $key ] ) ) { continue; }
		\AIOSEO\Plugin\Common\Models\Post::savePost( $ids[ $key ], array(
			'title'               => $pair[0],
			'description'         => $pair[1],
			'canonical_url'       => $urls[ $key ],
			'og_title'            => $pair[0],
			'og_description'      => $pair[1],
			'og_image_type'       => 'custom_image',
			'og_image_custom_url' => $hero['url'],
			'twitter_use_og'      => true,
		) );
	}
}

function gwl_nrp_build( $slug = 'novaridge' ) {
	gwl_nrp_slug( $slug );
	$c = gwl_nrp_content( $slug );
	if ( empty( $c ) ) { return 'unknown property: ' . $slug; }
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
	update_option( 'blogname', $c['property'] );
	update_option( 'blogdescription', $c['locality'] . '. Part of Gateway Lodge Group.' );
	$urls['home'] = home_url( '/' );

	// 3. Form --------------------------------------------------------------
	$form_id = gwl_nrp_form();

	// 4. Page content ------------------------------------------------------
	gwl_nrp_save_page( $ids['home'], gwl_nrp_home( $urls ) );
	gwl_nrp_save_page( $ids['about'], gwl_nrp_about( $urls ) );
	gwl_nrp_save_page( $ids['facilities'], gwl_nrp_facilities( $urls ) );
	gwl_nrp_save_page( $ids['contact'], gwl_nrp_contact( $urls, $form_id ) );
	foreach ( $ids as $id ) { gwl_nrp_page_meta( $id ); }

	// 5. Menu --------------------------------------------------------------
	$menu_slug = $c['slug'] . '-main';
	$menu = wp_get_nav_menu_object( $menu_slug );
	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( $c['short'] . ' Main' );
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
	$header_id = gwl_nrp_themer( $c['slug'] . '-header', $c['short'] . ' Header', 'type_header', gwl_nrp_header_template( $menu_slug, $urls['contact'] ) );
	$footer_id = gwl_nrp_themer( $c['slug'] . '-footer', $c['short'] . ' Footer', 'type_footer', gwl_nrp_footer_template( $urls ) );

	// 7. SEO ---------------------------------------------------------------
	gwl_nrp_seo( $ids, $urls, $c );

	// 8. Theme-level CSS the layout depends on -----------------------------
	gwl_nrp_custom_css();

	// 9. Clear caches ------------------------------------------------------
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
	$report['slug'] = $c['slug'];
	update_option( 'gwl_nrp_report', $report );
	return $report;
}
