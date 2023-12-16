<?php

class Flance_Import_Woocommerce extends Flance_Import_Json_Convert {

	public function __construct() {
		add_action( 'admin_post_flance_import_to_woocommerce', array( $this, 'handle_import_to_woocommerce' ) );
	}

	public function handle_import_to_woocommerce() {
		check_admin_referer( 'flance_import_doordash_nonce', 'flance_import_doordash_nonce' );
		$jsonFilePath = isset( $_POST['file_path'] ) ? sanitize_text_field( $_POST['file_path'] ) : '';
		if ( empty( $jsonFilePath ) ) {
			wp_die( 'Invalid file path.' );
		}
		$this->jsonFilePath = $jsonFilePath;
		$dataArray = $this->readJson();
		if ( $dataArray === false ) {
			wp_die( 'Error reading or decoding JSON file.' );
		}
		// Your WooCommerce import logic using $dataArray
		foreach ( $dataArray as $productData ) {
			// Add your product creation logic here
			// Example:
			// wc_insert_product($productData, true);
		}
		wp_redirect( admin_url( 'admin.php?page=flance_import_doordash&success=true&import_to_woocommerce=true' ) );
		exit();
	}
}

// Usage
$importWooCommerce = new Flance_Import_Woocommerce();
