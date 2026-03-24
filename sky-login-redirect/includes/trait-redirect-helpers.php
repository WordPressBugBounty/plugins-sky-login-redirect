<?php
/**
 * Shared redirect helper methods for WooCommerce and EDD handlers.
 *
 * @package Sky_Login_Redirect
 */

declare(strict_types=1);

namespace SkyLoginRedirect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Redirect helper trait providing common URL validation and option checks.
 */
trait RedirectHelpers {
	/**
	 * Check if redirect is enabled in settings.
	 *
	 * @param string $option_key Option key to check.
	 * @return bool True if the option value is 'yes'.
	 */
	private function isRedirectEnabled( string $option_key ): bool {
		return 'yes' === carbonade( $option_key );
	}

	/**
	 * Validate URL.
	 *
	 * @param string|null $url URL to validate.
	 * @return bool True if the URL is valid.
	 */
	private function isValidUrl( ?string $url ): bool {
		return $url && filter_var( $url, FILTER_VALIDATE_URL ) !== false;
	}
}
