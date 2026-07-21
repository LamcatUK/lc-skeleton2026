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
	if ( function_exists( 'acf_register_block_type' ) ) {

		// INSERT NEW BLOCKS HERE.

	}
}
add_action( 'acf/init', 'lc_skeleton_acf_blocks' );
