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
		$jsonFilePath = isset( $_POST['file_path'] ) ? sanitize_text_field( $_POST['file_path'] ) : '';
		$dataArray    = $this->processJsonFile( $jsonFilePath );
		$this->convert_process_reco( $dataArray );
		$results = $this->import( false );
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
				$itemData   = $item['data']['itemPage']['itemHeader'];
				$optionList = [];
				foreach ( $items as $item ) {
					$itemData     = $item['data']['itemPage']['itemHeader'];
					$optionList   = [];
					$outputData[] = [
						'type'                  => 'simple',
						'sku'                   => $itemData['id'],
						'name'                  => $itemData['name'],
						'featured'              => 0,
						'short_description'     => $itemData['description'],
						'regular_price'         => $itemData['unitAmount'] / 100,
						'currency'              => $itemData['currency'],
						'category_ids'          => $this->parse_categories_field( $item['data']['itemPage']['category'] ),
						'raw_image_id'          => $itemData['imgUrl'],
						'raw_gallery_image_ids' => array_map( function ( $img ) {
							return $img['url'];
						}, $itemData['imgUrlList'] ),
						'description'           => $itemData['description'],
						'optionLists'           => $this->set_recomended_products( $item ),
					];

				}
			}
			$this->parsed_data = $outputData;
		}
	}

	public function convert_process_reco( $inputData ) {
		$outputDataReco = [];
		foreach ( $inputData as $category => $items ) {
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
			}
			if ( $optionList['type'] === 'item' ) {
				$optionList = [
					[
						'type'                        => 'rec_product',
						'title'                       => $item['data']['itemPage']['optionLists']['name'],
						'wpc_pro_pao_option_products' => $recomded_products_id,
						'title_format'                => 'label',
						'place_holder'                => '',
						'char_limit'                  => 0,
						'char_min'                    => 0,
						'char_max'                    => 0,
						'desc_enable'                 => 1,
						'desc'                        => $item['data']['itemPage']['optionLists']['subtitle'],
						'required'                    => ! $item['data']['itemPage']['optionLists']['isOptional'],
						'position'                    => 1,
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

		return $optionList;
	}

	public function import( $display_results = true ) {
		$this->start_time = time();
		$index            = 0;
		$update_existing  = true;
		$data             = array(
			'imported'            => array(),
			'imported_variations' => array(),
			'failed'              => array(),
			'updated'             => array(),
			'skipped'             => array(),
		);
		$totalProducts    = count( $this->parsed_data );
		foreach ( $this->parsed_data as $parsed_data_key => $parsed_data ) {
			do_action( 'woocommerce_product_import_before_import', $parsed_data );
			$id         = isset( $parsed_data['id'] ) ? absint( $parsed_data['id'] ) : 0;
			$sku        = isset( $parsed_data['sku'] ) ? $parsed_data['sku'] : '';
			$id_exists  = false;
			$sku_exists = false;
			if ( $id ) {
				$product   = wc_get_product( $id );
				$id_exists = $product && 'importing' !== $product->get_status();
			}
			if ( $sku ) {
				$id_from_sku = wc_get_product_id_by_sku( $sku );
				$product     = $id_from_sku ? wc_get_product( $id_from_sku ) : false;
				$sku_exists  = $product && 'importing' !== $product->get_status();
			}
			if ( $id_exists && ! $update_existing ) {
				$data['skipped'][] = new WP_Error(
					'woocommerce_product_importer_error',
					esc_html__( 'A product with this ID already exists.', 'woocommerce' ),
					array(
						'id'  => $id,
						'row' => $this->get_row_id( $parsed_data ),
					)
				);
				continue;
			}
			if ( $sku_exists && ! $update_existing ) {
				$data['skipped'][] = new WP_Error(
					'woocommerce_product_importer_error',
					esc_html__( 'A product with this SKU already exists.', 'woocommerce' ),
					array(
						'sku' => esc_attr( $sku ),
						'row' => $this->get_row_id( $parsed_data ),
					)
				);
				continue;
			}
			if ( $update_existing && ( isset( $parsed_data['id'] ) || isset( $parsed_data['sku'] ) ) && ! $id_exists && ! $sku_exists ) {
				$data['skipped'][] = new WP_Error(
					'woocommerce_product_importer_error',
					esc_html__( 'No matching product exists to update.', 'woocommerce' ),
					array(
						'id'  => $id,
						'sku' => esc_attr( $sku ),
						'row' => $this->get_row_id( $parsed_data ),
					)
				);
				continue;
			}
			$result   = $this->process_item( $parsed_data );
			if ( is_wp_error( $result ) ) {
				$result->add_data( array( 'row' => $this->get_row_id( $parsed_data ) ) );
				$data['failed'][] = $result;
			} elseif ( $result['updated'] ) {
				$data['updated'][] = $result['id'];
			} else {
				if ( $result['is_variation'] ) {
					$data['imported_variations'][] = $result['id'];
				} else {
					$data['imported'][] = $result['id'];
				}
			}
			$index ++;
			if ( $display_results ) {
				$post_id  = $result['id'];
				$product  = wc_get_product( $post_id );
				$pao_data = $parsed_data['optionLists'];
				$product->update_meta_data( '_wpc_pro_pao_data', $pao_data );
				$progressPercentage = ( $index + 1 ) / $totalProducts * 100;
				$this->set_progress( $progressPercentage );
			}
			if ( $this->params['prevent_timeouts'] && ( $this->time_exceeded() || $this->memory_exceeded() ) ) {

				break;
			}
			break;
		}
		if ( $display_results ) {
			$success         = true;
			$message         = $success ? 'Product created successfully' : 'Failed to create product';
			$data['success'] = $success;
			$data['message'] = $message;
		}

		return $data;
	}

}


$importWooCommerce
	= new Flance_Import_Woocommerce();
