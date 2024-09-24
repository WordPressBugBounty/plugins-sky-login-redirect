<?php
/**
 * Functions
 * PHP version 7
 *
 * @category Functions
 * @package  SkyLoginRedirect
 * @author   Utopique <support@utopique.net>
 * @license  GPL https://utopique.net
 * @link     https://utopique.net
 */

namespace SkyLoginRedirect\Functions;

use function SkyLoginRedirect\Sky_Login_Redirect_fs as Sky_Login_Redirect_fs;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Load admin pages
 *
 * @return null
 */
require_once FLASHSPEED_ROOT . 'admin/metatags.php';
require_once FLASHSPEED_ROOT . 'admin/assets.php';
require_once FLASHSPEED_ROOT . 'admin/analytics.php';
require_once FLASHSPEED_ROOT . 'admin/cache.php';
require_once FLASHSPEED_ROOT . 'admin/headers.php';
require_once FLASHSPEED_ROOT . 'admin/advanced.php';
require_once FLASHSPEED_ROOT . 'admin/agency.php';

/**
 * Execute plugin
 */
require_once FLASHSPEED_ROOT . 'free/metatags.php';
require_once FLASHSPEED_ROOT . 'free/assets.php';

/**
 * Load FlashSpeed assets
 *
 * @param array $hook hook
 *
 * @return void
 */
function FlashSpeed_Premium_assets($hook)
{
    // Only on our plugin pages
    $array = [
        'toplevel_page_flashspeed',
        'flashspeed_page_flashspeed-account',
        'flashspeed_page_flashspeed-contact',
        'flashspeed_page_flashspeed-pricing',
    ];

    if ($hook === $array[0]) {

        // Only for premium
        if (Sky_Login_Redirect_fs()->can_use_premium_code()) {
            // tom-select
            // needs to be loaded before flashspeed.js
            wp_enqueue_style(
                'tom-select',
                plugins_url(
                    'premium/vendor/tom-select/css/tom-select.css',
                    dirname(plugin_basename(__FILE__))
                ),
                false,
                '2.2.2',
                'all'
            );
            wp_enqueue_script(
                'tom-select',
                plugins_url(
                    'premium/vendor/tom-select/js/tom-select.complete.min.js',
                    dirname(plugin_basename(__FILE__))
                ),
                false,
                '2.2.2',
                true
            );
        }

        wp_enqueue_style(
            'flashspeed-css',
            plugins_url(
                'css/flashspeed.css',
                __FILE__
            ),
            false,
            FLASHSPEED_VERSION,
            'all'
        );
        wp_enqueue_script(
            'flashspeed-js',
            plugins_url(
                'js/flashspeed.js',
                __FILE__
            ),
            false,
            FLASHSPEED_VERSION,
            true
        );
    }
}
add_action(
    'admin_enqueue_scripts',
    __NAMESPACE__ . '\\FlashSpeed_Premium_assets'
);

/**
 * Logic
 *
 * @param $classes array classes
 *
 * @return void
 */
function FlashSpeed_Get_plan($classes)
{
    $array = [
        'toplevel_page_flashspeed',
        'flashspeed_page_flashspeed-account',
        'flashspeed_page_flashspeed-contact',
        'flashspeed_page_flashspeed-pricing',
    ];

    if (in_array(get_plugin_page_hook('flashspeed', 'flashspeed'), $array)) {
        if (\SkyLoginRedirect\Sky_Login_Redirect_fs()->is_plan('agency', true)) {
            $plan = ' plan-agency ';
        }
        if (\SkyLoginRedirect\Sky_Login_Redirect_fs()->is_plan('pro', true)) {
            $plan = ' plan-pro ';
        }
        if (\SkyLoginRedirect\Sky_Login_Redirect_fs()->is_plan('growth', true)) {
            $plan = ' plan-growth ';
        }
        if (\SkyLoginRedirect\Sky_Login_Redirect_fs()->is_plan('starter', true)) {
            $plan = ' plan-starter ';
        }
        if (\SkyLoginRedirect\Sky_Login_Redirect_fs()->is_not_paying() || \SkyLoginRedirect\Sky_Login_Redirect_fs()->is_free_plan()) {
            $plan = ' plan-free ';
        }

        $classes .= $plan;
    }
    return $classes;
}
add_filter(
    'admin_body_class',
    __NAMESPACE__ . '\\FlashSpeed_Get_plan'
);

/**
 * Get the path to the wp-config.php file.
 *
 * @return string|false The path to wp-config.php, or false if not found.
 */
function FlashSpeed_Get_Wp_Config_path()
{
    $base_path = dirname(__FILE__);

    // Traverse up the directory tree until we find wp-config.php or reach the server root.
    while (! file_exists($base_path . '/wp-config.php') && dirname($base_path) !== $base_path) {
        $base_path = dirname($base_path);
    }

    if (file_exists($base_path . '/wp-config.php')) {
        return $base_path . '/wp-config.php';
    }

    return false;
}

/**
 * Add admin notices
 *
 * @param string $type        type
 * @param string $message     message
 * @param string $dismissable dismissable key
 *
 * @return void
 */
function FlashSpeed_Admin_Notice_message(string $type, string $message, string $dismissable)
{
    $dismiss = ($dismissable ? 'data-dismissible='.$dismissable : '');
    echo <<<EOT
    <div class="notice notice-$type is-dismissible" $dismiss>
        <p>$message</p>
    </div>
EOT;
}
