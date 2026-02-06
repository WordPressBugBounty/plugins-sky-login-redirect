<?php

/**
 * Plugin Name: Sky Login Redirect
 * Plugin URI: https://utopique.net/products/sky-login-redirect-premium/
 * Description: Redirects users to the page they were prior to logging in or out. Features an awesome login customizer.
 * Version: 4.1.5
 * Author: Utopique
 * Author URI: https://utopique.net/
 * Developer: Utopique
 * Developer URI: https://utopique.net/
 * Copyright: (c) 2009-2026 Utopique
 * Text Domain: sky-login-redirect
 * Domain Path: /languages
 * License: GPLv3 or later
 * Requires at least: 5.6
 * Tested up to: 6.9
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
/**
 * Redirect type enumeration.
 */
if ( !enum_exists( 'SkyLoginRedirect\\RedirectType' ) ) {
    enum RedirectType : string
    {
        case PREVIOUS = 'previous';
        case CUSTOM = 'custom';
        case DEFAULT = 'default';
    }
}
/**
 * User selection type enumeration.
 */
if ( !enum_exists( 'SkyLoginRedirect\\UserSelectionType' ) ) {
    enum UserSelectionType : string
    {
        case USER = 'user';
        case ROLE = 'role';
        case ALL = 'all';
    }
}
/**
 * WordPress action type enumeration.
 */
if ( !enum_exists( 'SkyLoginRedirect\\ActionType' ) ) {
    enum ActionType : string
    {
        case LOGIN = 'login';
        case LOGOUT = 'logout';
        case REGISTER = 'register';
    }
}
// Current version
define( 'SLR_VERSION', '4.1.5' );
// Plugin root path
define( 'SLR_ROOT', trailingslashit( plugin_dir_path( __FILE__ ) ) );
/**
 * FS
 */
if ( function_exists( __NAMESPACE__ . '\\Sky_Login_Redirect_fs' ) ) {
    Sky_Login_Redirect_fs()->set_basename( false, __FILE__ );
} else {
    if ( !function_exists( __NAMESPACE__ . '\\Sky_Login_Redirect_fs' ) ) {
        /**
         * Create a helper function for easy SDK access.
         *
         * @return $Sky_Login_Redirect_fs
         */
        function Sky_Login_Redirect_fs() {
            global $Sky_Login_Redirect_fs;
            if ( !isset( $Sky_Login_Redirect_fs ) ) {
                /**
                 * Include Freemius SDK.
                 */
                include_once SLR_ROOT . '/vendor/freemius/wordpress-sdk/start.php';
                $Sky_Login_Redirect_fs = fs_dynamic_init( array(
                    'id'             => '3088',
                    'slug'           => 'sky-login-redirect',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_f0e9c9d4e383120cf38d5b44b586b',
                    'is_premium'     => false,
                    'premium_suffix' => 'Pro',
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'menu'           => array(
                        'slug' => 'sky-login-redirect',
                    ),
                    'is_live'        => true,
                ) );
            }
            return $Sky_Login_Redirect_fs;
        }

        // Init Freemius.
        Sky_Login_Redirect_fs();
        // Signal that SDK was initiated.
        do_action( 'Sky_Login_Redirect_fs_loaded' );
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

        // Hook uninstall cleanup to Freemius after_uninstall action.
        Sky_Login_Redirect_fs()->add_action( 'after_uninstall', __NAMESPACE__ . '\\sky_login_redirect_fs_uninstall_cleanup' );
    }
    // Translations are automatically loaded by WordPress for plugins hosted on WordPress.org
    /**
     * Load Carbon Fields dependency via Composer.
     *
     * @return void
     */
    function Sky_Load_carbonfields() {
        include_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
        \Carbon_Fields\Carbon_Fields::boot();
        /**
         * Remove sidebar creation
         */
        if ( is_admin() ) {
            $sidebar_manager = \Carbon_Fields\Carbon_Fields::resolve( 'sidebar_manager' );
            remove_action( 'admin_enqueue_scripts', array($sidebar_manager, 'enqueue_scripts') );
        }
    }

    add_action( 'after_setup_theme', __NAMESPACE__ . '\\Sky_Load_carbonfields' );
    /**
     * Load plugin options file.
     *
     * @return void
     */
    function Sky_Load_plugin() {
        include_once plugin_dir_path( __FILE__ ) . 'includes/options.php';
    }

    add_action( 'plugins_loaded', __NAMESPACE__ . '\\Sky_Load_plugin' );
    include_once plugin_dir_path( __FILE__ ) . 'lib/admin/meta.php';
    include_once plugin_dir_path( __FILE__ ) . 'includes/notices.php';
    include_once plugin_dir_path( __FILE__ ) . 'includes/ajax-handlers.php';
    include_once plugin_dir_path( __FILE__ ) . 'includes/class-redirect-manager.php';
    /**
     * Sky_Maybe_Is_ssl
     *
     * @return true if ssl is enabled
     */
    function Sky_Maybe_Is_ssl() : bool {
        if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ) {
            return true;
        }
        if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) {
            return true;
        }
        return function_exists( 'is_ssl' ) && is_ssl();
    }

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

    // Load cookie manager
    require_once SLR_ROOT . 'includes/class-cookie-manager.php';
    $cookie_manager = new CookieManager();
    add_action( 'template_redirect', $cookie_manager->setLastVisitedPage( ... ) );
    // Load security header manager
    require_once SLR_ROOT . 'includes/class-security-header-manager.php';
    $security_manager = new SecurityHeaderManager();
    add_action( 'send_headers', $security_manager->addSecurityHeaders( ... ) );
    /**
     * Set redirect field on login page to last visited page URL
     *
     * @return void
     */
    function Sky_set_login_redirect_field() : void {
        if ( !is_login_page() ) {
            return;
        }
        $redirect_to = Sky_get_last_page_visited_cookie();
        wp_register_script(
            'slr-inline',
            false,
            array(),
            ( defined( 'SLR_VERSION' ) ? SLR_VERSION : null ),
            true
        );
        wp_enqueue_script( 'slr-inline' );
        wp_add_inline_script( 'slr-inline', 'document.addEventListener("DOMContentLoaded",function(){var f=document.getElementById("redirect_to");if(f){f.value=' . wp_json_encode( esc_url_raw( $redirect_to ) ) . ';}});', 'after' );
    }

    add_action( 'login_enqueue_scripts', __NAMESPACE__ . '\\Sky_set_login_redirect_field' );
    /**
     * Clear login cookies
     *
     * @return void
     */
    function Sky_clear_cookies_on_logout() : void {
        global $wpdb;
        // Set the cookie expiration time to a year ago
        $expiration_time = time() - YEAR_IN_SECONDS;
        // Define the cookie prefixes to clear
        $cookie_prefixes = array(
            'wordpress_',
            'woocommerce_',
            $wpdb->prefix . 'woocommerce_',
            'comment_',
            'wp-postpass_',
            'wp-settings-',
            'wp-lang'
        );
        // Loop through the cookie prefixes and clear any matching cookies
        foreach ( $cookie_prefixes as $prefix ) {
            foreach ( $_COOKIE as $cookie_key => $cookie_value ) {
                // Sanitize cookie key to prevent injection attacks
                $sanitized_key = sanitize_key( $cookie_key );
                if ( strpos( $sanitized_key, $prefix ) === 0 ) {
                    $opts = array(
                        'expires'  => $expiration_time,
                        'path'     => '/',
                        'secure'   => Sky_Maybe_Is_ssl(),
                        'httponly' => true,
                        'samesite' => 'Lax',
                    );
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

    add_action( 'wp_logout', __NAMESPACE__ . '\\Sky_clear_cookies_on_logout', PHP_INT_MAX );
    /**
     * Get last_page_visited cookie: this is our referer URL
     *
     * @return string The referer URL if it exists, otherwise an empty string.
     */
    function Sky_get_last_page_visited_cookie() : string {
        $raw_cookie = filter_input( INPUT_COOKIE, 'last_page_visited', FILTER_UNSAFE_RAW );
        if ( $raw_cookie ) {
            $raw_cookie = wp_unslash( (string) $raw_cookie );
            $referer_url = sanitize_url( $raw_cookie );
            return esc_url_raw( $referer_url );
        }
        // wp_get_referer() can return false, so we need to handle that case
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
    function Slr_clean_logout_url(  $logout_url, $redirect  ) {
        return remove_query_arg( 'redirect_to', $logout_url );
    }

    add_filter(
        'logout_url',
        __NAMESPACE__ . '\\Slr_clean_logout_url',
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
    function Slr_redirection(  $redirect_to, $requested_redirect_to, $user  ) : string {
        static $redirect_manager = null;
        if ( null === $redirect_manager ) {
            $redirect_manager = new RedirectManager();
        }
        return $redirect_manager->processRedirect( $redirect_to, $requested_redirect_to, $user );
    }

    add_filter(
        'login_redirect',
        __NAMESPACE__ . '\\Slr_redirection',
        100,
        3
    );
    add_filter(
        'logout_redirect',
        __NAMESPACE__ . '\\Slr_redirection',
        100,
        3
    );
    /**
     * Shortcode : [login-logout]
     *
     * @return string Login or logout link.
     */
    function Slr_Login_Logout_shortcode() : string {
        if ( is_user_logged_in() ) {
            return sprintf( '<a class="logout-btn slr-lilo-shortcode" href="%s">%s</a>', esc_url( wp_logout_url() ), esc_html__( 'Logout', 'sky-login-redirect' ) );
        }
        return sprintf( '<a class="login-btn slr-lilo-shortcode" href="%s">%s</a>', esc_url( wp_login_url() ), esc_html__( 'Login', 'sky-login-redirect' ) );
    }

    add_shortcode( 'login-logout', __NAMESPACE__ . '\\Slr_Login_Logout_shortcode' );
    // Execute shortcodes in widget_text
    add_filter( 'widget_text', 'do_shortcode' );
    /**
     * Register Login Customizer
     *
     * @return void
     */
    function Slr_Register_Login_customizer() {
        include_once plugin_dir_path( __FILE__ ) . 'includes/login-customizer.php';
    }

    add_action( 'carbon_fields_register_fields', __NAMESPACE__ . '\\Slr_Register_Login_customizer' );
    /**
     * Get cached options from transient
     *
     * @return array
     */
    function Slr_Get_Cached_options() : array {
        global $wpdb;
        // Try object cache first to avoid direct DB queries when possible.
        $cached = wp_cache_get( 'sky_login_redirect', 'slr' );
        if ( false !== $cached && is_array( $cached ) && $cached ) {
            return $cached;
        }
        // Try transient cache as second layer (survives object cache purges)
        $transient_cache = get_transient( 'slr_options_cache' );
        if ( false !== $transient_cache && is_array( $transient_cache ) && $transient_cache ) {
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
        $cache = array();
        foreach ( $rows as $row ) {
            $cache[$row['option_name']] = maybe_unserialize( $row['option_value'] );
        }
        // Only update option if it doesn't exist or has changed
        $existing_option = get_option( 'sky_login_redirect', false );
        if ( false === $existing_option ) {
            add_option(
                'sky_login_redirect',
                $cache,
                '',
                false
            );
            // autoload=no
        } elseif ( $existing_option !== $cache ) {
            update_option( 'sky_login_redirect', $cache, false );
        }
        // Populate both cache layers
        wp_cache_set(
            'sky_login_redirect',
            $cache,
            'slr',
            HOUR_IN_SECONDS
        );
        set_transient( 'slr_options_cache', $cache, DAY_IN_SECONDS );
        return $cache;
    }

    add_action( 'carbon_fields_theme_options_container_saved', __NAMESPACE__ . '\\Slr_Get_Cached_options' );
    /**
     * Getter : retrieve cached options from transient
     *
     * @param mixed $key     the key to retrieve
     * @param bool  $default false by default
     *
     * @return mixed the value for a given key
     */
    function carbonade(  string $key, $default = false  ) {
        static $cache;
        if ( null === $cache ) {
            $cache = (array) get_option( 'sky_login_redirect', [] );
        }
        $k = '_' . $key;
        // Return raw value - escaping should be done at output time, not retrieval
        return ( array_key_exists( $k, $cache ) ? $cache[$k] : $default );
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
    add_action( 'before_woocommerce_init', function () {
        if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        }
    } );
    /**
     * Enqueue scripts
     *
     * @param mixed $hook the current admin hook
     *
     * @return mixed styles and scripts
     */
    function Slr_Add_scripts(  $hook  ) {
        $svg = plugins_url( 'lib/img/sky-login-redirect.svg', __FILE__ );
        $handle = 'slr-admin-css';
        wp_register_style(
            $handle,
            false,
            array(),
            SLR_VERSION
        );
        // empty style as a target for inline CSS
        wp_enqueue_style( $handle );
        wp_add_inline_style( $handle, "\n:root{\n    --slr-accent:#a2ff37;\n    --slr-icon-size:16px;\n}\n\n#toplevel_page_sky-login-redirect .wp-menu-image{\n    background-color:var(--slr-accent);\n    mask-image:url('{$svg}');\n    mask-repeat:no-repeat;\n    mask-position:center;\n    mask-size:var(--slr-icon-size);\n    -webkit-mask-image:url('{$svg}');\n    -webkit-mask-repeat:no-repeat;\n    -webkit-mask-position:center;\n    -webkit-mask-size:var(--slr-icon-size);\n    transition:transform .9s ease;\n    will-change:transform;\n}\n\n/* Hover / focus rotates the icon. */\n#toplevel_page_sky-login-redirect:hover .wp-menu-image,\n#toplevel_page_sky-login-redirect .wp-menu-image:hover,\na.toplevel_page_sky-login-redirect:hover .wp-menu-image{\n    transform:rotate(180deg);\n}\n\n/* Respect reduced motion: no transition. */\n@media (prefers-reduced-motion: reduce){\n    #toplevel_page_sky-login-redirect .wp-menu-image{\n        transition:none;\n    }\n}\n\n/* Fallback when CSS masks aren't supported. */\n@supports not ((mask-image:url('')) or (-webkit-mask-image:url(''))){\n    #toplevel_page_sky-login-redirect .wp-menu-image{\n        background-color:transparent;\n        background-image:url('{$svg}');\n        background-repeat:no-repeat;\n        background-position:center;\n        background-size:var(--slr-icon-size);\n    }\n}" );
        $plugin_screens = [
            'toplevel_page_sky-login-redirect',
            'login-redirect_page_sky-login-redirect-account',
            'login-redirect_page_sky-login-redirect-contact',
            'login-redirect_page_sky-login-redirect-pricing'
        ];
        if ( in_array( $hook, $plugin_screens, true ) ) {
            // Only on our plugin page
            if ( $hook === $plugin_screens[0] ) {
                wp_enqueue_style(
                    'utopique-elements',
                    plugins_url( 'lib/css/elements.css', __FILE__ ),
                    false,
                    SLR_VERSION,
                    'all'
                );
                wp_enqueue_style(
                    'slr',
                    plugins_url( 'lib/css/slr.css', __FILE__ ),
                    false,
                    SLR_VERSION,
                    'all'
                );
                wp_enqueue_script(
                    'slr-js',
                    plugins_url( 'lib/js/slr.js', __FILE__ ),
                    ['jquery', 'underscore', 'code-editor'],
                    SLR_VERSION,
                    true
                );
                wp_localize_script( 'slr-js', 'SLR', array(
                    'upgrade_url'      => Sky_Login_Redirect_fs()->get_upgrade_url(),
                    'pro_feature'      => __( 'unlock with Pro version', 'sky-login-redirect' ),
                    'business_feature' => __( 'unlock with Business version', 'sky-login-redirect' ),
                    'iconsBase'        => trailingslashit( plugins_url( 'lib/img/icons', __FILE__ ) ),
                ) );
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
                // Enqueue WordPress bundled Select2 (available since WP 4.2)
                wp_enqueue_style( 'select2' );
                wp_enqueue_script( 'select2' );
                // Enqueue custom AJAX select script
                wp_enqueue_script(
                    'slr-ajax-select',
                    plugins_url( 'assets/js/slr-ajax-select.js', __FILE__ ),
                    ['jquery', 'select2'],
                    SLR_VERSION,
                    true
                );
                // Localize script with AJAX data
                wp_localize_script( 'slr-ajax-select', 'slrAjax', [
                    'nonce' => wp_create_nonce( 'slr_ajax_nonce' ),
                    'i18n'  => [
                        'selectPage' => __( 'Select a page...', 'sky-login-redirect' ),
                        'selectUser' => __( 'Select users...', 'sky-login-redirect' ),
                    ],
                ] );
                // remove WP emoji on our pages
                remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
                remove_action( 'admin_print_styles', 'print_emoji_styles' );
            }
            return;
        }
    }

    add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\Slr_Add_scripts' );
}
// FS endif