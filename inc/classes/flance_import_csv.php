<?php

class Flance_Import_Csv extends Flance_Import_Json_Convert {
	public function __construct() {
		add_action( 'wp_ajax_flance_import_to_csv', array( $this, 'handle_import_to_csv' ) );
		add_action( 'wp_ajax_get_import_progress_csv', array( $this, 'get_import_progress' ) );
		if ( ! session_id() ) {
			session_start();
		}
	}

	public function handle_import_to_csv() {
		check_ajax_referer( 'flance_ajax_nonce', 'security' );
		$jsonFilePath = isset( $_POST['file_path'] ) ? sanitize_text_field( $_POST['file_path'] ) : '';
		$dataArray    = $this->processJsonFile( $jsonFilePath );
		$this->convert_process_reco( $dataArray );
		$this->import( false );
		$this->convert_process( $dataArray );
		$this->set_progress( 0 );
		$results = $this->import();
		wp_send_json( $results );
		wp_die();
	}

	public function convert_process( $inputData ) {
		$outputData = [];
		foreach ( $inputData as $category => $items ) {

			foreach ( $items as $item ) {
				$itemData     = $item['data']['itemPage']['itemHeader'];
				$optionList   = [];
				$images = array_map( function ( $img ) {
						return $img['url'];
					}, $itemData['imgUrlList'] );

				$outputData[] = [
					'type'                  => 'simple',
					'sku'                   => $itemData['id'],
					'name'                  => $itemData['name'],
					'featured'              => 0,
					'short_description'     => $itemData['description'],
					'regular_price'         => $itemData['unitAmount'] / 100,
					'currency'              => $itemData['currency'],
					'category_ids'          =>  $item['data']['itemPage']['category'] ,
					'Images'          => $itemData['imgUrl'].','.implode(',', $images),
					'description'           => $itemData['description'],
					'optionLists'           => json_encode($this->set_recomended_products( $item )),
				];
			}
		}
		flance_write_log( $outputData, 'logs/csvoutputdata.log');
		$this->parsed_data = $outputData;
	}

	public function convert_process_reco( $inputData ) {
		$outputDataReco = [];
		foreach ( $inputData as $category => $items ) {
			if (!empty($items) && is_array($items)) {
				foreach ( $items as $item ) {
					$itemData   = $item['data']['itemPage']['itemHeader'];
					$optionList = [];
					if ( $item['data']['itemPage']['optionLists'] ) {
						$recomded_products_sku = [];
						foreach ( $item['data']['itemPage']['optionLists'] as $optionList ) {
							if ( $optionList['type'] === 'item' ) {
								$options = $optionList['options'];
								foreach ( $options as $option ) {
									$outputDataReco[ $option['id'] ] = [
										'type'          => 'simple',
										'sku'           => $option['id'],
										'name'          => $option['name'],
										'featured'      => 0,
										'regular_price' => $option['unitAmount'] / 100,
										'currency'      => $itemData['currency'],
									];
									$recomded_products_sku[]         = $option['id'];
								}
							}
						}
					}
				}
			}
		}
		$this->parsed_data = $outputDataReco;
	}

	public function set_recomended_products( $item ) {
		$itemData   = $item['data']['itemPage']['itemHeader'];
		$optionList = [];
		if ( $item['data']['itemPage']['optionLists'] ) {
			$recomded_products_id = [];
			foreach ( $item['data']['itemPage']['optionLists'] as $optionList ) {
				if ( $optionList['type'] === 'item' ) {
					$options = $optionList['options'];
					foreach ( $options as $option ) {
						$outputData[]           = [
							'type'          => 'simple',
							'sku'           => $option['id'],
							'name'          => $option['name'],
							'featured'      => 0,
							'regular_price' => $option['unitAmount'] / 100,
							'currency'      => $itemData['currency'],
						];
						$recomded_products_id[] = ( wc_get_product_id_by_sku( $option['id'] ) ) ? wc_get_product_id_by_sku( $option['id'] ) : null;

					}
				}
				if ( $optionList['type'] === 'item' ) {

					$optionList = [
						[
							'type'                        => 'rec_product',
							'title'                       => $optionList['name'],
							'wpc_pro_pao_option_products' => $recomded_products_id,
							'title_format'                => 'label',
							'place_holder'                => '',
							'char_limit'                  => 0,
							'char_min'                    => 0,
							'char_max'                    => 0,
							'position'                    => 0,
							'desc_enable'                 => 1,
							'desc'                        => $optionList['subtitle'],
							'required'                    => ! $optionList['isOptional'],
							'options'                     => [
								[
									'label'      => 'rec',
									'price'      => '',
									'price_type' => 'quantity_based',
									'default'    => 0,
								],
							],
						],
					];
				}
			}


		}

		return $optionList;
	}

	public function import( $display_results = true ) {
		$this->start_time = time();
		$index            = 0;
		$data             = array(
			'imported'            => array(),
			'imported_variations' => array(),
			'failed'              => array(),
			'updated'             => array(),
			'skipped'             => array(),
		);
		$totalProducts    = count( $this->parsed_data );

		if ( $display_results ) {
			$theme_directory = FLANCE_DOORDASH_PLUGIN_DIR;

			$csvFile = fopen( $theme_directory . '/output/output.csv', 'w' );
			$header  = array_keys( $this->parsed_data[0] );
			fputcsv( $csvFile, $header );
		}
		foreach ( $this->parsed_data as $parsed_data_key => $parsed_data ) {

			if ( $display_results ) {
				$index ++;

				fputcsv( $csvFile, $parsed_data );
				$pao_data           = $parsed_data['optionLists'];
				$progressPercentage = ( $index + 1 ) / $totalProducts * 100;
				$this->set_progress( $progressPercentage );
			}

		}
		fclose( $csvFile );
		$success         = true;
		$message         = $success ? 'Product created successfully' : 'Failed to create product';
		$data['success'] = $success;
		$data['message'] = $message;
		$data['url']= FLANCE_DOORDASH_PLUGIN_URL.'/output/output.csv';
		return $data;
	}


}

$importCsv = new Flance_Import_Csv();
