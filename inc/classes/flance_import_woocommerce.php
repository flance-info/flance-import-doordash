<?php

class Flance_Import_Woocommerce extends Flance_Import_Json_Convert {
	public function __construct() {
		add_action( 'wp_ajax_flance_import_to_woocommerce', array( $this, 'handle_import_to_woocommerce' ) );
		add_action( 'wp_ajax_get_import_progress', array( $this, 'get_import_progress' ) );
		if ( ! session_id() ) {
			session_start();
		}
	}


	public function handle_import_to_woocommerce() {
		check_ajax_referer( 'flance_ajax_nonce', 'security' );
		$jsonFilePath  = isset( $_POST['file_path'] ) ? sanitize_text_field( $_POST['file_path'] ) : '';
		$dataArray     = $this->processJsonFile( $jsonFilePath );
		$totalProducts = count( $dataArray );
		$this->set_progress( 0 );
		$index = 0;
		foreach ( $dataArray as   $productData ) {
			// Add your product creation logic here
			// Example:
			// wc_insert_product($productData, true);
			$success = true;
			$message = $success ? 'Product created successfully' : 'Failed to create product';
			$progressPercentage = ( $index + 1 ) / $totalProducts * 100;
			$index++;
			$this->set_progress( $progressPercentage );
			session_write_close();
			sleep( 1 );
			session_start();
		}
		$results = array(
			'success' => $success,
			'data'    => [ 'message' => $message ],
		);
		wp_send_json( $results );
		wp_die();
	}

}


$importWooCommerce = new Flance_Import_Woocommerce();
