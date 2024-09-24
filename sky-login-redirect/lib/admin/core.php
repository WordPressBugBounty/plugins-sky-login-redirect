<?php


/**
 * Create option page
 *
 * @return null
 */
function SLR_Add_Admin_submenu()
{
    add_submenu_page(
        'sky-login-redirect',                  // Parent slug
        'Sky Login Redirect settings',         // Page title
        'Settings',                            // Menu title
        'manage_options',                      // Capabilities
        'sky-login-redirect-settings',         // Slug
        'SLR_Options_page'                     // Display callback
    );
}
add_action('admin_menu', 'SLR_Add_Admin_submenu', 20);
