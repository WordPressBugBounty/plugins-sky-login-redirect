<?php
/**
 * Admin meta links and credits.
 *
 * Modern PHP 8.1+ implementation with strict types.
 *
 * @category Admin_Meta
 * @package  Sky_Login_Redirect
 * @author   Utopique <support@utopique.net>
 * @license  GPL https://utopique.net
 * @link     https://utopique.net
 */

declare(strict_types=1);

namespace SkyLoginRedirect\Admin\Meta;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use function SkyLoginRedirect\Sky_Login_Redirect_fs;

/** Allowed HTML for links in footer. */
const LINK_ALLOWED_HTML = [
    'a'      => [ 'href' => [], 'target' => [], 'rel' => [] ],
    'strong' => [],
];

/** Plugin admin screens. */
const ADMIN_SCREENS = [
    'toplevel_page_sky-login-redirect',
    'login-redirect_page_sky-login-redirect-account',
    'login-redirect_page_sky-login-redirect-contact',
];

/**
 * Get plugin basename (works for both free and pro versions).
 *
 * @return string Plugin basename.
 */
function get_plugin_basename(): string {
    return plugin_basename( plugin_dir_path( dirname( __FILE__, 2 ) ) . 'sky-login-redirect.php' );
}

/**
 * Add settings link to plugins page.
 *
 * @param array $links Existing links.
 * @return array Modified links.
 */
function Slr_Settings_link( array $links ): array {
    array_unshift(
        $links,
        sprintf(
            '<a href="%s">%s</a>',
            esc_url( admin_url( 'admin.php?page=sky-login-redirect' ) ),
            esc_html__( 'Settings', 'sky-login-redirect' )
        )
    );
    return $links;
}
add_filter( 'plugin_action_links_' . get_plugin_basename(), __NAMESPACE__ . '\\Slr_Settings_link' );

/**
 * Add row meta links to plugins page.
 *
 * @param array  $links Existing links.
 * @param string $file  Plugin file.
 * @return array Modified links.
 */
function Slr_Row_meta( array $links, string $file ): array {
    if ( $file !== get_plugin_basename() ) {
        return $links;
    }

    $support = 'https://wordpress.org/support/plugin/sky-login-redirect/';

    return array_merge( $links, [
        'docs'    => sprintf(
            '<a href="%s" title="%s">%s</a>',
            esc_url( apply_filters( 'slr_docs_url', 'https://utopique.net/docs/' ) ),
            esc_attr__( 'View Documentation', 'sky-login-redirect' ),
            esc_html__( 'Docs', 'sky-login-redirect' )
        ),
        'support' => sprintf(
            '<a href="%s" title="%s">%s</a>',
            esc_url( apply_filters( 'slr_support_url', $support ) ),
            esc_attr__( 'Contact support', 'sky-login-redirect' ),
            esc_html__( 'Support', 'sky-login-redirect' )
        ),
        'rate'    => sprintf(
            '<a href="%s" target="_blank" title="%s">%s</a>',
            esc_url( apply_filters( 'slr_rate', $support . 'reviews/?rate=5#new-post' ) ),
            esc_attr__( 'Rate Sky Login Redirect', 'sky-login-redirect' ),
            esc_html__( 'Rate us', 'sky-login-redirect' )
        ),
    ] );
}
add_filter( 'plugin_row_meta', __NAMESPACE__ . '\\Slr_Row_meta', 10, 2 );

/**
 * Show custom credits in admin footer.
 *
 * @param string $footer_text Default footer text.
 * @return string Modified footer text.
 */
function Slr_Admin_credits( string $footer_text ): string {
    $screen = get_current_screen();
    if ( ! $screen || ! in_array( $screen->id, ADMIN_SCREENS, true ) ) {
        return $footer_text;
    }

    $text = wp_kses(
        sprintf(
            /* translators: 1: Product URL, 2: Product name */
            __( 'Thank you for using <a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>', 'sky-login-redirect' ),
            'https://utopique.net/products/sky-login-redirect-premium/',
            esc_html__( 'Sky Login Redirect', 'sky-login-redirect' )
        ),
        LINK_ALLOWED_HTML
    );

    $text .= ' &bull; ' . wp_kses(
        sprintf(
            /* translators: 1: Documentation URL, 2: Link label */
            __( 'Check out the <a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>', 'sky-login-redirect' ),
            'https://utopique.net/docs-category/login-redirect-pro/',
            esc_html__( 'documentation', 'sky-login-redirect' )
        ),
        LINK_ALLOWED_HTML
    );

    $fs = Sky_Login_Redirect_fs();
    if ( $fs->is_not_paying() || $fs->is_free_plan() ) {
        $text .= ' &bull; ' . wp_kses(
            sprintf(
                '<strong><a href="%s" target="_blank" rel="noopener noreferrer">%s</a></strong>',
                esc_url( $fs->get_upgrade_url() ),
                esc_html__( 'Go Pro', 'sky-login-redirect' )
            ),
            LINK_ALLOWED_HTML
        );
    }

    return $text;
}
add_filter( 'admin_footer_text', __NAMESPACE__ . '\\Slr_Admin_credits' );
