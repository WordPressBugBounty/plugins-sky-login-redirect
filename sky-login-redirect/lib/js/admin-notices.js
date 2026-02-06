jQuery( function() {
	jQuery( document ).on( 'click', '.slr-promo .notice-dismiss', function() {
		var data = {
			action: slrNoticeParams.action,
			nonce: slrNoticeParams.nonce
		};
		jQuery.post( slrNoticeParams.ajaxurl, data, function( response ) {
			if ( response.success ) {
				console.log( 'Sky Login Redirect: Notice dismissed successfully' );
			} else {
				console.error( 'Sky Login Redirect: Failed to dismiss notice', response );
			}
		});
	});
});
