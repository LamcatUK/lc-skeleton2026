<?php
/**
 * 404 template.
 *
 * @package lc-skeleton2026
 */

get_header();
?>

<div class="container">
	<h1><?php esc_html_e( 'Page not found', 'lc-skeleton2026' ); ?></h1>
	<p><?php esc_html_e( "The page you're looking for doesn't exist.", 'lc-skeleton2026' ); ?></p>
</div>

<?php
get_footer();
