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
				// A scoped rule beats the sitewide list, so it goes first.
				array_unshift( $apply, array( $s[1], $s[2] ) );
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
