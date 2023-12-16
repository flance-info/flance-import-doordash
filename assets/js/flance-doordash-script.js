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
			async: true,
			beforeSend: function () {
				$('#progressBar').attr('value', 0).show();
				startProgressInterval();
			},
			success: function (response) {
				$('#progressBar').hide();
				console.log(response);
				if (response.success) {

				} else {

				}
			},
			error: function (error) {
				$('#progressBar').hide();
				console.log(error);
			},
			complete: function () {
				$('#progressBar').hide();
			}
		});
	});

	function startProgressInterval() {
    var intervalId = setInterval(function () {
        if ($('#progressBar').is(':visible')) {
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
        } else {
            clearInterval(intervalId);
        }
    }, 500);
}

});
