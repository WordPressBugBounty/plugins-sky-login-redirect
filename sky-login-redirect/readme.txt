=== Sky Login Redirect ===
Contributors: skyminds
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=DNSC3NVBWR66L
Tags: login redirect, logout redirect, custom login, woocommerce login, login customizer, user redirect, role redirect, login page, redirect users, membership
Requires at least: 5.6
Tested up to: 6.9
Requires PHP: 8.1
Stable tag: 4.1.5
License: GPLv3 or later

Control where users land after login/logout. Redirect by role, user, or previous page. Includes a powerful login customizer and WooCommerce support.

== Description ==

**Take complete control of your WordPress login experience!** Sky Login Redirect is the most flexible and powerful login/logout redirect plugin for WordPress, trusted by thousands of sites worldwide.

= 🎯 Why Choose Sky Login Redirect? =

**Perfect for:**
✓ Membership sites that need role-based redirects
✓ WooCommerce stores wanting seamless checkout flows
✓ Multi-author blogs with custom dashboards
✓ Client sites requiring branded login pages
✓ Any site wanting better user experience

= 🚀 Core Features (FREE) =

**Smart Redirects**
* Redirect users to **previous page** they were viewing
* Set redirects by **user role** (Admin, Editor, Subscriber, etc.)
* Target **specific users** with custom redirects
* Global redirects for all users
* Separate login and logout redirect rules
* **Automatic loop detection** prevents infinite redirects

**Login Page Customizer**
* Custom logo upload
* Background color or image
* Form styling (colors, borders, padding)
* Button customization (colors, size, alignment)
* Live preview of changes
* No coding required!

**WooCommerce Integration** (Enhanced in v4.1)
* Preserves cart/checkout redirects automatically
* Smart My Account endpoint handling
* Prevents redirect loops on customer-logout
* Shop page fallback on logout

**Performance & Security**
* Built with modern PHP 8.1+ architecture
* AJAX-powered admin interface (Select2)
* Rate limiting on AJAX endpoints
* Dual-layer caching for speed
* 40-60% faster than previous versions

= ⚡ Technical Excellence =

* **Modern codebase:** Enums, readonly classes, strict types
* **Enterprise-grade security:** Rate limiting, output escaping, nonce verification
* **Optimized performance:** Object caching, transients, minimal database queries
* **Developer-friendly:** Debug logging, extensible architecture, clean code

**Important:** Version 4.1.0 requires PHP 8.1 or higher for modern features and enhanced security.

= 💎 Pro Features =

Upgrade to [Sky Login Redirect Pro](https://utopique.net/products/sky-login-redirect-premium/ "Sky Login Redirect Pro") for advanced functionality:

**Advanced Redirects**
* More granular redirect rules
* Easy Digital Downloads integration
* Advanced WooCommerce customization
* Conditional logic for redirects

**Content Restriction**
* Restrict pages/posts to logged-in users
* Role-based content access control
* Redirect non-authorized users

**Shortcodes & Widgets**
* `[slr_login_form]` - Embed login form anywhere
* `[slr_login_link]` - Custom login/logout links
* Automatic menu integration
* Modal login form with customizer

**Enhanced Customization**
* WooCommerce My Account page styling
* Custom CSS editor
* Additional UX/UI options
* Advanced form styling

**Priority Support**
* Direct developer access
* Faster response times
* Custom feature requests considered

[View all Pro features →](https://utopique.net/products/sky-login-redirect-premium/)

== Installation ==

1. Install the plugin.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Visit the 'Login Redirect' settings page to set your login and logout redirects or edit your login page's styles.

== Frequently Asked Questions ==

= How do I set up redirects? =

1. Go to **Settings → Login Redirect** in your WordPress admin
2. Choose your redirect type (Previous Page, Custom Page, or WordPress Default)
3. Select which users/roles the redirect applies to
4. Save changes and test!

= Can I redirect different user roles to different pages? =

Yes! You can set unique redirects for each user role (Administrator, Editor, Author, Subscriber, etc.) and even target specific users by username.

= Does it work with WooCommerce? =

Absolutely! Version 4.1.0 includes enhanced WooCommerce integration:
* Preserves cart/checkout redirects automatically
* Smart My Account endpoint handling
* Customizable logout redirects
* No conflicts with WooCommerce login flow

= Will it work with my membership plugin? =

Yes! Sky Login Redirect is compatible with most membership plugins including MemberPress, Restrict Content Pro, Paid Memberships Pro, and others.

= Redirections don't seem to trigger - what should I do? =

1. Verify redirect rules are saved in **Settings → Login Redirect**
2. Clear your browser cache and cookies
3. Re-save permalinks: **Settings → Permalinks → Save Changes**
4. If using WooCommerce, re-save WooCommerce settings
5. Check for plugin conflicts by temporarily disabling other plugins

= Can I redirect users back to the page they were viewing? =

Yes! Select "Previous Page" as your redirect option. The plugin intelligently tracks the last page visited and redirects users there after login.

= Does it support custom login pages? =

Yes! The plugin includes a visual login page customizer where you can:
* Upload custom logos
* Change colors and backgrounds
* Style forms and buttons
* Match your brand perfectly

= What's new in version 4.1.0? =

Version 4.1.0 brings major improvements:
* Modern PHP 8.1+ architecture for better performance
* Enhanced WooCommerce integration
* AJAX rate limiting for security
* Improved redirect loop detection
* Cleaner, more maintainable codebase

= Is it translation ready? =

Yes! The plugin is fully translation-ready and includes a .pot file for translators.

= Where can I get support? =

Free support is available through the [WordPress.org support forum](https://wordpress.org/support/plugin/sky-login-redirect/). Pro users get priority email support.

== Screenshots ==

1. Login and logout redirection rules for roles, specific users or all users. You can redirect to the previous page, to a custom page, or use the WordPress default.
2. The page customizer allows you to customize the logo and the page background (color or background image)
3. The form customizer allows you to customize the login form.
4. The submit button customizer allows you to customize the login submit button.

== Changelog ==

= 4.1.5 - 2026-01-14 =
*   Fix: Add default color values to login customizer fields (WordPress default colors)
*   Fix: Update iframe sandbox to allow forms and scripts for WordPress Playground and wordpress.com compatibility
*   Fix - Critical: Prevent enum redeclaration fatal error when both free and premium versions are active
*   Fix: Remove deprecated load_plugin_textdomain() call
*   Fix: Replace parse_url() with wp_parse_url() for better WordPress compatibility
*   Fix: Remove debug error_log() code from production
*   Fix: Add proper nonce verification and input sanitization for AJAX handlers
*   Fix: Sanitize $_SERVER variables with wp_unslash()
*   Fix: Use WordPress bundled Select2
*   Fix: Add translators comment for placeholder in notice

Older versions changes can be found in [the changelog](https://utopique.net/products/sky-login-redirect-premium/#changelog "Sky Login Redirect changelog")

== Upgrade Notice ==

= 4.1.5 =
**Major Update!** Modern PHP 8.1+ architecture, enhanced WooCommerce integration, improved security & performance. Requires PHP 8.1+. Backup before updating!
