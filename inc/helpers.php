<?php

add_action( 'admin_enqueue_scripts', 'flance_doordash_enqueue_styles' );
function flance_doordash_enqueue_styles() {

	if ( is_admin() && isset( $_GET['page'] ) && $_GET['page'] === 'flance_import_doordash' ) {
		wp_enqueue_style( 'flance-doordash-styles', FLANCE_DOORDASH_PLUGIN_URL . 'assets/css/flance-doordash-styles.css', [], time() );
	}
	wp_enqueue_script( 'flance-ajax-script', FLANCE_DOORDASH_PLUGIN_URL . 'assets/js/flance-doordash-script.js', array( 'jquery' ), time(), true );
	wp_localize_script( 'flance-ajax-script', 'flance_ajax_object', array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'flance_ajax_nonce' ),
	) );
}