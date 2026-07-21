</main>

<footer id="colophon" class="container">
	<?php
	wp_nav_menu(
		array(
			'theme_location' => 'footer',
			'menu_class'     => 'navbar-nav',
			'container'      => false,
			'fallback_cb'    => false,
		)
	);
	?>
	<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
</footer>

<?php wp_footer(); ?>
</body>
</html>
