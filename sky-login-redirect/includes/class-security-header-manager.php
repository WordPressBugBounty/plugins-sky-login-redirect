<?php
/**
 * Security header management for login/admin pages.
 *
 * Modern PHP 8.1+ implementation with strict types and enums.
 *
 * @package Sky_Login_Redirect
 */

declare(strict_types=1);

namespace SkyLoginRedirect;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Security header enumeration.
 */
enum SecurityHeader: string {
    case X_CONTENT_TYPE = 'X-Content-Type-Options: nosniff';
    case X_FRAME = 'X-Frame-Options: SAMEORIGIN';
    case REFERRER_POLICY = 'Referrer-Policy: strict-origin-when-cross-origin';
    case PERMISSIONS_POLICY = 'Permissions-Policy: geolocation=(), microphone=(), camera=()';

    /**
     * Get header name.
     */
    public function getName(): string {
        return explode( ':', $this->value )[0];
    }

    /**
     * Get header value.
     */
    public function getValue(): string {
        return trim( explode( ':', $this->value, 2 )[1] ?? '' );
    }
}

/**
 * Security header manager.
 */
final class SecurityHeaderManager {
    /**
     * Add security headers to login/admin pages.
     */
    public function addSecurityHeaders(): void {
        if ( ! $this->shouldAddHeaders() ) {
            return;
        }

        if ( headers_sent() ) {
            return;
        }

        $this->sendHeader( SecurityHeader::X_CONTENT_TYPE );
        $this->sendHeader( SecurityHeader::X_FRAME );
        $this->sendHeader( SecurityHeader::REFERRER_POLICY );
        $this->sendHeader( SecurityHeader::PERMISSIONS_POLICY );
    }

    /**
     * Check if headers should be added.
     */
    private function shouldAddHeaders(): bool {
        return $this->isLoginPage() || is_admin();
    }

    /**
     * Check if current page is a login page.
     */
    private function isLoginPage(): bool {
        global $pagenow;

        if ( 'wp-login.php' === $pagenow ) {
            return true;
        }

        if ( function_exists( 'is_singular' ) && is_singular() ) {
            $post = get_post();
            if ( $post && function_exists( 'has_shortcode' ) && has_shortcode( (string) $post->post_content, 'login-form' ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Send a security header.
     */
    private function sendHeader( SecurityHeader $header ): void {
        if ( headers_sent() ) {
            return;
        }

        header( $header->value );
    }
}
