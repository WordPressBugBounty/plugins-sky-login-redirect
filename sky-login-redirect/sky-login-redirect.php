<?php

/**
 * Plugin Name: Sky Login Redirect
 * Plugin URI: https://utopique.net/products/sky-login-redirect-premium/
 * Description: Advanced login/logout redirects with user/role rules, content restriction, login customizer, and WooCommerce integration.
 * Version: 4.2.6
 * Author: Utopique
 * Author URI: https://utopique.net/
 * Developer: Utopique
 * Developer URI: https://utopique.net/
 * Copyright: (c) 2009-2026 Utopique
 * Text Domain: sky-login-redirect
 * License: GPLv3 or later
 * Requires at least: 5.6
 * Tested up to: 7.0
 * Requires PHP: 8.1
 * WC requires at least: 3.3
 * WC tested up to: 11
 *
 * Modern PHP 8.1+ implementation with strict types and enums.
 *
 * @category        Login_Redirect
 * @package         Sky_Login_Redirect
 * @author          Utopique <support@utopique.net>
 * @license         GPL https://utopique.net
 * @link            https://utopique.net
 */
declare (strict_types = 1);
namespace SkyLoginRedirect;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Current version.
define( 'SLR_VERSION', '4.2.6' );
// Plugin root path.
define( 'SLR_ROOT', trailingslashit( plugin_dir_path( __FILE__ ) ) );
// Composer autoloader — must be loaded unconditionally so that:
// 1. Freemius SDK global functions (fs_dynamic_init, etc.) are available at plugin load time.
// 2. CF classes (Carbon_Fields\Widget, etc.) are available for class inheritance site-wide.
require_once SLR_ROOT . 'vendor/autoload.php';
/** Plugin admin screen IDs. */
const PLUGIN_SCREENS = [
    'toplevel_page_sky-login-redirect',
    'login-redirect_page_sky-login-redirect-account',
    'login-redirect_page_sky-login-redirect-contact',
    'login-redirect_page_sky-login-redirect-pricing'
];
/**
 * Freemius
 */
if ( function_exists( __NAMESPACE__ . '\\sky_login_redirect_fs' ) ) {
    sky_login_redirect_fs()->set_basename( false, __FILE__ );
} else {
    /**
     * DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE
     * `function_exists` CALL ABOVE TO PROPERLY WORK.
     */
    if ( !function_exists( __NAMESPACE__ . '\\sky_login_redirect_fs' ) ) {
        // Create a helper function for easy SDK access.
        function sky_login_redirect_fs() {
            global $sky_login_redirect_fs;
            if ( !isset( $sky_login_redirect_fs ) ) {
                // Include Freemius SDK.
                // SDK is auto-loaded through Composer
                $sky_login_redirect_fs = \fs_dynamic_init( array(
                    'id'               => '3088',
                    'slug'             => 'sky-login-redirect',
                    'type'             => 'plugin',
                    'public_key'       => 'pk_f0e9c9d4e383120cf38d5b44b586b',
                    'is_premium'       => false,
                    'premium_suffix'   => 'Pro',
                    'has_addons'       => false,
                    'has_paid_plans'   => true,
                    'is_org_compliant' => true,
                    'menu'             => array(
                        'slug' => 'sky-login-redirect',
                    ),
                    'is_live'          => true,
                ) );
            }
            return $sky_login_redirect_fs;
        }

        /**
         * Return the local plugin icon path for Freemius.
         *
         * @return string Icon file path.
         */
        function get_plugin_icon() : string {
            return __DIR__ . '/assets/img/sky-login-redirect.png';
        }

        if ( is_admin() ) {
            // Init Freemius.
            sky_login_redirect_fs();
            // Ensure the plugin icon resolves to the committed local asset
            // regardless of the Freemius SDK assets folder copy.
            sky_login_redirect_fs()->add_filter( 'plugin_icon', __NAMESPACE__ . '\\get_plugin_icon' );
            // Signal that SDK was initiated.
            do_action( 'sky_login_redirect_fs_loaded' );
        }
        /**
         * Clean up all plugin data on uninstall.
         * Hooked to Freemius after_uninstall to allow uninstall tracking.
         *
         * @return void
         */
        function sky_login_redirect_fs_uninstall_cleanup() : void {
            global $wpdb;
            // Delete main plugin options.
            delete_option( 'sky_login_redirect' );
            delete_option( 'SLR_BFCM' );
            // Delete all Carbon Fields options (prefixed with _slr_).
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $wpdb->esc_like( '_slr_' ) . '%' ) );
            // Delete transients.
            delete_transient( 'sky_login_redirect' );
            // Clear object cache.
            wp_cache_delete( 'sky_login_redirect', 'slr' );
        }

        // Hook uninstall cleanup to Freemius after_uninstall action — admin only.
        // Freemius uninstall flows always run in an admin context; registering this
        // hook on frontend or WP-CLI would trigger a needless FS initialisation.
        if ( is_admin() ) {
            sky_login_redirect_fs()->add_action( 'after_uninstall', __NAMESPACE__ . '\\sky_login_redirect_fs_uninstall_cleanup' );
        }
        // Translations are automatically loaded by WordPress for plugins hosted on WordPress.org
        /**
         * Load Carbon Fields dependency via Composer.
         *
         * Boots CF on every admin request and on CF REST saves so that:
         *  - The admin sidebar menu item is registered on every admin page load
         *    (CF's Container::make() registers the WP menu — if CF does not boot,
         *    the menu item never appears anywhere in the admin).
         *  - Field values can be saved via the CF REST API.
         *
         * CF is intentionally NOT booted on frontend or WP-CLI requests — that is
         * where the performance win lives (5–15 ms PHP time, ~1–2 MB memory saved
         * per public page load).
         *
         * REST_REQUEST is not yet defined at after_setup_theme, so CF REST requests
         * are detected by inspecting the raw REQUEST_URI instead.
         *
         * @return void
         */
        function load_carbon_fields() : void {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $request_uri = ( isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '' );
            $is_cf_rest = str_contains( $request_uri, '/carbon-fields/' );
            // Boot on all admin requests and on CF REST saves.
            // Skip everything else (frontend, WP-CLI).
            if ( !is_admin() && !$is_cf_rest ) {
                return;
            }
            \Carbon_Fields\Carbon_Fields::boot();
            // Remove CF's sidebar widget manager scripts — not needed for theme options.
            $sidebar_manager = \Carbon_Fields\Carbon_Fields::resolve( 'sidebar_manager' );
            remove_action( 'admin_enqueue_scripts', [$sidebar_manager, 'enqueue_scripts'] );
        }

        add_action( 'after_setup_theme', __NAMESPACE__ . '\\load_carbon_fields' );
        /**
         * Load plugin options file.
         *
         * @return void
         */
        function load_plugin() : void {
            include_once plugin_dir_path( __FILE__ ) . 'includes/options.php';
        }

        add_action( 'plugins_loaded', __NAMESPACE__ . '\\load_plugin' );
        include_once plugin_dir_path( __FILE__ ) . 'includes/meta.php';
        //include_once plugin_dir_path( __FILE__ ) . 'includes/notices.php';
        include_once plugin_dir_path( __FILE__ ) . 'includes/class-redirect-manager.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-css-builder.php';
        /**
         * Determines if the current page is the WordPress login page.
         *
         * @return bool True if inside WordPress login page.
         */
        function is_login_page() : bool {
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
         * Get the canonical URL of the current page.
         * Uses get_permalink() for singular pages, falls back to REQUEST_URI.
         *
         * @return string Current page URL.
         */
        function get_current_url() : string {
            if ( function_exists( 'is_singular' ) && is_singular() ) {
                $permalink = get_permalink( get_the_ID() );
                if ( $permalink ) {
                    return esc_url_raw( $permalink );
                }
            }
            $req = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_DEFAULT );
            $req = ( $req ? wp_unslash( (string) $req ) : '/' );
            return esc_url_raw( home_url( $req ) );
        }

        /**
         * Check whether any configured redirect rule needs prior-page tracking.
         *
         * @return bool Whether the tracking assets are required.
         */
        function slr_needs_prior_tracking() : bool {
            static $needed;
            if ( null !== $needed ) {
                return $needed;
            }
            $needed = false;
            foreach ( carbonade_pipe( 'slr_xlogin_logout' ) as $rule ) {
                if ( 'prior' === ($rule['slr_xselect_login'] ?? '') || 'prior' === ($rule['slr_xselect_logout'] ?? '') ) {
                    $needed = true;
                    break;
                }
            }
            return $needed;
        }

        // Track the prior page only when an active rule needs it.
        $slr_suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' );
        add_action( 'wp_enqueue_scripts', function () use($slr_suffix) {
            if ( is_admin() || is_login_page() || !slr_needs_prior_tracking() ) {
                return;
            }
            wp_enqueue_script(
                'slr-local-tracker',
                plugins_url( "assets/js/slr-local-tracker{$slr_suffix}.js", __FILE__ ),
                [],
                ( defined( 'SLR_VERSION' ) ? SLR_VERSION : null ),
                true
            );
        } );
        add_action( 'login_enqueue_scripts', function () use($slr_suffix) {
            if ( !slr_needs_prior_tracking() ) {
                return;
            }
            wp_enqueue_script(
                'slr-login-injector',
                plugins_url( "assets/js/slr-login-injector{$slr_suffix}.js", __FILE__ ),
                [],
                ( defined( 'SLR_VERSION' ) ? SLR_VERSION : null ),
                true
            );
        } );
        // Load on all frontend pages for logged-out users — covers WooCommerce My Account,
        // custom login pages using [woocommerce_my_account], or any other page with a
        // WC login form. The script bails immediately when no login form is found.
        add_action( 'wp_enqueue_scripts', function () use($slr_suffix) {
            if ( is_user_logged_in() || !slr_needs_prior_tracking() ) {
                return;
            }
            wp_enqueue_script(
                'slr-login-injector',
                plugins_url( "assets/js/slr-login-injector{$slr_suffix}.js", __FILE__ ),
                [],
                ( defined( 'SLR_VERSION' ) ? SLR_VERSION : null ),
                true
            );
        } );
        // Load PERF-005 migration for Select2 to association fields
        require_once SLR_ROOT . 'includes/migration-perf005.php';
        // Load security header manager
        require_once SLR_ROOT . 'includes/class-security-header-manager.php';
        $security_manager = new SecurityHeaderManager();
        add_action( 'send_headers', $security_manager->addSecurityHeaders( ... ) );
        /**
         * Clear login cookies
         *
         * @return void
         */
        function clear_cookies_on_logout() : void {
            global $wpdb;
            // Set the cookie expiration time to a year ago
            $expiration_time = time() - YEAR_IN_SECONDS;
            // Define the cookie prefixes to clear
            $cookie_prefixes = [
                'wordpress_',
                'woocommerce_',
                $wpdb->prefix . 'woocommerce_',
                'comment_',
                'wp-postpass_',
                'wp-settings-',
                'wp-lang'
            ];
            // Loop through the cookie prefixes and clear any matching cookies
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Keys are sanitized below.
            $cookie_keys = array_keys( $_COOKIE );
            foreach ( $cookie_prefixes as $prefix ) {
                foreach ( $cookie_keys as $cookie_key ) {
                    // Sanitize cookie key to prevent injection attacks
                    $sanitized_key = sanitize_text_field( wp_unslash( $cookie_key ) );
                    if ( strpos( $sanitized_key, $prefix ) === 0 ) {
                        $opts = [
                            'expires'  => $expiration_time,
                            'path'     => '/',
                            'secure'   => is_ssl(),
                            'httponly' => true,
                            'samesite' => 'Lax',
                        ];
                        if ( defined( 'COOKIE_DOMAIN' ) && COOKIE_DOMAIN ) {
                            $opts['domain'] = COOKIE_DOMAIN;
                        }
                        setcookie( $sanitized_key, '', $opts );
                    }
                }
            }
            // Clear-Site-Data header
            // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Clear-Site-Data
            if ( !headers_sent() && apply_filters( 'slr_use_clear_site_data_on_logout', false ) ) {
                header( 'Clear-Site-Data: "cookies"' );
            }
        }

        add_action( 'wp_logout', __NAMESPACE__ . '\\clear_cookies_on_logout', PHP_INT_MAX );
        /**
         * Get the real client IP address, checking for proxy/CDN headers.
         *
         * By default ONLY REMOTE_ADDR is trusted. Proxy headers like X-Forwarded-For
         * and CF-Connecting-IP are attacker-controllable on sites not behind the
         * corresponding proxy/CDN, so trusting them by default would let an attacker
         * rotate the rate-limit key and bypass brute-force protection.
         *
         * Opt in via the 'slr_rate_limit_ip' filter, e.g.:
         *   add_filter( 'slr_rate_limit_ip', fn() => [ 'HTTP_CF_CONNECTING_IP', 'REMOTE_ADDR' ] );
         *
         * @return string Validated IP address, or empty string if not found.
         */
        function get_client_ip() : string {
            $ip_headers = apply_filters( 'slr_rate_limit_ip', ['REMOTE_ADDR'] );
            foreach ( $ip_headers as $header ) {
                // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized below
                $ip = ( isset( $_SERVER[$header] ) ? sanitize_text_field( wp_unslash( $_SERVER[$header] ) ) : '' );
                if ( empty( $ip ) ) {
                    continue;
                }
                if ( $header === 'HTTP_X_FORWARDED_FOR' ) {
                    $ips = explode( ',', $ip );
                    $ip = trim( $ips[0] );
                }
                $ip = filter_var( trim( $ip ), FILTER_VALIDATE_IP );
                if ( $ip !== false ) {
                    return $ip;
                }
            }
            return '';
        }

        /**
         * Get the URL the user was on before reaching the login page.
         *
         * Reads the slr_referrer POST field injected by slr-login-injector.js (localStorage),
         * with a fallback to wp_get_referer().
         *
         * @return string Pre-login URL, or empty string if unavailable.
         */
        function get_pre_login_url() : string {
            $referrer = filter_input( INPUT_POST, 'slr_referrer', FILTER_DEFAULT );
            if ( $referrer ) {
                return esc_url_raw( wp_unslash( (string) $referrer ) );
            }
            $referer = wp_get_referer();
            return ( $referer ? (string) $referer : '' );
        }

        /**
         * Removes the 'redirect_to' parameter from the logout URL.
         *
         * This function filters the WordPress logout URL to remove the 'redirect_to'
         * GET parameter, resulting in a cleaner URL.
         *
         * @param string $logout_url The original logout URL.
         * @param string $redirect   The redirect URL (not used in this function).
         * @return string The modified logout URL without the 'redirect_to' parameter.
         */
        function clean_logout_url(  $logout_url, $redirect  ) {
            return remove_query_arg( 'redirect_to', $logout_url );
        }

        add_filter(
            'logout_url',
            __NAMESPACE__ . '\\clean_logout_url',
            10,
            2
        );
        /**
         * Redirection for login and logout using RedirectManager.
         *
         * @param mixed $redirect_to           Default redirect URL.
         * @param mixed $requested_redirect_to Requested redirect URL.
         * @param mixed $user                  Current user object.
         * @return string Validated redirect URL.
         */
        function process_redirection(  $redirect_to, $requested_redirect_to, $user  ) : string {
            static $redirect_manager = null;
            if ( null === $redirect_manager ) {
                $redirect_manager = new RedirectManager();
            }
            // WordPress and third-party plugins sometimes pass bool/int as
            // $redirect_to via the login_redirect filter — normalise before
            // passing to the strictly-typed RedirectManager.
            $redirect_to = ( is_string( $redirect_to ) ? $redirect_to : null );
            $requested_redirect_to = ( is_string( $requested_redirect_to ) ? $requested_redirect_to : null );
            $user_obj = ( $user instanceof \WP_User ? $user : null );
            return $redirect_manager->processRedirect( $redirect_to, $requested_redirect_to, $user_obj );
        }

        add_filter(
            'login_redirect',
            __NAMESPACE__ . '\\process_redirection',
            PHP_INT_MAX,
            3
        );
        add_filter(
            'logout_redirect',
            __NAMESPACE__ . '\\process_redirection',
            PHP_INT_MAX,
            3
        );
        /**
         * Determine whether premium code may be used without triggering a
         * Freemius initialisation on frontend or WP-CLI requests.
         *
         * Admin:    FS is already initialised (see the is_admin() block above).
         *           We call the real FS methods and persist the result so that
         *           frontend requests can read it without touching FS.
         *
         * Frontend / WP-CLI: read the lightweight WP option written on the last
         *           admin load. No FS initialisation occurs.
         *
         * Result is statically cached so the DB / FS is only hit once per request.
         *
         * @return bool True when premium features may be loaded.
         */
        function slr_can_use_premium() : bool {
            static $result = null;
            if ( null !== $result ) {
                return $result;
            }
            if ( is_admin() ) {
                // FS is already initialised in the is_admin() block above.
                $result = sky_login_redirect_fs()->is__premium_only() && sky_login_redirect_fs()->can_use_premium_code();
                // Persist for frontend / WP-CLI where FS must not be initialised.
                update_option( '_slr_premium_active', ( $result ? '1' : '0' ), true );
            } else {
                // Frontend / WP-CLI: read the cached option. No FS needed.
                $result = '1' === get_option( '_slr_premium_active', '0' );
            }
            return $result;
        }

        // PREMIUM LOGIC : centralised in premium/bootstrap.php
        if ( slr_can_use_premium() ) {
            require_once plugin_dir_path( __FILE__ ) . 'premium/bootstrap.php';
        }
        /**
         * Shortcode : [login-logout]
         *
         * @return string Login or logout link.
         */
        function login_logout_shortcode() : string {
            if ( is_user_logged_in() ) {
                return sprintf( '<a class="logout-btn slr-lilo-shortcode" href="%s">%s</a>', esc_url( wp_logout_url() ), esc_html__( 'Logout', 'sky-login-redirect' ) );
            }
            return sprintf( '<a class="login-btn slr-lilo-shortcode" href="%s">%s</a>', esc_url( wp_login_url() ), esc_html__( 'Login', 'sky-login-redirect' ) );
        }

        add_shortcode( 'login-logout', __NAMESPACE__ . '\\login_logout_shortcode' );
        /**
         * Register Login Customizer.
         *
         * Loaded directly on after_setup_theme (priority 20) so it is always
         * available on the login page, regardless of whether CF has booted.
         * The customizer reads options exclusively via carbonade() and does not
         * register any Carbon Fields fields, so it has no CF boot dependency.
         *
         * @return void
         */
        function register_login_customizer() : void {
            include_once plugin_dir_path( __FILE__ ) . 'includes/login-customizer.php';
        }

        add_action( 'after_setup_theme', __NAMESPACE__ . '\\register_login_customizer', 20 );
        /**
         * Get cached options from transient
         *
         * @return array
         */
        function get_cached_options() : array {
            global $wpdb;
            // Try object cache first to avoid direct DB queries when possible.
            $cached = wp_cache_get( 'sky_login_redirect', 'slr' );
            if ( false !== $cached && is_array( $cached ) ) {
                return $cached;
            }
            // Try transient cache as second layer (survives object cache purges)
            $transient_cache = get_transient( 'slr_options_cache' );
            if ( false !== $transient_cache && is_array( $transient_cache ) ) {
                wp_cache_set(
                    'sky_login_redirect',
                    $transient_cache,
                    'slr',
                    HOUR_IN_SECONDS
                );
                return $transient_cache;
            }
            // Only query database if both caches miss
            $rows = $wpdb->get_results( $wpdb->prepare( "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s", $wpdb->esc_like( '_slr_' ) . '%' ), ARRAY_A );
            $cache = [];
            foreach ( $rows as $row ) {
                $cache[$row['option_name']] = maybe_unserialize( $row['option_value'] );
            }
            // Populate both cache layers — no DB write on read path (VIP compat).
            wp_cache_set(
                'sky_login_redirect',
                $cache,
                'slr',
                HOUR_IN_SECONDS
            );
            set_transient( 'slr_options_cache', $cache, DAY_IN_SECONDS );
            return $cache;
        }

        //add_action(
        //	'carbon_fields_theme_options_container_saved',
        //	__NAMESPACE__ . '\\get_cached_options'
        //);
        /**
         * Flush object cache keys when Carbon Fields options are saved.
         * Ensures redirect/restrict rules and other cached data are refreshed.
         *
         * @return void
         */
        function flush_slr_object_cache() : void {
            wp_cache_delete( 'sky_login_redirect', 'slr' );
            delete_transient( 'slr_options_cache' );
            wp_cache_delete( 'slr_redirect_rules', 'slr' );
            wp_cache_delete( 'slr_restrict_rules', 'slr' );
            wp_cache_delete( 'slr_menu_ids', 'slr' );
        }

        add_action( 'carbon_fields_theme_options_container_saved', __NAMESPACE__ . '\\flush_slr_object_cache', 5 );
        /**
         * Internal shared option-cache loader.
         *
         * Calls get_cached_options() once per request and memoises the result so
         * both carbonade() and carbonade_pipe() share the same array without a
         * second DB / transient / object-cache round-trip.
         *
         * @return array Flat map of every _slr_* wp_option row.
         */
        function slr_options_cache() : array {
            static $cache;
            if ( null === $cache ) {
                $cache = get_cached_options();
            }
            return $cache;
        }

        /**
         * Getter for scalar Carbon Fields theme options.
         *
         * Works for simple fields (text, select, checkbox, …) that Carbon Fields
         * stores as a single wp_option row.  For complex (repeater) fields use
         * carbonade_pipe() instead.
         *
         * @param string $key     Option key without leading underscore.
         * @param mixed  $default Returned when the key is not found.
         * @return mixed Raw option value.
         */
        function carbonade(  string $key, $default = false  ) {
            $k = '_' . $key;
            // Return raw value - escaping should be done at output time, not retrieval
            $cache = slr_options_cache();
            return ( array_key_exists( $k, $cache ) ? $cache[$k] : $default );
        }

        /**
         * Reconstruct a Carbon Fields complex (repeater) field from flat rows.
         *
         * Carbon Fields stores complex fields as individual wp_option rows with a
         * pipe-separated hierarchy:
         *   _{field}|{sub_field}|{group_index}|{item_index}|{property}
         *
         * This function reassembles those rows into the same nested array that
         * carbon_get_theme_option() would return, without requiring CF to be
         * booted — safe to call on wp-login.php and any frontend context.
         *
         * Sub-field type detection rules:
         *   - property === '_empty'           → empty array  (empty multiselect/assoc)
         *   - item has > 1 property or 'id'   → array of objects  (association field)
         *   - multiple items, only 'value'    → flat string array  (multiselect)
         *   - single item, only 'value'       → scalar string  (select / text / …)
         *
         * @param string $key     Complex field name without leading underscore.
         * @param array  $default Returned when no matching rows are found.
         * @return array Reconstructed array of group entries.
         */
        function carbonade_pipe(  string $key, array $default = []  ) : array {
            $cache = slr_options_cache();
            $prefix = '_' . $key . '|';
            $prefix_len = strlen( $prefix );
            $raw = [];
            // [ group_idx => [ sub_field => [ item_idx => [ prop => val ] ] ] ]
            foreach ( $cache as $option_name => $value ) {
                if ( strncmp( $option_name, $prefix, $prefix_len ) !== 0 ) {
                    continue;
                }
                $parts = explode( '|', substr( $option_name, $prefix_len ), 4 );
                if ( count( $parts ) !== 4 ) {
                    continue;
                }
                [
                    $sub_field,
                    $group_str,
                    $item_str,
                    $property
                ] = $parts;
                if ( '' === $sub_field ) {
                    continue;
                    // group-type marker row (e.g. |||0|value = _)
                }
                $raw[(int) $group_str][$sub_field][(int) $item_str][$property] = $value;
            }
            if ( empty( $raw ) ) {
                return $default;
            }
            ksort( $raw );
            $groups = [];
            foreach ( $raw as $sub_fields ) {
                $entry = [];
                foreach ( $sub_fields as $sub_field => $items ) {
                    ksort( $items );
                    $first = reset( $items );
                    // Empty-array marker (empty multiselect / association)
                    if ( isset( $first['_empty'] ) ) {
                        $entry[$sub_field] = [];
                        continue;
                    }
                    // Association field: item carries more than just 'value'
                    if ( count( $first ) > 1 || isset( $first['id'] ) ) {
                        $entry[$sub_field] = array_values( $items );
                        continue;
                    }
                    // Multiselect: several items each with only 'value'
                    if ( count( $items ) > 1 ) {
                        $entry[$sub_field] = array_column( array_values( $items ), 'value' );
                        continue;
                    }
                    // Scalar: single item with only 'value' (select, text, textarea, …)
                    $entry[$sub_field] = $first['value'] ?? '';
                }
                $groups[] = $entry;
            }
            return $groups;
        }

        /**
         * Declare HPOS (High-Performance Order Storage) compatibility for Sky Login Redirect plugin.
         *
         * This function declares that the plugin is compatible with WooCommerce's custom order tables (HPOS).
         * It's important to declare compatibility even if the plugin doesn't directly interact with WooCommerce tables,
         * as it informs WooCommerce and store owners that this plugin won't interfere with HPOS functionality.
         *
         * @since 3.7.5
         *
         * @return void
         */
        function declare_woocommerce_compatibility() : void {
            if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'product_instance_caching', __FILE__, true );
            }
        }

        add_action( 'before_woocommerce_init', __NAMESPACE__ . '\\declare_woocommerce_compatibility' );
        /**
         * Output the SVG sprite in the admin head.
         *
         * @return void
         */
        function output_admin_svg_sprite() : void {
            $sprite = SLR_ROOT . 'assets/icons/icons-sprite.svg';
            if ( is_readable( $sprite ) ) {
                echo '<div aria-hidden="true" style="position:absolute;width:0;height:0;overflow:hidden">';
                // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
                echo file_get_contents( $sprite );
                // phpcs:ignore
                echo '</div>';
            }
        }

        /**
         * Enqueue scripts
         *
         * @param mixed $hook The current admin hook.
         *
         * @return mixed styles and scripts
         */
        function enqueue_admin_scripts(  $hook  ) {
            $svg = plugins_url( 'assets/img/sky-login-redirect.svg', __FILE__ );
            $handle = 'slr-admin-css';
            wp_register_style(
                $handle,
                '',
                [],
                SLR_VERSION
            );
            // empty style as a target for inline CSS
            wp_enqueue_style( $handle );
            wp_add_inline_style( $handle, "\n:root{\n\t--slr-accent:#a2ff37;\n\t--slr-icon-size:16px;\n}\n\n#toplevel_page_sky-login-redirect .wp-menu-image{\n\tbackground-color:var(--slr-accent);\n\tmask-image:url('{$svg}');\n\tmask-repeat:no-repeat;\n\tmask-position:center;\n\tmask-size:var(--slr-icon-size);\n\t-webkit-mask-image:url('{$svg}');\n\t-webkit-mask-repeat:no-repeat;\n\t-webkit-mask-position:center;\n\t-webkit-mask-size:var(--slr-icon-size);\n\ttransition:transform .9s ease;\n\twill-change:transform;\n}\n\n/* Hover / focus rotates the icon. */\n#toplevel_page_sky-login-redirect:hover .wp-menu-image,\n#toplevel_page_sky-login-redirect .wp-menu-image:hover,\na.toplevel_page_sky-login-redirect:hover .wp-menu-image{\n\ttransform:rotate(180deg);\n}\n\n/* Respect reduced motion: no transition. */\n@media (prefers-reduced-motion: reduce){\n\t#toplevel_page_sky-login-redirect .wp-menu-image{\n\t\ttransition:none;\n\t}\n}\n\n/* Fallback when CSS masks aren't supported. */\n@supports not ((mask-image:url('')) or (-webkit-mask-image:url(''))){\n\t#toplevel_page_sky-login-redirect .wp-menu-image{\n\t\tbackground-color:transparent;\n\t\tbackground-image:url('{$svg}');\n\t\tbackground-repeat:no-repeat;\n\t\tbackground-position:center;\n\t\tbackground-size:var(--slr-icon-size);\n\t}\n}" );
            if ( in_array( $hook, PLUGIN_SCREENS, true ) ) {
                // Only on our main plugin page
                if ( $hook === PLUGIN_SCREENS[0] ) {
                    add_action( 'admin_head', __NAMESPACE__ . '\\output_admin_svg_sprite' );
                    wp_enqueue_style(
                        'utopique-elements',
                        plugins_url( 'assets/css/elements.css', __FILE__ ),
                        [],
                        SLR_VERSION,
                        'all'
                    );
                    wp_enqueue_style(
                        'slr',
                        plugins_url( 'assets/css/slr.css', __FILE__ ),
                        [],
                        SLR_VERSION,
                        'all'
                    );
                    wp_enqueue_script(
                        'slr-js',
                        plugins_url( 'assets/js/slr.js', __FILE__ ),
                        ['jquery', 'underscore', 'code-editor'],
                        SLR_VERSION,
                        true
                    );
                    wp_localize_script( 'slr-js', 'SLR', [
                        'upgrade_url'      => sky_login_redirect_fs()->get_upgrade_url(),
                        'pro_feature'      => __( 'unlock with Pro version', 'sky-login-redirect' ),
                        'business_feature' => __( 'unlock with Business version', 'sky-login-redirect' ),
                    ] );
                    // Codemirror editor
                    $cm_css['codeEditor'] = wp_enqueue_code_editor( [
                        'type' => 'text/css',
                    ] );
                    wp_localize_script( 'jquery', 'cm_css', $cm_css );
                    $cm_js['codeEditor'] = wp_enqueue_code_editor( [
                        'type' => 'text/html',
                    ] );
                    wp_localize_script( 'jquery', 'cm_js', $cm_js );
                    wp_enqueue_script( 'wp-theme-plugin-editor' );
                    wp_enqueue_style( 'wp-codemirror' );
                    // remove WP emoji on our pages
                    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
                    remove_action( 'admin_print_styles', 'print_emoji_styles' );
                }
                return;
            }
        }

        add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_admin_scripts' );
    }
}
// end of freemius