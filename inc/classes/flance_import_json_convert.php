<?php
include_once WC_ABSPATH . 'includes/import/class-wc-product-csv-importer.php';
if (class_exists('WC_Product_CSV_Importer')) {
	class Flance_Import_Json_Convert extends WC_Product_CSV_Importer {
		protected $jsonFilePath;

		public function __construct( $jsonFilePath ) {
			$this->jsonFilePath = $jsonFilePath;
		}

		public function readJson() {
			$jsonContent = file_get_contents( $this->jsonFilePath );
			if ( $jsonContent === false ) {
				return false;
			}
			$dataArray = json_decode( $jsonContent, true );
			if ( json_last_error() != JSON_ERROR_NONE ) {
				return false;
			}

			return $dataArray;
		}

		public function processJsonFile( $jsonFilePath ) {
			$upload_path  = FLANCE_UPLOAD_PATH;
			$jsonFilePath = $upload_path . $jsonFilePath;
			if ( empty( $jsonFilePath ) ) {
				wp_die( 'Invalid file path.' );
			}
			$this->jsonFilePath = $jsonFilePath;
			$dataArray          = $this->readJson();
			if ( $dataArray === false ) {
				wp_die( 'Error reading or decoding JSON file.' );
			}

			return $dataArray;
		}

		public function set_progress( $percentComplete ) {
			$_SESSION['flance_import_progress'] = $percentComplete;
		}

		public function get_progress() {
			return isset( $_SESSION['flance_import_progress'] ) ? $_SESSION['flance_import_progress'] : 0;
		}

		public function get_import_progress() {
			check_ajax_referer( 'flance_ajax_nonce', 'security' );
			$percentComplete = $this->get_progress();
			wp_send_json_success( array( 'percent_complete' => $percentComplete ) );
		}
	}
}