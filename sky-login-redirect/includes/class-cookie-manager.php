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

/**
 * Cookie manager for last page visited tracking.
 */
final class CookieManager {
    private const COOKIE_NAME = 'last_page_visited';
    private const RATE_LIMIT_PREFIX = 'slr_cookie_rate_';
    private const MAX_ATTEMPTS_PER_MINUTE = 10;

    /**
     * Set the last visited page cookie with rate limiting.
     */
    public function setLastVisitedPage(): void {
        if ( is_admin() || is_user_logged_in() || $this->isLoginPage() ) {
            return;
        }

        if ( ! $this->checkRateLimit() ) {
            return;
        }

        $current_url = $this->getCurrentUrl();
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
     */
    public function getLastVisitedUrl(): string {
        $raw_cookie = filter_input( INPUT_COOKIE, self::COOKIE_NAME, FILTER_UNSAFE_RAW );
        if ( ! $raw_cookie ) {
            return '';
        }

        $cookie = wp_unslash( (string) $raw_cookie );
        return esc_url_raw( $cookie );
    }

    /**
     * Check if current page is a login page.
     */
    private function isLoginPage(): bool {
        global $pagenow;

        // Check if current page is wp-login.php
        if ( 'wp-login.php' === $pagenow ) {
            return true;
        }

        // Check if current page contains the [login-form] shortcode
        if ( function_exists( 'is_singular' ) && is_singular() ) {
            $post = get_post();
            if ( $post && function_exists( 'has_shortcode' ) && has_shortcode( (string) $post->post_content, 'login-form' ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get current page URL.
     */
    private function getCurrentUrl(): string {
        $req = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_UNSAFE_RAW );
        $req = $req ? wp_unslash( (string) $req ) : '/';
        return esc_url_raw( home_url( $req ) );
    }

    /**
     * Check rate limit for cookie updates.
     */
    private function checkRateLimit(): bool {
        $attempts = $this->getRateLimitAttempts();
        return $attempts <= self::MAX_ATTEMPTS_PER_MINUTE;
    }

    /**
     * Get current rate limit attempts.
     */
    private function getRateLimitAttempts(): int {
        $key = $this->getRateLimitKey();
        return (int) get_transient( $key );
    }

    /**
     * Increment rate limit counter.
     */
    private function incrementRateLimit(): void {
        $key = $this->getRateLimitKey();
        $attempts = $this->getRateLimitAttempts();
        set_transient( $key, $attempts + 1, MINUTE_IN_SECONDS );
    }

    /**
     * Get rate limit transient key.
     */
    private function getRateLimitKey(): string {
        $ip_address = isset( $_SERVER['REMOTE_ADDR'] )
            ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
            : '';
        return self::RATE_LIMIT_PREFIX . md5( $ip_address );
    }

    /**
     * Set the cookie with secure options.
     */
    private function setCookie( string $url ): void {
        $options = [
            'expires'  => time() + HOUR_IN_SECONDS,
            'path'     => '/',
            'secure'   => $this->isSsl(),
            'httponly' => true,
            'samesite' => 'Lax',
        ];

        if ( defined( 'COOKIE_DOMAIN' ) && COOKIE_DOMAIN ) {
            $options['domain'] = COOKIE_DOMAIN;
        }

        setcookie( self::COOKIE_NAME, $url, $options );
    }

    /**
     * Check if SSL is enabled.
     */
    private function isSsl(): bool {
        if ( isset( $_SERVER['HTTPS'] ) ) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- HTTPS is a boolean-like value
            $https = strtolower( (string) wp_unslash( $_SERVER['HTTPS'] ) );
            if ( 'on' === $https || '1' === $https ) {
                return true;
            }
        }

        if ( isset( $_SERVER['SERVER_PORT'] ) ) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- SERVER_PORT is numeric
            $port = (string) wp_unslash( $_SERVER['SERVER_PORT'] );
            if ( '443' === $port ) {
                return true;
            }
        }

        return false;
    }
}
