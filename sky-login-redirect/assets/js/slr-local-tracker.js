/**
 * localStorage page tracker.
 * Stores a short-lived, same-origin URL in localStorage for login redirects.
 */

(function () {
	const loginForm =
		document.getElementById('loginform') ||
		document.getElementById('edd_login_form') ||
		document.getElementById('edd-blocks-form__login') ||
		document.querySelector('form.woocommerce-form-login');

	// Dedicated login pages must not replace the true prior page. A modal
	// form#login may exist on every page, so it intentionally is not included.
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
	} catch {
		// localStorage may be blocked (e.g., strict privacy settings) — silently fail.
	}
})();

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

		// The global modal refreshes its host page and must never consume the
		// previous-page value intended for a dedicated login form.
		const loginForm =
			document.getElementById('loginform') ||
			document.getElementById('edd_login_form') ||
			document.getElementById('edd-blocks-form__login') ||
			document.querySelector('form.woocommerce-form-login');
		if (!loginForm) {
			return;
		}

		// Update the server-rendered fallback when present; otherwise create it.
		const hiddenField =
			loginForm.querySelector?.('input[name="slr_referrer"]') ||
			document.createElement('input');
		if (!hiddenField.parentNode) {
			hiddenField.type = 'hidden';
			hiddenField.name = 'slr_referrer';
			loginForm.appendChild(hiddenField);
		}
		hiddenField.value = lastPage;

		localStorage.removeItem('slr_last_page');
	} catch {
		// localStorage may be blocked (e.g., strict privacy settings) — silently fail.
	}
})();
