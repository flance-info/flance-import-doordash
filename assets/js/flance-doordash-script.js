jQuery(document).ready(function ($) {
	$('#progressBar').hide();
	$('#importToWooCommerceBtn').click(function () {
		var file_path = $(this).data('file-path');
		var data = {
			'action': 'flance_import_to_woocommerce',
			'file_path': file_path,
			'security': flance_ajax_object.nonce
		};

		$.ajax({
			url: flance_ajax_object.ajax_url,
			type: 'POST',
			data: data,
			beforeSend: function () {
				$('#progressBar').attr('value', 0).show();
				startProgressInterval();
			},
			success: function (response) {
				$('#progressBar').hide();

				if (response.success) {
					alert('Success: ' + response.data.message);
				} else {
					alert('Error: ' + response.data.message);
				}
			},
			error: function (error) {
				$('#progressBar').hide();
				alert('Error: ' + error.responseText);
			},
			complete: function () {
				$('#progressBar').hide();
			}
		});
	});

	function startProgressInterval() {
		setInterval(function () {
			$.ajax({
				url: flance_ajax_object.ajax_url,
				type: 'GET',
				data: {
					'action': 'get_import_progress',
					'security': flance_ajax_object.nonce
				},
				success: function (response) {
					if (response.success) {
						var percentComplete = response.data.percent_complete;
						$('#progressBar').attr('value', percentComplete);
					}
				},
				error: function (error) {
					console.log('Error: ' + error.responseText);
				}
			});
		}, 1000);
	}
});
