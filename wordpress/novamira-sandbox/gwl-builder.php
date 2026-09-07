<?php
/**
 * Gateway Lodge Group : Elementor container/widget builder helpers.
 * Native Elementor containers + core/XPRO widgets only. No inner sections,
 * no atomic elements, no HTML widgets for layout.
 */

function gwl_id() { static $n = 0; $n++; return substr( md5( 'gwl' . $n . microtime() ), 0, 7 ); }

function gwl_media( $key ) {
	$m = get_option( 'gwl_media_map', array() );
	if ( empty( $m[ $key ] ) ) { return array( 'url' => '', 'id' => '' ); }
	return array( 'url' => wp_get_attachment_url( $m[ $key ] ), 'id' => $m[ $key ] );
}

function gwl_pad( $t, $r, $b, $l ) {
	return array( 'unit' => 'px', 'top' => (string) $t, 'right' => (string) $r, 'bottom' => (string) $b, 'left' => (string) $l, 'isLinked' => '' );
}

/** A container. $o keys: bg, bgimg, overlay, width(boxed|full), dir, gap, pad, align, cols, minh, id */
function gwl_container( $children, $o = array() ) {
	// A helper that has nothing to render returns null; drop those rather than
	// letting a null land in the element tree.
	$children = array_values( array_filter( (array) $children ) );
	$s = array(
		'content_width' => isset( $o['width'] ) ? $o['width'] : 'boxed',
		'flex_direction' => isset( $o['dir'] ) ? $o['dir'] : 'column',
	);
	if ( isset( $o['gap'] ) ) { $s['flex_gap'] = array( 'unit' => 'px', 'size' => $o['gap'], 'column' => (string) $o['gap'], 'row' => (string) $o['gap'] ); }
	if ( isset( $o['pad'] ) ) { $s['padding'] = $o['pad']; }
	if ( isset( $o['bg'] ) ) { $s['background_background'] = 'classic'; $s['background_color'] = $o['bg']; }
	if ( isset( $o['bgimg'] ) ) {
		$img = gwl_media( $o['bgimg'] );
		$s['background_background'] = 'classic';
		$s['background_image'] = array( 'url' => $img['url'], 'id' => $img['id'], 'size' => '' );
		$s['background_position'] = 'center center';
		$s['background_size'] = 'cover';
		$s['background_repeat'] = 'no-repeat';
	}
	if ( isset( $o['overlay'] ) ) {
		$s['background_overlay_background'] = 'classic';
		$s['background_overlay_color'] = $o['overlay'];
		$s['background_overlay_opacity'] = array( 'unit' => 'px', 'size' => isset( $o['overlay_op'] ) ? $o['overlay_op'] : 0.55 );
	}
	if ( isset( $o['align'] ) ) { $s['flex_align_items'] = $o['align']; }
	if ( isset( $o['justify'] ) ) { $s['flex_justify_content'] = $o['justify']; }
	if ( isset( $o['minh'] ) ) { $s['min_height'] = array( 'unit' => 'vh', 'size' => $o['minh'] ); }
	// A capped-width block is a flex child of a column container, so it also has to
	// align itself to the centre; without this it sits hard left of the section.
	if ( isset( $o['maxw'] ) ) {
		$s['content_width'] = 'full';
		$s['width'] = array( 'unit' => 'px', 'size' => $o['maxw'] );
		$s['_flex_align_self'] = 'center';
	}
	// Containers size themselves as flex children through `width`, not a flex-basis
	// control (Elementor has none). A few points are shaved off the requested share
	// so the row's gap still fits and the items do not wrap.
	if ( isset( $o['basis'] ) ) {
		$s['content_width'] = 'full';
		$s['width'] = array( 'unit' => '%', 'size' => max( 10, $o['basis'] - 2.5 ) );
		$s['width_tablet'] = array( 'unit' => '%', 'size' => max( 10, $o['basis'] - 2.5 ) );
		$s['width_mobile'] = array( 'unit' => '%', 'size' => 100 );
		$s['_flex_size'] = 'custom';
		$s['_flex_grow'] = isset( $o['grow'] ) ? $o['grow'] : 1;
		$s['_flex_shrink'] = 1;
	} elseif ( isset( $o['grow'] ) ) {
		$s['_flex_size'] = 'custom';
		$s['_flex_grow'] = $o['grow'];
		$s['_flex_shrink'] = 1;
	}
	if ( isset( $o['border_color'] ) ) { $s['border_border'] = 'solid'; $s['border_width'] = array( 'unit' => 'px', 'top' => '1', 'right' => '1', 'bottom' => '1', 'left' => '1', 'isLinked' => '1' ); $s['border_color'] = $o['border_color']; }
	if ( isset( $o['extra'] ) ) { $s = array_merge( $s, $o['extra'] ); }
	if ( isset( $o['cls'] ) ) { $s['_css_classes'] = $o['cls']; }
	return array( 'id' => gwl_id(), 'elType' => 'container', 'settings' => $s, 'elements' => $children, 'isInner' => ! empty( $o['inner'] ) );
}

function gwl_widget( $type, $settings ) {
	return array( 'id' => gwl_id(), 'elType' => 'widget', 'widgetType' => $type, 'settings' => $settings, 'elements' => array() );
}

function gwl_heading( $text, $o = array() ) {
	$s = array(
		'title' => $text,
		'header_size' => isset( $o['tag'] ) ? $o['tag'] : 'h2',
		'align' => isset( $o['align'] ) ? $o['align'] : 'center',
		'title_color' => isset( $o['color'] ) ? $o['color'] : '#5C1620',
		'typography_typography' => 'custom',
		'typography_font_family' => isset( $o['family'] ) ? $o['family'] : 'Cormorant Garamond',
		'typography_font_weight' => isset( $o['weight'] ) ? $o['weight'] : '600',
		'typography_line_height' => array( 'unit' => 'em', 'size' => isset( $o['lh'] ) ? $o['lh'] : 1.15 ),
	);
	if ( isset( $o['size'] ) ) { $s['typography_font_size'] = array( 'unit' => 'px', 'size' => $o['size'] ); $s['typography_font_size_mobile'] = array( 'unit' => 'px', 'size' => isset( $o['size_m'] ) ? $o['size_m'] : max( 26, (int) round( $o['size'] * 0.55 ) ) ); }
	if ( isset( $o['ls'] ) ) { $s['typography_letter_spacing'] = array( 'unit' => 'em', 'size' => $o['ls'] ); }
	if ( isset( $o['tt'] ) ) { $s['typography_text_transform'] = $o['tt']; }
	if ( isset( $o['margin'] ) ) { $s['_margin'] = $o['margin']; }
	if ( isset( $o['link'] ) ) { $s['link'] = array( 'url' => $o['link'], 'is_external' => '', 'nofollow' => '' ); }
	if ( isset( $o['maxw'] ) ) { $s['_element_custom_width'] = array( 'unit' => 'px', 'size' => $o['maxw'] ); }
	return gwl_widget( 'heading', $s );
}

function gwl_eyebrow( $text, $align = 'center' ) {
	return gwl_heading( $text, array( 'tag' => 'div', 'align' => $align, 'color' => '#DBA845', 'family' => 'Jost', 'weight' => '600', 'size' => 12, 'size_m' => 11, 'ls' => 0.26, 'tt' => 'uppercase', 'lh' => 1.4 ) );
}

/** The brand section mark: a gold slash. */
function gwl_slash( $align = 'center' ) {
	return gwl_heading( '/', array( 'tag' => 'div', 'align' => $align, 'color' => '#DBA845', 'family' => 'Cormorant Garamond', 'weight' => '300', 'size' => 40, 'size_m' => 34, 'lh' => 1, 'margin' => array( 'unit' => 'px', 'top' => '6', 'right' => '0', 'bottom' => '6', 'left' => '0', 'isLinked' => '' ) ) );
}

function gwl_text( $html, $o = array() ) {
	$s = array(
		'editor' => $html,
		'align' => isset( $o['align'] ) ? $o['align'] : 'center',
		'text_color' => isset( $o['color'] ) ? $o['color'] : '#5A5049',
		'typography_typography' => 'custom',
		'typography_font_family' => 'Jost',
		'typography_font_size' => array( 'unit' => 'px', 'size' => isset( $o['size'] ) ? $o['size'] : 17 ),
		'typography_line_height' => array( 'unit' => 'em', 'size' => 1.75 ),
		'typography_font_weight' => '400',
	);
	if ( isset( $o['maxw'] ) ) { $s['_element_custom_width'] = array( 'unit' => 'px', 'size' => $o['maxw'] ); }
	if ( isset( $o['margin'] ) ) { $s['_margin'] = $o['margin']; }
	return gwl_widget( 'text-editor', $s );
}

/** style: primary | outline_light | outline_dark | gold */
function gwl_button( $text, $url, $style = 'primary', $align = 'center' ) {
	$s = array(
		'text' => $text,
		'link' => array( 'url' => $url, 'is_external' => '', 'nofollow' => '' ),
		'align' => $align,
		'typography_typography' => 'custom',
		'typography_font_family' => 'Jost',
		'typography_font_weight' => '600',
		'typography_text_transform' => 'uppercase',
		'typography_letter_spacing' => array( 'unit' => 'em', 'size' => 0.16 ),
		'typography_font_size' => array( 'unit' => 'px', 'size' => 12 ),
		'border_radius' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => '1' ),
		'text_padding' => gwl_pad( 17, 34, 17, 34 ),
		'border_border' => 'solid',
		'border_width' => array( 'unit' => 'px', 'top' => '1', 'right' => '1', 'bottom' => '1', 'left' => '1', 'isLinked' => '1' ),
	);
	if ( 'primary' === $style ) {
		$s['background_color'] = '#5C1620'; $s['button_text_color'] = '#FFFFFF'; $s['border_color'] = '#5C1620';
		$s['button_background_hover_color'] = '#4A1119'; $s['button_hover_border_color'] = '#4A1119'; $s['hover_color'] = '#FFFFFF';
	} elseif ( 'gold' === $style ) {
		$s['background_color'] = '#DBA845'; $s['button_text_color'] = '#4A1119'; $s['border_color'] = '#DBA845';
		$s['button_background_hover_color'] = '#ECC989'; $s['button_hover_border_color'] = '#ECC989'; $s['hover_color'] = '#4A1119';
	} elseif ( 'outline_light' === $style ) {
		$s['background_color'] = 'rgba(0,0,0,0)'; $s['button_text_color'] = '#FFFFFF'; $s['border_color'] = '#FFFFFF';
		$s['button_background_hover_color'] = '#FFFFFF'; $s['hover_color'] = '#5C1620'; $s['button_hover_border_color'] = '#FFFFFF';
	} else {
		$s['background_color'] = 'rgba(0,0,0,0)'; $s['button_text_color'] = '#5C1620'; $s['border_color'] = '#5C1620';
		$s['button_background_hover_color'] = '#5C1620'; $s['hover_color'] = '#FFFFFF'; $s['button_hover_border_color'] = '#5C1620';
	}
	return gwl_widget( 'button', $s );
}

function gwl_image( $key, $o = array() ) {
	$img = gwl_media( $key );
	$s = array(
		'image' => array( 'url' => $img['url'], 'id' => $img['id'], 'size' => '' ),
		'image_size' => 'large',
		'align' => 'center',
		'image_border_radius' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => '1' ),
	);
	if ( isset( $o['ratio'] ) ) {
		$s['_element_custom_width'] = array( 'unit' => '%', 'size' => 100 );
	}
	if ( isset( $o['link'] ) ) { $s['link_to'] = 'custom'; $s['link'] = array( 'url' => $o['link'], 'is_external' => '', 'nofollow' => '' ); }
	if ( isset( $o['cls'] ) ) { $s['_css_classes'] = $o['cls']; }
	return gwl_widget( 'image', $s );
}

/** Standard centred section header: eyebrow (optional), slash, h2, lede (optional). */
function gwl_section_head( $eyebrow, $title, $lede = '', $align = 'center' ) {
	$k = array();
	if ( $eyebrow ) { $k[] = gwl_eyebrow( $eyebrow, $align ); }
	$k[] = gwl_slash( $align );
	$k[] = gwl_heading( $title, array( 'tag' => 'h2', 'align' => $align, 'size' => 46 ) );
	if ( $lede ) { $k[] = gwl_text( '<p>' . $lede . '</p>', array( 'align' => $align, 'size' => 18 ) ); }
	return gwl_container( $k, array( 'width' => 'full', 'gap' => 6, 'align' => 'center' === $align ? 'center' : 'flex-start', 'maxw' => 760, 'pad' => gwl_pad( 0, 0, 26, 0 ) ) );
}

/** A card: image, meta, title, body, link. */
function gwl_card( $imgkey, $meta, $title, $body, $link, $linktext, $offset = 0 ) {
	$k = array( gwl_image( $imgkey, array( 'link' => $link ) ) );
	$inner = array();
	if ( $meta ) { $inner[] = gwl_eyebrow( $meta, 'left' ); }
	$inner[] = gwl_heading( $title, array( 'tag' => 'h3', 'align' => 'left', 'size' => 25, 'link' => $link ) );
	$inner[] = gwl_text( '<p>' . $body . '</p>', array( 'align' => 'left', 'size' => 16 ) );
	$inner[] = gwl_button( $linktext, $link, 'outline_dark', 'left' );
	$k[] = gwl_container( $inner, array( 'width' => 'full', 'gap' => 10, 'inner' => true, 'pad' => gwl_pad( 26, 26, 30, 26 ) ) );
	$o = array(
		'width' => 'full', 'gap' => 0, 'bg' => '#FFFFFF', 'border_color' => '#E6DDD3',
		'grow' => 1, 'basis' => 33.5,
	);
	if ( $offset ) { $o['extra'] = array( 'margin' => array( 'unit' => 'px', 'top' => (string) $offset, 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => '' ), 'margin_mobile' => gwl_pad( 0, 0, 0, 0 ) ); }
	return gwl_container( $k, $o );
}

/** Icon-box feature using the core icon-box widget. */
function gwl_feature( $icon, $title, $body ) {
	return gwl_widget( 'icon-box', array(
		'selected_icon' => array( 'value' => $icon, 'library' => 'fa-solid' ),
		'title_text' => $title,
		'description_text' => $body,
		'position' => 'top',
		'title_size' => 'h3',
		'primary_color' => '#DBA845',
		'title_color' => '#5C1620',
		'description_color' => '#5A5049',
		'title_typography_typography' => 'custom',
		'title_typography_font_family' => 'Cormorant Garamond',
		'title_typography_font_weight' => '600',
		'title_typography_font_size' => array( 'unit' => 'px', 'size' => 23 ),
		'description_typography_typography' => 'custom',
		'description_typography_font_family' => 'Jost',
		'description_typography_font_size' => array( 'unit' => 'px', 'size' => 16 ),
		'description_typography_line_height' => array( 'unit' => 'em', 'size' => 1.7 ),
		'icon_size' => array( 'unit' => 'px', 'size' => 30 ),
		'icon_space' => array( 'unit' => 'px', 'size' => 16 ),
		// The approved design centres these boxes with the icon above the title.
		'text_align' => 'center',
		'_padding' => gwl_pad( 30, 26, 30, 26 ),
		'_background_background' => 'classic',
		'_background_color' => '#FFFFFF',
		'_border_border' => 'solid',
		'_border_width' => array( 'unit' => 'px', 'top' => '1', 'right' => '1', 'bottom' => '1', 'left' => '1', 'isLinked' => '1' ),
		'_border_color' => '#E6DDD3',
		// Widgets size themselves with _element_custom_width, which requires
		// _element_width to be set to 'initial'.
		'_element_width' => 'initial',
		'_element_custom_width' => array( 'unit' => '%', 'size' => 31 ),
		'_element_custom_width_tablet' => array( 'unit' => '%', 'size' => 47 ),
		'_element_custom_width_mobile' => array( 'unit' => '%', 'size' => 100 ),
		'_flex_size' => 'custom',
		'_flex_grow' => 1,
		'_flex_shrink' => 1,
	) );
}

function gwl_icon_list( $items, $cols = 2 ) {
	$li = array();
	foreach ( $items as $t ) {
		$li[] = array( '_id' => gwl_id(), 'text' => $t, 'selected_icon' => array( 'value' => 'fas fa-check', 'library' => 'fa-solid' ) );
	}
	return gwl_widget( 'icon-list', array(
		'icon_list' => $li,
		'view' => 'traditional',
		'icon_color' => '#DBA845',
		'icon_size' => array( 'unit' => 'px', 'size' => 15 ),
		'text_color' => '#5A5049',
		'icon_typography_typography' => 'custom',
		'icon_typography_font_family' => 'Jost',
		'icon_typography_font_size' => array( 'unit' => 'px', 'size' => 16 ),
		'space_between' => array( 'unit' => 'px', 'size' => 14 ),
	) );
}

/** Saves an Elementor document. */
function gwl_save_elementor( $post_id, $elements, $doc_type = 'wp-page' ) {
	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post_id, '_elementor_template_type', $doc_type );
	update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $elements ) ) );

	// Elementor caches rendered markup in _elementor_element_cache. Writing data
	// directly does not invalidate it, so the frontend would keep serving the old
	// HTML with old element ids while the CSS is regenerated against the new ones,
	// leaving the page unstyled. Clear it on every save.
	delete_post_meta( $post_id, '_elementor_element_cache' );
	delete_post_meta( $post_id, '_elementor_page_assets' );

	if ( class_exists( '\\Elementor\\Plugin' ) ) {
		$css = \Elementor\Core\Files\CSS\Post::create( $post_id );
		$css->delete();
		$css = \Elementor\Core\Files\CSS\Post::create( $post_id );
		$css->update();
	}
	return true;
}

/** Creates or updates a page by slug. */
function gwl_page( $slug, $title, $parent = 0 ) {
	$p = get_page_by_path( $slug, OBJECT, 'page' );
	if ( $p ) {
		wp_update_post( array( 'ID' => $p->ID, 'post_title' => $title, 'post_status' => 'publish', 'post_parent' => $parent ) );
		return $p->ID;
	}
	return wp_insert_post( array(
		'post_type' => 'page', 'post_name' => $slug, 'post_title' => $title,
		'post_status' => 'publish', 'post_author' => 1, 'post_parent' => $parent, 'post_content' => '',
	) );
}
