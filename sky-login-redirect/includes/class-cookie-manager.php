<?php
/**
 * Cookie management for tracking last visited page.
 *
 * Modern PHP 8.1+ implementation with strict types.
 *
 * @package Sky_Login_Redirect
 */

declare(strict_types=1);

namespace SkyLoginRedirect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use function SkyLoginRedirect\is_login_page;

/**
 * Cookie manager for last page visited tracking.
 */
final class CookieManager {
	private const COOKIE_NAME             = 'last_page_visited';
	private const RATE_LIMIT_PREFIX       = 'slr_cookie_rate_';
	private const MAX_ATTEMPTS_PER_MINUTE = 10;

	/**
	 * Set the last visited page cookie with rate limiting.
	 *
	 * @return void
	 */
	public function setLastVisitedPage(): void {
		if ( is_admin() || is_user_logged_in() || is_login_page() ) {
			return;
		}

		if ( ! $this->checkRateLimit() ) {
			return;
		}

		$current_url  = $this->getCurrentUrl();
		$existing_url = $this->getLastVisitedUrl();

		// Only set cookie if URL has changed
		if ( $current_url === $existing_url ) {
			return;
		}

		$this->incrementRateLimit();
		$this->setCookie( $current_url );
	}

	/**
	 * Get the last visited page URL from cookie.
	 *
	 * @return string Sanitized URL or empty string.
	 */
	public function getLastVisitedUrl(): string {
		$raw_cookie = filter_input( INPUT_COOKIE, self::COOKIE_NAME, FILTER_DEFAULT );
		if ( ! $raw_cookie ) {
			return '';
		}

		$cookie = wp_unslash( (string) $raw_cookie );
		return esc_url_raw( $cookie );
	}

	/**
	 * Get current page URL.
	 *
	 * @return string Current sanitized URL.
	 */
	private function getCurrentUrl(): string {
		$req = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_DEFAULT );
		$req = $req ? wp_unslash( (string) $req ) : '/';
		return esc_url_raw( home_url( $req ) );
	}

	/**
	 * Check rate limit for cookie updates.
	 *
	 * @return bool True if under the rate limit.
	 */
	private function checkRateLimit(): bool {
		$attempts = $this->getRateLimitAttempts();
		return $attempts <= self::MAX_ATTEMPTS_PER_MINUTE;
	}

	/**
	 * Get current rate limit attempts.
	 *
	 * Uses object cache instead of transients to avoid DB writes on every frontend page.
	 *
	 * @return int Number of attempts in the current window.
	 */
	private function getRateLimitAttempts(): int {
		$key = $this->getRateLimitKey();
		return (int) wp_cache_get( $key, 'slr_rate' );
	}

	/**
	 * Increment rate limit counter.
	 *
	 * Uses object cache instead of transients to avoid DB writes on every frontend page.
	 *
	 * @return void
	 */
	private function incrementRateLimit(): void {
		$key      = $this->getRateLimitKey();
		$attempts = $this->getRateLimitAttempts();
		wp_cache_set( $key, $attempts + 1, 'slr_rate', MINUTE_IN_SECONDS );
	}

	/**
	 * Get rate limit cache key.
	 *
	 * @return string MD5-hashed key based on client IP.
	 */
	private function getRateLimitKey(): string {
		$ip_address = isset( $_SERVER['REMOTE_ADDR'] )
			? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
			: '';
		return self::RATE_LIMIT_PREFIX . md5( $ip_address );
	}

	/**
	 * Set the cookie with secure options.
	 *
	 * @param string $url URL to store in the cookie.
	 * @return void
	 */
	private function setCookie( string $url ): void {
		$options = array(
			'expires'  => time() + HOUR_IN_SECONDS,
			'path'     => '/',
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		);

		if ( defined( 'COOKIE_DOMAIN' ) && COOKIE_DOMAIN ) {
			$options['domain'] = COOKIE_DOMAIN;
		}

		setcookie( self::COOKIE_NAME, $url, $options );
	}
}
