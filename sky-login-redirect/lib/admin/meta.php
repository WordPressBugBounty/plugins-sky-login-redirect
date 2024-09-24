<?php
/**
 * Admin: meta
 * PHP version 7
 *
 * @category Admin_Meta
 * @package  Sky_Login_Redirect
 * @author   Utopique <support@utopique.net>
 * @license  GPL https://utopique.net
 * @link     https://utopique.net
 */

namespace SkyLoginRedirect\Admin\Meta;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use function SkyLoginRedirect\Sky_Login_Redirect_fs as Sky_Login_Redirect_fs;

/**
 * Add settings link to plugins page
 *
 * @param $links the existing links underneath the plugin name
 *
 * @return $links
 */
function Slr_Settings_link($links)
{
    $settings_link = sprintf(
        '<a href="%s">%s</a>',
        esc_url(admin_url('admin.php?page=sky-login-redirect')),
        __('Settings', 'sky-login-redirect')
    );
    array_unshift($links, $settings_link); // or array_push
    return $links;
}
//$plugin = plugin_basename(__FILE__);

$plugin_dir_path = plugin_dir_path(dirname(__FILE__, 2));
$plugin = plugin_basename($plugin_dir_path . '/sky-login-redirect.php');

add_filter(
    "plugin_action_links_{$plugin}",
    __NAMESPACE__ . '\\Slr_Settings_link',
    10,
    1
);

/**
 * Add additional useful links to plugins page
 *
 * @param $links the links array
 * @param $file  the plugin file
 *
 * @return array
 */
function Slr_Row_meta($links, $file)
{
    $plugin_dir_path = plugin_dir_path(dirname(__FILE__, 2));
    //if ($file === plugin_basename(__FILE__)) {
    if ($file === plugin_basename($plugin_dir_path . '/sky-login-redirect.php')) {
        $support = 'https://wordpress.org/support/plugin/sky-login-redirect/';
        $row_meta = array(
            'docs'    => '<a href="' . esc_url(
                apply_filters('slr_docs_url', 'https://utopique.net/docs/')
            ) . '" title="' . esc_attr(
                __('View Documentation', 'sky-login-redirect')
            ) . '">' . __('Docs', 'sky-login-redirect') . '</a>',
            'support' => '<a href="' . esc_url(
                apply_filters(
                    'slr_support_url',
                    $support
                )
            ) . '" title="' . esc_attr(
                __('Contact support', 'sky-login-redirect')
            ) . '">' . __('Support', 'sky-login-redirect') . '</a>',
            'rate' => '<a href="' . esc_url(
                apply_filters(
                    'slr_rate',
                    $support . 'reviews/?rate=5#new-post'
                )
            ) . '" target="_blank" title="' . esc_attr(
                __('Rate Sky Login Redirect', 'sky-login-redirect')
            ) . '">' . __('Rate us', 'sky-login-redirect') . '</a>',
        );
        return array_merge($links, $row_meta);
    }
    return (array) $links;
}
add_filter('plugin_row_meta', __NAMESPACE__ . '\\Slr_Row_meta', 10, 2);

/**
 * Show credits line
 *
 * @param $footer_text the footer text
 *
 * @return mixed
 */
function Slr_Admin_credits($footer_text)
{
    $current_screen = get_current_screen();
    $hook = $current_screen->id;
    $array = [
        'toplevel_page_sky-login-redirect',
        'login-redirect_page_sky-login-redirect-account',
        'login-redirect_page_sky-login-redirect-contact',
    ];
    if (!in_array($hook, $array)) {
        return $footer_text;
    }

    $footer_text = sprintf(
        __(
            'Thank you for using <a href="%s" target="_blank">%s</a>',
            'sky-login-redirect'
        ),
        'https://utopique.net/products/sky-login-redirect-premium/',
        __('Sky Login Redirect', 'sky-login-redirect')
    );

    $footer_text .= ' &bull; ' . sprintf(
        __(
            'Check out the <a href="%s" target="_blank">%s</a>',
            'sky-login-redirect'
        ),
        'https://utopique.net/docs-category/login-redirect-pro/',
        __('documentation', 'sky-login-redirect')
    );

    $Sky_Login_Redirect_fs = Sky_Login_Redirect_fs();
    if ($Sky_Login_Redirect_fs->is_not_paying()
        || $Sky_Login_Redirect_fs->is_free_plan()
    ) {
        $footer_text .= ' &bull; ' . sprintf(
            __('<strong><a href="%s">%s</strong></a>', 'sky-login-redirect'),
            $Sky_Login_Redirect_fs->get_upgrade_url(),
            __('Go Pro', 'sky-login-redirect')
        );
    }

    return $footer_text;
}
add_filter('admin_footer_text', __NAMESPACE__ . '\\Slr_Admin_credits');
