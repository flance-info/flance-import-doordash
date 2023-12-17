<?php

class Flance_Import_Admin_Menu {
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_post_flance_import_doordash', array( $this, 'handle_import_doordash' ) );
		add_action( 'admin_post_flance_convert_to_csv', array( $this, 'handle_convert_to_csv' ) );
		add_action( 'admin_post_flance_import_to_woocommerce', array( $this, 'handle_import_to_woocommerce' ) );
	}

	public function add_menu() {
		add_submenu_page(
				'woocommerce',
				'Import DoorDash',
				'Import DoorDash',
				'manage_options',
				'flance_import_doordash',
				array( $this, 'import_doordash_page' )
		);
	}

	public function import_doordash_page() {
		?>
		<div class="wrap">
			<h2>Import DoorDash Data</h2>
			<?php
			if ( isset( $_GET['success'] ) && $_GET['success'] === 'true' ) {
				?>
				<div class="notice notice-success is-dismissible"><p>Data imported successfully!</p></div>
				<?php
			}
			?>
			<br/>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
				<?php wp_nonce_field( 'flance_import_doordash_nonce', 'flance_import_doordash_nonce' ); ?>
				<input type="hidden" name="action" value="flance_import_doordash">
				<label for="json_file">Upload JSON File:</label>
				<input type="file" name="json_file" id="json_file" accept=".json">
				<input type="submit" class="button button-primary stm-button" name="upload" value="Upload">
			</form>
			<br/>
			<?php
			if ( isset( $_GET['upload_and_convert'] ) && $_GET['upload_and_convert'] === 'true' ) {
				$file_path = get_transient( 'flance_import_doordash_file_path' );
				?>

				<div class="import-container">
					<button type="button" id="importToCsvBtn" class="button button-primary stm-button" data-file-path="<?php echo esc_attr( $file_path ); ?>">
						Convert to CSV
					</button>
					<progress id="progressBarCsv" value="0" max="100"></progress>
					<div class="download-flance"><a href="#">Download Csv file </a></div>
				</div>
				<div class="import-container">
					<button type="button" id="importToWooCommerceBtn" class="button button-primary stm-button" data-file-path="<?php echo esc_attr( $file_path ); ?>">
						Import to WooCommerce
					</button>
					<progress id="progressBar" value="0" max="100"></progress>
				</div>
				<?php
			}
			?>
		</div>
		<?php
	}

	public function handle_import_doordash() {
		check_admin_referer( 'flance_import_doordash_nonce', 'flance_import_doordash_nonce' );
		if ( ! empty( $_FILES['json_file']['tmp_name'] ) ) {
			$upload_path = FLANCE_UPLOAD_PATH;
			if ( ! file_exists( $upload_path ) ) {
				wp_mkdir_p( $upload_path );
			}
			$allowed_types  = array( 'json' );
			$file_info      = pathinfo( $_FILES['json_file']['name'] );
			$file_extension = strtolower( $file_info['extension'] );
			if ( in_array( $file_extension, $allowed_types ) ) {
				$file_name = 'imported_file_' . time() . '.' . $file_extension;
				$file_path = $upload_path . $file_name;
				move_uploaded_file( $_FILES['json_file']['tmp_name'], $file_path );

				set_transient( 'flance_import_doordash_file_path', $file_name, DAY_IN_SECONDS );
				//$file_path = get_transient( 'flance_import_doordash_file_path' );
				//echo $file_name;
				//exit;
				wp_redirect( admin_url( 'admin.php?page=flance_import_doordash&success=true&upload_and_convert=true' ) );
				exit();
			} else {
				wp_die( 'Invalid file type. Please upload a JSON file.' );
			}
		} else {
			wp_die( 'No file uploaded. Please select a JSON file.' );
		}
	}

	public function handle_convert_to_csv() {
		// Handle Convert to CSV logic here
	}

	public function handle_import_to_woocommerce() {
		// Handle Import to WooCommerce logic here
	}
}

new Flance_Import_Admin_Menu();


