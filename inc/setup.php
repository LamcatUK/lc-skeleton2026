<?php
/**
 * Theme setup — supports, nav menus.
 *
 * @package lc-skeleton2026
 */

defined( 'ABSPATH' ) || exit;

/**
 * Core theme supports and nav menu locations.
 *
 * @return void
 */
function lc_skeleton_setup() {
	load_theme_textdomain( 'lc-skeleton2026', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'disable-custom-colors' );

	// Rename/extend per project.
	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'lc-skeleton2026' ),
			'footer'  => __( 'Footer Menu', 'lc-skeleton2026' ),
		)
	);
}
add_action( 'after_setup_theme', 'lc_skeleton_setup' );
