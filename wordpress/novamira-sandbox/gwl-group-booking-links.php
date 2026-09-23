<?php
/**
 * Plugin Name: Gateway Lodge Group booking links
 * Description: Points every Book Now button on the group site at the group's
 *              STAAH booking engine. Idempotent: running it again reports
 *              nothing to change. Run with gwl_group_booking_links( false ) for
 *              a dry run, gwl_group_booking_links() to write.
 *
 * The buttons live inside Elementor's _elementor_data, so there is nothing in
 * the page tree to regenerate: this is the repeatable edit that replaces
 * opening each page in Elementor by hand.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** The calls to action that are the booking button, wherever they appear. */
function gwl_gbl_is_booking_cta( $text ) {
	$t = trim( wp_strip_all_tags( (string) $text ) );
	return in_array(
		strtolower( $t ),
		array( 'book now', 'book your stay', 'check availability', 'book direct' ),
		true
	);
}

function gwl_gbl_booking_url() {
	return defined( 'GWL_GROUP_BOOKING_URL' )
		? GWL_GROUP_BOOKING_URL
		: 'https://www.swiftbook.io/inst/#group?groupId=202NTbXvK6b2PKOg2OTI=&JDRN=Y';
}

/**
 * Rewrite one element tree in place. Returns the number of links changed.
 */
function gwl_gbl_walk( &$elements, $url, &$log, $title ) {
	$changed = 0;

	foreach ( $elements as &$el ) {
		if ( isset( $el['elType'] ) && 'widget' === $el['elType'] ) {
			$s = isset( $el['settings'] ) ? $el['settings'] : array();

			// Elementor's button uses text/link; Xpro and the other addons
			// keep to the same names often enough to be worth checking both.
			$labels = array();
			foreach ( array( 'text', 'button_text', 'title_text' ) as $k ) {
				if ( isset( $s[ $k ] ) ) {
					$labels[] = $s[ $k ];
				}
			}

			$is_cta = false;
			foreach ( $labels as $label ) {
				if ( gwl_gbl_is_booking_cta( $label ) ) {
					$is_cta = true;
					break;
				}
			}

			if ( $is_cta ) {
				foreach ( array( 'link', 'button_link' ) as $k ) {
					if ( ! isset( $s[ $k ] ) || ! is_array( $s[ $k ] ) ) {
						continue;
					}
					$was = isset( $s[ $k ]['url'] ) ? $s[ $k ]['url'] : '';
					if ( $was === $url && 'on' === ( isset( $s[ $k ]['is_external'] ) ? $s[ $k ]['is_external'] : '' ) ) {
						continue;
					}
					$s[ $k ]['url']         = $url;
					$s[ $k ]['is_external'] = 'on';
					$s[ $k ]['nofollow']    = isset( $s[ $k ]['nofollow'] ) ? $s[ $k ]['nofollow'] : '';
					$el['settings']         = $s;
					$log[]                  = sprintf(
						'%s / %s [%s] %s -> %s',
						$title,
						isset( $el['widgetType'] ) ? $el['widgetType'] : '?',
						isset( $el['id'] ) ? $el['id'] : '?',
						'' === $was ? '(no link)' : $was,
						$url
					);
					$changed++;
				}
			}
		}

		if ( ! empty( $el['elements'] ) ) {
			$changed += gwl_gbl_walk( $el['elements'], $url, $log, $title );
		}
	}
	unset( $el );

	return $changed;
}

/**
 * @param bool $write false to report without saving.
 */
function gwl_group_booking_links( $write = true ) {
	global $wpdb;

	$url  = gwl_gbl_booking_url();
	$log  = array();
	$hits = 0;

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
		$data = json_decode( $row['data'], true );
		if ( ! is_array( $data ) ) {
			continue;
		}

		$changed = gwl_gbl_walk( $data, $url, $log, $row['post_title'] . ' (#' . $row['ID'] . ')' );
		if ( ! $changed ) {
			continue;
		}
		$hits += $changed;

		if ( $write ) {
			// Elementor stores this slashed, exactly as its own save does:
			// update_metadata unslashes whatever it is handed, so JSON that
			// is not slashed first comes back out with its escapes stripped.
			update_metadata( 'post', (int) $row['ID'], '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
			if ( class_exists( '\\Elementor\\Plugin' ) ) {
				\Elementor\Plugin::$instance->files_manager->clear_cache();
			}
		}
	}

	return array(
		'url'     => $url,
		'write'   => (bool) $write,
		'changed' => $hits,
		'log'     => $log,
	);
}
