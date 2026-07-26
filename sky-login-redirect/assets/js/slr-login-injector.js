/**
 * Login page injector.
 * Reads localStorage and injects slr_referrer hidden field into the login form.
 */

(function () {
	try {
		const storedPage = localStorage.getItem('slr_last_page');
		if (!storedPage) {
			return;
		}

		let lastPage = '';
		try {
			const parsedPage = JSON.parse(storedPage);
			if (
				typeof parsedPage?.url === 'string' &&
				Number(parsedPage?.expires) > Date.now()
			) {
				const parsedUrl = new URL(
					parsedPage.url,
					window.location.origin
				);
				if (parsedUrl.origin === window.location.origin) {
					lastPage = parsedUrl.href;
				}
			}
		} catch {
			// Discard values written by versions before the structured format.
		}

		if (!lastPage) {
			localStorage.removeItem('slr_last_page');
			return;
		}

		// Target WordPress, WooCommerce, modal, and EDD login forms.
		const loginForm =
			document.getElementById('loginform') ||
			document.getElementById('edd_login_form') ||
			document.getElementById('edd-blocks-form__login') ||
			document.querySelector('form.woocommerce-form-login, form#login');
		if (!loginForm) {
			return;
		}

		// Create hidden input field
		const hiddenField = document.createElement('input');
		hiddenField.type = 'hidden';
		hiddenField.name = 'slr_referrer';
		hiddenField.value = lastPage;

		// Inject into form
		loginForm.appendChild(hiddenField);

		// Clear after use so it does not persist beyond the login attempt.
		localStorage.removeItem('slr_last_page');
	} catch (e) {
		// localStorage may be blocked (e.g., strict privacy settings) — silently fail.
	}
})();
