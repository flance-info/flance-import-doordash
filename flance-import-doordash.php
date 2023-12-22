<?php
/*
Plugin Name: Flance Import DoorDash to WooCommerce
Description: Import DoorDash data into WooCommerce.
Version: 1.0
Author:            Rusty
Author URI:        http://www.flance.info
Text Domain:       flance-import-doordash
Domain Path:       /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FLANCE_DOORDASH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'FLANCE_DOORDASH_PLUGIN_URL', plugins_url( '/', __FILE__ ) );
define( 'FLANCE_UPLOAD_DIR', wp_upload_dir() );
define( 'FLANCE_UPLOAD_PATH', FLANCE_UPLOAD_DIR['basedir'] . '/flance_import/json_file/' );

if ( ! function_exists( 'is_flance_woocommerce_active' ) ) {
	function is_flance_woocommerce_active() {
		$active_plugins = (array) get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}

		return in_array( 'woocommerce/woocommerce.php', $active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins ) || class_exists( 'WooCommerce' );
	}
}

add_action( 'plugins_loaded', 'flance_load_plugin' );
function flance_load_plugin() {
	if ( is_flance_woocommerce_active() ) {
		require_once 'inc/loader.php';
	}
}

