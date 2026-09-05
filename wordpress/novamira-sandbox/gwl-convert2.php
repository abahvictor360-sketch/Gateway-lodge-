<?php
/**
 * Gateway Lodge Group: section walker and page renderer.
 * Companion to gwl-convert.php.
 */

require_once __DIR__ . '/gwl-convert.php';

/**
 * Walks a container's children in source order and emits matching widgets.
 */
function gwl_c_blocks( DOMElement $wrap, $align = 'center', $dark = false ) {
	$out = array();
	$ink  = $dark ? '#FFFFFF' : '#5C1620';
	$body = $dark ? 'rgba(255,255,255,0.78)' : '#5A5049';

	foreach ( $wrap->childNodes as $n ) {
		if ( ! ( $n instanceof DOMElement ) ) { continue; }
		$tag = strtolower( $n->nodeName );
		$cls = (string) $n->getAttribute( 'class' );

		if ( 'span' === $tag && ( false !== strpos( $cls, 'eyebrow' ) || false !== strpos( $cls, 'coming-soon-badge' ) ) ) {
			$out[] = gwl_eyebrow( gwl_c_text( $n ), $align );
			continue;
		}
		if ( false !== strpos( $cls, 'divider' ) && 'div' === $tag ) {
			$out[] = gwl_slash( $align );
			continue;
		}
		if ( preg_match( '/^h([1-4])$/', $tag, $m ) ) {
			$lvl  = (int) $m[1];
			$size = array( 1 => 58, 2 => 44, 3 => 25, 4 => 20 );
			$out[] = gwl_heading( gwl_c_text( $n ), array(
				'tag'    => $tag,
				'align'  => $align,
				'size'   => $size[ $lvl ],
				'size_m' => ( 1 === $lvl ) ? 34 : ( ( 2 === $lvl ) ? 28 : 20 ),
				'color'  => $ink,
			) );
			continue;
		}
		if ( 'p' === $tag ) {
			$inner = gwl_c_inner_html( $n );
			if ( '' === trim( wp_strip_all_tags( $inner ) ) ) { continue; }
			$btn = null;
			foreach ( $n->childNodes as $c ) {
				if ( $c instanceof DOMElement && 'a' === strtolower( $c->nodeName )
					&& false !== strpos( (string) $c->getAttribute( 'class' ), 'btn' ) ) { $btn = $c; }
			}
			if ( $btn && gwl_c_text( $btn ) === gwl_c_text( $n ) ) {
				$b = gwl_c_button( $btn );
				$out[] = gwl_button( $b[0], $b[1], $b[2], $align );
				continue;
			}
			$lede = false !== strpos( $cls, 'lede' );
			$note = false !== strpos( $cls, 'booking-note' );
			$out[] = gwl_text( '<p>' . $inner . '</p>', array(
				'align' => $align,
				'size'  => $lede ? 18 : ( $note ? 13 : 17 ),
				'color' => $note ? '#8A8078' : $body,
			) );
			continue;
		}
		if ( 'a' === $tag && false !== strpos( $cls, 'btn' ) ) {
			$b = gwl_c_button( $n );
			$out[] = gwl_button( $b[0], $b[1], $b[2], $align );
			continue;
		}
		if ( 'ul' === $tag && false !== strpos( $cls, 'icon-list' ) ) {
			$w = gwl_c_icon_list( $n );
			if ( $w ) { $out[] = $w; }
			continue;
		}
		if ( 'img' === $tag ) {
			$out[] = gwl_c_image_widget( $n->getAttribute( 'src' ), $n->getAttribute( 'alt' ) );
			continue;
		}
		if ( false !== strpos( $cls, 'map-embed' ) ) {
			$label = gwl_c_text( $n );
			$addr  = 'Accra, Ghana';
			if ( false !== stripos( $label, 'tamale' ) ) { $addr = 'Tamale, Northern Region, Ghana'; }
			elseif ( false !== stripos( $label, 'lakeside' ) ) { $addr = 'Lakeside Estate, Accra, Ghana'; }
			elseif ( false !== stripos( $label, 'drake' ) || false !== stripos( $label, 'ridge' ) ) { $addr = '4 Drake Avenue, Accra, Ghana'; }
			$out[] = gwl_widget( 'google_maps', array(
				'address' => $addr,
				'zoom'    => array( 'unit' => 'px', 'size' => 14 ),
				'height'  => array( 'unit' => 'px', 'size' => 420 ),
				'css_filters_css_filter' => 'custom',
				'css_filters_saturate'   => array( 'unit' => '%', 'size' => 70 ),
			) );
			continue;
		}
		if ( false !== strpos( $cls, 'faq-list' ) ) {
			$w = gwl_c_faq( $n );
			if ( $w ) { $out[] = $w; }
			continue;
		}
		if ( false !== strpos( $cls, 'card-grid' ) ) {
			$cards = array(); $i = 0;
			foreach ( $n->childNodes as $c ) {
				if ( $c instanceof DOMElement && false !== strpos( (string) $c->getAttribute( 'class' ), 'card' ) ) {
					$cards[] = gwl_c_card( $c, 40 * $i ); $i++;
				}
			}
			if ( $cards ) {
				$out[] = gwl_container( $cards, array( 'width' => 'full', 'dir' => 'row', 'gap' => 28, 'align' => 'flex-start', 'extra' => array( 'flex_wrap' => 'wrap' ) ) );
			}
			continue;
		}
		if ( false !== strpos( $cls, 'feature-grid' ) ) {
			$feats = array();
			$xp = new DOMXPath( $n->ownerDocument );
			foreach ( $xp->query( './*[contains(@class,"feature")]', $n ) as $f ) {
				$h = $f->getElementsByTagName( 'h3' )->item( 0 );
				$p = $f->getElementsByTagName( 'p' )->item( 0 );
				$title = gwl_c_text( $h );
				if ( '' === $title ) { continue; }
				$feats[] = gwl_feature( gwl_c_icon( $title ), $title, gwl_c_text( $p ) );
			}
			if ( $feats ) {
				$out[] = gwl_container( $feats, array( 'width' => 'full', 'dir' => 'row', 'gap' => 22, 'align' => 'stretch', 'extra' => array( 'flex_wrap' => 'wrap' ) ) );
			}
			continue;
		}
		if ( false !== strpos( $cls, 'image-strip' ) ) {
			$imgs = array();
			foreach ( $n->getElementsByTagName( 'img' ) as $im ) {
				$imgs[] = gwl_container(
					array( gwl_c_image_widget( $im->getAttribute( 'src' ), $im->getAttribute( 'alt' ) ) ),
					array( 'width' => 'full', 'gap' => 0, 'basis' => 33.5 )
				);
			}
			if ( $imgs ) {
				$out[] = gwl_container( $imgs, array( 'width' => 'full', 'dir' => 'row', 'gap' => 20, 'align' => 'stretch', 'extra' => array( 'flex_wrap' => 'wrap' ) ) );
			}
			continue;
		}
		if ( false !== strpos( $cls, 'booking-panel' ) ) {
			$out[] = gwl_widget( 'shortcode', array( 'shortcode' => '[gateway_booking]' ) );
			continue;
		}
		if ( 'form' === $tag ) {
			$fid = get_option( 'gwl_form_id' );
			if ( $fid ) { $out[] = gwl_widget( 'shortcode', array( 'shortcode' => '[wpforms id="' . $fid . '"]' ) ); }
			continue;
		}
		if ( false !== strpos( $cls, 'property-strip' ) || false !== strpos( $cls, 'social-icons' ) ) {
			continue;
		}
		if ( in_array( $tag, array( 'div', 'section', 'article', 'ul', 'nav' ), true ) ) {
			$a2 = ( false !== strpos( $cls, 'text-center' ) ) ? 'center' : $align;
			$sub = gwl_c_blocks( $n, $a2, $dark );
			if ( $sub ) { $out = array_merge( $out, $sub ); }
		}
	}
	return $out;
}

/** Converts one <section> element into a top level Elementor container. */
function gwl_c_section( DOMElement $sec ) {
	$cls  = (string) $sec->getAttribute( 'class' );
	$dark = false !== strpos( $cls, 'section-dark' );
	$bg   = null;
	if ( false !== strpos( $cls, 'section-alt' ) )  { $bg = '#F7F3EE'; }
	if ( false !== strpos( $cls, 'section-sand' ) ) { $bg = '#ECE3D8'; }
	if ( $dark ) { $bg = '#4A1119'; }

	$xp = new DOMXPath( $sec->ownerDocument );
	$pad   = gwl_pad( 104, 0, 104, 0 );
	$padm  = gwl_pad( 60, 0, 60, 0 );

	/* ---- hero ---- */
	if ( false !== strpos( $cls, 'hero' ) ) {
		$compact = false !== strpos( $cls, 'hero-compact' );
		$kids = array();
		foreach ( $xp->query( './/*[contains(@class,"hero-content")]', $sec ) as $hc ) {
			foreach ( $hc->childNodes as $n ) {
				if ( ! ( $n instanceof DOMElement ) ) { continue; }
				$t   = strtolower( $n->nodeName );
				$c2  = (string) $n->getAttribute( 'class' );
				if ( false !== strpos( $c2, 'hero-eyebrow' ) ) { $kids[] = gwl_eyebrow( gwl_c_text( $n ), 'left' ); continue; }
				if ( 'h1' === $t ) {
					$kids[] = gwl_heading( gwl_c_text( $n ), array( 'tag' => 'h1', 'align' => 'left', 'size' => $compact ? 58 : 72, 'size_m' => 34, 'color' => '#FFFFFF', 'lh' => 1.06 ) );
					continue;
				}
				if ( false !== strpos( $c2, 'hero-sub' ) ) {
					$kids[] = gwl_text( '<p>' . gwl_c_text( $n ) . '</p>', array( 'align' => 'left', 'color' => 'rgba(255,255,255,0.88)', 'size' => 18, 'maxw' => 620 ) );
					continue;
				}
				if ( false !== strpos( $c2, 'btn-row' ) ) {
					$btns = array();
					foreach ( $n->getElementsByTagName( 'a' ) as $a ) {
						$b = gwl_c_button( $a );
						$btns[] = gwl_button( $b[0], $b[1], $b[2], 'left' );
					}
					if ( $btns ) {
						$kids[] = gwl_container( $btns, array( 'width' => 'full', 'dir' => 'row', 'gap' => 14, 'extra' => array( 'flex_wrap' => 'wrap', '_flex_size' => 'none' ) ) );
					}
					continue;
				}
				if ( 'p' === $t ) {
					$kids[] = gwl_text( '<p>' . gwl_c_text( $n ) . '</p>', array( 'align' => 'left', 'color' => 'rgba(255,255,255,0.88)', 'size' => 18, 'maxw' => 620 ) );
				}
			}
		}
		$o = array(
			'width' => 'full', 'overlay' => '#201411', 'overlay_op' => 0.56,
			'minh' => $compact ? 52 : 88, 'justify' => $compact ? 'center' : 'flex-end',
			'pad' => $compact ? gwl_pad( 96, 0, 76, 0 ) : gwl_pad( 120, 0, 96, 0 ),
		);
		$con = gwl_container( array( gwl_container( $kids, array( 'width' => 'boxed', 'gap' => 14, 'align' => 'flex-start' ) ) ), $o );

		// poster / video
		$vid = $sec->getElementsByTagName( 'video' )->item( 0 );
		if ( $vid ) {
			$poster = $vid->getAttribute( 'poster' );
			$pid = gwl_c_att_id( $poster );
			if ( $pid ) {
				$con['settings']['background_background'] = 'classic';
				$con['settings']['background_image'] = array( 'url' => wp_get_attachment_url( $pid ), 'id' => $pid, 'size' => '' );
				$con['settings']['background_position'] = 'center center';
				$con['settings']['background_size'] = 'cover';
			}
			$src = $vid->getElementsByTagName( 'source' )->item( 0 );
			if ( $src ) {
				$vid_id = gwl_c_att_id( $src->getAttribute( 'src' ) );
				if ( $vid_id ) {
					$con['settings']['background_background'] = 'video';
					$con['settings']['background_video_link'] = wp_get_attachment_url( $vid_id );
					if ( $pid ) {
						$con['settings']['background_video_fallback'] = array( 'url' => wp_get_attachment_url( $pid ), 'id' => $pid, 'size' => '' );
					}
				}
			}
		}
		return $con;
	}

	/* ---- split (image beside text) ---- */
	// The wrapper is the element that actually holds a .split-body child.
	// Matching on the class alone wrongly caught "split-bleed" and skipped it.
	$split = null;
	foreach ( $xp->query( './/*[contains(@class,"split")]', $sec ) as $s ) {
		$has_body = false;
		foreach ( $s->childNodes as $c ) {
			if ( $c instanceof DOMElement ) {
				$cc = (string) $c->getAttribute( 'class' );
				if ( false !== strpos( $cc, 'split-body' ) || false !== strpos( $cc, 'split-media' ) ) { $has_body = true; }
			}
		}
		if ( ! $has_body ) { continue; }
		$split = $s; break;
	}
	if ( $split ) {
		$reverse = false !== strpos( (string) $split->getAttribute( 'class' ), 'reverse' );
		$media = null; $bodyEl = null;
		foreach ( $split->childNodes as $c ) {
			if ( ! ( $c instanceof DOMElement ) ) { continue; }
			$cc = (string) $c->getAttribute( 'class' );
			if ( false !== strpos( $cc, 'split-media' ) ) { $media = $c; }
			if ( false !== strpos( $cc, 'split-body' ) )  { $bodyEl = $c; }
		}
		$mediaKids = $media ? gwl_c_blocks( $media, 'center', $dark ) : array();
		$bodyKids  = $bodyEl ? gwl_c_blocks( $bodyEl, 'left', $dark ) : array();
		$mCon = gwl_container( $mediaKids, array( 'width' => 'full', 'gap' => 0, 'basis' => 50 ) );
		$bCon = gwl_container( $bodyKids, array( 'width' => 'full', 'gap' => 12, 'basis' => 50, 'justify' => 'center' ) );
		$kids = $reverse ? array( $bCon, $mCon ) : array( $mCon, $bCon );
		$o = array( 'width' => 'boxed', 'dir' => 'row', 'gap' => 52, 'align' => 'center', 'pad' => $pad, 'extra' => array( 'padding_mobile' => $padm, 'flex_wrap' => 'wrap' ) );
		if ( $bg ) {
			return gwl_container(
				array( gwl_container( $kids, array( 'width' => 'boxed', 'dir' => 'row', 'gap' => 52, 'align' => 'center', 'extra' => array( 'flex_wrap' => 'wrap' ) ) ) ),
				array( 'width' => 'full', 'bg' => $bg, 'pad' => $pad, 'extra' => array( 'padding_mobile' => $padm ) )
			);
		}
		return gwl_container( $kids, $o );
	}

	/* ---- everything else: walk the container in order ---- */
	$align = ( false !== strpos( $cls, 'text-center' ) ) ? 'center' : 'center';
	$inner = null;
	foreach ( $xp->query( './*[contains(@class,"container")]', $sec ) as $c ) { $inner = $c; break; }
	$src = $inner ? $inner : $sec;
	$kids = gwl_c_blocks( $src, $align, $dark );
	if ( ! $kids ) { return null; }

	// section heads and prose get the reading-width cap
	$wrapped = array();
	$buffer  = array();
	$flush = function() use ( &$buffer, &$wrapped ) {
		if ( $buffer ) {
			$wrapped[] = gwl_container( $buffer, array( 'width' => 'full', 'gap' => 6, 'align' => 'center', 'maxw' => 760 ) );
			$buffer = array();
		}
	};
	foreach ( $kids as $k ) {
		$w = $k['widgetType'] ?? '';
		$isNarrow = in_array( $w, array( 'heading', 'text-editor' ), true );
		if ( $isNarrow ) { $buffer[] = $k; continue; }
		$flush();
		$wrapped[] = $k;
	}
	$flush();

	// Stacked blocks (an image above a heading, a grid under a lede) need a gap,
	// otherwise they sit flush against one another.
	$gap = 36;
	$o = array( 'width' => 'boxed', 'gap' => $gap, 'align' => 'center', 'pad' => $pad, 'extra' => array( 'padding_mobile' => $padm ) );
	if ( $bg ) {
		return gwl_container(
			array( gwl_container( $wrapped, array( 'width' => 'boxed', 'gap' => $gap, 'align' => 'center' ) ) ),
			array( 'width' => 'full', 'bg' => $bg, 'pad' => $pad, 'extra' => array( 'padding_mobile' => $padm ) )
		);
	}
	return gwl_container( $wrapped, $o );
}

/** Convert one source page into the matching WordPress page. */
function gwl_c_convert( $file, $slug ) {
	$ids = get_option( 'gwl_page_ids', array() );
	if ( empty( $ids[ $slug ] ) ) { return 'no page for ' . $slug; }

	$r = wp_remote_get( GWL_SRC . $file, array( 'timeout' => 25 ) );
	if ( is_wp_error( $r ) || 200 !== wp_remote_retrieve_response_code( $r ) ) {
		return 'fetch failed: ' . $file;
	}
	$html = wp_remote_retrieve_body( $r );

	$doc = new DOMDocument();
	libxml_use_internal_errors( true );
	$doc->loadHTML( '<?xml encoding="utf-8" ?>' . $html );
	libxml_clear_errors();
	$xp = new DOMXPath( $doc );

	$main = $xp->query( '//main[@id="main"]' )->item( 0 );
	if ( ! $main ) { return 'no <main> in ' . $file; }

	$elements = array();
	foreach ( $main->childNodes as $n ) {
		if ( ! ( $n instanceof DOMElement ) || 'section' !== strtolower( $n->nodeName ) ) { continue; }
		$sec = gwl_c_section( $n );
		if ( $sec ) { $elements[] = $sec; }
	}
	if ( ! $elements ) { return 'no sections built for ' . $slug; }

	gwl_save_elementor( $ids[ $slug ], $elements );
	update_post_meta( $ids[ $slug ], '_wp_page_template', 'elementor_header_footer' );
	return count( $elements );
}
