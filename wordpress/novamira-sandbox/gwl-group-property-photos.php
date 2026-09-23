<?php
/**
 * Plugin Name: Gateway Lodge Group property photographs
 * Description: Puts the three properties' own photographs on the group site in
 *              place of the stock imagery, wherever the group site shows a
 *              property. Idempotent: a second run reports nothing to change.
 *              gwl_gpp_apply( false ) reports without writing.
 *
 * The photographs already exist, on each property's own landing site, so they
 * are sideloaded from there rather than uploaded again: one source of truth for
 * a photograph, and the group site's copy carries the same description the
 * landing page gives it.
 *
 * Only the places where an image IS a property are swapped. The same stock
 * files are used elsewhere on the site as generic illustration - an offer, a
 * contact page, a page about the area - and those are a separate question.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** key => [ landing site, file stem, alt text ]. The alt text is the landing page's own. */
function gwl_gpp_photos() {
	return array(
		'nr-balcony-skyline' => array( 'novaridge', 'balcony-skyline', 'Balcony seating overlooking Accra at Gateway Nova Ridge' ),
		'nr-hero-living'     => array( 'novaridge', 'hero-living', 'Living room and entrance hall at Gateway Nova Ridge' ),
		'nr-bedroom-king'    => array( 'novaridge', 'bedroom-king', 'King bedroom with upholstered headboard at Gateway Nova Ridge' ),
		'nr-kitchen-wide'    => array( 'novaridge', 'kitchen-wide', 'Full fitted kitchen at Gateway Nova Ridge' ),
		'nr-balcony'         => array( 'novaridge', 'balcony', 'Balcony and glass balustrade at Gateway Nova Ridge' ),

		'lk-exterior-frontage' => array( 'lakeside', 'exterior-frontage', 'The frontage of Gateway Lodge Lakeside' ),
		'lk-exterior-approach' => array( 'lakeside', 'exterior-approach', 'Approach to Gateway Lodge Lakeside' ),
		'lk-bedroom-king'      => array( 'lakeside', 'bedroom-king', 'King bedroom at Gateway Lodge Lakeside' ),
		'lk-kitchen-wide'      => array( 'lakeside', 'kitchen-wide', 'Fitted kitchen at Gateway Lodge Lakeside' ),
		'lk-living-open'       => array( 'lakeside', 'living-open', 'Open-plan living area at Gateway Lodge Lakeside' ),

		'tm-hero-courtyard'  => array( 'tamale', 'hero-courtyard', 'Courtyard and two-storey frontage at Gateway Lodge Tamale' ),
		'tm-exterior-street' => array( 'tamale', 'exterior-street', 'Street view of Gateway Lodge Tamale' ),
		'tm-bedroom-king'    => array( 'tamale', 'bedroom-king', 'Bedroom with teal curtains at Gateway Lodge Tamale' ),
		'tm-kitchen'         => array( 'tamale', 'kitchen', 'Fitted kitchen with cooker and washing machine at Gateway Lodge Tamale' ),
		'tm-living-open'     => array( 'tamale', 'living-open', 'Open living room with chandelier at Gateway Lodge Tamale' ),
	);
}

/**
 * Which stock attachment becomes which photograph, per page.
 *
 * Page-scoped because the same stock file plays two parts: on the cards it is
 * the property, in the gallery strip it is one frame of four.
 */
function gwl_gpp_swaps() {
	$cards = array(
		11027 => 'nr-balcony-skyline',
		11028 => 'lk-exterior-frontage',
		11029 => 'tm-hero-courtyard',
	);

	return array(
		10    => $cards, // Home
		1455  => $cards, // About Us
		11064 => $cards, // Our Properties
		11069 => $cards, // Stay
		11070 => $cards, // Accommodation
	);
}

/**
 * Rules pinned to one widget, for pages where the same file plays two parts.
 *
 * The gallery page shows the three property strips first and then reuses the
 * same stock files further down under headings that are not a property -
 * parking, breakfast service, an event. Matching on the file would catch those
 * too, so the twelve frames of the property strips are named outright. Each
 * strip reads exterior, bedroom, kitchen, living; Nova Ridge has no exterior in
 * its shoot, so its first frame is the living room and entrance hall.
 */
function gwl_gpp_widget_swaps() {
	return array(
		11083 => array(
			'553ebf0' => 'nr-hero-living',
			'74b2d85' => 'nr-bedroom-king',
			'c552023' => 'nr-kitchen-wide',
			'db690e4' => 'nr-balcony',

			'483eb42' => 'lk-exterior-approach',
			'671144d' => 'lk-bedroom-king',
			'8c4e156' => 'lk-kitchen-wide',
			'c496a8b' => 'lk-living-open',

			'5c2d75a' => 'tm-exterior-street',
			'477e495' => 'tm-bedroom-king',
			'54f14be' => 'tm-kitchen',
			'7c095bd' => 'tm-living-open',
		),
	);
}

/** The landing site's copy of a photograph, full size where one is served. */
function gwl_gpp_source_url( $slug, $stem ) {
	$base = 'https://' . $slug . '.gatewaylodgegroup.com/wp-content/uploads/2026/09/';
	foreach ( array( '.jpg', '.jpeg', '.png' ) as $ext ) {
		$url = $base . $stem . $ext;
		$head = wp_remote_head( $url, array( 'timeout' => 20, 'sslverify' => false ) );
		if ( ! is_wp_error( $head ) && 200 === wp_remote_retrieve_response_code( $head ) ) {
			return $url;
		}
	}
	return '';
}

/**
 * Sideload each photograph into the group site's media library, once.
 *
 * @return array key => attachment id or an error string.
 */
function gwl_gpp_import() {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$ids = get_option( 'gwl_gpp_ids', array() );
	if ( ! is_array( $ids ) ) {
		$ids = array();
	}

	foreach ( gwl_gpp_photos() as $key => $photo ) {
		list( $slug, $stem, $alt ) = $photo;

		if ( isset( $ids[ $key ] ) && get_post( $ids[ $key ] ) ) {
			continue;
		}

		$url = gwl_gpp_source_url( $slug, $stem );
		if ( '' === $url ) {
			$ids[ $key ] = 'no source for ' . $slug . '/' . $stem;
			continue;
		}

		// A stem such as bedroom-king exists for all three properties, so the
		// group site's copy is named for the property it belongs to.
		$tmp = download_url( $url, 60 );
		if ( is_wp_error( $tmp ) ) {
			$ids[ $key ] = $tmp->get_error_message();
			continue;
		}

		$file = array(
			'name'     => $key . '.' . pathinfo( wp_parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ),
			'tmp_name' => $tmp,
		);
		$id = media_handle_sideload( $file, 0, $alt );
		if ( is_wp_error( $id ) ) {
			@unlink( $tmp );
			$ids[ $key ] = $id->get_error_message();
			continue;
		}

		update_post_meta( $id, '_wp_attachment_image_alt', $alt );
		wp_update_post( array( 'ID' => $id, 'post_title' => $alt ) );
		$ids[ $key ] = (int) $id;
	}

	update_option( 'gwl_gpp_ids', $ids );
	return $ids;
}

/** Swap one element tree in place. Returns the number of images changed. */
function gwl_gpp_walk( &$elements, $map, $widgets, &$log, $title ) {
	$changed = 0;

	foreach ( $elements as &$el ) {
		if ( ! empty( $el['settings'] ) && is_array( $el['settings'] ) ) {
			$el_id = isset( $el['id'] ) ? $el['id'] : '';

			foreach ( $el['settings'] as $control => $value ) {
				if ( ! is_array( $value ) || ! isset( $value['id'] ) || ! isset( $value['url'] ) ) {
					continue;
				}

				$old = (int) $value['id'];

				// A widget named outright wins; otherwise the file decides.
				if ( isset( $widgets[ $el_id ] ) ) {
					$new = $widgets[ $el_id ];
				} elseif ( isset( $map[ $old ] ) ) {
					$new = $map[ $old ];
				} else {
					continue;
				}

				if ( $old === (int) $new['id'] ) {
					continue; // already carries this photograph
				}

				$el['settings'][ $control ]['id']  = $new['id'];
				$el['settings'][ $control ]['url'] = $new['url'];
				$log[] = sprintf( '%s / %s [%s] %d -> %d (%s)', $title, $control, '' === $el_id ? '?' : $el_id, $old, $new['id'], $new['key'] );
				$changed++;
			}
		}

		if ( ! empty( $el['elements'] ) ) {
			$changed += gwl_gpp_walk( $el['elements'], $map, $widgets, $log, $title );
		}
	}
	unset( $el );

	return $changed;
}

/**
 * @param bool $write false to report without saving.
 */
function gwl_gpp_apply( $write = true ) {
	$ids    = gwl_gpp_import();
	$errors = array_filter( $ids, 'is_string' );
	if ( $errors ) {
		return array( 'import_errors' => $errors );
	}

	$log   = array();
	$total = 0;

	$by_file   = gwl_gpp_swaps();
	$by_widget = gwl_gpp_widget_swaps();

	foreach ( array_unique( array_merge( array_keys( $by_file ), array_keys( $by_widget ) ) ) as $page_id ) {
		$rules = isset( $by_file[ $page_id ] ) ? $by_file[ $page_id ] : array();
		$raw = get_post_meta( $page_id, '_elementor_data', true );
		if ( is_array( $raw ) ) {
			$raw = wp_json_encode( $raw );
		}
		$data = json_decode( $raw, true );
		if ( ! is_array( $data ) ) {
			$log[] = 'no elementor data on #' . $page_id;
			continue;
		}

		$map = array();
		foreach ( $rules as $old_id => $key ) {
			$new_id = isset( $ids[ $key ] ) ? (int) $ids[ $key ] : 0;
			if ( ! $new_id ) {
				continue;
			}
			$map[ (int) $old_id ] = array(
				'id'  => $new_id,
				'url' => wp_get_attachment_url( $new_id ),
				'key' => $key,
			);
		}

		$widgets = array();
		foreach ( ( isset( $by_widget[ $page_id ] ) ? $by_widget[ $page_id ] : array() ) as $widget_id => $key ) {
			$new_id = isset( $ids[ $key ] ) ? (int) $ids[ $key ] : 0;
			if ( ! $new_id ) {
				continue;
			}
			$widgets[ $widget_id ] = array(
				'id'  => $new_id,
				'url' => wp_get_attachment_url( $new_id ),
				'key' => $key,
			);
		}

		$title   = get_the_title( $page_id ) . ' (#' . $page_id . ')';
		$changed = gwl_gpp_walk( $data, $map, $widgets, $log, $title );
		if ( ! $changed ) {
			continue;
		}
		$total += $changed;

		if ( $write ) {
			// Slashed, exactly as Elementor's own save does: update_metadata
			// unslashes whatever it is handed.
			update_metadata( 'post', (int) $page_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
		}
	}

	if ( $write && $total && class_exists( '\\Elementor\\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	return array(
		'write'   => (bool) $write,
		'photos'  => $ids,
		'changed' => $total,
		'log'     => $log,
	);
}
