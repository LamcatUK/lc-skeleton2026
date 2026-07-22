<?php
/**
 * Site-Wide Settings ACF options page. Field group lives in acf-json/ —
 * add fields via the field editor as a project needs them; make sure they
 * actually sync to acf-json (Custom Fields > Site-Wide Settings > Screen
 * Options / the JSON sync notice) so they travel with the theme rather than
 * staying database-only.
 *
 * @package lc-skeleton2026
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the options page. Hooked to acf/init (ACF's own recommended
 * hook for this) rather than called at file-scope — calling it directly
 * runs before ACF wants its translations loaded and throws a
 * _load_textdomain_just_in_time notice under WP 6.7+.
 *
 * @return void
 */
function lc_skeleton_register_options_page() {
	if ( function_exists( 'acf_add_options_page' ) ) {
		acf_add_options_page(
			array(
				'page_title' => 'Site-Wide Settings',
				'menu_title' => 'Site-Wide Settings',
				'menu_slug'  => 'theme-general-settings',
				'capability' => 'edit_posts',
			)
		);
	}
}
add_action( 'acf/init', 'lc_skeleton_register_options_page' );
