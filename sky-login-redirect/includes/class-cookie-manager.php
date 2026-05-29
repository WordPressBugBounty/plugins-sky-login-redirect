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

	/**
	 * Singleton instance.
	 */
	private static ?self $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return self The CookieManager instance.
	 */
	public static function getInstance(): self {
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Private constructor to enforce singleton pattern.
	 */
	private function __construct() {}

	/**
	 * Get the real client IP address, checking for proxy/CDN headers.
	 * 
	 * This static method checks multiple headers in order of reliability:
	 * 1. HTTP_CF_CONNECTING_IP (Cloudflare)
	 * 2. HTTP_X_FORWARDED_FOR (standard proxy header)
	 * 3. REMOTE_ADDR (direct connection)
	 * 
	 * The order can be filtered via the 'slr_rate_limit_ip' filter for custom environments.
	 * 
	 * @return string The client IP address, or empty string if not found.
	 */
	public static function getClientIp(): string {
		// Allow filtering of IP detection order for custom environments
		$ip_headers = apply_filters('slr_rate_limit_ip', [
			'HTTP_CF_CONNECTING_IP', // Cloudflare
			'HTTP_X_FORWARDED_FOR',  // Standard proxy header
			'REMOTE_ADDR',           // Direct connection
		]);

		foreach ($ip_headers as $header) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- We sanitize below
			$ip = $_SERVER[$header] ?? '';
			if (empty($ip)) {
				continue;
			}

			// X-Forwarded-For can contain multiple IPs (client, proxy1, proxy2)
			// We want the first one (the original client)
			if ($header === 'HTTP_X_FORWARDED_FOR') {
				$ips = explode(',', $ip);
				$ip = trim($ips[0]);
			}

			// Validate IP
			$ip = filter_var(trim($ip), FILTER_VALIDATE_IP);
			if ($ip !== false) {
				return $ip;
			}
		}

		return '';
	}

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
		$this->set_cookie( $current_url );
	}

	/**
	 * Get the last visited page URL from cookie.
	 *
	 * @return string The sanitized URL from the cookie, or empty string if not set.
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
	 * Static method to get the last visited page URL.
	 * Uses the singleton instance for consistency.
	 *
	 * @return string The sanitized URL from the cookie, or empty string if not set.
	 */
	public static function getLastVisitedUrlStatic(): string {
		return self::getInstance()->getLastVisitedUrl();
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
		$ip_address = self::getClientIp();
		return self::RATE_LIMIT_PREFIX . md5( $ip_address );
	}

	/**
	 * Set the cookie with secure options.
	 *
	 * @param string $url URL to store in the cookie.
	 * @return void
	 */
	private function set_cookie( string $url ): void {
		$options = [
			'expires'  => time() + HOUR_IN_SECONDS,
			'path'     => '/',
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		];

		if ( defined( 'COOKIE_DOMAIN' ) && COOKIE_DOMAIN ) {
			$options['domain'] = COOKIE_DOMAIN;
		}

		setcookie( self::COOKIE_NAME, $url, $options );
	}
}
