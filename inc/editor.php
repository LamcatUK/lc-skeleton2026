<?php
/**
 * Block editor tweaks. Standing per-theme convention for this user — not
 * covered by the lcp-blog-options plugin (which handles ACF block edit-mode,
 * comments/tags/emoji site-wide, but not this).
 *
 * @package lc-skeleton2026
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load the theme's actual compiled stylesheet into the block editor iframe
 * (fonts, colours, every block's real CSS — full parity with the frontend,
 * not a hand-picked subset), plus a small editor-only stylesheet on top
 * that contains top-level blocks to a page-width column instead of
 * full-bleed. add_editor_style() accepts an array — order matters, since
 * css/editor.css references var(--container-max-width), which only
 * resolves because theme.min.css's :root block loads first in the same
 * iframe document. Relies on the 'editor-styles' support already added in
 * inc/setup.php.
 *
 * @return void
 */
function lc_skeleton2026_add_editor_styles() {
	add_editor_style( array( 'css/theme.min.css', 'css/editor.min.css' ) );
}
add_action( 'after_setup_theme', 'lc_skeleton2026_add_editor_styles' );

/**
 * Disable the block editor's fullscreen mode by default, and work around a
 * known ACF bug where switching Visual/Text tabs forces unwanted focus jumps
 * while typing.
 *
 * @return void
 */
// phpcs:disable
function lc_skeleton_disable_editor_fullscreen_by_default() {
	$script = "jQuery( window ).load(function() { const isFullscreenMode = wp.data.select( 'core/edit-post' ).isFeatureActive( 'fullscreenMode' ); if ( isFullscreenMode ) { wp.data.dispatch( 'core/edit-post' ).toggleFeature( 'fullscreenMode' ); } });";

	// ACF known bug workaround: prevent switchEditors.go from forcing focus when enabling TinyMCE.
	// See: https://support.advancedcustomfields.com/forums/topic/bug-focus-forced-down-page-when-inserting-removing-blocks/
	$script .= "\n(function(){ if (!window.wp || !wp.data) { return; } wp.domReady(function(){
		function isTypingInBlockEditor(){ try { var sel = wp.data.select('core/block-editor'); return !!(sel && (sel.getSelectionStart() || sel.getSelectedBlock())); } catch(e){ return false; } }

		try {
			if (window.switchEditors && typeof window.switchEditors.go === 'function') {
				var originalGo = window.switchEditors.go;
				window.switchEditors.go = function(id, mode){
					if (isTypingInBlockEditor()) {
						var el = document.getElementById(id);
						var alreadyInit = false;
						if (window.tinymce) {
							var ed = window.tinymce.get(id);
							alreadyInit = !!ed;
						}
						if (alreadyInit) {
							return;
						}
					}
					return originalGo.apply(this, arguments);
				};
			}
		} catch(e){}
	}); });";
	wp_add_inline_script( 'wp-blocks', $script );
}
add_action( 'enqueue_block_editor_assets', 'lc_skeleton_disable_editor_fullscreen_by_default' );
// phpcs:enable
