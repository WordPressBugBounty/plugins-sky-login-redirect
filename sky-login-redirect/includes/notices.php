<?php
/**
 * Notices
 * PHP version 7
 *
 * @category Notices
 * @package  Sky_Login_Redirect
 * @author   Utopique <support@utopique.net>
 * @license  GPL https://utopique.net
 * @link     https://utopique.net
 */

namespace SkyLoginRedirect\Notices;

/**
 * Add plugin activation time
 *
 * @return null
 */
function Slr_Activation_time()
{
    $get_activation_time = strtotime("now");
    add_option('SLR_activation_time', $get_activation_time);
}
register_activation_hook(__FILE__, __NAMESPACE__ . '\\Slr_Activation_time');

/**
 * Slr_Add_Notices_script
 *
 * Add JS script to record dismiss action
 *
 * @return file admin-notices.js
 */
function Slr_Add_Notices_script()
{
    wp_register_script(
        'slr-notice-update',
        plugins_url('lib/js/admin-notices.js', __FILE__),
        ['jquery'],
        SLR_VERSION,
        true
    );
    wp_localize_script(
        'slr-notice-update',
        'notice_params',
        array(
            'ajaxurl' => get_admin_url() . 'admin-ajax.php',
        )
    );
    wp_enqueue_script('slr-notice-update');
}
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\\Slr_Add_Notices_script');

/**
 * Record dismiss
 *
 * Action hook (wp_ajax_SLR_EOY_2020) must correspond to (wp_ajax_)
 * And also to the action name in the JS file (SLR_EOY_2020)
 *
 * @return null
 */
function Slr_Dismiss_notice()
{
    update_option('SLR_EOY_2020', true);
}
add_action('wp_ajax_SLR_EOY_2020', __NAMESPACE__ . '\\Slr_Dismiss_notice');

/**
 * Check if notice should be shown or not
 *
 * Wait at least 48 hours before displaying the notice
 *
 * @return null
 */
function Slr_Check_Installation_time()
{
    $install_date = get_option('SLR_activation_time');
    $past_date = strtotime('-2 days');
    if ($past_date >= $install_date) {
        // if no record set, display the notice
        if (get_option('SLR_EOY_2020') != true) {
            add_action('admin_notices', __NAMESPACE__ . '\\Slr_EOY_Admin_notice');
        }
    }
}
add_action('admin_init', __NAMESPACE__ . '\\Slr_Check_Installation_time');

/**
 * Slr_EOY_Admin_notice
 *
 * @return void
 */
function Slr_EOY_Admin_notice()
{
    $current_screen = get_current_screen();
    $hook = $current_screen->id;
    $array = [
        'toplevel_page_sky-login-redirect',
        'login-redirect_page_sky-login-redirect-account',
        'login-redirect_page_sky-login-redirect-pricing',
        'plugins',
        'dashboard',
    ];
    $SLR_fs = Sky_Login_Redirect_fs();

    if (in_array($hook, $array)
        && date("Ymd") < '20210101' // end of year sale
        && ($SLR_fs->is_not_paying() || $SLR_fs->is_free_plan())
    ) {
        // class name is important as it's detected by the JS script: EOY-2020
        echo sprintf(
            '<div class="EOY-2020 notice notice-info is-dismissible"><p>'
            . __('End of year sale: save %s on', 'sky-login-redirect')
            . ' <a href="%s" target="_blank">'
            . __('Sky Login Redirect Pro', 'sky-login-redirect')
            . '</a>.</p></div>',
            '20%',
            esc_url('https://utopique.net/products/sky-login-redirect-premium/')
        );
    }
}
