=== Sky Login Redirect ===
Contributors: skyminds
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=DNSC3NVBWR66L
Tags: login redirect, logout redirect, custom login, woocommerce login, login customizer, user redirect, role redirect, login page, redirect users, membership
Requires at least: 5.6
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 4.2.2
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
* AJAX-powered admin interface
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

= 4.2.2 - 2026-06-02 =
*   Fix - Users not covered by any redirect rule (including administrators) were forced to the homepage on login/logout. The plugin now preserves the default WordPress destination (e.g. the dashboard) when no rule matches the user, so a rule scoped to specific roles only affects those roles.
*   Fix - Role-based content restriction restricted every page the role viewed instead of only the pages selected in the rule. It now respects the chosen "Content to restrict" list, consistent with user- and logged-out-based rules.
*   Improvement - Both redirect rules and content restriction rules now match against all of a user's roles instead of only the primary role, so multi-role users are handled correctly.

= 4.2.1 - 2026-05-31 =
*   Security - Modal login brute-force protection now uses a transient instead of the non-persistent object cache, so the rate limiter works on sites without Redis/Memcached.
*   Security - Client IP detection now trusts only REMOTE_ADDR by default; spoofable proxy headers (X-Forwarded-For, CF-Connecting-IP) are opt-in via the slr_rate_limit_ip filter, closing a rate-limit bypass.
*   Fix - Fatal error on the modal login form caused by a missing get_current_url() import.
*   Fix - WooCommerce registration redirect no longer discards the default destination when the custom redirect is disabled.
*   Fix - Possible TypeError when building a nav-menu login/logout link with no saved link type.
*   Internal - Removed redundant wp_set_current_user()/wp_set_auth_cookie() after wp_signon() in AJAX login.
*   Internal - PERF-005 migration: removed a duplicate option read and guarded upgrader array keys.

= 4.2.0 - 2026-05-29 =
*   New - IPs are now correctly detected, when behind a proxy.
*   New - Migrate 5 fields to CF association with lazy-loaded paginated search.
*   Fix - Two separate CookieManager instances were created.
*   Fix - Restrict rule header template now displays the selected content-to-restrict title at a glance
*   Fix - Rule header template: defensive typeof check for slr_xrole[0] against future association field format changes
*   New - carbonade_pipe(): reconstructs CF complex (repeater) fields from flat pipe-delimited wp_option rows without Carbon Fields being booted — safe on wp-login.php and any frontend context
*   Internal - slr_options_cache() shared cache loader: carbonade() and carbonade_pipe() share a single get_cached_options() call per request

Older versions changes can be found in [the changelog](https://utopique.net/products/sky-login-redirect-premium/#changelog "Sky Login Redirect changelog")

== Upgrade Notice ==

= 4.2.2 =
**Important redirect fix.** Resolves a bug where users not matched by a redirect rule (including administrators) were sent to the homepage instead of their normal destination. Role rules now apply only to the selected roles and correctly handle multi-role users. Recommended for everyone using role- or user-specific rules.

= 4.2.1 =
**Security & stability fix.** Restores modal login brute-force protection (the rate limiter was inactive on sites without a persistent object cache) and hardens client-IP detection against header spoofing. Also fixes a fatal error on the modal login form and a WooCommerce registration redirect regression. Recommended for all users. NOTE: if your site is behind Cloudflare or a reverse proxy, add the slr_rate_limit_ip filter to keep trusting forwarded IP headers (see changelog/docs).

= 4.2.0 =
**Content Restriction & Redirect Fix!** Fixed a critical bug where content restriction rules and redirect rules were silently not applied due to how Carbon Fields stores complex fields. Also fixes the "Content to restrict" field showing 0 results. Upgrade recommended for all users of the Restrict Content feature.