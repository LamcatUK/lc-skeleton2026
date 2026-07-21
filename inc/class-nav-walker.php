<?php
/**
 * Lightweight nav walker. Outputs nav-link/dropdown-menu class names (kept
 * for familiarity) but has none of Bootstrap's navwalker complexity — no
 * linkmod/icon handling, no Bootstrap 4/5 branching. Submenus are shown via
 * CSS :hover/:focus-within, no JS involved.
 *
 * @package lc-skeleton2026
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'LC_Skeleton_Nav_Walker' ) ) {

	/**
	 * Custom nav walker.
	 */
	class LC_Skeleton_Nav_Walker extends Walker_Nav_Menu {

		/**
		 * Starts the list before the elements are added.
		 *
		 * @param string   $output Passed by reference.
		 * @param int      $depth  Depth of menu item.
		 * @param stdClass $args   Menu args.
		 * @return void
		 */
		public function start_lvl( &$output, $depth = 0, $args = null ) {
			$output .= '<ul class="dropdown-menu">';
		}

		/**
		 * Ends the list after the elements are added.
		 *
		 * @param string   $output Passed by reference.
		 * @param int      $depth  Depth of menu item.
		 * @param stdClass $args   Menu args.
		 * @return void
		 */
		public function end_lvl( &$output, $depth = 0, $args = null ) {
			$output .= '</ul>';
		}

		/**
		 * Starts the element output.
		 *
		 * @param string   $output Passed by reference.
		 * @param WP_Post  $item   Menu item.
		 * @param int      $depth  Depth of menu item.
		 * @param stdClass $args   Menu args.
		 * @param int      $id     Menu item ID.
		 * @return void
		 */
		public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
			$has_children = in_array( 'menu-item-has-children', $item->classes, true );

			$li_classes = array( 'nav-item' );
			if ( $has_children ) {
				$li_classes[] = 'dropdown';
			}

			$link_classes = array( 'nav-link' );
			if ( in_array( 'current-menu-item', $item->classes, true ) ) {
				$link_classes[] = 'active';
			}

			$output .= '<li class="' . esc_attr( implode( ' ', $li_classes ) ) . '">';
			$output .= '<a class="' . esc_attr( implode( ' ', $link_classes ) ) . '" href="' . esc_url( $item->url ) . '"';
			if ( in_array( 'current-menu-item', $item->classes, true ) ) {
				$output .= ' aria-current="page"';
			}
			$output .= '>' . esc_html( $item->title ) . '</a>';
		}

		/**
		 * Ends the element output.
		 *
		 * @param string   $output Passed by reference.
		 * @param WP_Post  $item   Menu item.
		 * @param int      $depth  Depth of menu item.
		 * @param stdClass $args   Menu args.
		 * @return void
		 */
		public function end_el( &$output, $item, $depth = 0, $args = null ) {
			$output .= '</li>';
		}
	}
}
