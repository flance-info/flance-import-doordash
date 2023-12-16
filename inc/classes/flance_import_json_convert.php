<?php

class Flance_Import_Json_Convert {
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
		$upload_path = FLANCE_UPLOAD_PATH;
		$jsonFilePath = $upload_path. $jsonFilePath;
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
}