document.addEventListener(
    'click',
    function ( e ) {
		if ( ! e.target.closest( '.slr-promo .notice-dismiss' ) ) {
			return;
		}

		var body = new URLSearchParams();
		body.append( 'action', slrNotice.action );
		body.append( 'nonce', slrNotice.nonce );

		fetch(
            slrNotice.ajax_url,
            {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
				body: body.toString()
            }
		)
		.then(
            function ( res ) {
                return res.json(); }
        )
		.then(
            function ( response ) {
                if ( response.success ) {
                        console.log( 'Sky Login Redirect: Notice dismissed successfully' );
                } else {
                    console.error( 'Sky Login Redirect: Failed to dismiss notice', response );
                }
            }
        )
		.catch(
            function ( err ) {
                console.error( 'Sky Login Redirect: Dismiss request failed', err );
            }
        );
	}
);
