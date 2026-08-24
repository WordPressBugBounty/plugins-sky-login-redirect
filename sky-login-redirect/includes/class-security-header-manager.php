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

use function SkyLoginRedirect\is_login_page;

/**
 * Security header enumeration.
 */
enum SecurityHeader: string {
	case X_CONTENT_TYPE     = 'X-Content-Type-Options: nosniff';
	case X_FRAME            = 'X-Frame-Options: SAMEORIGIN';
	case REFERRER_POLICY    = 'Referrer-Policy: strict-origin-when-cross-origin';
	case PERMISSIONS_POLICY = 'Permissions-Policy: geolocation=(), microphone=(), camera=()';

	/**
	 * Get header name.
	 *
	 * @return string Header name portion before the colon.
	 */
	public function getName(): string {
		return explode( ':', $this->value )[0];
	}

	/**
	 * Get header value.
	 *
	 * @return string Header value portion after the colon.
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
	 *
	 * @return void
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
	 *
	 * @return bool True on login or admin pages.
	 */
	private function shouldAddHeaders(): bool {
		return is_login_page() || is_admin();
	}

	/**
	 * Send a security header.
	 *
	 * @param SecurityHeader $header Header enum case to send.
	 * @return void
	 */
	private function sendHeader( SecurityHeader $header ): void {
		if ( headers_sent() ) {
			return;
		}

		$name = $header->getName();
		foreach ( headers_list() as $existing_header ) {
			if ( str_starts_with( strtolower( $existing_header ), strtolower( $name . ':' ) ) ) {
				return;
			}
		}

		$value = apply_filters( 'slr_security_header_value', $header->getValue(), $name );
		if ( ! is_string( $value ) || '' === trim( $value ) || preg_match( '/[\r\n]/', $value ) ) {
			return;
		}

		header( $name . ': ' . trim( $value ), false );
	}
}
