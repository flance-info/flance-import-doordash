<?php

class Flance_Import_Woocommerce extends Flance_Import_Json_Convert {

	public function __construct() {
		add_action('wp_ajax_flance_import_to_woocommerce', array($this, 'handle_import_to_woocommerce'));
	}

	public function handle_import_to_woocommerce() {

		 check_ajax_referer('flance_ajax_nonce', 'security');
		$jsonFilePath = isset( $_POST['file_path'] ) ? sanitize_text_field( $_POST['file_path'] ) : '';
		$dataArray    = $this->processJsonFile( $jsonFilePath );

		foreach ( $dataArray as $productData ) {
			// Add your product creation logic here
			// Example:
			// wc_insert_product($productData, true);

			$success = true; // Set to true if the product creation is successful
			$message = $success ? 'Product created successfully' : 'Failed to create product';

sleep(2);
		}
		$results = array(
				'success' => $success,
				'data' => ['message' => $message],
			);
		wp_send_json( $results );
		wp_die();
	}

	private function redirectWithSuccess() {
		wp_redirect( admin_url( 'admin.php?page=flance_import_doordash&success=true&import_to_woocommerce=true' ) );
		exit();
	}
}


$importWooCommerce = new Flance_Import_Woocommerce();
