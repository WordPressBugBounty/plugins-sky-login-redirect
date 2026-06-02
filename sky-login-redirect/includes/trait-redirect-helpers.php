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

	/**
	 * Normalise a filter-supplied redirect value to a string.
	 *
	 * WordPress filters such as woocommerce_login_redirect normally pass a
	 * string, but a lower-priority callback from another plugin can return any
	 * type (bool, int, array, object). Scalars are cast; non-scalars (array,
	 * object, null) collapse to an empty string so downstream string operations
	 * never throw a TypeError.
	 *
	 * @param mixed $value Raw value from the filter chain.
	 * @return string Safe string representation.
	 */
	private function toRedirectString( mixed $value ): string {
		return is_scalar( $value ) ? (string) $value : '';
	}
}
