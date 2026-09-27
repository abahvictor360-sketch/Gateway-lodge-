<?php
/**
 * Plugin Name: Gateway Lodge Group review copy
 * Description: Applies the client's 27 September review to the group site's
 *              live copy: no onsite dining, restaurant or lounge anywhere, the
 *              swimming pool at Nova Ridge rather than Lakeside, and the fully
 *              equipped kitchen in its place. Idempotent - a second run reports
 *              nothing to change. gwl_grc_apply( false ) reports without writing.
 *
 * The wording lives in gwl-group-review-copy.json beside this file, generated
 * from the corrected static pages so the two cannot drift. Rules are applied
 * longest first, so a whole sentence is rewritten before any phrase inside it.
 * Rules whose text means different things on different pages are scoped to a
 * page slug rather than applied across the site.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gwl_grc_rules() {
	$file = __DIR__ . '/gwl-group-review-copy.json';
	if ( ! file_exists( $file ) ) {
		return array( 'global' => array(), 'scoped' => array() );
	}
	$data = json_decode( file_get_contents( $file ), true );
	return is_array( $data ) ? $data : array( 'global' => array(), 'scoped' => array() );
}

/**
 * The theme's own demo pages are still installed and share some wording with
 * ours, so the rules are held to the Gateway pages: Home, About Us, Contact,
 * and everything built for the group site.
 */
function gwl_grc_is_gateway_page( $id ) {
	return 10 === $id || 1455 === $id || 1999 === $id || $id >= 11060;
}

/** Rewrite every string in one element tree. Returns the number of replacements. */
function gwl_grc_walk( &$value, $rules, &$log, $title ) {
	$changed = 0;

	if ( is_array( $value ) ) {
		foreach ( $value as $k => &$v ) {
			$changed += gwl_grc_walk( $v, $rules, $log, $title );
		}
		unset( $v );
		return $changed;
	}

	if ( ! is_string( $value ) || '' === $value ) {
		return 0;
	}

	foreach ( $rules as $rule ) {
		list( $old, $new ) = $rule;
		if ( false === strpos( $value, $old ) ) {
			continue;
		}
		$n       = substr_count( $value, $old );
		$value   = str_replace( $old, $new, $value );
		$changed += $n;
		$log[]   = $title . ': ' . mb_substr( $old, 0, 48 );
	}

	return $changed;
}

/**
 * @param bool $write false to report without saving.
 */
function gwl_grc_apply( $write = true ) {
	global $wpdb;

	$rules  = gwl_grc_rules();
	$log    = array();
	$total  = 0;
	$pages  = array();

	$rows = $wpdb->get_results(
		"SELECT p.ID, p.post_title, p.post_name, m.meta_value AS data
		   FROM {$wpdb->postmeta} m
		   JOIN {$wpdb->posts} p ON p.ID = m.post_id
		  WHERE m.meta_key = '_elementor_data'
		    AND p.post_status IN ( 'publish', 'draft' )
		    AND p.post_type <> 'revision'",
		ARRAY_A
	);

	foreach ( $rows as $row ) {
		if ( ! gwl_grc_is_gateway_page( (int) $row['ID'] ) ) {
			continue;
		}
		$data = json_decode( $row['data'], true );
		if ( ! is_array( $data ) ) {
			continue;
		}

		$apply = $rules['global'];
		foreach ( $rules['scoped'] as $s ) {
			if ( $s[0] === $row['post_name'] ) {
				// Scoped rules are short - a single word in some cases - so they
				// run last, after every sentence-length rule has had its turn.
				$apply[] = array( $s[1], $s[2] );
			}
		}

		$title   = $row['post_title'] . ' (#' . $row['ID'] . ')';
		$changed = gwl_grc_walk( $data, $apply, $log, $title );
		if ( ! $changed ) {
			continue;
		}
		$total   += $changed;
		$pages[]  = $title . ' x' . $changed;

		if ( $write ) {
			// Slashed, as Elementor's own save does: update_metadata unslashes.
			update_metadata( 'post', (int) $row['ID'], '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
		}
	}

	if ( $write && $total && class_exists( '\\Elementor\\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	return array(
		'write'   => (bool) $write,
		'rules'   => count( $rules['global'] ) + count( $rules['scoped'] ),
		'changed' => $total,
		'pages'   => $pages,
		'log'     => array_slice( $log, 0, 40 ),
	);
}

/**
 * Widgets whose wording the first run mangled, before the ordering above was
 * fixed: the scoped rules ran first and rewrote a word inside sentences that a
 * longer rule should have replaced whole. Setting them by widget id is exact,
 * where matching on the mangled text would not be - two of these phrases occur
 * twice on the same page, meaning different things.
 */
function gwl_grc_widget_text() {
	return array(
		// The kitchen page.
		'33604c9' => 'A Home Kitchen, Away From Home',
		'9655e23' => 'Lakeside',
		'2ad4066' => 'Nova Ridge',
		'176af34' => 'Tamale',
		'6ee868e' => 'Everything a Long Trip Needs',
		'b6e47fe' => 'What You Will Find',
		'c5aba00' => 'Gateway Lodge does not run a restaurant, and that is the point. A guest staying a week does not want a menu every evening; they want a fridge, a cooker and a table. Every unit has all three, which is what makes a long stay feel like living somewhere rather than visiting.',
		'9129b23' => 'Cooker and Hob',
		'b9f09ea' => 'Washing Machine',
		'32db2ca' => 'Shops Within Reach',

		// The eating out page.
		'dce679e' => 'Cook In, Whenever You Would Rather',

		// Contact. All three map widgets were set to "Accra, Ghana", so the
		// Tamale map showed Accra. Each takes its own address, and the lines
		// beneath them take the Ghana Post digital code with it.
		'847b228' => 'Nova by DevtracoPlus, Continental Road, Ridge, Accra, Ghana',
		'84de727' => '21 South Boundary Road, Lakeside Estate, Accra, Ghana',
		'cf03fa7' => 'QD55 Maidenhair Street, Watherston Residential Area, Tamale, Ghana',
		'71f5dea' => 'Nova by DevtracoPlus, Continental Road, Ridge, Accra<br>Digital address G4-061-8151',
		'1997f76' => '21 South Boundary Road, Lakeside Estate, Accra<br>Digital address GD-118-3802',
		'fc2a2b5' => 'QD55 Maidenhair Street, Watherston Residential Area, Tamale<br>Digital address NT-0065-2009',

		// Facilities. The three location tags were at three different levels
		// of detail - a district, a city, a region - so each takes the locality
		// its address gives it.
		'73b8e1b' => 'Lakeside Estate, Accra',
		'0be9b1e' => 'Watherston, Tamale',
	);
}

function gwl_grc_set_widget_text( $write = true ) {
	global $wpdb;

	$map    = gwl_grc_widget_text();
	$report = array();

	$rows = $wpdb->get_results(
		"SELECT p.ID, m.meta_value AS data
		   FROM {$wpdb->postmeta} m
		   JOIN {$wpdb->posts} p ON p.ID = m.post_id
		  WHERE m.meta_key = '_elementor_data'
		    AND p.post_status IN ( 'publish', 'draft' )
		    AND p.post_type <> 'revision'",
		ARRAY_A
	);

	foreach ( $rows as $row ) {
		if ( ! gwl_grc_is_gateway_page( (int) $row['ID'] ) ) {
			continue;
		}
		$data = json_decode( $row['data'], true );
		if ( ! is_array( $data ) ) {
			continue;
		}

		$changed = 0;
		$walk = function ( &$els ) use ( &$walk, $map, &$changed, &$report ) {
			foreach ( $els as &$el ) {
				if ( ! empty( $el['id'] ) && isset( $map[ $el['id'] ] ) && ! empty( $el['settings'] ) ) {
					$text = $map[ $el['id'] ];
					foreach ( array( 'title', 'title_text', 'editor', 'address' ) as $key ) {
						if ( ! isset( $el['settings'][ $key ] ) ) {
							continue;
						}
						$want = ( 'editor' === $key ) ? '<p>' . $text . '</p>' : $text;
						if ( $el['settings'][ $key ] === $want ) {
							break;
						}
						$el['settings'][ $key ] = $want;
						$report[] = $el['id'] . ' -> ' . mb_substr( $text, 0, 40 );
						$changed++;
						break;
					}
				}
				if ( ! empty( $el['elements'] ) ) {
					$walk( $el['elements'] );
				}
			}
			unset( $el );
		};
		$walk( $data );

		if ( $changed && $write ) {
			update_metadata( 'post', (int) $row['ID'], '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
		}
	}

	if ( $write && $report && class_exists( '\\Elementor\\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	return $report ? $report : array( 'nothing to change' );
}

/**
 * The reservations number, set rather than substituted.
 *
 * A plain find-and-replace is not safe here: the eight-digit form the review
 * first gave is a prefix of the nine-digit one, so running it twice would
 * append a digit. This pass parks the canonical value behind a token first, so
 * it lands on the same result whatever state it finds. It also reaches the
 * WhatsApp link inside the footer's social-icon repeater, which the widget map
 * above cannot address.
 */
function gwl_grc_reservations_number() {
	return array( 'digits' => '233504000000', 'display' => '+233 50 400 0000' );
}

function gwl_grc_set_phone( $write = true ) {
	global $wpdb;

	$num    = gwl_grc_reservations_number();
	$report = array();

	$rows = $wpdb->get_results(
		"SELECT p.ID, p.post_title, m.meta_value AS data
		   FROM {$wpdb->postmeta} m
		   JOIN {$wpdb->posts} p ON p.ID = m.post_id
		  WHERE m.meta_key = '_elementor_data'
		    AND p.post_status IN ( 'publish', 'draft' )
		    AND p.post_type <> 'revision'",
		ARRAY_A
	);

	foreach ( $rows as $row ) {
		if ( ! gwl_grc_is_gateway_page( (int) $row['ID'] ) ) {
			continue;
		}
		$before = $row['data'];
		$after  = $before;

		// The old number is a prefix of the new one, so a plain replace would
		// append a digit on a second run. Parking the canonical value behind a
		// token first makes the pass idempotent whatever state it finds.
		$token = 'GWLRESERVATIONSNUMBER';

		$after = str_replace( $num['digits'], $token, $after );
		foreach ( array( '23350400000', '233240000000' ) as $old ) {
			$after = str_replace( $old, $token, $after );
		}
		$after = str_replace( $token, $num['digits'], $after );

		$after = str_replace( $num['display'], $token, $after );
		$after = str_replace( '+233 50 400 000', $num['display'], $after );
		$after = str_replace( $token, $num['display'], $after );

		if ( $after === $before ) {
			continue;
		}
		$report[] = $row['post_title'] . ' (#' . $row['ID'] . ')';
		if ( $write ) {
			update_metadata( 'post', (int) $row['ID'], '_elementor_data', wp_slash( $after ) );
		}
	}

	if ( $write && $report && class_exists( '\\Elementor\\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	return $report ? $report : array( 'nothing to change' );
}

/**
 * The Dining category becomes the kitchen, and Dining Experiences becomes
 * eating out: page titles, slugs and the menu entries that point at them.
 */
function gwl_grc_rename( $write = true ) {
	$map = array(
		'dining' => array(
			'slug'  => 'kitchen',
			'title' => 'Fully Equipped Kitchen',
			'menu'  => 'Fully Equipped Kitchen',
		),
		'dining-experiences' => array(
			'slug'  => 'eating-out',
			'title' => 'Eating Out',
			'menu'  => 'Eating Out',
		),
	);

	$report = array();

	foreach ( $map as $old_slug => $to ) {
		$page = get_page_by_path( $old_slug );
		if ( ! $page ) {
			$page = get_page_by_path( $to['slug'] );
			$report[ $old_slug ] = $page ? 'already renamed (#' . $page->ID . ')' : 'not found';
			if ( ! $page ) {
				continue;
			}
		} elseif ( $write ) {
			wp_update_post( array(
				'ID'         => $page->ID,
				'post_name'  => $to['slug'],
				'post_title' => $to['title'],
			) );
			$report[ $old_slug ] = 'renamed #' . $page->ID . ' -> ' . $to['slug'];
		} else {
			$report[ $old_slug ] = 'would rename #' . $page->ID . ' -> ' . $to['slug'];
			continue;
		}

		// Menu entries are custom links on this site, so both the label and the
		// URL have to move with the page.
		$items = get_posts( array(
			'post_type'   => 'nav_menu_item',
			'numberposts' => -1,
			'post_status' => 'any',
		) );
		foreach ( $items as $item ) {
			$url = get_post_meta( $item->ID, '_menu_item_url', true );
			if ( ! $url || false === strpos( $url, '/' . $old_slug . '/' ) ) {
				continue;
			}
			if ( ! $write ) {
				$report[ 'menu:' . $item->ID ] = 'would repoint ' . $url;
				continue;
			}
			update_post_meta( $item->ID, '_menu_item_url', str_replace( '/' . $old_slug . '/', '/' . $to['slug'] . '/', $url ) );
			wp_update_post( array( 'ID' => $item->ID, 'post_title' => $to['menu'] ) );
			$report[ 'menu:' . $item->ID ] = 'repointed to /' . $to['slug'] . '/';
		}
	}

	return $report;
}
