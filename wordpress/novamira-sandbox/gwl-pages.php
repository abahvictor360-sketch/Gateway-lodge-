<?php
/** Gateway Lodge Group : page-level section helpers. */
require_once __DIR__ . '/gwl-builder.php';

function gwl_xl() { return gwl_pad( 104, 0, 104, 0 ); }
function gwl_xlm() { return gwl_pad( 60, 0, 60, 0 ); }
function gwl_secopts( $bg = null ) {
	$o = array( 'width' => 'boxed', 'gap' => 0, 'pad' => gwl_xl(), 'extra' => array( 'padding_mobile' => gwl_xlm() ) );
	if ( $bg ) { $o['width'] = 'full'; $o['bg'] = $bg; }
	return $o;
}

/** Compact page hero with a background image. */
function gwl_hero( $imgkey, $eyebrow, $title, $sub = '' ) {
	$k = array( gwl_eyebrow( $eyebrow, 'left' ) );
	$k[] = gwl_heading( $title, array( 'tag' => 'h1', 'align' => 'left', 'size' => 58, 'size_m' => 34, 'color' => '#FFFFFF', 'lh' => 1.08 ) );
	if ( $sub ) {
		$k[] = gwl_text( '<p>' . $sub . '</p>', array( 'align' => 'left', 'color' => 'rgba(255,255,255,0.88)', 'size' => 18, 'maxw' => 620 ) );
	}
	return gwl_container(
		array( gwl_container( $k, array( 'width' => 'boxed', 'gap' => 12, 'align' => 'flex-start' ) ) ),
		array( 'width' => 'full', 'bgimg' => $imgkey, 'overlay' => '#201411', 'overlay_op' => 0.58, 'minh' => 52, 'justify' => 'center', 'pad' => gwl_pad( 96, 0, 76, 0 ) )
	);
}

/** Centred intro block. */
function gwl_intro( $eyebrow, $title, $body, $bg = null ) {
	return gwl_container(
		array( gwl_section_head( $eyebrow, $title, $body ) ),
		gwl_secopts( $bg )
	);
}

/** Feature grid section. */
function gwl_features_section( $eyebrow, $title, $features, $bg = null, $lede = '' ) {
	$rows = array();
	foreach ( $features as $f ) { $rows[] = gwl_feature( $f[0], $f[1], $f[2] ); }
	$inner = array( gwl_section_head( $eyebrow, $title, $lede ), gwl_container( $rows, array( 'width' => 'full', 'dir' => 'row', 'gap' => 22, 'align' => 'stretch', 'extra' => array( 'flex_wrap' => 'wrap' ) ) ) );
	if ( $bg ) {
		return gwl_container( array( gwl_container( $inner, array( 'width' => 'boxed', 'gap' => 0 ) ) ), array( 'width' => 'full', 'bg' => $bg, 'pad' => gwl_xl(), 'extra' => array( 'padding_mobile' => gwl_xlm() ) ) );
	}
	return gwl_container( $inner, gwl_secopts() );
}

/** Checklist section. */
function gwl_list_section( $eyebrow, $title, $items, $bg = null, $lede = '' ) {
	$inner = array( gwl_section_head( $eyebrow, $title, $lede ), gwl_container( array( gwl_icon_list( $items ) ), array( 'width' => 'full', 'maxw' => 880, 'gap' => 0 ) ) );
	if ( $bg ) {
		return gwl_container( array( gwl_container( $inner, array( 'width' => 'boxed', 'gap' => 0, 'align' => 'center' ) ) ), array( 'width' => 'full', 'bg' => $bg, 'pad' => gwl_xl(), 'extra' => array( 'padding_mobile' => gwl_xlm() ) ) );
	}
	return gwl_container( $inner, array_merge( gwl_secopts(), array( 'align' => 'center' ) ) );
}

/** Image + text split. $side = 'left'|'right' for the image. */
function gwl_split( $imgkey, $eyebrow, $title, $body, $btn = null, $side = 'left', $bg = null, $items = array() ) {
	$textkids = array();
	if ( $eyebrow ) { $textkids[] = gwl_eyebrow( $eyebrow, 'left' ); }
	$textkids[] = gwl_slash( 'left' );
	$textkids[] = gwl_heading( $title, array( 'tag' => 'h2', 'align' => 'left', 'size' => 42 ) );
	$textkids[] = gwl_text( '<p>' . $body . '</p>', array( 'align' => 'left', 'size' => 17 ) );
	if ( $items ) { $textkids[] = gwl_icon_list( $items ); }
	if ( $btn ) { $textkids[] = gwl_button( $btn[0], $btn[1], 'outline_dark', 'left' ); }
	$media = gwl_container( array( gwl_image( $imgkey ) ), array( 'width' => 'full', 'gap' => 0, 'basis' => 50, 'grow' => 1 ) );
	$text = gwl_container( $textkids, array( 'width' => 'full', 'gap' => 12, 'basis' => 50, 'grow' => 1, 'justify' => 'center' ) );
	$kids = ( 'left' === $side ) ? array( $media, $text ) : array( $text, $media );
	$o = array( 'width' => 'boxed', 'dir' => 'row', 'gap' => 52, 'align' => 'center', 'pad' => gwl_xl(), 'extra' => array( 'padding_mobile' => gwl_xlm(), 'flex_wrap' => 'wrap' ) );
	if ( $bg ) {
		return gwl_container( array( gwl_container( $kids, array( 'width' => 'boxed', 'dir' => 'row', 'gap' => 52, 'align' => 'center', 'extra' => array( 'flex_wrap' => 'wrap' ) ) ) ), array( 'width' => 'full', 'bg' => $bg, 'pad' => gwl_xl(), 'extra' => array( 'padding_mobile' => gwl_xlm() ) ) );
	}
	return gwl_container( $kids, $o );
}

/** Card row. $cards = array(array(imgkey, meta, title, body, url, linktext)) */
function gwl_cards_section( $eyebrow, $title, $cards, $bg = null, $lede = '' ) {
	$row = array();
	$i = 0;
	foreach ( $cards as $c ) {
		$row[] = gwl_card( $c[0], $c[1], $c[2], $c[3], $c[4], $c[5], 40 * $i );
		$i++;
	}
	$inner = array( gwl_section_head( $eyebrow, $title, $lede ), gwl_container( $row, array( 'width' => 'full', 'dir' => 'row', 'gap' => 28, 'align' => 'flex-start', 'extra' => array( 'flex_wrap' => 'wrap' ) ) ) );
	if ( $bg ) {
		return gwl_container( array( gwl_container( $inner, array( 'width' => 'boxed', 'gap' => 0 ) ) ), array( 'width' => 'full', 'bg' => $bg, 'pad' => gwl_xl(), 'extra' => array( 'padding_mobile' => gwl_xlm() ) ) );
	}
	return gwl_container( $inner, gwl_secopts() );
}

/** Closing call to action band. */
function gwl_cta( $eyebrow, $title, $body, $btn_text, $btn_url, $dark = true ) {
	$k = array();
	if ( $eyebrow ) { $k[] = gwl_eyebrow( $eyebrow ); }
	$k[] = gwl_slash();
	$k[] = gwl_heading( $title, array( 'tag' => 'h2', 'size' => 42, 'color' => $dark ? '#FFFFFF' : '#5C1620' ) );
	if ( $body ) { $k[] = gwl_text( '<p>' . $body . '</p>', array( 'size' => 17, 'color' => $dark ? 'rgba(255,255,255,0.78)' : '#5A5049', 'maxw' => 680 ) ); }
	$k[] = gwl_button( $btn_text, $btn_url, $dark ? 'gold' : 'primary' );
	return gwl_container(
		array( gwl_container( $k, array( 'width' => 'boxed', 'gap' => 10, 'align' => 'center' ) ) ),
		array( 'width' => 'full', 'bg' => $dark ? '#4A1119' : '#F7F3EE', 'pad' => gwl_xl(), 'extra' => array( 'padding_mobile' => gwl_xlm() ) )
	);
}

/** FAQ accordion using the core nested-accordion-free 'accordion' widget. */
function gwl_faq_section( $eyebrow, $title, $pairs, $bg = null ) {
	$tabs = array();
	foreach ( $pairs as $p ) {
		$tabs[] = array( '_id' => gwl_id(), 'tab_title' => $p[0], 'tab_content' => '<p>' . $p[1] . '</p>' );
	}
	$acc = gwl_widget( 'accordion', array(
		'tabs' => $tabs,
		'selected_icon' => array( 'value' => 'fas fa-plus', 'library' => 'fa-solid' ),
		'selected_active_icon' => array( 'value' => 'fas fa-minus', 'library' => 'fa-solid' ),
		'title_color' => '#5C1620',
		'tab_active_color' => '#5C1620',
		'icon_color' => '#DBA845',
		'content_color' => '#5A5049',
		'border_color' => '#E6DDD3',
		'title_typography_typography' => 'custom',
		'title_typography_font_family' => 'Cormorant Garamond',
		'title_typography_font_weight' => '600',
		'title_typography_font_size' => array( 'unit' => 'px', 'size' => 21 ),
		'content_typography_typography' => 'custom',
		'content_typography_font_family' => 'Jost',
		'content_typography_font_size' => array( 'unit' => 'px', 'size' => 16 ),
		'content_typography_line_height' => array( 'unit' => 'em', 'size' => 1.7 ),
	) );
	$inner = array( gwl_section_head( $eyebrow, $title ), gwl_container( array( $acc ), array( 'width' => 'full', 'maxw' => 880, 'gap' => 0 ) ) );
	if ( $bg ) {
		return gwl_container( array( gwl_container( $inner, array( 'width' => 'boxed', 'gap' => 0, 'align' => 'center' ) ) ), array( 'width' => 'full', 'bg' => $bg, 'pad' => gwl_xl(), 'extra' => array( 'padding_mobile' => gwl_xlm() ) ) );
	}
	return gwl_container( $inner, array_merge( gwl_secopts(), array( 'align' => 'center' ) ) );
}

function gwl_publish( $slug, $elements ) {
	$ids = get_option( 'gwl_page_ids', array() );
	if ( empty( $ids[ $slug ] ) ) { return 'missing page ' . $slug; }
	gwl_save_elementor( $ids[ $slug ], $elements );
	update_post_meta( $ids[ $slug ], '_wp_page_template', 'elementor_header_footer' );
	return $ids[ $slug ];
}

function gwl_url( $slug ) {
	$ids = get_option( 'gwl_page_ids', array() );
	return isset( $ids[ $slug ] ) ? get_permalink( $ids[ $slug ] ) : home_url( '/' );
}
