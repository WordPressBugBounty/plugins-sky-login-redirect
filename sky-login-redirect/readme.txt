=== Sky Login Redirect ===
Contributors: skyminds
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=DNSC3NVBWR66L
Tags: login redirect, login customizer, login menu, woocommerce login, login, edd login, modal login, modal customizer
Requires at least: 3.0
Tested up to: 6.3.1
Requires PHP: 7
Stable tag: 3.7.4
License: GPLv3 or later

Redirect all users, specific users, or roles to the page they were reading prior to logging in or logging out or to a custom page. Features an awesome login page customizer.

== Description ==

Sky Login Redirect redirects your users on log in and log out to the page you set in the options. You can choose to redirect specific users, specific roles, set global custom redirects to a given location or redirect everyone to the page they were reading prior to logging in or logging out.

It detects if the login request points to the backend and can prevent login redirect loops (when the referring page is a login or register page).

The settings page allow you to configure everything related to login in WordPress:

*   redirect users when they log in or log out: redirect users or roles, set custom redirects and redirects to the previous page
*   customize the login page with a few clicks to match the colors and styles of your site: colors, background color or image, borders, alignment, button styles
*   set a custom logo for your login page
*   additional settings related to the login page

[Sky Login Redirect Pro](https://utopique.net/products/sky-login-redirect-premium/ "Sky Login Redirect Pro") adds even more options and functionalities, among which you can find:

*   More rules for WordPress login and logout
*   Easy Digital Downloads redirects 
*   WooCommerce redirects
*   WooCommerce login customizer: you can now style My Account page to your colors
*   Additional UX/UI settings for the login page
*   Login & logout links shortcode
*   Login form shortcode
*   Automatic login and logout links in menus
*   Modal login form
*   Modal customizer
*   Custom CSS block
*   Restrict content to logged-in users or roles

== Installation ==

1. Install the plugin.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Visit the 'Login Redirect' settings page to set your login and logout redirects or edit your login page's styles.

== Frequently Asked Questions ==

= Redirections don't seem to trigger =

Ensure that you have set up redirection rules for login and logout on the settings page.

If redirections have been set already, re-validate your permalinks (WordPress and WooCommerce).

== Screenshots ==

1. Login and logout redirection rules for roles, specific users or all users. You can redirect to the previous page, to a custom page, or use the WordPress default.
2. The page customizer allows you to customize the logo and the page background (color or background image)
3. The form customizer allows you to customize the login form.
4. The submit button customizer allows you to customize the login submit button.

== Changelog ==

= 3.7.4 - 2023-09-05 =
*   New - FS is now managed through Composer
*   Enhancement - improve UX on mobile view so that the Save button does not overlap settings (thanks MBV for reporting)

= 3.7.3 - 2023-07-03 =
*   New - much more robust referer URL tracking function
*   New - add more pre-redirect functions for edge cases
*   New - clear cookies on logout
*   New - add Clear-Site-Data header
*   Enhancement - prepare and refactor menu query
*   Enhancement - add plugin meta in their own module
*   Enhancement - simplify premium code loading
*   Update - FS

Older versions changes can be found in [the changelog](https://utopique.net/products/sky-login-redirect-premium/#changelog "Sky Login Redirect changelog")

== Upgrade Notice ==
