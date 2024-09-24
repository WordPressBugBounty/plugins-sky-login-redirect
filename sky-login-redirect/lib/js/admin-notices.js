jQuery( function() {
	jQuery( document ).on( 'click', '.EOY-2020 .notice-dismiss', function() {
		var data = { action: 'SLR_EOY_2020', };
		jQuery.post( notice_params.ajaxurl, data, function() {});
	})
});
