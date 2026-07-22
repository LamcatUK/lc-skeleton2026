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
