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

/**
 * Manages redirect logic for login, logout, and registration actions.
 */
final class RedirectManager {
    private const REDIRECT_LOOP_THRESHOLD = 3;
    private const REDIRECT_LOOP_TIMEOUT = 30;

    /**
     * Process redirect based on rules and context.
     *
     * @param string|null $redirect_to           Default redirect URL.
     * @param string|null $requested_redirect_to Requested redirect URL.
     * @param WP_User|null $user                 Current user object.
     * @return string Validated redirect URL.
     */
    public function processRedirect(
        ?string $redirect_to,
        ?string $requested_redirect_to,
        $user
    ): string {
        // Honor explicit redirect_to parameter if present
        $explicit_redirect = $this->getExplicitRedirect();
        if ( $explicit_redirect ) {
            return $explicit_redirect;
        }

        // Get redirect rules from options
        $rules = carbon_get_theme_option( 'slr_xlogin_logout' );

        // No rules configured - redirect to homepage
        if ( empty( $rules ) ) {
            return esc_url_raw( home_url( '/' ) );
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

        // Fallback to homepage
        return esc_url_raw( home_url( '/' ) );
    }

    /**
     * Get explicit redirect_to parameter from URL if present.
     *
     * @return string|null Validated redirect URL or null.
     */
    private function getExplicitRedirect(): ?string {
        $requested_raw = filter_input( INPUT_GET, 'redirect_to', FILTER_UNSAFE_RAW );

        if ( ! $requested_raw ) {
            return null;
        }

        $requested = wp_unslash( (string) $requested_raw );
        return wp_validate_redirect( $requested, admin_url( '/' ) );
    }

    /**
     * Check if a redirect rule applies to the current user.
     *
     * @param array $rule Redirect rule configuration.
     * @param WP_User|null $user Current user object.
     * @return bool True if rule applies.
     */
    private function ruleApplies( array $rule, $user ): bool {
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
     * @param array $rule Redirect rule configuration.
     * @param WP_User|null $user Current user object.
     * @return bool True if rule applies to user.
     */
    private function ruleAppliesToUser( array $rule, $user ): bool {
        if ( ! $user || ! is_a( $user, 'WP_User' ) ) {
            return false;
        }

        $target_users = $rule['slr_xuser'] ?? [];

        foreach ( $target_users as $usr ) {
            // Extract user ID from format "Display Name (ID=123)"
            if ( preg_match( '/\(ID=(\d+)\)/', $usr, $matches ) ) {
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
     * @param array $rule Redirect rule configuration.
     * @param WP_User|null $user Current user object.
     * @return bool True if rule applies to role.
     */
    private function ruleAppliesToRole( array $rule, $user ): bool {
        if ( ! isset( $user->roles ) || ! is_array( $user->roles ) ) {
            return false;
        }

        $target_roles = $rule['slr_xrole'] ?? [];
        $user_role = $user->roles[0] ?? '';

        return in_array( $user_role, $target_roles, true );
    }

    /**
     * Get redirect URL based on rule configuration.
     *
     * @param array $rule Redirect rule configuration.
     * @param string|null $redirect_to Default redirect URL.
     * @param string|null $requested_redirect_to Requested redirect URL.
     * @param WP_User|null $user Current user object.
     * @return string|null Redirect URL or null.
     */
    private function getRedirectUrlForRule(
        array $rule,
        ?string $redirect_to,
        ?string $requested_redirect_to,
        $user
    ): ?string {
        // Determine action type (login, logout, register)
        $action = $this->getCurrentAction();

        // Get redirect type for this action
        $redirect_type = $rule["slr_xselect_{$action}"] ?? '';

        return match ( $redirect_type ) {
            'prior' => $this->getPriorUrl( $redirect_to, $requested_redirect_to, $user ),
            'page' => $this->getPageUrl( $rule, $action ),
            'custom' => $this->getCustomUrl( $rule, $action ),
            default => admin_url( '/' ),
        };
    }

    /**
     * Get current action type from filter context.
     *
     * @return string Action type (login, logout, register).
     */
    private function getCurrentAction(): string {
        $filter = current_filter();

        return str_replace(
            [ '_redirect', 'wp_ajax_nopriv_ajax' ],
            '',
            $filter
        );
    }

    /**
     * Get page redirect URL.
     *
     * @param array $rule Redirect rule configuration.
     * @param string $action Action type.
     * @return string|null Page permalink or null.
     */
    private function getPageUrl( array $rule, string $action ): ?string {
        $page_id = $rule["slr_x{$action}_page"] ?? 0;

        return $page_id ? get_permalink( $page_id ) : null;
    }

    /**
     * Get custom redirect URL.
     *
     * @param array $rule Redirect rule configuration.
     * @param string $action Action type.
     * @return string|null Custom URL or null.
     */
    private function getCustomUrl( array $rule, string $action ): ?string {
        return $rule["slr_x{$action}_url"] ?? null;
    }

    /**
     * Get prior/referer URL with loop protection.
     *
     * @param string|null $redirect_to Default redirect URL.
     * @param string|null $requested_redirect_to Requested redirect URL.
     * @param WP_User|null $user Current user object.
     * @return string|null Prior URL or null.
     */
    private function getPriorUrl(
        ?string $redirect_to,
        ?string $requested_redirect_to,
        $user
    ): ?string {
        $referer = Sky_get_last_page_visited_cookie();

        // Redirect loop protection
        if ( $this->isRedirectLoop( $redirect_to, $user ) ) {
            return home_url( '/' );
        }

        $this->trackRedirectAttempt( $redirect_to, $user );

        // Prevent wp-login.php loops (login only)
        if ( $this->isLoginToLoginPage( $redirect_to ) ) {
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
     * @param string|null $redirect_to Redirect URL.
     * @param WP_User|null $user Current user.
     * @return bool True if looping detected.
     */
    private function isRedirectLoop( ?string $redirect_to, $user ): bool {
        $user_id = is_object( $user ) && isset( $user->ID ) ? $user->ID : 0;
        $redirect_key = 'slr_redirect_' . md5( $redirect_to . $user_id . current_filter() );
        $attempts = (int) get_transient( $redirect_key );

        return $attempts > self::REDIRECT_LOOP_THRESHOLD;
    }

    /**
     * Track redirect attempt for loop detection.
     *
     * @param string|null $redirect_to Redirect URL.
     * @param WP_User|null $user Current user.
     * @return void
     */
    private function trackRedirectAttempt( ?string $redirect_to, $user ): void {
        $user_id = is_object( $user ) && isset( $user->ID ) ? $user->ID : 0;
        $redirect_key = 'slr_redirect_' . md5( $redirect_to . $user_id . current_filter() );
        $attempts = (int) get_transient( $redirect_key );

        set_transient( $redirect_key, $attempts + 1, self::REDIRECT_LOOP_TIMEOUT );
    }

    /**
     * Check if redirecting to login page during login.
     *
     * @param string|null $redirect_to Redirect URL.
     * @return bool True if login loop detected.
     */
    private function isLoginToLoginPage( ?string $redirect_to ): bool {
        return false !== strpos( (string) $redirect_to, 'wp-login.php' )
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

        $my_account_id = wc_get_page_id( 'myaccount' );

        if ( url_to_postid( (string) $redirect_to ) !== $my_account_id ) {
            return false;
        }

        $endpoints = [ 'customer-logout', 'lost-password' ];

        foreach ( $endpoints as $endpoint ) {
            if ( false !== strpos( (string) $redirect_to, $endpoint ) ) {
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

        return get_permalink( wc_get_page_id( 'myaccount' ) );
    }

    /**
     * Check if should redirect to wp-admin.
     *
     * @param string|null $requested_redirect_to Requested redirect URL.
     * @return bool True if should redirect to admin.
     */
    private function shouldRedirectToAdmin( ?string $requested_redirect_to ): bool {
        return false !== strpos( (string) $requested_redirect_to, 'wp-admin' )
            && current_filter() === 'login_redirect';
    }

    /**
     * Check if logging out from wp-admin.
     *
     * @param string|null $referer Referer URL.
     * @return bool True if logout from admin.
     */
    private function isLogoutFromAdmin( ?string $referer ): bool {
        return false !== strpos( (string) $referer, 'wp-admin' )
            && current_filter() === 'logout_redirect';
    }

    /**
     * Check if referer is login page.
     *
     * @param string|null $referer Referer URL.
     * @return bool True if referer is login page.
     */
    private function isRefererLoginPage( ?string $referer ): bool {
        $home_url = trailingslashit( home_url() );
        $login_url = $home_url . 'wp-login.php';

        return $referer === $login_url;
    }
}
