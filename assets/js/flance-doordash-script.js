jQuery(document).ready(function ($) {
	$('#progressBar').hide();
	$('.download-flance').hide();

	$('#importToWooCommerceBtn').click(function () {
		var file_path = $(this).data('file-path');
		var data = {
			'action': 'flance_import_to_woocommerce',
			'file_path': file_path,
			'security': flance_ajax_object.nonce
		};

		$('#progressBar').attr('value', 0).show();
		startProgressRequests(data);
	});

	function startProgressRequests(data) {
		// Make the first request
		makeImportRequest(data);
	}

	function makeImportRequest(data) {
		$.ajax({
			url: flance_ajax_object.ajax_url,
			type: 'POST',
			data: data,
			async: true,
			beforeSend: function () {
				//startProgressInterval();
			},
			success: function (response) {
				console.log(response);
				if (response.success) {

					if (response.data.hasMoreData) {
						let percentComplete = response.data.percent_complete;
						data.parsed_data_key = response.data.nextParsedDataKey;
						$('#progressBar').attr('value', percentComplete);
						makeImportRequest(data);
					} else {
						$('#progressBar').hide();
					}
				} else {

				}
			},
			error: function (error) {
				$('#progressBar').hide();
				console.log(error);
			}
		});
	}

	function startProgressInterval() {
		var intervalId = setInterval(function () {
			$.ajax({
				url: flance_ajax_object.ajax_url,
				type: 'GET',
				async: true,
				data: {
					'action': 'get_import_progress',
					'security': flance_ajax_object.nonce
				},
				success: function (response) {
					if (response.success) {
						var percentComplete = response.data.percent_complete;
						$('#progressBar').attr('value', percentComplete);

						if (percentComplete >= 100) {
							clearInterval(intervalId);
							$('#progressBar').hide();
						}
					}
				},
				error: function (error) {
					console.log('Error: ' + error.responseText);
				}
			});
		}, 500);
	}
});


jQuery(document).ready(function ($) {
	$('#progressBarCsv').hide();
	$('#importToCsvBtn').click(function () {
		var file_path = $(this).data('file-path');
		var data = {
			'action': 'flance_import_to_csv',
			'file_path': file_path,
			'security': flance_ajax_object.nonce
		};

		$.ajax({
			url: flance_ajax_object.ajax_url,
			type: 'POST',
			data: data,
			async: true,
			beforeSend: function () {
				$('#progressBarCsv').attr('value', 0).show();
				startProgressIntervalCsv();
			},
			success: function (response) {
				$('#progressBarCsv').hide();
				console.log(response);
				let downloadUrl = response.url;
				$('.download-flance a').attr('href', downloadUrl);
				$('.download-flance').show();
			},
			error: function (error) {
				$('#progressBarCsv').hide();
				console.log(error);
			},
			complete: function () {
				$('#progressBarCsv').hide();
			}
		});
	});

	function startProgressIntervalCsv() {
		var intervalId = setInterval(function () {
			if ($('#progressBarCsv').is(':visible')) {
				$.ajax({
					url: flance_ajax_object.ajax_url,
					type: 'GET',
					async: true,
					data: {
						'action': 'get_import_progress_csv',
						'security': flance_ajax_object.nonce
					},
					success: function (response) {
						if (response.success) {
							var percentComplete = response.data.percent_complete;
							$('#progressBarCsv').attr('value', percentComplete);

							if (percentComplete >= 100) {
								clearInterval(intervalId);
								$('#progressBarCsv').hide();
							}
						}
					},
					error: function (error) {
						console.log('Error: ' + error.responseText);
					}
				});
			} else {
				clearInterval(intervalId);
			}
		}, 500);
	}

});
