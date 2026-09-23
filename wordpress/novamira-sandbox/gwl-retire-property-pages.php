<?php
/**
 * Plugin Name: Gateway Lodge retire the group property pages
 * Description: Moves the group site's three property pages to the trash, now
 *              that each property has its own landing site. Idempotent: a
 *              second run reports nothing left to do. Run with
 *              gwl_retire_property_pages_run( false ) for a dry run.
 *
 * Trash, not delete: the pages are recoverable from wp-admin for as long as
 * WordPress keeps them, and nothing links to them either way. Their old URLs
 * are 301'd to the subdomains by gateway-lodge-site.php, which matches on the
 * request path and so keeps working once the pages are gone.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param bool $write false to report without trashing.
 */
function gwl_retire_property_pages_run( $write = true ) {
	global $wpdb;

	$map    = function_exists( 'gwl_retired_property_pages' )
		? gwl_retired_property_pages()
		: array(
			'gateway-nova-ridge'     => 'https://novaridge.gatewaylodgegroup.com/',
			'gateway-lodge-lakeside' => 'https://lakeside.gatewaylodgegroup.com/',
			'gateway-lodge-tamale'   => 'https://tamale.gatewaylodgegroup.com/',
		);
	$report = array( 'write' => (bool) $write, 'pages' => array(), 'links_left' => array() );

	foreach ( $map as $slug => $target ) {
		$page = get_page_by_path( $slug );
		if ( ! $page ) {
			$report['pages'][ $slug ] = 'gone';
			continue;
		}
		if ( 'trash' === $page->post_status ) {
			$report['pages'][ $slug ] = 'already trashed (#' . $page->ID . ')';
			continue;
		}
		if ( ! $write ) {
			$report['pages'][ $slug ] = 'would trash #' . $page->ID . ' (' . $page->post_status . ')';
			continue;
		}
		wp_trash_post( $page->ID );
		$report['pages'][ $slug ] = 'trashed #' . $page->ID . ' -> ' . $target;
	}

	// Anything still pointing at a retired page is a dead link the moment the
	// page goes, so say so rather than leaving it to be found on the site.
	foreach ( array_keys( $map ) as $slug ) {
		$hits = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.ID, p.post_type, p.post_title
				   FROM {$wpdb->postmeta} m
				   JOIN {$wpdb->posts} p ON p.ID = m.post_id
				  WHERE m.meta_key = '_elementor_data'
				    AND p.post_status IN ( 'publish', 'draft' )
				    AND p.post_type <> 'revision'
				    AND m.meta_value LIKE %s",
				'%' . $wpdb->esc_like( $slug ) . '%'
			),
			ARRAY_A
		);
		if ( $hits ) {
			$report['links_left'][ $slug ] = $hits;
		}
	}

	return $report;
}
