<?php
/**
 * Site-Wide Settings "Icons" tab: lets an admin upload an SVG straight into
 * img/icons/ (where get_icon_choices()/get_icon() in inc/utilities.php
 * already look for them), sanitised on the way in, with no Media Library
 * residue — the upload field is a one-time drop zone, not persistent
 * storage, so the file it produces is what's actually committed to the repo.
 *
 * @package lc-skeleton2026
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render a grid of every icon currently in img/icons/ — the "message"
 * field's content is just raw HTML, so this builds it directly rather than
 * templating. Reuses get_icon_choices()/get_icon() from inc/utilities.php
 * (loaded after this file, but that's fine — this only runs later, from
 * the acf/load_field hook below, by which point every inc/ file has
 * already been required) instead of re-globbing img/icons/ itself.
 *
 * SVG markup is echoed as-is, same trust model as get_icon() on the
 * frontend — anything in img/icons/ already went through
 * lc_skeleton_sanitise_svg_markup() on the way in via this same file, or
 * was placed there directly by a developer, so it isn't re-sanitised here.
 *
 * @return string HTML grid, or a plain "no icons yet" message.
 */
function lc_skeleton_render_icon_grid() {
	$choices = get_icon_choices();

	if ( ! $choices ) {
		return '<p>No icons in img/icons/ yet — upload one below.</p>';
	}

	$items = '';
	foreach ( array_keys( $choices ) as $slug ) {
		$items .= '<div style="width:100px;text-align:center;">'
			. '<div style="width:100px;height:100px;display:flex;align-items:center;justify-content:center;box-sizing:border-box;border:1px solid #dcdcde;border-radius:4px;background:#fff;padding:10px;">'
			. get_icon( $slug ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already-sanitised theme-owned SVG, same trust model as get_icon()'s frontend usage.
			. '</div>'
			. '<div style="margin-top:4px;font-size:11px;word-break:break-all;">' . esc_html( $slug ) . '</div>'
			. '</div>';
	}

	return '<div style="display:flex;flex-wrap:wrap;gap:16px;">' . $items . '</div>'
		. '<style>#acf-field_lc_ss_icons_preview svg{max-width:80px;max-height:80px;width:100%;height:100%;}</style>';
}
/*
 * Not using acf/load_field to set $field['message'] here: the message
 * field type's own render_field() always pipes its output through
 * acf_esc_html(), which is wp_kses() against $allowedposttags (WordPress's
 * standard post-content allowlist) — svg/path/circle/style etc. aren't in
 * it, so the grid would render as empty boxes with the <style> tag's CSS
 * left behind as bare text (its own tags stripped, no text content to
 * strip from the svg elements). This is unconditional in ACF core, not
 * something the field's own esc_html/new_lines settings can turn off.
 *
 * acf/render_field/key=... is a separate action ACF fires for the same
 * field (see acf_add_action_variations() in acf-field-functions.php) —
 * hooking it here adds a second, independent echo that bypasses the
 * message field type's render_field()/acf_esc_html() path entirely, so
 * this output is never kses'd.
 */
add_action(
	'acf/render_field/key=field_lc_ss_icons_preview',
	function () {
		echo lc_skeleton_render_icon_grid(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- deliberately bypassing acf_esc_html()'s wp_kses('acf'), see comment above; content is our own already-sanitised SVGs plus hardcoded layout markup, not user input.
	}
);

/**
 * SVG uploads are disabled in WordPress core by default. Rather than a
 * blanket upload_mimes filter (which would let SVGs be uploaded anywhere
 * in wp-admin for as long as this theme is active), this only allows it
 * for this one ACF field's own upload request, identified by field key.
 *
 * @param array $mimes Allowed mime types, keyed by extension.
 * @return array
 */
function lc_skeleton_allow_icon_svg_upload( $mimes ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- reading which ACF field is uploading, not acting on submitted data.
	if ( isset( $_POST['field_key'] ) && 'field_lc_ss_icon_upload' === $_POST['field_key'] ) {
		$mimes['svg'] = 'image/svg+xml';
	}
	return $mimes;
}
add_filter( 'upload_mimes', 'lc_skeleton_allow_icon_svg_upload' );

/**
 * wp_check_filetype_and_ext() cross-checks a file's real content against
 * its claimed mime type and doesn't know about svg by default — without
 * this, the upload would still be rejected ("does not match extension")
 * even with the mime type allowed above.
 *
 * @param array  $data     Filetype/extension/mime data.
 * @param string $file     Full path to the uploaded file.
 * @param string $filename Original filename.
 * @param array  $mimes    Allowed mime types.
 * @return array
 */
function lc_skeleton_allow_icon_svg_filetype( $data, $file, $filename, $mimes ) {
	if ( isset( $mimes['svg'] ) && preg_match( '/\.svg$/i', $filename ) ) {
		$data['ext']  = 'svg';
		$data['type'] = 'image/svg+xml';
	}
	return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'lc_skeleton_allow_icon_svg_filetype', 10, 4 );

/**
 * Sanitise raw SVG markup down to a safe element/attribute allowlist via
 * wp_kses() — strips <script>, event handler attributes, and anything else
 * not explicitly listed. Allowlist adapted from lc-eternal2025's
 * lc_sanitise_svg(); this is just the sanitisation core, not that
 * function's attachment-resolution/attribute-injection parts, since here
 * the icon is sanitised once at upload time before it's written to disk,
 * not re-processed on every frontend render.
 *
 * @param string $svg_markup Raw SVG markup.
 * @return string Sanitised SVG markup.
 */
function lc_skeleton_sanitise_svg_markup( $svg_markup ) {
	$svg_markup = preg_replace( '/<\?xml.*?\?>/i', '', (string) $svg_markup );

	$allowed_svg_tags = array(
		'svg'            => array(
			'aria-hidden'         => true,
			'class'               => true,
			'data-name'           => true,
			'fill'                => true,
			'focusable'           => true,
			'height'              => true,
			'id'                  => true,
			'preserveaspectratio' => true,
			'role'                => true,
			'stroke'              => true,
			'stroke-width'        => true,
			'viewbox'             => true,
			'width'               => true,
			'xmlns'               => true,
			'xmlns:xlink'         => true,
		),
		'g'              => array(
			'class'             => true,
			'clip-path'         => true,
			'data-name'         => true,
			'fill'              => true,
			'fill-rule'         => true,
			'id'                => true,
			'mask'              => true,
			'opacity'           => true,
			'stroke'            => true,
			'stroke-linecap'    => true,
			'stroke-linejoin'   => true,
			'stroke-miterlimit' => true,
			'stroke-width'      => true,
			'transform'         => true,
		),
		'path'           => array(
			'class'             => true,
			'clip-rule'         => true,
			'd'                 => true,
			'fill'              => true,
			'fill-rule'         => true,
			'opacity'           => true,
			'stroke'            => true,
			'stroke-linecap'    => true,
			'stroke-linejoin'   => true,
			'stroke-miterlimit' => true,
			'stroke-width'      => true,
			'transform'         => true,
		),
		'circle'         => array(
			'class'        => true,
			'cx'           => true,
			'cy'           => true,
			'fill'         => true,
			'opacity'      => true,
			'r'            => true,
			'stroke'       => true,
			'stroke-width' => true,
		),
		'ellipse'        => array(
			'class'        => true,
			'cx'           => true,
			'cy'           => true,
			'fill'         => true,
			'opacity'      => true,
			'rx'           => true,
			'ry'           => true,
			'stroke'       => true,
			'stroke-width' => true,
		),
		'line'           => array(
			'class'          => true,
			'stroke'         => true,
			'stroke-linecap' => true,
			'stroke-width'   => true,
			'x1'             => true,
			'x2'             => true,
			'y1'             => true,
			'y2'             => true,
		),
		'polygon'        => array(
			'class'        => true,
			'fill'         => true,
			'opacity'      => true,
			'points'       => true,
			'stroke'       => true,
			'stroke-width' => true,
		),
		'polyline'       => array(
			'class'        => true,
			'fill'         => true,
			'opacity'      => true,
			'points'       => true,
			'stroke'       => true,
			'stroke-width' => true,
		),
		'rect'           => array(
			'class'        => true,
			'fill'         => true,
			'height'       => true,
			'opacity'      => true,
			'rx'           => true,
			'ry'           => true,
			'stroke'       => true,
			'stroke-width' => true,
			'width'        => true,
			'x'            => true,
			'y'            => true,
		),
		'defs'           => array(),
		'style'          => array(
			'type' => true,
		),
		'clippath'       => array(
			'id' => true,
		),
		'mask'           => array(
			'height'    => true,
			'id'        => true,
			'maskunits' => true,
			'width'     => true,
			'x'         => true,
			'y'         => true,
		),
		'lineargradient' => array(
			'id' => true,
			'x1' => true,
			'x2' => true,
			'y1' => true,
			'y2' => true,
		),
		'radialgradient' => array(
			'cx' => true,
			'cy' => true,
			'fx' => true,
			'fy' => true,
			'id' => true,
			'r'  => true,
		),
		'stop'           => array(
			'offset'       => true,
			'stop-color'   => true,
			'stop-opacity' => true,
		),
		'title'          => array(),
		'desc'           => array(),
		'use'            => array(
			'height'     => true,
			'href'       => true,
			'width'      => true,
			'x'          => true,
			'xlink:href' => true,
			'y'          => true,
		),
	);

	$svg_markup = wp_kses( $svg_markup, $allowed_svg_tags );

	// Single-colour icons with no fill anywhere in the source (e.g. Simple
	// Icons' brand marks) don't otherwise respect the surrounding text
	// colour — SVG's fill defaults to black regardless of CSS context.
	// Injecting fill="currentColor" on the root <svg> makes them behave
	// like Lucide's own stroke="currentColor" icons already do. Only when
	// nothing already specifies a fill: a genuinely multi-colour icon that
	// hardcodes different fills per path is left alone rather than
	// flattened to one colour.
	if ( false === strpos( $svg_markup, 'fill=' ) ) {
		$svg_markup = preg_replace( '/<svg\b/i', '<svg fill="currentColor"', $svg_markup, 1 );
	}

	return $svg_markup;
}

/**
 * On Site-Wide Settings save, take whatever was dropped into the icon
 * upload field, sanitise it, write it into img/icons/{slug}.svg, then
 * delete the Media Library attachment and clear the field — so the field
 * is always empty again afterward, ready for the next drop, and nothing
 * icon-related lingers in the Media Library.
 *
 * @param int|string $post_id ACF's save context — 'options' for this options page (see inc/options.php, no custom post_id set).
 * @return void
 */
function lc_skeleton_handle_icon_upload( $post_id ) {
	if ( 'options' !== $post_id ) {
		return;
	}

	$attachment_id = get_field( 'icon_upload', 'option' );

	if ( ! $attachment_id ) {
		return;
	}

	// Belt and braces alongside the upload_mimes/wp_check_filetype_and_ext
	// filters above — refuse anything that isn't genuinely SVG by mime type,
	// same check lc-eternal2025's lc_sanitise_svg() uses.
	if ( 'image/svg+xml' !== get_post_mime_type( $attachment_id ) ) {
		wp_delete_attachment( $attachment_id, true );
		update_field( 'icon_upload', false, 'option' );
		return;
	}

	$file_path = get_attached_file( $attachment_id );

	if ( ! $file_path || ! file_exists( $file_path ) ) {
		wp_delete_attachment( $attachment_id, true );
		update_field( 'icon_upload', false, 'option' );
		return;
	}

	$svg_markup = file_get_contents( $file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

	if ( false === $svg_markup || '' === trim( $svg_markup ) ) {
		wp_delete_attachment( $attachment_id, true );
		update_field( 'icon_upload', false, 'option' );
		return;
	}

	$svg_markup = lc_skeleton_sanitise_svg_markup( $svg_markup );

	// Icon slug comes from the original uploaded filename, not a separate
	// "name this icon" field — matches get_icon_choices()'s own convention
	// of the filename being the icon's identity.
	$slug = sanitize_title( pathinfo( get_the_title( $attachment_id ), PATHINFO_FILENAME ) );
	if ( '' === $slug ) {
		$slug = 'icon-' . $attachment_id;
	}

	$icons_dir = get_template_directory() . '/img/icons';
	wp_mkdir_p( $icons_dir );

	$target = $icons_dir . '/' . $slug . '.svg';
	file_put_contents( $target, $svg_markup ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	chmod( $target, 0664 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_chmod

	wp_delete_attachment( $attachment_id, true );
	update_field( 'icon_upload', false, 'option' );
}
add_action( 'acf/save_post', 'lc_skeleton_handle_icon_upload', 20 );
