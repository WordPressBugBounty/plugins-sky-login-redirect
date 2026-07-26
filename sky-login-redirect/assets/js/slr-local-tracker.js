/**
 * localStorage page tracker.
 * Stores a short-lived, same-origin URL in localStorage for login redirects.
 */

(function () {
	const loginForm =
		document.getElementById('loginform') ||
		document.getElementById('edd_login_form') ||
		document.getElementById('edd-blocks-form__login') ||
		document.querySelector('form.woocommerce-form-login, form#login');

	// Never overwrite the previous page while viewing an embedded login form.
	if (
		loginForm ||
		document.body.classList.contains('wp-admin') ||
		document.body.classList.contains('login')
	) {
		return;
	}

	try {
		const url = new URL(window.location.href);

		localStorage.setItem(
			'slr_last_page',
			JSON.stringify({
				url: `${url.origin}${url.pathname}`,
				expires: Date.now() + 30 * 60 * 1000,
			})
		);
	} catch (e) {
		// localStorage may be blocked (e.g., strict privacy settings) — silently fail.
	}
})();
