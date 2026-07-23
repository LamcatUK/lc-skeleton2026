<?php
/**
 * Reusable, project-agnostic utility functions — safe to lift verbatim into
 * other projects built on this skeleton. Project-specific helpers (coupled
 * to a project's own field schema or content structure) belong in
 * inc/helpers.php instead — don't create that file until something actually
 * needs it.
 *
 * @package lc-skeleton2026
 */

defined( 'ABSPATH' ) || exit;

/**
 * Strip formatting from a UK phone number for use in tel: links.
 *
 * @param string $phone Phone number as entered (spaces, brackets, dashes allowed).
 * @return string
 */
function parse_phone( $phone ) {
	$phone = preg_replace( '/\s+/', '', $phone );
	$phone = preg_replace( '/\(0\)/', '', $phone );
	$phone = preg_replace( '/[\(\)\.]/', '', $phone );
	$phone = preg_replace( '/-/', '', $phone );
	$phone = preg_replace( '/^0/', '+44', $phone );
	return $phone;
}

/**
 * Pluralise a word based on quantity.
 *
 * @param int         $quantity Quantity to check.
 * @param string      $singular Singular form.
 * @param string|null $plural   Explicit plural form, if the default suffix rules don't apply.
 * @return string
 */
function pluralise( $quantity, $singular, $plural = null ) {
	if ( 1 === $quantity || ! strlen( $singular ) ) {
		return $singular;
	}
	if ( null !== $plural ) {
		return $plural;
	}

	$last_letter = strtolower( $singular[ strlen( $singular ) - 1 ] );
	switch ( $last_letter ) {
		case 'y':
			return substr( $singular, 0, -1 ) . 'ies';
		case 's':
			return $singular . 'es';
		default:
			return $singular . 's';
	}
}

/**
 * Estimate reading time for a piece of content.
 *
 * @param string $content          Content to estimate.
 * @param int    $words_per_minute Reading speed assumption.
 * @param bool   $with_gutenberg   Parse content as Gutenberg blocks before stripping tags.
 * @param bool   $formatted        Return a formatted sentence instead of a bare number.
 * @return int|string
 */
function estimate_reading_time_in_minutes( $content = '', $words_per_minute = 300, $with_gutenberg = false, $formatted = false ) {
	if ( $with_gutenberg ) {
		$blocks       = parse_blocks( $content );
		$content_html = '';

		foreach ( $blocks as $block ) {
			$content_html .= render_block( $block );
		}

		$content = $content_html;
	}

	$content = wp_strip_all_tags( $content );

	if ( ! $content ) {
		return 0;
	}

	$words_count = str_word_count( $content );
	$minutes     = ceil( $words_count / $words_per_minute );

	if ( $formatted ) {
		$minutes = '<p class="reading">Estimated reading time ' . $minutes . ' ' . pluralise( $minutes, 'minute' ) . '</p>';
	}

	return $minutes;
}
