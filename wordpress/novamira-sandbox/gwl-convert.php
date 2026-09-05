<?php
/**
 * Gateway Lodge Group: faithful HTML -> Elementor converter.
 *
 * Reads the original static pages from the project repo and rebuilds each one
 * section by section, keeping the source order, headings and copy. Only native
 * Elementor containers and core/Xpro widgets are emitted.
 */

require_once __DIR__ . '/gwl-builder.php';

define( 'GWL_SRC', 'https://raw.githubusercontent.com/abahvictor360-sketch/Gateway-lodge-/main/' );

/* ------------------------------------------------------------------ utils */

function gwl_c_text( $node ) {
	$t = $node ? $node->textContent : '';
	$t = preg_replace( '/\s+/', ' ', $t );
	return trim( $t );
}

function gwl_c_inner_html( DOMNode $node ) {
	$html = '';
	foreach ( $node->childNodes as $c ) {
		$html .= $node->ownerDocument->saveHTML( $c );
	}
	return trim( preg_replace( '/\s+/', ' ', $html ) );
}

function gwl_c_has( $node, $class ) {
	if ( ! $node || ! method_exists( $node, 'getAttribute' ) ) { return false; }
	return in_array( $class, preg_split( '/\s+/', (string) $node->getAttribute( 'class' ) ), true );
}

/** Map an "image/foo.jpg" src from the source markup to a media library id. */
function gwl_c_att_id( $src ) {
	static $map = null;
	if ( null === $map ) {
		global $wpdb;
		$map = array();
		$rows = $wpdb->get_results( "SELECT ID,guid FROM {$wpdb->posts} WHERE post_type='attachment'", ARRAY_A );
		foreach ( $rows as $r ) { $map[ basename( $r['guid'] ) ] = (int) $r['ID']; }
	}
	$name = basename( parse_url( (string) $src, PHP_URL_PATH ) );
	if ( isset( $map[ $name ] ) ) { return $map[ $name ]; }
	// the source references .jpg; masters may have been stored under another extension
	$base = preg_replace( '/\.[a-z0-9]+$/i', '', $name );
	foreach ( $map as $k => $id ) {
		if ( 0 === strpos( $k, $base ) ) { return $id; }
	}
	return 0;
}

function gwl_c_image_widget( $src, $alt = '' ) {
	$id = gwl_c_att_id( $src );
	return gwl_widget( 'image', array(
		'image' => array( 'url' => $id ? wp_get_attachment_url( $id ) : '', 'id' => $id ?: '', 'size' => '' ),
		'image_size' => 'large',
		'align' => 'center',
		'image_border_radius' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => '1' ),
	) );
}

/** Rewrite a source href (about.html) to the live permalink. */
function gwl_c_href( $href ) {
	$ids = get_option( 'gwl_page_ids', array() );
	$map = array(
		'index.html' => 'home', 'properties.html' => 'properties',
		'property-nova-ridge.html' => 'gateway-nova-ridge',
		'property-lakeside.html' => 'gateway-lodge-lakeside',
		'property-tamale.html' => 'gateway-lodge-tamale',
		'future-properties.html' => 'future-properties', 'stay.html' => 'stay',
		'accommodation.html' => 'accommodation', 'rooms-units.html' => 'rooms-units',
		'facilities.html' => 'facilities', 'dining.html' => 'dining',
		'guest-services.html' => 'guest-services', 'faqs.html' => 'faqs',
		'experiences.html' => 'experiences', 'things-to-do.html' => 'things-to-do',
		'local-attractions.html' => 'local-attractions', 'leisure.html' => 'leisure',
		'dining-experiences.html' => 'dining-experiences',
		'events-celebrations.html' => 'events-celebrations',
		'corporate-meetings.html' => 'corporate-meetings', 'gallery.html' => 'gallery',
		'real-estate.html' => 'real-estate', 'gateway-developments.html' => 'gateway-developments',
		'current-projects.html' => 'current-projects', 'residential.html' => 'residential',
		'serviced-residences.html' => 'serviced-residences', 'villas-apartments.html' => 'villas-apartments',
		'investment-opportunities.html' => 'investment-opportunities', 'offers.html' => 'offers',
		'offers-direct-booking.html' => 'direct-booking-offers', 'offers-weekend.html' => 'weekend-offers',
		'offers-long-stay.html' => 'long-stay', 'offers-corporate-rates.html' => 'corporate-rates',
		'offers-seasonal.html' => 'seasonal-promotions', 'offers-property-specific.html' => 'property-specific-offers',
		'about.html' => 'about-us', 'contact.html' => 'contact', 'journal.html' => 'journal',
		'privacy-policy.html' => 'privacy-policy', 'terms.html' => 'terms',
	);
	$href = (string) $href;
	if ( '' === $href ) { return home_url( '/' ); }
	if ( preg_match( '#^(https?:|mailto:|tel:)#i', $href ) ) { return $href; }
	$frag = '';
	if ( false !== strpos( $href, '#' ) ) {
		list( $href, $frag ) = explode( '#', $href, 2 );
		$frag = '#' . $frag;
	}
	if ( isset( $map[ $href ], $ids[ $map[ $href ] ] ) ) {
		return get_permalink( $ids[ $map[ $href ] ] ) . $frag;
	}
	return home_url( '/' ) . $frag;
}

/** Pick a Font Awesome icon for a feature box from its title. */
function gwl_c_icon( $title ) {
	$t = strtolower( $title );
	$table = array(
		'wifi' => 'fas fa-wifi', 'park' => 'fas fa-car', 'desk' => 'fas fa-concierge-bell',
		'housekeep' => 'fas fa-broom', 'air' => 'fas fa-snowflake', 'laundry' => 'fas fa-tshirt',
		'pool' => 'fas fa-swimmer', 'swim' => 'fas fa-swimmer', 'dining' => 'fas fa-utensils',
		'restaurant' => 'fas fa-utensils', 'breakfast' => 'fas fa-mug-hot', 'kitchen' => 'fas fa-blender',
		'room' => 'fas fa-bed', 'bed' => 'fas fa-bed', 'suite' => 'fas fa-bed',
		'comfort' => 'fas fa-couch', 'experience' => 'fas fa-compass', 'event' => 'fas fa-glass-cheers',
		'celebrat' => 'fas fa-glass-cheers', 'corporate' => 'fas fa-briefcase', 'business' => 'fas fa-briefcase',
		'meeting' => 'fas fa-briefcase', 'leisure' => 'fas fa-umbrella-beach', 'location' => 'fas fa-map-marker-alt',
		'transfer' => 'fas fa-plane', 'airport' => 'fas fa-plane', 'security' => 'fas fa-shield-alt',
		'quality' => 'fas fa-gem', 'trust' => 'fas fa-handshake', 'growth' => 'fas fa-seedling',
		'invest' => 'fas fa-chart-line', 'resident' => 'fas fa-home', 'villa' => 'fas fa-home',
		'apartment' => 'fas fa-city', 'develop' => 'fas fa-drafting-compass', 'project' => 'fas fa-hard-hat',
		'market' => 'fas fa-store', 'craft' => 'fas fa-hands', 'wildlife' => 'fas fa-paw',
		'heritage' => 'fas fa-mosque', 'city' => 'fas fa-city', 'coast' => 'fas fa-water',
		'beach' => 'fas fa-umbrella-beach', 'guide' => 'fas fa-map-signs', 'travel' => 'fas fa-route',
		'rate' => 'fas fa-tag', 'offer' => 'fas fa-tag', 'book' => 'fas fa-calendar-check',
		'weekend' => 'fas fa-calendar-week', 'stay' => 'fas fa-hourglass-half', 'famil' => 'fas fa-users',
		'group' => 'fas fa-users', 'service' => 'fas fa-concierge-bell', 'plan' => 'fas fa-ruler-combined',
		'price' => 'fas fa-hand-holding-usd', 'gallery' => 'fas fa-images', 'overview' => 'fas fa-info-circle',
	);
	foreach ( $table as $k => $icon ) {
		if ( false !== strpos( $t, $k ) ) { return $icon; }
	}
	return 'fas fa-check';
}

/* -------------------------------------------------------------- fragments */

function gwl_c_button( DOMElement $a ) {
	$cls = (string) $a->getAttribute( 'class' );
	$style = 'outline_dark';
	if ( false !== strpos( $cls, 'btn-primary' ) ) { $style = 'primary'; }
	elseif ( false !== strpos( $cls, 'btn-gold' ) ) { $style = 'gold'; }
	elseif ( false !== strpos( $cls, 'btn-outline-dark' ) ) { $style = 'outline_dark'; }
	elseif ( false !== strpos( $cls, 'btn-outline' ) ) { $style = 'outline_light'; }
	return array( gwl_c_text( $a ), gwl_c_href( $a->getAttribute( 'href' ) ), $style );
}

function gwl_c_icon_list( DOMElement $ul ) {
	$items = array();
	foreach ( $ul->getElementsByTagName( 'li' ) as $li ) {
		$t = gwl_c_text( $li );
		if ( '' !== $t ) { $items[] = $t; }
	}
	return $items ? gwl_icon_list( $items ) : null;
}

function gwl_c_faq( DOMElement $wrap ) {
	$tabs = array();
	foreach ( $wrap->getElementsByTagName( 'details' ) as $d ) {
		$sum = $d->getElementsByTagName( 'summary' )->item( 0 );
		$q = gwl_c_text( $sum );
		$a = '';
		foreach ( $d->childNodes as $c ) {
			if ( $c instanceof DOMElement && 'summary' !== $c->nodeName ) { $a .= gwl_c_text( $c ) . ' '; }
		}
		if ( '' !== $q ) { $tabs[] = array( '_id' => gwl_id(), 'tab_title' => $q, 'tab_content' => '<p>' . trim( $a ) . '</p>' ); }
	}
	if ( ! $tabs ) { return null; }
	return gwl_widget( 'accordion', array(
		'tabs' => $tabs,
		'selected_icon' => array( 'value' => 'fas fa-plus', 'library' => 'fa-solid' ),
		'selected_active_icon' => array( 'value' => 'fas fa-minus', 'library' => 'fa-solid' ),
		'title_color' => '#5C1620', 'tab_active_color' => '#5C1620',
		'icon_color' => '#DBA845', 'content_color' => '#5A5049', 'border_color' => '#E6DDD3',
		'title_typography_typography' => 'custom',
		'title_typography_font_family' => 'Cormorant Garamond',
		'title_typography_font_weight' => '600',
		'title_typography_font_size' => array( 'unit' => 'px', 'size' => 21 ),
		'content_typography_typography' => 'custom',
		'content_typography_font_family' => 'Jost',
		'content_typography_font_size' => array( 'unit' => 'px', 'size' => 16 ),
		'content_typography_line_height' => array( 'unit' => 'em', 'size' => 1.7 ),
	) );
}

/** A .card from the source becomes a bordered container. */
function gwl_c_card( DOMElement $card, $offset = 0 ) {
	$kids = array();
	$img = $card->getElementsByTagName( 'img' )->item( 0 );
	$link = '';
	foreach ( $card->getElementsByTagName( 'a' ) as $a ) {
		if ( gwl_c_has( $a, 'card-link' ) ) { $link = gwl_c_href( $a->getAttribute( 'href' ) ); }
	}
	if ( $img ) { $kids[] = gwl_c_image_widget( $img->getAttribute( 'src' ), $img->getAttribute( 'alt' ) ); }

	$body = array();
	$xp = new DOMXPath( $card->ownerDocument );
	foreach ( $xp->query( './/*[contains(@class,"card-tag") or contains(@class,"post-meta")]', $card ) as $tag ) {
		$body[] = gwl_eyebrow( gwl_c_text( $tag ), 'left' );
		break;
	}
	$h3 = $card->getElementsByTagName( 'h3' )->item( 0 );
	if ( $h3 ) {
		$o = array( 'tag' => 'h3', 'align' => 'left', 'size' => 25 );
		if ( $link ) { $o['link'] = $link; }
		$body[] = gwl_heading( gwl_c_text( $h3 ), $o );
	}
	foreach ( $card->getElementsByTagName( 'p' ) as $p ) {
		$t = gwl_c_text( $p );
		if ( '' !== $t ) { $body[] = gwl_text( '<p>' . $t . '</p>', array( 'align' => 'left', 'size' => 16 ) ); }
		break;
	}
	foreach ( $card->getElementsByTagName( 'a' ) as $a ) {
		if ( gwl_c_has( $a, 'card-link' ) ) {
			$body[] = gwl_button( str_replace( array( '→', '&rarr;' ), '', gwl_c_text( $a ) ), gwl_c_href( $a->getAttribute( 'href' ) ), 'outline_dark', 'left' );
			break;
		}
	}
	$kids[] = gwl_container( $body, array( 'width' => 'full', 'gap' => 10, 'inner' => true, 'pad' => gwl_pad( 26, 26, 30, 26 ) ) );

	$o = array( 'width' => 'full', 'gap' => 0, 'bg' => '#FFFFFF', 'border_color' => '#E6DDD3', 'grow' => 1, 'basis' => 33.5 );
	if ( $offset ) {
		$o['extra'] = array(
			'margin' => array( 'unit' => 'px', 'top' => (string) $offset, 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => '' ),
			'margin_mobile' => gwl_pad( 0, 0, 0, 0 ),
		);
	}
	return gwl_container( $kids, $o );
}
