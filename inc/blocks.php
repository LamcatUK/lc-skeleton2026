<?php
/**
 * Register ACF blocks.
 *
 * @package lc-skeleton2026
 */

/**
 * Register ACF blocks.
 *
 * New blocks are inserted below the marker comment by add_block.sh — leave
 * it in place.
 *
 * @return void
 */
function lc_skeleton_acf_blocks() {
	if ( function_exists( 'acf_register_block_type' ) ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf

		// INSERT NEW BLOCKS HERE.

	}
}
add_action( 'acf/init', 'lc_skeleton_acf_blocks' );

/**
 * Give plain-text core blocks a render_callback that wraps their output in
 * .container. Most page width in this theme comes from ACF blocks building
 * their own .container internally — a bare core/paragraph or core/heading
 * dropped into the_content() would otherwise render edge-to-edge with no
 * container padding.
 *
 * @param array  $args Block type args.
 * @param string $name Block type name.
 * @return array
 */
function lc_skeleton2026_core_block_type_args( $args, $name ) {
	$wrapped_blocks = array( 'core/paragraph', 'core/heading', 'core/list', 'core/separator' );

	if ( in_array( $name, $wrapped_blocks, true ) ) {
		$args['render_callback'] = 'lc_skeleton2026_wrap_block_in_container';
	}

	return $args;
}
add_filter( 'register_block_type_args', 'lc_skeleton2026_core_block_type_args', 10, 2 );

/**
 * Render callback that wraps a core block's content in .container.
 *
 * @param array  $attributes Block attributes — unused, required by the render_callback signature.
 * @param string $content    Rendered block content.
 * @return string
 */
function lc_skeleton2026_wrap_block_in_container( $attributes, $content ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	return '<div class="container">' . $content . '</div>';
}
