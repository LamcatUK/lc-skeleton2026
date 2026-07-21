<?php
/**
 * Enqueue theme CSS/JS. filemtime versioning, no dependencies (no jQuery,
 * no Bootstrap JS) — plain vanilla output, loads immediately.
 *
 * @package lc-skeleton2026
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue theme.min.css.
 *
 * @return void
 */
function lc_skeleton_enqueue_styles() {
	$rel = '/css/theme.min.css';
	$abs = get_stylesheet_directory() . $rel;
	if ( file_exists( $abs ) ) {
		wp_enqueue_style( 'lc-skeleton-theme', get_stylesheet_directory_uri() . $rel, array(), filemtime( $abs ) );
	}
}
add_action( 'wp_enqueue_scripts', 'lc_skeleton_enqueue_styles' );

/**
 * Enqueue theme.min.js.
 *
 * @return void
 */
function lc_skeleton_enqueue_scripts() {
	$rel = '/js/theme.min.js';
	$abs = get_stylesheet_directory() . $rel;
	if ( file_exists( $abs ) ) {
		wp_enqueue_script( 'lc-skeleton-theme', get_stylesheet_directory_uri() . $rel, array(), filemtime( $abs ), true );
	}
}
add_action( 'wp_enqueue_scripts', 'lc_skeleton_enqueue_scripts' );
