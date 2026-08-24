<?php
/**
 * Redirect Manager for Sky Login Redirect
 *
 * Modern PHP 8.1+ implementation with strict types.
 * Handles all redirect logic for login, logout, and registration.
 *
 * @package Sky_Login_Redirect
 */

declare(strict_types=1);

namespace SkyLoginRedirect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_User;
use function SkyLoginRedirect\carbonade;
use function SkyLoginRedirect\carbonade_pipe;

/**
 * Manages redirect logic for login, logout, and registration actions.
 */
final class RedirectManager {
	private const REDIRECT_LOOP_THRESHOLD = 3;
	private const REDIRECT_LOOP_TIMEOUT   = 30;

	/**
	 * Cached WooCommerce My Account page ID.
	 *
	 * @var int|null
	 */
	private ?int $wc_myaccount_id = null;

	/**
	 * Process redirect based on rules and context.
	 *
	 * @param string|null  $redirect_to           Default redirect URL.
	 * @param string|null  $requested_redirect_to Requested redirect URL.
	 * @param WP_User|null $user                 Current user object.
	 * @return string Validated redirect URL.
	 */
	public function processRedirect(
		?string $redirect_to,
		?string $requested_redirect_to,
		?WP_User $user
	): string {
		// Honor explicit redirect_to parameter if present
		$explicit_redirect = $this->getExplicitRedirect();
		if ( $explicit_redirect ) {
			return $explicit_redirect;
		}

		// carbonade_pipe() reassembles CF complex field rows without CF being booted —
		// safe on wp-login.php where carbon_get_theme_option() is unavailable.
		$cache_key = 'slr_redirect_rules';
		$rules     = wp_cache_get( $cache_key, 'slr' );
		if ( false === $rules ) {
			$rules = carbonade_pipe( 'slr_xlogin_logout' );
			wp_cache_set( $cache_key, $rules, 'slr', HOUR_IN_SECONDS );
		}

		// No rules configured - leave the default redirect untouched so users
		// not covered by any rule (e.g. administrators) land where WordPress and
		// other plugins intended (the dashboard, a requested page, etc.).
		if ( empty( $rules ) ) {
			return $this->defaultRedirect( $redirect_to );
		}

		// Find and apply matching rule
		foreach ( $rules as $rule ) {
			if ( $this->ruleApplies( $rule, $user ) ) {
				$url = $this->getRedirectUrlForRule(
					$rule,
					$redirect_to,
					$requested_redirect_to,
					$user
				);

				if ( $url ) {
					return $url;
				}
			}
		}

		// No rule matched this user - preserve the default redirect computed by
		// WordPress/other plugins (e.g. wp-admin for administrators) rather than
		// forcing every uncovered user to the homepage.
		return $this->defaultRedirect( $redirect_to );
	}

	/**
	 * Default redirect to use when the plugin has no matching rule.
	 *
	 * Honors the incoming redirect target so users outside the configured rules
	 * keep WordPress' default behaviour. Falls back to the homepage only when no
	 * destination was provided.
	 *
	 * @param string|null $redirect_to Default redirect URL from the filter.
	 * @return string Validated redirect URL.
	 */
	private function defaultRedirect( ?string $redirect_to ): string {
		if ( ! empty( $redirect_to ) ) {
			return wp_validate_redirect( $redirect_to, home_url( '/' ) );
		}

		return esc_url_raw( home_url( '/' ) );
	}

	/**
	 * Get explicit redirect_to parameter from URL if present.
	 *
	 * @return string|null Validated redirect URL or null.
	 */
	private function getExplicitRedirect(): ?string {
		$requested_raw = filter_input( INPUT_GET, 'redirect_to', FILTER_DEFAULT );

		if ( ! $requested_raw ) {
			return null;
		}

		$requested = wp_unslash( (string) $requested_raw );
		return wp_validate_redirect( $requested, admin_url( '/' ) );
	}

	/**
	 * Check if a redirect rule applies to the current user.
	 *
	 * @param array        $rule Redirect rule configuration.
	 * @param WP_User|null $user Current user object.
	 * @return bool True if rule applies.
	 */
	private function ruleApplies( array $rule, ?WP_User $user ): bool {
		$rule_type = $rule['slr_xselect_redirect'] ?? '';

		return match ( $rule_type ) {
			'user' => $this->ruleAppliesToUser( $rule, $user ),
			'role' => $this->ruleAppliesToRole( $rule, $user ),
			'all' => true,
			default => false,
		};
	}

	/**
	 * Check if rule applies to specific user.
	 *
	 * @param array        $rule Redirect rule configuration.
	 * @param WP_User|null $user Current user object.
	 * @return bool True if rule applies to user.
	 */
	private function ruleAppliesToUser( array $rule, ?WP_User $user ): bool {
		if ( ! $user instanceof WP_User ) {
			return false;
		}

		$target_users = $rule['slr_xuser'] ?? [];

		foreach ( $target_users as $usr ) {
			// New format: Carbon Fields association field returns array with 'id' key
			if ( is_array( $usr ) && isset( $usr['id'] ) ) {
				$user_id = (int) $usr['id'];
				if ( $user_id === $user->ID ) {
					return true;
				}
				continue;
			}

			// Legacy format: "Display Name (ID=123)" - for backward compatibility during migration
			if ( is_string( $usr ) && preg_match( '/\(ID=(\d+)\)/', $usr, $matches ) ) {
				$user_id = (int) $matches[1];
				if ( $user_id === $user->ID ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if rule applies to user role.
	 *
	 * @param array        $rule Redirect rule configuration.
	 * @param WP_User|null $user Current user object.
	 * @return bool True if rule applies to role.
	 */
	private function ruleAppliesToRole( array $rule, ?WP_User $user ): bool {
		if ( ! $user instanceof WP_User || ! is_array( $user->roles ) ) {
			return false;
		}

		$target_roles = $rule['slr_xrole'] ?? [];
		$target_roles = is_array( $target_roles ) ? $target_roles : [ $target_roles ];

		// Match against every role the user holds, not just the primary one,
		// so multi-role users are handled correctly.
		return (bool) array_intersect( $user->roles, $target_roles );
	}

	/**
	 * Get redirect URL based on rule configuration.
	 *
	 * @param array        $rule Redirect rule configuration.
	 * @param string|null  $redirect_to Default redirect URL.
	 * @param string|null  $requested_redirect_to Requested redirect URL.
	 * @param WP_User|null $user Current user object.
	 * @return string|null Redirect URL or null.
	 */
	private function getRedirectUrlForRule(
		array $rule,
		?string $redirect_to,
		?string $requested_redirect_to,
		?WP_User $user
	): ?string {
		// Determine action type (login, logout, register)
		$action = $this->getCurrentAction();

		// Get redirect type for this action
		$redirect_type = $rule[ "slr_xselect_{$action}" ] ?? '';

		return match ( $redirect_type ) {
			'prior'  => $this->getPriorUrl( $redirect_to, $requested_redirect_to, $user ),
			'page'   => $this->getPageUrl( $rule, $action ),
			'custom' => $this->getCustomUrl( $rule, $action ),
			default  => null, // Action not configured for this rule — skip, let next rule or default apply.
		};
	}

	/**
	 * Get current action type from filter context.
	 *
	 * Uses an explicit map so WooCommerce/EDD redirect filters (which bypass
	 * WordPress's standard login_redirect) resolve to the same action keys as
	 * the core filters.
	 *
	 * @return string Action type (login, logout, register).
	 */
	private function getCurrentAction(): string {
		return match ( current_filter() ) {
			'login_redirect',
			'woocommerce_login_redirect',
			'edd_login_redirect',
			'wp_ajax_nopriv_ajaxlogin' => 'login',
			'logout_redirect',
			'woocommerce_logout_default_redirect_url' => 'logout',
			'woocommerce_registration_redirect',
			'edd_register_redirect'    => 'register',
			default                    => str_replace(
				[ '_redirect', 'wp_ajax_nopriv_ajax' ],
				'',
				current_filter()
			),
		};
	}

	/**
	 * Get page redirect URL.
	 *
	 * @param array  $rule Redirect rule configuration.
	 * @param string $action Action type.
	 * @return string|null Page permalink or null.
	 */
	private function getPageUrl( array $rule, string $action ): ?string {
		$page_data = $rule[ "slr_x{$action}_page" ] ?? null;

		// Handle new association field format (array of items)
		if ( is_array( $page_data ) && ! empty( $page_data ) ) {
			$first_item = $page_data[0];
			if ( is_array( $first_item ) && isset( $first_item['id'] ) ) {
				$page_id   = (int) $first_item['id'];
				$permalink = $page_id ? get_permalink( $page_id ) : false;
				return $permalink ? $permalink : null;
			}
		}

		// Legacy format: direct page ID (for backward compatibility during migration)
		if ( is_numeric( $page_data ) ) {
			$page_id   = (int) $page_data;
			$permalink = $page_id ? get_permalink( $page_id ) : false;
			return $permalink ? $permalink : null;
		}

		return null;
	}

	/**
	 * Get custom redirect URL.
	 *
	 * @param array  $rule Redirect rule configuration.
	 * @param string $action Action type.
	 * @return string|null Custom URL or null.
	 */
	private function getCustomUrl( array $rule, string $action ): ?string {
		$url = $rule[ "slr_x{$action}_url" ] ?? null;
		return $url ? wp_validate_redirect( $url, home_url( '/' ) ) : null;
	}

	/**
	 * Get prior/referer URL with loop protection.
	 *
	 * @param string|null  $redirect_to Default redirect URL.
	 * @param string|null  $requested_redirect_to Requested redirect URL.
	 * @param WP_User|null $user Current user object.
	 * @return string|null Prior URL or null.
	 */
	private function getPriorUrl(
		?string $redirect_to,
		?string $requested_redirect_to,
		?WP_User $user
	): ?string {
		$referer = get_pre_login_url();

		// Redirect loop protection
		if ( $this->isRedirectLoop( $redirect_to, $user ) ) {
			return home_url( '/' );
		}

		$this->trackRedirectAttempt( $redirect_to, $user );

		// Prevent wp-login.php loops (login only)
		if ( $this->isLoginToLoginPage( $redirect_to ) ) {
			// Cookie fallback: security plugins (e.g. WPS Limit Login) may strip
			// the redirect_to field, leaving $redirect_to pointing at wp-login.php.
			// The cookie still holds the real previous page URL.
			if ( $referer && ! $this->isRefererLoginPage( $referer ) ) {
				return wp_validate_redirect( $referer, home_url( '/' ) );
			}
			return wp_validate_redirect( home_url( '/' ), home_url( '/' ) );
		}

		// WooCommerce: prevent My Account endpoint loops
		if ( $this->isWooCommerceEndpointLoop( $redirect_to ) ) {
			return $this->getWooCommerceMyAccountUrl();
		}

		// Honor wp-admin redirect for login
		if ( $this->shouldRedirectToAdmin( $requested_redirect_to ) ) {
			return wp_validate_redirect(
				esc_url_raw( $requested_redirect_to ),
				home_url( '/' )
			);
		}

		// Logout from wp-admin: redirect to homepage
		if ( $this->isLogoutFromAdmin( $referer ) ) {
			return wp_validate_redirect( home_url( '/' ), home_url( '/' ) );
		}

		// Handle subdirectory WordPress installations
		if ( $this->isRefererLoginPage( $referer ) ) {
			return wp_validate_redirect(
				(string) $redirect_to,
				home_url( '/' )
			);
		}

		// Use referer if available
		if ( $referer ) {
			return wp_validate_redirect( $referer, home_url( '/' ) );
		}

		// Fallback to requested redirect
		return wp_validate_redirect(
			(string) $redirect_to,
			home_url( '/' )
		);
	}

	/**
	 * Check if redirect is looping.
	 *
	 * @param string|null  $redirect_to Redirect URL.
	 * @param WP_User|null $user Current user.
	 * @return bool True if looping detected.
	 */
	private function isRedirectLoop( ?string $redirect_to, ?WP_User $user ): bool {
		$attempts = (int) wp_cache_get( $this->getRedirectKey( $redirect_to, $user ), 'slr_loop' );

		return $attempts > self::REDIRECT_LOOP_THRESHOLD;
	}

	/**
	 * Track redirect attempt for loop detection.
	 *
	 * @param string|null  $redirect_to Redirect URL.
	 * @param WP_User|null $user Current user.
	 * @return void
	 */
	private function trackRedirectAttempt( ?string $redirect_to, ?WP_User $user ): void {
		$key      = $this->getRedirectKey( $redirect_to, $user );
		$attempts = (int) wp_cache_get( $key, 'slr_loop' );

		wp_cache_set( $key, $attempts + 1, 'slr_loop', self::REDIRECT_LOOP_TIMEOUT );
	}

	/**
	 * Build cache key for redirect loop detection.
	 *
	 * @param string|null  $redirect_to Redirect URL.
	 * @param WP_User|null $user Current user.
	 * @return string Cache key.
	 */
	private function getRedirectKey( ?string $redirect_to, ?WP_User $user ): string {
		$user_id = $user instanceof WP_User ? $user->ID : 0;
		return 'slr_redirect_' . md5( $redirect_to . $user_id . current_filter() );
	}

	/**
	 * Check if redirecting to login page during login.
	 *
	 * @param string|null $redirect_to Redirect URL.
	 * @return bool True if login loop detected.
	 */
	private function isLoginToLoginPage( ?string $redirect_to ): bool {
		return str_contains( (string) $redirect_to, 'wp-login.php' )
			&& current_filter() === 'login_redirect';
	}

	/**
	 * Check if WooCommerce endpoint would cause loop.
	 *
	 * @param string|null $redirect_to Redirect URL.
	 * @return bool True if WooCommerce loop detected.
	 */
	private function isWooCommerceEndpointLoop( ?string $redirect_to ): bool {
		if ( ! function_exists( 'wc_get_page_id' ) || current_filter() !== 'login_redirect' ) {
			return false;
		}

		$my_account_id = $this->getWooCommerceMyAccountId();

		// Cache url_to_postid() result — expensive uncached DB query (VIP / Cache Product Objects compat).
		$cache_key = 'slr_u2p_' . md5( (string) $redirect_to );
		$post_id   = wp_cache_get( $cache_key, 'slr' );
		if ( false === $post_id ) {
			$post_id = url_to_postid( (string) $redirect_to );
			wp_cache_set( $cache_key, $post_id, 'slr', HOUR_IN_SECONDS );
		}

		if ( (int) $post_id !== $my_account_id ) {
			return false;
		}

		$endpoints = [ 'customer-logout', 'lost-password' ];

		foreach ( $endpoints as $endpoint ) {
			if ( str_contains( (string) $redirect_to, $endpoint ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get WooCommerce My Account page URL.
	 *
	 * @return string|null My Account URL or null.
	 */
	private function getWooCommerceMyAccountUrl(): ?string {
		if ( ! function_exists( 'wc_get_page_id' ) ) {
			return null;
		}

		$permalink = get_permalink( $this->getWooCommerceMyAccountId() );
		return $permalink ? $permalink : null;
	}

	/**
	 * Get cached WooCommerce My Account page ID.
	 *
	 * @return int My Account page ID.
	 */
	private function getWooCommerceMyAccountId(): int {
		if ( null === $this->wc_myaccount_id ) {
			$this->wc_myaccount_id = function_exists( 'wc_get_page_id' )
				? wc_get_page_id( 'myaccount' )
				: 0;
		}
		return $this->wc_myaccount_id;
	}

	/**
	 * Check if should redirect to wp-admin.
	 *
	 * @param string|null $requested_redirect_to Requested redirect URL.
	 * @return bool True if should redirect to admin.
	 */
	private function shouldRedirectToAdmin( ?string $requested_redirect_to ): bool {
		return str_contains( (string) $requested_redirect_to, 'wp-admin' )
			&& current_filter() === 'login_redirect';
	}

	/**
	 * Check if logging out from wp-admin.
	 *
	 * @param string|null $referer Referer URL.
	 * @return bool True if logout from admin.
	 */
	private function isLogoutFromAdmin( ?string $referer ): bool {
		return str_contains( (string) $referer, 'wp-admin' )
			&& current_filter() === 'logout_redirect';
	}

	/**
	 * Check if referer is a login page (wp-login.php or WooCommerce My Account).
	 *
	 * WooCommerce My Account doubles as the WC login page. If the HTTP referer is
	 * My Account we must not treat it as the "previous page" for prior-page
	 * redirects — doing so creates a My Account redirect loop.
	 *
	 * @param string|null $referer Referer URL.
	 * @return bool True if referer is login page.
	 */
	private function isRefererLoginPage( ?string $referer ): bool {
		$login_url = wp_login_url();

		if ( $this->urlsMatch( $referer, $login_url ) ) {
			return true;
		}

		// Only apply the checks below for login contexts — not logout.
		// A user logging OUT from My Account / a custom login page should not have
		// those pages treated as "the login page" (getPriorUrl() would then return
		// the WP logout URL and redirect to wp-login.php).
		$login_filters = [ 'login_redirect', 'woocommerce_login_redirect', 'wp_ajax_nopriv_ajaxlogin' ];
		if ( ! in_array( current_filter(), $login_filters, true ) ) {
			return false;
		}

		// WooCommerce My Account page doubles as the WC login page.
		// phpcs:ignore WordPress.WP.AlternativeFunctions -- wc_get_page_id is WC's own API
		if ( function_exists( 'wc_get_page_id' ) ) {
			$my_account_url = get_permalink( wc_get_page_id( 'myaccount' ) );
			if ( $my_account_url && $this->urlsMatch( $referer, $my_account_url ) ) {
				return true;
			}
		}

		// Custom login URL configured in the Shop tweaks.
		$custom_login_url = (string) carbonade( 'slr_custom_login_url', '' );
		if ( 'custom' === carbonade( 'slr_login_url_select' )
			&& $custom_login_url
			&& $this->urlsMatch( $referer, $custom_login_url )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Compare two URLs without query strings, fragments, or trailing-slash differences.
	 *
	 * @param string|null $first  First URL.
	 * @param string|null $second Second URL.
	 * @return bool Whether both URLs identify the same page.
	 */
	private function urlsMatch( ?string $first, ?string $second ): bool {
		if ( ! $first || ! $second ) {
			return false;
		}

		$normalize = static function ( string $url ): string {
			$host   = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
			$path   = (string) wp_parse_url( $url, PHP_URL_PATH );
			$scheme = strtolower( (string) wp_parse_url( $url, PHP_URL_SCHEME ) );

			return $scheme . '://' . $host . untrailingslashit( '/' . ltrim( $path, '/' ) );
		};

		return $normalize( $first ) === $normalize( $second );
	}
}
