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

if (!defined('ABSPATH')) {
    exit;
}
// Enable debugging
define('WP_DEBUG', true);

// Display errors on the screen
define('WP_DEBUG_DISPLAY', true);

define('FLANCE_DOORDASH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FLANCE_DOORDASH_PLUGIN_URL', plugins_url('/', __FILE__));
define('FLANCE_UPLOAD_DIR', wp_upload_dir());
define('FLANCE_UPLOAD_PATH', FLANCE_UPLOAD_DIR['basedir'] . '/flance_import/json_file/');


require_once 'inc/loader.php';
