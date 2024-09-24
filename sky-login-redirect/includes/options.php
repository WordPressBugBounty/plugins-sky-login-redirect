<?php
/**
 * Options page
 * php version 7
 *
 * @category Options
 * @package  Sky_Login_Redirect
 * @author   Utopique <support@utopique.net>
 * @license  GPL https://utopique.net
 * @link     https://utopique.net
 */

namespace SkyLoginRedirect\Options;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use Carbon_Fields\Carbon_Fields;
use Carbon_Fields\Container;
use Carbon_Fields\Block;
use Carbon_Fields\Field;
use Carbon_Fields\Field\Complex_Field;

/**
 * Import the Sky_Login_Redirect_fs() function as defined in the plugin
 * and rename it as SLR_FS so we can use it
 */
use function SkyLoginRedirect\Sky_Login_Redirect_fs as SLR_FS;

/**
 * Créée une page d'options pour notre plugin.
 * Les onglets sont initialement vides mais sont définis
 * et remplis de champs via des filtres définis plus bas.
 *
 * @link https://carbonfields.net/docs/containers-theme-options/
 *
 * @return void
 */
function options_initialize_admin_page()
{
    $tabs = apply_filters('sky_login_redirect_options_tabs', []);

    if (empty($tabs)) {
        return;
    }

    // On crée la page d'options.
    $theme_options = Container::make(
        'theme_options',
        __('Sky Login Redirect', 'sky-login-redirect')
    );

    // On définit son slug utilisé dans l'URL de la page.
    $theme_options->set_page_file('sky-login-redirect');

    // On définit son nom dans le menu d'admin.
    $theme_options->set_page_menu_title(__('Login Redirect', 'sky-login-redirect'));

    // On définit sa position dans le menu d'admin.
    $theme_options->set_page_menu_position(26);

    // On change son icône dans le menu d'admin.
    //$theme_options->set_icon( 'dashicons-update-alt' );
    // Notes:
    // 1. remove rect declaration
    // 2. fill must be set (white)
    // 3. contrast: https://app.contrast-finder.org/result.html?foreground=%239437FF&background=rgb%2860%2C67%2C74%29&ratio=7&isBackgroundTested=false&algo=HSV&lang=fr
    // 4. Take encoded: https://yoksel.github.io/url-encoder/
    // 5. Do not encode it as base64 as it creates a background image
    // for the div and not a real img tag
    //$theme_options->set_icon('data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" focusable="false" width="1em" height="1em" style="transform: rotate(360deg);" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><g transform="translate(24 0) scale(-1 1)"><path d="M6.359 7.808l1.594-.488a1 1 0 0 1 .585 1.913l-3.825 1.17a1 1 0 0 1-1.249-.665l-1.17-3.825a1 1 0 1 1 1.913-.585l.44 1.441c2.408-3.709 7.185-5.304 11.426-3.566a9.381 9.381 0 0 1 5.38 5.831a1 1 0 1 1-1.905.608A7.381 7.381 0 0 0 6.36 7.808zm12.327 8.195l-1.775.443a1 1 0 1 1-.484-1.94l3.643-.909a.997.997 0 0 1 .61-.08a1 1 0 0 1 .84.75l.968 3.88a1 1 0 0 1-1.94.484l-.33-1.322a9.381 9.381 0 0 1-16.384-1.796l-.26-.634a1 1 0 1 1 1.851-.758l.26.633a7.381 7.381 0 0 0 13.001 1.25z" fill="white"/></g></svg>'));

    // pre WP6.0 : animated
    //$theme_options->set_icon("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cg transform='translate(24 0) scale(-1 1)'%3E%3Cpath d='M6.359 7.808l1.594-.488a1 1 0 0 1 .585 1.913l-3.825 1.17a1 1 0 0 1-1.249-.665l-1.17-3.825a1 1 0 1 1 1.913-.585l.44 1.441c2.408-3.709 7.185-5.304 11.426-3.566a9.381 9.381 0 0 1 5.38 5.831a1 1 0 1 1-1.905.608A7.381 7.381 0 0 0 6.36 7.808zm12.327 8.195l-1.775.443a1 1 0 1 1-.484-1.94l3.643-.909a.997.997 0 0 1 .61-.08a1 1 0 0 1 .84.75l.968 3.88a1 1 0 0 1-1.94.484l-.33-1.322a9.381 9.381 0 0 1-16.384-1.796l-.26-.634a1 1 0 1 1 1.851-.758l.26.633a7.381 7.381 0 0 0 13.001 1.25z' fill='rgb(162, 255, 55)' /%3E%3C/g%3E%3C/svg%3E");

    // WP6.0+ : use CSS only, defined inline in the main plugin file
    $theme_options->set_icon('none');

    // On gère les classes
    if (SLR_FS()->is_plan('platinum', true)) {
        $plan = 'plan-platinum';
    }
    if (SLR_FS()->is_plan('business', true)) {
        $plan = 'plan-business';
    }
    if (SLR_FS()->is_plan('pro', true)) {
        // pro has been renamed to starter
        $plan = 'plan-starter';
    }
    if (SLR_FS()->is_not_paying() || SLR_FS()->is_free_plan()) {
        $plan = 'free';
    }
    $theme_options->set_classes($plan);

    // Et enfin, pour chaque onglet, on charge les champs de l'onglet concerné.
    foreach ($tabs as $tab_slug => $tab_title) {
        $theme_options->add_tab(
            esc_html($tab_title),
            apply_filters("sky_login_redirect_options_fields_tab_{$tab_slug}", [])
        );
    }
}
add_action(
    'carbon_fields_register_fields',
    __NAMESPACE__ . '\\options_initialize_admin_page'
);


/**
 * Liste des onglets dans lesquels seront rangés les champs de notre page d'options.
 *
 * @param array $tabs []
 *
 * @return array $tabs Tableau des onglets :
 * la clé d'une entrée est utilisée par le filtre chargeant les champs de l'onglet,
 * la valeur d'une entrée est le titre de l'onglet.
 */
function options_set_tabs($tabs)
{
    return [
        'login_logout' => __('Redirects', 'sky-login-redirect'),
        'customizer' => __('Customizer', 'sky-login-redirect'),
        'tweaks'  => __('Tweaks', 'sky-login-redirect'),
        'woocommerce'   => __('Shop', 'sky-login-redirect'),
        'blocks'   => __('Blocks', 'sky-login-redirect'),
        'modal' => __('Modal', 'sky-login-redirect'),
        'restrict' => __('Restrict', 'sky-login-redirect'),
        'plugins' => __('Plugins', 'sky-login-redirect'),
    ];
}
add_filter('sky_login_redirect_options_tabs', __NAMESPACE__ . '\\options_set_tabs');

/**
 * Ajoute des champs dans l'onglet "Login and Logout".
 *
 * @return array $fields Le tableau contenant nos champs.
 * @link   https://carbonfields.net/docs/fields-usage/
 */
function options_login_logout_tab_theme_fields()
{
    $fields = [];

    $fields[] = Field::make('html', 'login_logout_h2')
    ->set_html(
        sprintf(
            '<h2>%s</h2>',
            __('Login and logout redirects', 'sky-login-redirect')
        )
    );

    $fields[] = Field::make('html', 'slr_login_logout_p')
    ->set_html(
        sprintf(
            '<p class="widgets">%s<p>',
            __('Set your login and logout redirects rules below. Prioritise them with drag and drop.', 'sky-login-redirect')
        ) .
        sprintf('<p class="widgets">%s<p>', '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" focusable="false" width="1.625em" height="1.625em" style="vertical-align: -0.125em;-ms-transform: rotate(360deg); -webkit-transform: rotate(360deg); transform: rotate(360deg);" preserveAspectRatio="xMidYMid meet" viewBox="0 0 48 48"><circle fill="#2196F3" cx="24" cy="24" r="21"/><path fill="#fff" d="M22 22h4v11h-4z"/><circle fill="#fff" cx="24" cy="16.5" r="2.5"/><rect x="0" y="0" width="48" height="48" fill="rgba(0, 0, 0, 0)" /></svg> ' . __('<strong>Important:</strong> one catch-all rule for <strong>all users</strong> must be set.', 'sky-login-redirect'))
    );
    $chicons = apply_filters('chicons', sqrt(9));
    // user categories dropdown
    $categories = [
        'all' => __('all users', 'sky-login-redirect'),
        'user' => __('specific user(s)', 'sky-login-redirect'),
        'role' => __('specific user role(s)', 'sky-login-redirect')
    ];
    // redirects options dropdown
    $redirects = [
        'prior' => __('the previous page', 'sky-login-redirect'),
        'custom' => __('a custom URL', 'sky-login-redirect'),
        'page' => __('a custom page', 'sky-login-redirect'),
        /*'meta' =>  __( 'custom URL with user meta', 'sky-login-redirect' ),*/
        'wp' =>  __('WP admin (default)', 'sky-login-redirect')
    ];
    $maroilles = sprintf(
        '<span class="more-rules"><a href="%s">%s</a></span>',
        SLR_FS()->get_upgrade_url(),
        __('Upgrade for more rules.', 'sky-login-redirect')
    );
    $maroilles = apply_filters('maroilles', $maroilles);
    $fields[] = Field::make(
        'complex',
        'slr_xlogin_logout',
        __('Add a new rule:', 'sky-login-redirect')
    )
        ->set_max($chicons)
        ->set_help_text(
            /*
            __(
                'Click on [+] to add a new rule. Reorder rules via drag and drop.',
                'sky-login-redirect'
            ) . ' ' .
            */
            $maroilles
        )
        ->add_fields(
            array(

            Field::make(
                'select',
                'slr_xselect_redirect',
                __('Redirect:', 'sky-login-redirect')
            )
            ->set_options($categories),

            // USER multiselect
            Field::make(
                'multiselect',
                'slr_xuser',
                __('User(s):', 'sky-login-redirect')
            )->add_options(
                function () {
                    $usr = [];
                    $users = get_users();
                    foreach ($users as $user) {
                        $usr[$user->display_name . ' (ID=' . $user->ID .')'] = $user->display_name;
                    }
                    return $usr;
                }
            )
            ->set_conditional_logic(
                [ [ 'field' => 'slr_xselect_redirect', 'value' => 'user' ] ]
            ),

            // ROLE multiselect
            Field::make(
                'multiselect',
                'slr_xrole',
                __(
                    'Role(s):',
                    'sky-login-redirect'
                )
            )
                ->add_options(
                    function () {
                        global $wp_roles;
                        $roles = $wp_roles->get_names();
                        return $roles;
                    }
                )
                ->set_conditional_logic(
                    [ [ 'field' => 'slr_xselect_redirect', 'value' => 'role' ] ]
                ),

            // Login redirect type
            Field::make(
                'select',
                'slr_xselect_login',
                __('Redirect login to:', 'sky-login-redirect')
            )
            ->set_options($redirects),

            // LOGIN URL for all types
            Field::make(
                'text',
                'slr_xlogin_url',
                __('Redirect login to:', 'sky-login-redirect')
            )
                ->set_help_text(__('Redirect to this URL after a successful login.', 'sky-login-redirect'))
                ->set_attribute('placeholder', 'https://')
                ->set_attribute('type', 'url')
                ->set_classes('indent')
                ->set_conditional_logic(
                    [ [ //'field' => 'slr_xselect_login', 'value' => 'custom',
                        'field' => 'slr_xselect_login', 'value' => [ 'custom', 'meta' ], 'compare' => 'IN' ] ]
                ),


            // select page for the login redirect
            Field::make('select', 'slr_xlogin_page', __('Page:', 'sky-login-redirect'))
                ->add_options(
                    function () {
                        $usr = [];
                        $posts = get_posts([ 'post_type' => 'page' ]);
                        foreach ($posts as $post) {
                            $usr[$post->ID] = $post->post_title;
                            //$usr[$post->post_title . ' (ID=' . $post->ID .')'] = $post->post_title;
                        }
                        return $usr;
                    }
                )
                ->set_classes('indent')
                ->set_conditional_logic(
                    [ [
                        'field' => 'slr_xselect_login', 'value' => 'page'
                    ] ]
                ),


                // USER meta : login
                /*
                Field::make( 'text', 'slr_xlogin_meta_key', __( 'User meta key:', 'sky-login-redirect' ) )
                    ->set_help_text( __( 'The user meta key.', 'sky-login-redirect' ) )
                    ->set_attribute( 'placeholder', 'User meta key' )
                    ->set_classes( 'indent' )
                    ->set_conditional_logic( [ [ 'field' => 'slr_xselect_login', 'value' => 'meta' ] ] ),

                Field::make( 'text', 'slr_xlogin_meta_value', __( 'User meta value:', 'sky-login-redirect' ) )
                    ->set_help_text( __( 'The user meta value.', 'sky-login-redirect' ) )
                    ->set_attribute( 'placeholder', 'User meta value' )
                    ->set_classes( 'indent' )
                    ->set_conditional_logic( [ [ 'field' => 'slr_xselect_login', 'value' => 'meta' ] ] ),
                */

            // Logout redirect type
            Field::make(
                'select',
                'slr_xselect_logout',
                __(
                    'Redirect logout to:',
                    'sky-login-redirect'
                )
            )
                ->set_options($redirects),

            // LOGOUT URL for all types
                Field::make(
                    'text',
                    'slr_xlogout_url',
                    __(
                        'Redirect logout to:',
                        'sky-login-redirect'
                    )
                )
                    ->set_help_text(
                        __(
                            'Redirect to this URL after a successful logout.',
                            'sky-login-redirect'
                        )
                    )
                    ->set_attribute('placeholder', 'https://')
                    ->set_attribute('type', 'url')
                    ->set_classes('indent')
                    ->set_conditional_logic(
                        [
                            [
                                'field' => 'slr_xselect_logout',
                                'value' => ['custom','meta' ],
                                'compare' => 'IN'
                            ]
                        ]
                    ),


                // select page for the logout redirect
                Field::make(
                    'select',
                    'slr_xlogout_page',
                    __(
                        'Page:',
                        'sky-login-redirect'
                    )
                )
                    ->add_options(
                        function () {
                            $usr = [];
                            $posts = get_posts([ 'post_type' => 'page' ]);
                            foreach ($posts as $post) {
                                $usr[$post->ID] = $post->post_title;
                            }
                            return $usr;
                        }
                    )
                ->set_classes('indent')
                ->set_conditional_logic(
                    [ [
                        'field' => 'slr_xselect_logout', 'value' => 'page'
                    ] ]
                ),

                // USER meta : logout
                /*
                Field::make( 'text', 'slr_xlogout_meta_key', __( 'User meta key:', 'sky-login-redirect' ) )
                    ->set_help_text( __( 'The user meta key.', 'sky-login-redirect' ) )
                    ->set_attribute( 'placeholder', 'User meta key' )
                    ->set_classes( 'indent' )
                    ->set_conditional_logic( [ [ 'field' => 'slr_xselect_logout', 'value' => 'meta' ] ] ),

                Field::make( 'text', 'slr_xlogout_meta_value', __( 'User meta value:', 'sky-login-redirect' ) )
                    ->set_help_text( __( 'The user meta value.', 'sky-login-redirect' ) )
                    ->set_attribute( 'placeholder', 'User meta value' )
                    ->set_classes( 'indent' )
                    ->set_conditional_logic( [ [ 'field' => 'slr_xselect_logout', 'value' => 'meta' ] ] ),
                */

                                )
        )
                                ->set_layout('tabbed-vertical')
                                ->set_header_template('<% if(slr_xselect_redirect) { %> <%- $_index %>. [<%- slr_xselect_redirect %>] <%- slr_xrole %><%- slr_xuser %><% } %>');

    return $fields;
}
add_filter('sky_login_redirect_options_fields_tab_login_logout', __NAMESPACE__ . '\\options_login_logout_tab_theme_fields', 10);

/**
 * Ajoute des champs dans l'onglet "tweaks".
 *
 * @return array $fields Le tableau contenant nos champs.
 * @link   https://carbonfields.net/docs/fields-usage/
 */
function options_tweaks_tab_theme_fields()
{
    $fields = [];
    $fields[] = Field::make('html', 'extra_login_tweaks')
        ->set_html(sprintf('<h2>%s</h2>', __('Extra Login tweaks', 'sky-login-redirect')));

    $fields[] = Field::make('checkbox', 'slr_logo_link', __('Point login logo link to the site\'s URL', 'sky-login-redirect'))
        ->set_classes('slider-checkbox')
        ->set_option_value('yes');

    $fields[] = Field::make('checkbox', 'slr_logo_text', __('Set site\'s name to login logo text', 'sky-login-redirect'))
        ->set_classes('slider-checkbox')
        ->set_option_value('yes');

    $fields[] = Field::make('checkbox', 'slr_check_remember_me', __('Check the Remember Me checkbox by default', 'sky-login-redirect'))
        ->set_classes('slider-checkbox')
        ->set_option_value('yes');

    $fields[] = Field::make('checkbox', 'slr_hide_remember_me', __('Hide the Remember Me checkbox', 'sky-login-redirect'))
        ->set_classes('slider-checkbox')
        ->set_option_value('yes');

    $fields[] = Field::make('checkbox', 'slr_hide_backtoblog', sprintf('Remove the "Go to %s" link', get_bloginfo('name')))
        ->set_classes('slider-checkbox')
        ->set_option_value('yes');

    $fields[] = Field::make('checkbox', 'slr_hide_privacy_policy', __('Remove privacy policy link', 'sky-login-redirect'))
        ->set_classes('slider-checkbox')
        ->set_option_value('yes');

    $fields[] = Field::make('checkbox', 'slr_hide_language_switcher', __('Remove language switcher dropdown', 'sky-login-redirect'))
        ->set_classes('slider-checkbox')
        ->set_option_value('yes');

    $fields[] = Field::make('html', 'admin_login_tweaks')
        ->set_html(sprintf('<h2>%s</h2>', __('Admin tweaks', 'sky-login-redirect')));

    $fields[] = Field::make('checkbox', 'slr_remove_login_shake', __('Remove login error shake effect', 'sky-login-redirect'))
        ->set_classes('slider-checkbox starter')
        ->set_option_value('yes');

    $fields[] = Field::make('checkbox', 'slr_norobots', __('Noindex and nofollow login page', 'sky-login-redirect'))
        ->set_classes('slider-checkbox starter')
        ->set_option_value('yes');

    $fields[] = Field::make('checkbox', 'slr_change_user_session', __('Change user\'s session timeout', 'sky-login-redirect'))
        ->set_classes('slider-checkbox starter');

    $fields[] = Field::make('select', 'slr_user_session_time', __('Session timeout', 'sky-login-redirect'))
        ->set_conditional_logic([ [ 'field' => 'slr_change_user_session', 'value' => true ] ])
        ->set_help_text(__('WordPress default is 2 days.', 'sky-login-redirect'))
        ->set_classes('indent starter')
        ->add_options(
            array(
                'default' => __('default', 'sky-login-redirect'),
                '1_hour' => __('1 hour', 'sky-login-redirect'),
                '1_day' => __('1 day', 'sky-login-redirect'),
                '1_week' => __('1 week', 'sky-login-redirect'),
                '2_weeks' => __('2 weeks', 'sky-login-redirect'),
                '1_month' => __('1 month', 'sky-login-redirect'),
                '6_month' => __('6 months', 'sky-login-redirect'),
                '1_year' => __('1 year', 'sky-login-redirect'),
            )
        )
        ->set_conditional_logic([ [ 'field' => 'slr_change_user_session', 'value' => true ] ]);

    $fields[] = Field::make('html', 'login_design')
        ->set_html(sprintf('<h2>%s</h2>', __('Custom login page code', 'sky-login-redirect')));

    $fields[] = Field::make('textarea', 'slr_login_css', __('Add CSS code', 'sky-login-redirect'))
        ->set_classes('indent-grid starter codemirror-css');

    $fields[] = Field::make('textarea', 'slr_header_code', __('Add login header code', 'sky-login-redirect'))
        ->set_classes('indent-grid starter codemirror-js');

    $fields[] = Field::make('textarea', 'slr_form_code', __('Add login form code', 'sky-login-redirect'))
        ->set_classes('indent-grid starter codemirror-js');

    $fields[] = Field::make('textarea', 'slr_footer_code', __('Add login footer code', 'sky-login-redirect'))
        ->set_classes('indent-grid starter codemirror-js');

    $fields[] = Field::make('html', 'tweaks_upsell')
        ->set_html(sprintf('<div class="upselly"><div id="buy"><a class="button" href="%s">%s</a></div></div>', SLR_FS()->get_upgrade_url(), __('Upgrade your plan', 'sky-login-redirect')));

    return $fields;
}
add_filter(
    'sky_login_redirect_options_fields_tab_tweaks',
    __NAMESPACE__ . '\\options_tweaks_tab_theme_fields',
    10
);


/**
 * Ajoute des champs dans l'onglet "WooCommerce".
 *
 * @return array $fields Le tableau contenant nos champs.
 * @link   https://carbonfields.net/docs/fields-usage/
 */
function options_woocommerce_tab_theme_fields()
{
    $fields = [];

    // login urls array
    $login_urls = [];
    $login_urls['default'] = __('wp-login.php (default)', 'sky-login-redirect');

    // check plugin list
    $plugins = get_plugins();

    // EASY DIGITAL DOWNLOADS

    $fields[] = Field::make('html', 'slr_edd')
        ->set_html(sprintf('<h2 class="slr_edd">%s</h2>', __('Easy Digital Downloads', 'sky-login-redirect')));

    // EDD is simply installed, not active
    if (array_key_exists('easy-digital-downloads/easy-digital-downloads.php', $plugins)) {
        $edd = sprintf('<h2 class="woocommerce-install">' . __('These options require Easy Digital Downloads to be active. <a href="%s">Enable Easy Digital Downloads here</a>.', 'sky-login-redirect') . '</h2>', esc_url(admin_url('/plugins.php')));
    } else {
        // EDD is not installed
        $edd = sprintf(
            '<h2 class="woocommerce-install">' . __('These options require Easy Digital Downloads to be installed and active. You can <a href="%s">download Easy Digital Downloads here</a>.', 'sky-login-redirect') . '</h2>',
            esc_url(admin_url('/plugin-install.php?s=edd&tab=search&type=term'))
        );
    }

    if (!class_exists('Easy_Digital_Downloads')) {
        // code that requires EDD
        $fields[] = Field::make('html', 'edd_missing')
            ->set_html($edd);
    }

    $login_urls['custom'] = __('Custom URL', 'sky-login-redirect');

    // register
    $fields[] = Field::make('checkbox', 'slr_edd_register_redirect', __('Redirect Easy Digital Downloads customers after they register', 'sky-login-redirect'))
        ->set_classes('slider-checkbox starter')
        ->set_option_value('yes');

    $fields[] = Field::make('text', 'slr_edd_register_redirect_url', __('Redirect to:', 'sky-login-redirect'))
        ->set_classes('indent show-field')
        ->set_conditional_logic([ [ 'field' => 'slr_edd_register_redirect', 'value' => true ] ])
        ->set_attribute('placeholder', 'https://')
        ->set_attribute('type', 'url')
        ->set_required(true);

    // login
    $fields[] = Field::make(
        'checkbox',
        'slr_edd_login_redirect',
        __('Redirect Easy Digital Downloads customers after they login', 'sky-login-redirect')
    )
        ->set_classes('slider-checkbox starter')
        ->set_option_value('yes');

    $fields[] = Field::make('text', 'slr_edd_login_redirect_url', __('Redirect to:', 'sky-login-redirect'))
        ->set_classes('indent show-field')
        ->set_conditional_logic([ [ 'field' => 'slr_edd_login_redirect', 'value' => true ] ])
        ->set_attribute('placeholder', 'https://')
        ->set_attribute('type', 'url')
        ->set_required(true);

    // WOOOCOMMERCE

    $fields[] = Field::make('html', 'slr_woocommerce')
        ->set_html(sprintf('<h2>%s</h2>', __('WooCommerce', 'sky-login-redirect')));

    // WooCommerce is simply installed, not active
    if (array_key_exists('woocommerce/woocommerce.php', $plugins)) {
        $woocommerce = sprintf(
            '<h2 class="woocommerce-install">' . __('These options require WooCommerce to be active. <a href="%s">Enable WooCommerce here</a>.', 'sky-login-redirect') . '</h2>',
            esc_url(admin_url('/plugins.php'))
        );
    } else {
        // Woocommerce is not installed
        $woocommerce = sprintf(
            '<h2 class="woocommerce-install">' . __('These options require WooCommerce to be installed and active. You can <a href="%s">download WooCommerce here</a>.', 'sky-login-redirect') . '</h2>',
            esc_url(admin_url('/plugin-install.php?s=woocommerce&tab=search&type=term'))
        );
    }

    if (!class_exists('WooCommerce')) {
        // code that requires WooCommerce
        $fields[] = Field::make('html', 'woocommerce_missing')
                                    ->set_html($woocommerce);
        $wc_shop = '';
        $wc_myaccount = '';
    } else {
        $wc_shop = get_permalink(wc_get_page_id('shop'));
        $wc_myaccount = get_permalink(wc_get_page_id('myaccount'));
        $login_urls['woocommerce'] = __('WooCommerce: My Account', 'sky-login-redirect');
    }

    // register :ok
    $fields[] = Field::make('checkbox', 'slr_wc_register_redirect', __('Redirect WooCommerce customers after they register', 'sky-login-redirect'))
        ->set_classes('slider-checkbox starter')
        ->set_option_value('yes');

    $fields[] = Field::make('text', 'slr_wc_register_redirect_url', __('Redirect to:', 'sky-login-redirect'))
        ->set_classes('indent show-field')
        ->set_conditional_logic([ [ 'field' => 'slr_wc_register_redirect', 'value' => true ] ])
        ->set_attribute('placeholder', 'https://')
        ->set_attribute('type', 'url')
        ->set_required(true)
        ->set_help_text(
            __(
                'Enable registration in WooCommerce → Settings → Account → Enable registration on the My Account page',
                'sky-login-redirect'
            )
        )
        ->set_default_value($wc_shop);

    // login : ok
    $fields[] = Field::make('checkbox', 'slr_wc_login_redirect', __('Redirect WooCommerce customers after they login', 'sky-login-redirect'))
        ->set_classes('slider-checkbox starter')
        ->set_option_value('yes');

    $fields[] = Field::make('text', 'slr_wc_login_redirect_url', __('Redirect to:', 'sky-login-redirect'))
        ->set_classes('indent show-field')
        ->set_conditional_logic(
            [ [ 'field' => 'slr_wc_login_redirect', 'value' => true ] ]
        )
        ->set_attribute('placeholder', 'https://')
        ->set_attribute('type', 'url')
        ->set_required(true);

    // logout
    // skip for now

    // WooCommerce customizer
    $fields[] = Field::make('html', 'slr_woocommerce_customizer')
        ->set_html(sprintf('<h2>%s</h2>', __('WooCommerce Customizer', 'sky-login-redirect')));

    $fields[] = Field::make('checkbox', 'slr_wc_login_customizer', __('Customize WooCommerce login', 'sky-login-redirect'))
        ->set_classes('slider-checkbox business')
        ->set_option_value('yes');

    $fields[] = Field::make('html', 'woo_login_block')
        ->set_html(
            sprintf('<h3 class="separator">%s</h3>', __('Login block', 'sky-login-redirect'))
        );

    $fields[] = Field::make('color', 'slr_wc_login_block_color', __('Background color', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_conditional_logic(
            [ [ 'field' => 'slr_wc_login_customizer', 'value' => true ] ]
        );

    $fields[] = Field::make('text', 'slr_wc_login_block_radius', __('Border radius (px)', 'sky-login-redirect'))
        ->set_classes('indent inline-flex')
        ->set_attribute('type', 'number')
        ->set_attribute('min', 0)
        ->set_attribute('max', 50)
        ->set_attribute('step', 1)
        ->set_conditional_logic(
            [ [ 'field' => 'slr_wc_login_customizer', 'value' => true ] ]
        );

    $fields[] = Field::make('html', 'woo_login_form')
        ->set_html(sprintf('<h3 class="separator">%s</h3>', __('Login form', 'sky-login-redirect')));

    $fields[] = Field::make('color', 'slr_wc_login_form_color', __('Background color', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_conditional_logic(
            [ [ 'field' => 'slr_wc_login_customizer', 'value' => true ] ]
        );

    $fields[] = Field::make('text', 'slr_wc_login_form_radius', __('Border radius (px)', 'sky-login-redirect'))
        ->set_classes('indent inline-flex')
        ->set_attribute('type', 'number')
        ->set_attribute('min', 0)
        ->set_attribute('max', 50)
        ->set_attribute('step', 1)
        ->set_conditional_logic(
            [ [ 'field' => 'slr_wc_login_customizer', 'value' => true ] ]
        );

    $fields[] = Field::make('text', 'slr_wc_login_form_padding', __('Padding (px)', 'sky-login-redirect'))
        ->set_classes('indent inline-flex')
        ->set_attribute('type', 'number')
        ->set_attribute('min', 0)
        ->set_attribute('max', 50)
        ->set_attribute('step', 1)
        ->set_conditional_logic(
            [ [ 'field' => 'slr_wc_login_customizer', 'value' => true ] ]
        );

    $fields[] = Field::make('color', 'slr_wc_login_labels_color', __('Labels color', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_conditional_logic(
            [ [ 'field' => 'slr_wc_login_customizer', 'value' => true ] ]
        );

    $fields[] = Field::make('color', 'slr_wc_login_links_color', __('Links color', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_conditional_logic(
            [ [ 'field' => 'slr_wc_login_customizer', 'value' => true ] ]
        );

    $fields[] = Field::make('html', 'woo_login_button')
        ->set_html(
            sprintf('<h3 class="separator">%s</h3>', __('Login button', 'sky-login-redirect'))
        );

    $fields[] = Field::make('color', 'slr_wc_login_button_background_color', __('Background color', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_conditional_logic(
            [ [ 'field' => 'slr_wc_login_customizer', 'value' => true ] ]
        );

    $fields[] = Field::make('color', 'slr_wc_login_button_background_color_hover', __('Background color (hover)', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_conditional_logic(
            [ [ 'field' => 'slr_wc_login_customizer', 'value' => true ] ]
        );

    $fields[] = Field::make('color', 'slr_wc_login_button_text_color', __('Text color', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_conditional_logic(
            [ [ 'field' => 'slr_wc_login_customizer', 'value' => true ] ]
        );

    $fields[] = Field::make('color', 'slr_wc_login_button_text_color_hover', __('Text color (hover)', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_conditional_logic(
            [ [ 'field' => 'slr_wc_login_customizer', 'value' => true ] ]
        );

    $fields[] = Field::make('color', 'slr_wc_login_button_border_color', __('Border color', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_conditional_logic(
            [ [ 'field' => 'slr_wc_login_customizer', 'value' => true ] ]
        );

    $fields[] = Field::make('text', 'slr_wc_login_button_radius', __('Border radius (px)', 'sky-login-redirect'))
        ->set_classes('indent inline-flex')
        ->set_attribute('type', 'number')
        ->set_attribute('min', 0)
        ->set_attribute('max', 50)
        ->set_attribute('step', 1)
        ->set_conditional_logic(
            [ [ 'field' => 'slr_wc_login_customizer', 'value' => true ] ]
        );

    // align

    $fields[] = Field::make('select', 'slr_wc_login_button_align', __('Alignment', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_width(50)
        ->set_options(
            [ 'default' => __('default', 'sky-login-redirect'), 'end' => __('end', 'sky-login-redirect'), 'start' => __('start', 'sky-login-redirect'), 'center' => __('center', 'sky-login-redirect') ]
        )
        ->set_conditional_logic(
            [ [ 'field' => 'slr_wc_login_customizer', 'value' => true ] ]
        );

    $fields[] = Field::make('select', 'slr_wc_login_button_size', __('Button size', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_options([ '' => 'default', 'custom' => 'custom', 'full-width' => 'full-width'])
        ->set_conditional_logic(
            [ [ 'field' => 'slr_wc_login_customizer', 'value' => true ] ]
        );

    $fields[] = Field::make('text', 'slr_wc_login_button_width', __('Custom width (px)', 'sky-login-redirect'))
        ->set_classes('indent inline-flex')
        ->set_attribute('type', 'number')
        ->set_attribute('min', 110)
        ->set_attribute('max', 500)
        ->set_attribute('step', 1)
        ->set_conditional_logic(
            [ [ 'field' => 'slr_wc_login_button_size', 'value' => 'custom' ] ]
        );

    $fields[] = Field::make('text', 'slr_wc_login_button_height', __('Custom height (px)', 'sky-login-redirect'))
        ->set_classes('indent inline-flex')
        ->set_attribute('type', 'number')
        ->set_attribute('min', 10)
        ->set_attribute('max', 500)
        ->set_attribute('step', 1)
        ->set_conditional_logic(
            [ [ 'field' => 'slr_wc_login_button_size', 'value' => 'custom' ] ]
        );

    // TWEAKS
    $fields[] = Field::make('html', 'slr_shop_tweaks')
        ->set_html(sprintf('<h2>%s</h2>', __('Tweaks', 'sky-login-redirect')));

    $fields[] = Field::make('select', 'slr_login_url_select', __('Set login URL to:', 'sky-login-redirect'))
        ->set_classes('indent business')
        ->set_width(50)
        ->set_options($login_urls);

    $fields[] = Field::make('text', 'slr_custom_login_url', __('Login page:', 'sky-login-redirect'))
        ->set_classes('indent business')
        ->set_conditional_logic([ [ 'field' => 'slr_login_url_select', 'value' => 'custom' ] ])
        ->set_attribute('placeholder', 'https://')
        ->set_attribute('type', 'url')
        ->set_required(true)
        ->set_help_text(
            __('Page URL where users login to the site.', 'sky-login-redirect')
        );

    /*
    $fields[] = Field::make( 'checkbox', 'slr_custom_register', __( 'Set custom register page', 'sky-login-redirect' ) )
    ->set_classes( 'slider-checkbox' );

    $fields[] = Field::make( 'text', 'slr_custom_register_url', __( 'Register page:', 'sky-login-redirect' ) )
    ->set_classes( 'indent' )
    ->set_conditional_logic( [ [ 'field' => 'slr_custom_register', 'value' => true ] ] )
    ->set_attribute( 'placeholder', 'https://' )
    ->set_attribute( 'type', 'url' )
    ->set_required( true )
    ->set_help_text( __( 'Page URL where users register to the site.', 'sky-login-redirect' ) );
    */

    $fields[] = Field::make('html', 'woo_upsell')
        ->set_html(
            sprintf('<div class="upselly business"><div id="buy"><a class="button" href="%s">%s</a></div></div>', SLR_FS()->get_upgrade_url(), __('Upgrade your plan', 'sky-login-redirect'))
        );

    return $fields;
}
add_filter(
    'sky_login_redirect_options_fields_tab_woocommerce',
    __NAMESPACE__ . '\\options_woocommerce_tab_theme_fields',
    10
);

/**
 * Ajoute des champs dans l'onglet "Customizer".
 *
 * @return array $fields Le tableau contenant nos champs.
 * @link   https://carbonfields.net/docs/fields-usage/
 */
function options_customizer_tab_theme_fields()
{
    $fields = [];

    // wp_head extra scripts/styles.
    //$fields[] = Field::make( 'header_scripts', 'extra_header_code' );

    // wp_footer extra scripts/styles.
    //$fields[] = Field::make( 'footer_scripts', 'extra_footer_code' );

    $fields[] = Field::make('html', 'customizer_preview')
        ->set_html(
            sprintf('<h2>%s</h2>', __('Login customizer preview', 'sky-login-redirect'))
        );

    $fields[] = Field::make('html', 'customizer_page')
        ->set_html(sprintf('<h2>%s</h2>', __('Login page', 'sky-login-redirect')))
        ->set_classes('babar');

    $fields[] = Field::make('image', 'slr_custom_logo', __('Login logo', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_value_type('url');

    $fields[] = Field::make('color', 'slr_page_background_color', __('Background color', 'sky-login-redirect'))
        ->set_classes('indent');

    $fields[] = Field::make('image', 'slr_page_background_image', __('Background image', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_value_type('url');

    $fields[] = Field::make('html', 'customizer_form')
        ->set_html(sprintf('<h2>%s</h2>', __('Login form', 'sky-login-redirect')));

    $fields[] = Field::make('color', 'slr_form_background_color', __('Background color', 'sky-login-redirect'))
        ->set_classes('indent');

    $fields[] = Field::make('image', 'slr_form_background_image', __('Background image', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_value_type('url');

    $fields[] = Field::make('color', 'slr_form_labels_color', __('Labels color', 'sky-login-redirect'))
        ->set_classes('indent');

    $fields[] = Field::make('color', 'slr_form_nav_color', __('Navigation links color', 'sky-login-redirect'))
        ->set_classes('indent');

    $fields[] = Field::make('color', 'slr_form_backtoblog_color', __('Back to blog link color', 'sky-login-redirect'))
        ->set_classes('indent');

    $fields[] = Field::make('color', 'slr_form_privacy_color', __('Privacy page link color', 'sky-login-redirect'))
        ->set_classes('indent');

    $fields[] = Field::make('html', 'customizer_submit')
        ->set_html(sprintf('<h2>%s</h2>', __('Login button', 'sky-login-redirect')));

    $fields[] = Field::make('color', 'slr_form_submit_background_color', __('Background color', 'sky-login-redirect'))
        ->set_classes('indent');

    $fields[] = Field::make('color', 'slr_form_submit_background_color_hover', __('Background color (hover)', 'sky-login-redirect'))
        ->set_classes('indent');

    $fields[] = Field::make('color', 'slr_form_submit_border_color', __('Border color', 'sky-login-redirect'))
        ->set_classes('indent');

    $fields[] = Field::make('text', 'slr_form_submit_radius', __('Border radius (px)', 'sky-login-redirect'))
        ->set_classes('indent inline-flex')
        ->set_attribute('type', 'number')
        ->set_attribute('min', 0)
        ->set_attribute('max', 50)
        ->set_attribute('step', 1);

    $fields[] = Field::make('text', 'slr_form_submit_border_width', __('Border width (px)', 'sky-login-redirect'))
        ->set_classes('indent inline-flex')
        ->set_attribute('type', 'number')
        ->set_attribute('min', 0)
        ->set_attribute('max', 50)
        ->set_attribute('step', 0.1);

    $fields[] = Field::make('color', 'slr_form_submit_text_color', __('Text color', 'sky-login-redirect'))
        ->set_classes('indent');

    $fields[] = Field::make('color', 'slr_form_submit_text_color_hover', __('Text color (hover)', 'sky-login-redirect'))
        ->set_classes('indent');

    $fields[] = Field::make('select', 'slr_form_submit_align', __('Alignment', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_width(50)
        ->set_options(
            [ 'default' => __('default', 'sky-login-redirect'), 'end' => __('end', 'sky-login-redirect'), 'start' => __('start', 'sky-login-redirect'), 'center' => __('center', 'sky-login-redirect') ]
        );

    $fields[] = Field::make('select', 'slr_form_submit_size', __('Button size', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_options(
            [ '' => 'default', 'custom' => 'custom', 'full-width' => 'full-width']
        );

    $fields[] = Field::make('text', 'slr_form_submit_size_width', __('Custom width (px)', 'sky-login-redirect'))
        ->set_classes('indent inline-flex')
        ->set_attribute('type', 'number')
        ->set_attribute('min', 65)
        ->set_attribute('max', 500)
        ->set_attribute('step', 1)
        ->set_conditional_logic(
            array( array( 'field' => 'slr_form_submit_size', 'value' => 'custom' ) )
        );

    $fields[] = Field::make('text', 'slr_form_submit_size_height', __('Custom height (px)', 'sky-login-redirect'))
        ->set_classes('indent inline-flex')
        ->set_attribute('type', 'number')
        ->set_attribute('min', 10)
        ->set_attribute('max', 500)
        ->set_attribute('step', 1)
        ->set_conditional_logic(
            array( array( 'field' => 'slr_form_submit_size', 'value' => 'custom' ) )
        );

    return $fields;
}
add_filter(
    'sky_login_redirect_options_fields_tab_customizer',
    __NAMESPACE__ . '\\options_customizer_tab_theme_fields',
    10
);

/**
 * Ajoute des champs dans l'onglet "Plugins".
 *
 * @return array $fields Le tableau contenant nos champs.
 * @link   https://carbonfields.net/docs/fields-usage/
 */
function options_plugins_tab_theme_fields()
{
    $fields = [];
    $installed = __('✔ Plugin is installed and active.', 'sky-login-redirect');
    $classy = 'installed';
    $itsec_installed = '';
    $bbq_installed = '';
    $class_ithemes = '';
    $class_bbq = '';
    $class = '';
    $better_comments_installed = sprintf(
        '<a href="%s">%s</a>',
        esc_url(admin_url('/plugin-install.php?s=better+comments+utopique&tab=search&type=term')),
        __('Install Better Comments', 'sky-login-redirect')
    );
    $better_comments_inactive = sprintf(
        '<a href="%s">%s</a>',
        esc_url(admin_url('/plugins.php')),
        __('Activate Better Comments', 'sky-login-redirect')
    );

    if (function_exists('ithemes_updater_get_licensed_site_url')) {
        $itsec_installed = $installed;
        $class_ithemes = $classy;
    }

    if (class_exists('BBQ_Pro') || is_plugin_active('block-bad-queries/block-bad-queries.php')) {
        $bbq_installed = $installed;
        $class_bbq = $classy;
    }

    // get all plugins list
    $all_plugins = apply_filters('active_plugins', get_option('active_plugins'));

    // Better Comments is activated
    if (stripos(implode($all_plugins), 'better-comments.php')) {
        $better_comments_installed = $installed;
        $class = $classy;
    }

    $fields[] = Field::make('html', 'utopique_plugins')
        ->set_html(
            sprintf(
                '<h2 class="security-title">%s</h2>',
                __('Utopique Plugins', 'sky-login-redirect')
            )
            . '<div id="security-grid"><div class="security-block"><a href="https://wordpress.org/plugins/better-comments/" rel="noopener" target="_blank"><img src="'
            . esc_url(plugins_url('banners/better-comments.png', dirname(__FILE__)))
            . '" class="alignleft security" /></a><a href="https://wordpress.org/plugins/better-comments/" rel="noopener" target="_blank">Better Comments</a> is a great tool to help you style and customize your comment form and your comments section, in just a few clicks. It saves you from having to meddle with your child theme\'s CSS, just select the options you need and you\'re all set. <span class="plugin-installed ' . $class. '">'
            . $better_comments_installed . '</span></div></div>'
        );

    $fields[] = Field::make('html', 'ithemes_security')
        ->set_html(
            sprintf('<h2 class="security-title">%s</h2>', __('Login security plugins', 'sky-login-redirect'))
            . '<div id="security-grid"><div class="security-block"><a href="https://wordpress.org/plugins/block-bad-queries/" rel="noopener" target="_blank"><img src="'
            . esc_url(plugins_url('banners/block-bad-queries.jpg', dirname(__FILE__)))
            . '" class="alignleft security" /></a><a href="https://wordpress.org/plugins/block-bad-queries/" rel="noopener" target="_blank">Block Bad Queries (BBQ)</a> is a simple, super-fast plugin that protects your site against malicious URL requests. BBQ checks all incoming traffic and quietly blocks bad requests containing nasty stuff like eval, base64, and excessively-long request-strings. This is a simple yet solid solution for sites that are unable to use a strong .htaccess firewall. <span class="plugin-installed ' . $class_bbq . '">'
            . $bbq_installed . '</span></div>
			<div class="security-block"><a href="https://ithemes.pxf.io/c/2217288/708564/9639" rel="noopener" target="_blank"><img src="' . esc_url(plugins_url('banners/ithemes-security-pro.png', dirname(__FILE__))) . '" class="alignleft security" /></a><a href="https://ithemes.pxf.io/c/2217288/708564/9639" rel="noopener" target="_blank">iThemes Security Pro</a> improves your WordPress login security with strong password enforcement, bad users lockout, 2FA identification, password management and expiration, magic login links, and trusted devices. It does not just take care about login security, it is an all-around security Swiss Army knife that can keep the baddies at bay. Strongly recommended for WordPress. <span class="plugin-installed ' . $class_ithemes . '">'. $itsec_installed . '</span></div>
			</div>'
        );

    return $fields;
}
add_filter(
    'sky_login_redirect_options_fields_tab_plugins',
    __NAMESPACE__ . '\\options_plugins_tab_theme_fields',
    10
);


/**
 * Ajoute des champs dans l'onglet "Restrict".
 *
 * @return array $fields Le tableau contenant nos champs.
 * @link   https://carbonfields.net/docs/fields-usage/
 */
function options_restrict_tab_theme_fields()
{
    /* Post type */
    $args = [ 'public' => true, /* '_builtin' => false, */ ];
    $output = 'objects'; // 'names' or 'objects' (default: 'names')
    $operator = 'and'; // 'and' or 'or' (default: 'and')
    $post_types = get_post_types($args, $output, $operator);

    if ($post_types) { // If there are any custom public post types.
        $fields[] = Field::make('html', 'sky_restrict_on_cpt')
                                ->set_html(sprintf('<h2>%s</h2>', __('Restrict content', 'sky-login-redirect')));

        $fields[] = Field::make('html', 'slr_restrict_p')
                                ->set_html(
                                    sprintf('<p class="widgets platinum">%s<p><p class="widgets platinum">%s<p>', __('You can restrict content by forcing users to log in on your posts, pages or custom post types.', 'sky-login-redirect'), __('Create a rule to start restricting content to logged-in users, roles and specific users.', 'sky-login-redirect'))
                                );
        // user categories dropdown
        $categories = [
                                        'all' => __('all logged-out users', 'sky-login-redirect'),
                                        'user' => __('specific user(s)', 'sky-login-redirect'),
                                        'role' => __('specific user role(s)', 'sky-login-redirect')
                                    ];
        $bistoule = apply_filters('bistoule', 1);
        // redirects options dropdown
        $redirects = [ 'prior' => __('the previous page', 'sky-login-redirect'), 'custom' => __('a custom URL', 'sky-login-redirect'), /*'meta' =>  __( 'custom URL with user meta', 'sky-login-redirect' ),*/ 'wp' =>  __('WP admin (default)', 'sky-login-redirect') ];
        $maroilles = sprintf('<span class="more-rules"><a href="%s">%s</a></span>', SLR_FS()->get_upgrade_url(), __('Upgrade for more rules.', 'sky-login-redirect'));
        $maroilles = apply_filters('maroilles', $maroilles);
        // create a new rule complex field
        $fields[] = Field::make('complex', 'slr_xrestrict', __('Create a new rule:', 'sky-login-redirect'))
            ->set_max($bistoule)
            ->set_help_text(__('Click on [+] to add a new rule. Reorder rules via drag and drop.', 'sky-login-redirect') .  ' ' . $maroilles)
            ->set_classes('platinum')
            ->add_fields(
                array(

                    // rule name?

                    // restrict content multiselect
                    Field::make('multiselect', 'slr_xcpt_restrict', __('Content to restrict:', 'sky-login-redirect'))
                        ->add_options(
                            function () {
                                $usr = [];
                                $posts = get_posts([ 'post_type' => 'any' ]);
                                foreach ($posts as $post) {
                                    $usr[$post->post_title . ' (ID=' . $post->ID .')'] = $post->post_title;
                                    //$usr[$post->ID] = $post->post_title;
                                }
                                return $usr;
                            }
                        ),

                    // restrict for $categories
                    Field::make('select', 'slr_xselect_restrict', __('Restrict for:', 'sky-login-redirect'))
                        ->set_options($categories),

                    // USER multiselect
                    Field::make('multiselect', 'slr_xuser_restrict', __('User(s):', 'sky-login-redirect'))
                        ->add_options(
                            function () {
                                $usr = [];
                                $users = get_users();
                                foreach ($users as $user) {
                                    $usr[$user->display_name . ' (ID=' . $user->ID .')'] = $user->display_name;
                                }
                                return $usr;
                            }
                        )
                        ->set_conditional_logic(
                            [ [ 'field' => 'slr_xselect_restrict', 'value' => 'user' ] ]
                        ),

                    // ROLE multiselect
                    Field::make('multiselect', 'slr_xrole_restrict', __('Role(s):', 'sky-login-redirect'))
                        ->add_options(
                            function () {
                                global $wp_roles;
                                $roles = $wp_roles->get_names();
                                return $roles;
                            }
                        )
                        ->set_conditional_logic(
                            [ [ 'field' => 'slr_xselect_restrict', 'value' => 'role' ] ]
                        ),

                    // Message to display
                    Field::make('textarea', 'slr_restrict_message', __('Custom message', 'sky-login-redirect')),
                                            /*
                                            // Login redirect type
                                            Field::make( 'select', 'slr_xselect_login_restrict', __( 'Redirect login to:', 'sky-login-redirect' ) )
                                            ->set_options( $redirects ),

                                            // LOGIN URL for all types
                                            Field::make( 'text', 'slr_xlogin_url_restrict', __( 'Redirect login to:', 'sky-login-redirect' ) )
                                            ->set_help_text( __( 'Redirect to this URL after a successful login.', 'sky-login-redirect' ) )
                                            ->set_attribute( 'placeholder', 'https://' )
                                            ->set_attribute( 'type', 'url' )
                                            ->set_classes( 'indent' )
                                            ->set_conditional_logic( [ [ //'field' => 'slr_xselect_login', 'value' => 'custom',
                                            'field' => 'slr_xselect_login_restrict', 'value' => [ 'custom', 'meta' ], 'compare' => 'IN' ] ] ),
                                            */

                                            /*
                                            // Logout redirect type
                                            Field::make( 'select', 'slr_xselect_logout_restrict', __( 'Redirect logout to:', 'sky-login-redirect' ) )
                                            ->set_options( $redirects ),

                                            // LOGOUT URL for all types
                                            Field::make( 'text', 'slr_xlogout_url_restrict', __( 'Redirect logout to:', 'sky-login-redirect' ) )
                                            ->set_help_text( __( 'Redirect to this URL after a successful logout.', 'sky-login-redirect' ) )
                                            ->set_attribute( 'placeholder', 'https://' )
                                            ->set_attribute( 'type', 'url' )
                                            ->set_classes( 'indent' )
                                            ->set_conditional_logic( [ [ //'field' => 'slr_xselect_logout', 'value' => 'custom',
                                            'field' => 'slr_xselect_logout_restrict', 'value' => ['custom','meta' ], 'compare' => 'IN' ] ] ),

                                            */
                                        )
            )
                                        ->set_layout('tabbed-vertical')
                                        ->set_header_template('<% if(slr_xselect_restrict) { %> <%- $_index %>. [<%- slr_xselect_restrict %>] <%- slr_xrole_restrict %><%- slr_xuser_restrict %><%- slr_xcpt_restrict %><% } %>');




        /*
        foreach ( $post_types  as $post_type ) {
        $name = strtolower( $post_type->name );
        $Name = ucfirst( $name );
        $fields[] = Field::make( 'checkbox', 'slr_restrict_on_' . $name, sprintf( __( 'Restrict content on %s', 'sky-login-redirect' ), $Name.'s' ) )
        ->set_classes( 'slider-checkbox platinum' )
        ->set_option_value( 'yes' );

        $fields[] = Field::make( 'select', 'slr_restrict_type_on_' . $name, __( 'Type', 'sky-login-redirect' ) )
        ->set_classes( 'indent inline-flex' )
        ->set_width( 50 )
        ->set_options( [ 'include' => __( 'include', 'sky-login-redirect' ), 'exclude' => __( 'exclude', 'sky-login-redirect' ) ])
        ->set_conditional_logic( [ [ 'field' => 'slr_restrict_on_' . $name, 'value' => true ] ] );


        }
        */
    }

    $fields[] = Field::make('html', 'restrict_upsell')
        ->set_html(sprintf('<div class="upselly platinum"><div id="buy"><a class="button" href="%s">%s</a></div></div>', SLR_FS()->get_upgrade_url(), __('Upgrade your plan', 'sky-login-redirect')));


    return $fields;
}
add_filter(
    'sky_login_redirect_options_fields_tab_restrict',
    __NAMESPACE__ . '\\options_restrict_tab_theme_fields',
    10
);

/**
 * Ajoute des champs dans l'onglet "Modal".
 *
 * @return array $fields Le tableau contenant nos champs.
 * @link   https://carbonfields.net/docs/fields-usage/
 */
function options_modal_tab_theme_fields()
{
    $fields = [];

    // modal

    $fields[] = Field::make('html', 'slr_modal_login')
        ->set_html(sprintf('<h2>%s</h2>', __('Modal login', 'sky-login-redirect')));

    $fields[] = Field::make('checkbox', 'slr_modal_login_enable', __('Enable modal login link', 'sky-login-redirect'))
        ->set_classes('slider-checkbox platinum')
        ->set_option_value('yes');

    $fields[] = Field::make('html', 'slr_modal_login_p')
        ->set_html(
            sprintf(
                '<p class="widgets platinum">%s<p>',
                __('Add a login/logout link opening a modal login form with this shortcode:', 'sky-login-redirect')
            ) . '<ul class="slr-shortcode platinum"><li>' . __('Posts, pages, and custom post types:', 'sky-login-redirect') . ' <code>[modal-login]</code></li><li>'. __('Theme templates:', 'sky-login-redirect') . ' <code>&lt;?php echo do_shortcode( \'[modal-login]\' ); ?&gt;</code></li></ul>'
        );

    // modal customizer

    $fields[] = Field::make('html', 'modal_login_customizer_h2')
        ->set_html(sprintf('<h2>%s</h2>', __('Modal login form customizer', 'sky-login-redirect')));

    $fields[] = Field::make('checkbox', 'slr_modal_login_customizer', __('Customize modal login form', 'sky-login-redirect'))
        ->set_classes('slider-checkbox platinum')
        ->set_option_value('yes');

    $fields[] = Field::make('html', 'modal_login_form')
        ->set_html(sprintf('<h3 class="separator">%s</h3>', __('Login form', 'sky-login-redirect')));

    $fields[] = Field::make('color', 'slr_modal_login_form_color', __('Background color', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_conditional_logic(
            [ [ 'field' => 'slr_modal_login_customizer', 'value' => true ] ]
        );

    $fields[] = Field::make('text', 'slr_modal_login_form_radius', __('Border radius (px)', 'sky-login-redirect'))
        ->set_classes('indent inline-flex')
        ->set_attribute('type', 'number')
        ->set_attribute('min', 0)
        ->set_attribute('max', 50)
        ->set_attribute('step', 1)
        ->set_conditional_logic(
            [ [ 'field' => 'slr_modal_login_customizer', 'value' => true ] ]
        );

    $fields[] = Field::make('text', 'slr_modal_login_form_padding', __('Padding (px)', 'sky-login-redirect'))
        ->set_classes('indent inline-flex')
        ->set_attribute('type', 'number')
        ->set_attribute('min', 0)
        ->set_attribute('max', 50)
        ->set_attribute('step', 1)
        ->set_conditional_logic(
            [ [ 'field' => 'slr_modal_login_customizer', 'value' => true ] ]
        );

    $fields[] = Field::make('color', 'slr_modal_login_h1_color', __('Title color', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_conditional_logic(
            [ [ 'field' => 'slr_modal_login_customizer', 'value' => true ] ]
        );

    $fields[] = Field::make('color', 'slr_modal_login_labels_color', __('Labels color', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_conditional_logic(
            [ [ 'field' => 'slr_modal_login_customizer', 'value' => true ] ]
        );

    $fields[] = Field::make('color', 'slr_modal_login_links_color', __('Links color', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_conditional_logic(
            [ [ 'field' => 'slr_modal_login_customizer', 'value' => true ] ]
        );

    $fields[] = Field::make('html', 'modal_login_button')
        ->set_html(
            sprintf('<h3 class="separator">%s</h3>', __('Login button', 'sky-login-redirect'))
        );

    $fields[] = Field::make('color', 'slr_modal_login_button_background_color', __('Background color', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_conditional_logic(
            [ [ 'field' => 'slr_modal_login_customizer', 'value' => true ] ]
        );

    $fields[] = Field::make('color', 'slr_modal_login_button_background_color_hover', __('Background color (hover)', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_conditional_logic(
            [ [ 'field' => 'slr_modal_login_customizer', 'value' => true ] ]
        );

    $fields[] = Field::make('color', 'slr_modal_login_button_text_color', __('Text color', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_conditional_logic(
            [ [ 'field' => 'slr_modal_login_customizer', 'value' => true ] ]
        );

    $fields[] = Field::make('color', 'slr_modal_login_button_text_color_hover', __('Text color (hover)', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_conditional_logic(
            [ [ 'field' => 'slr_modal_login_customizer', 'value' => true ] ]
        );

    $fields[] = Field::make('color', 'slr_modal_login_button_border_color', __('Border color', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_conditional_logic(
            [ [ 'field' => 'slr_modal_login_customizer', 'value' => true ] ]
        );

    $fields[] = Field::make('text', 'slr_modal_login_button_radius', __('Border radius (px)', 'sky-login-redirect'))
        ->set_classes('indent inline-flex')
        ->set_attribute('type', 'number')
        ->set_attribute('min', 0)
        ->set_attribute('max', 50)
        ->set_attribute('step', 1)
        ->set_conditional_logic(
            [ [ 'field' => 'slr_modal_login_customizer', 'value' => true ] ]
        );

    // modal login/logout button

    $fields[] = Field::make('html', 'slr_modal_loginout_button')
        ->set_html(
            sprintf('<h2>%s</h2>', __('Modal login/logout button', 'sky-login-redirect'))
        );

    $fields[] = Field::make('html', 'slr_modal_loginout_button_p')
        ->set_html(
            sprintf('<p class="widgets platinum">%s<p>', __('This setting turns the login/logout link into a button in content only (i.e. menu links are not targeted).', 'sky-login-redirect'))
        );

    $fields[] = Field::make('checkbox', 'slr_modal_loginout_button_enable', __('Turn modal login/logout into a button', 'sky-login-redirect'))
        ->set_classes('slider-checkbox platinum')
        ->set_option_value('yes');

    $fields[] = Field::make('color', 'slr_modal_loginout_bg', __('Background color', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_conditional_logic(
            [ [ 'field' => 'slr_modal_loginout_button_enable', 'value' => true ] ]
        );

    $fields[] = Field::make('color', 'slr_modal_loginout_bg_hover', __('Background color (hover)', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_conditional_logic(
            [ [ 'field' => 'slr_modal_loginout_button_enable', 'value' => true ] ]
        );

    $fields[] = Field::make('color', 'slr_modal_loginout_color', __('Text color', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_conditional_logic(
            [ [ 'field' => 'slr_modal_loginout_button_enable', 'value' => true ] ]
        );

    $fields[] = Field::make('color', 'slr_modal_loginout_color_hover', __('Text color (hover)', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_conditional_logic(
            [ [ 'field' => 'slr_modal_loginout_button_enable', 'value' => true ] ]
        );

    $fields[] = Field::make('color', 'slr_modal_loginout_border_color', __('Border color', 'sky-login-redirect'))
        ->set_classes('indent')
        ->set_conditional_logic(
            [ [ 'field' => 'slr_modal_loginout_button_enable', 'value' => true ] ]
        );

    $fields[] = Field::make('text', 'slr_modal_loginout_radius', __('Border radius (px)', 'sky-login-redirect'))
        ->set_classes('indent inline-flex')
        ->set_attribute('type', 'number')
        ->set_attribute('min', 0)
        ->set_attribute('max', 50)
        ->set_attribute('step', 1)
        ->set_conditional_logic(
            [ [ 'field' => 'slr_modal_loginout_button_enable', 'value' => true ] ]
        );

    $fields[] = Field::make('text', 'slr_modal_loginout_padding', __('Padding (px)', 'sky-login-redirect'))
        ->set_classes('indent inline-flex w132')
        ->set_conditional_logic(
            [ [ 'field' => 'slr_modal_loginout_button_enable', 'value' => true ] ]
        );

    $fields[] = Field::make('select', 'slr_modal_loginout_align', __('Alignment', 'sky-login-redirect'))
        ->set_classes('indent inline-flex')
        ->set_width(50)
        ->set_options(
            [ 'default' => __('default (none)', 'sky-login-redirect'), 'end' => __('end', 'sky-login-redirect'), 'start' => __('start', 'sky-login-redirect'), 'center' => __('center', 'sky-login-redirect') ]
        )
        ->set_conditional_logic(
            [ [ 'field' => 'slr_modal_loginout_button_enable', 'value' => true ] ]
        );



    // align
    /*
    $fields[] = Field::make( 'select', 'slr_modal_login_button_align', __( 'Alignment', 'sky-login-redirect' ) )
    ->set_classes( 'indent' )
    ->set_width( 50 )
    ->set_options( [ 'default' => __( 'default', 'sky-login-redirect' ), 'align-right' => __( 'right', 'sky-login-redirect' ), 'align-left' => __( 'left', 'sky-login-redirect' ), 'align-center' => __( 'center', 'sky-login-redirect' ) ])
    ->set_conditional_logic( [ [ 'field' => 'slr_modal_login_customizer', 'value' => true ] ] );

    $fields[] = Field::make( 'select', 'slr_modal_login_button_size', __( 'Button size', 'sky-login-redirect' ) )
    ->set_classes( 'indent' )
    ->set_options( [ '' => 'default', 'custom' => 'custom', 'full-width' => 'full-width'])
    ->set_conditional_logic( [ [ 'field' => 'slr_modal_login_customizer', 'value' => true ] ] );

    $fields[] = Field::make( 'text', 'slr_modal_login_button_width', __( 'Custom width (px)', 'sky-login-redirect' ) )
    ->set_classes( 'indent inline-flex' )
    ->set_attribute( 'type', 'number' )
    ->set_attribute( 'min', 110 )
    ->set_attribute( 'max', 500 )
    ->set_attribute( 'step', 1 )
    ->set_conditional_logic( [ [ 'field' => 'slr_modal_login_button_size', 'value' => 'custom' ] ] );

    $fields[] = Field::make( 'text', 'slr_modal_login_button_height', __( 'Custom height (px)', 'sky-login-redirect' ) )
    ->set_classes( 'indent inline-flex' )
    ->set_attribute( 'type', 'number' )
    ->set_attribute( 'min', 10 )
    ->set_attribute( 'max', 500 )
    ->set_attribute( 'step', 1 )
    ->set_conditional_logic( [ [ 'field' => 'slr_modal_login_button_size', 'value' => 'custom' ] ] );
    */

    $fields[] = Field::make('html', 'modal_upsell')
        ->set_html(
            sprintf('<div class="upselly platinum"><div id="buy"><a class="button" href="%s">%s</a></div></div>', SLR_FS()->get_upgrade_url(), __('Upgrade your plan', 'sky-login-redirect'))
        );

    return $fields;
}
add_filter(
    'sky_login_redirect_options_fields_tab_modal',
    __NAMESPACE__ . '\\options_modal_tab_theme_fields',
    10
);

/**
 * Ajoute des champs dans l'onglet "Blocks".
 *
 * @return array $fields Le tableau contenant nos champs.
 * @link   https://carbonfields.net/docs/fields-usage/
 */
function options_blocks_tab_theme_fields()
{
    $fields = [];

    $fields[] = Field::make('html', 'slr_link_shortcode')
        ->set_html(
            sprintf('<h2>%s</h2>', __('Login/Logout link', 'sky-login-redirect'))
        );

    $fields[] = Field::make('html', 'slr_link_shortcode_p')
        ->set_html(
            sprintf('<p class="widgets">%s<p>', __('Add a login/logout link with this shortcode:', 'sky-login-redirect'))
            . '<ul class="slr-shortcode"><li>'
            . __('Posts, pages, custom post types, and widgets:', 'sky-login-redirect') . ' <code>[login-logout]</code></li><li>'
            . __('Theme templates:', 'sky-login-redirect')
            . ' <code>&lt;?php echo do_shortcode( \'[login-logout]\' ); ?&gt;</code></li></ul>'
        );

    // premium

    $fields[] = Field::make('html', 'slr_link_menu_h2')
        ->set_html(
            sprintf('<h2>%s</h2>', __('Login/Logout link in menu(s)', 'sky-login-redirect'))
        );

    $menus = get_terms('nav_menu');

    if (empty($menus)) {
        $fields[] = Field::make('html', 'slr_link_menu_choice')
            ->set_html(
                sprintf(
                    '<span class="widgets business">'
                    . __('No menu has been created yet. Head over to <a href="%s">%s</a> to create a menu and add elements.', 'sky-login-redirect')
                    . '</span>',
                    esc_url(admin_url('nav-menus.php')),
                    __('Appearance &rarr; Widgets', 'sky-login-redirect')
                )
            );
    } else {
        $fields[] = Field::make('html', 'slr_link_menu_choice')
            ->set_html(
                sprintf('<p class="widgets business">%s</p>', __('Select the menu(s) where to add the login/logout link.', 'sky-login-redirect'))
            );
    }

    $menus = array_combine(wp_list_pluck($menus, 'term_id'), wp_list_pluck($menus, 'name'));

    // types
    $link_types = [ 'plain' => 'plain link to login page', 'modal' => 'modal login form' ];

    foreach ($menus as $key => $value) {
        $fields[] = Field::make('checkbox', 'slr_menu_id_' . $key, $value)
            ->set_classes('slider-checkbox business indent')
            ->set_option_value('yes');

        $fields[] = Field::make('select', 'slr_menu_position_' . $key, __('Link position in menu', 'sky-login-redirect'))
            ->add_options(
                function () use ($key) {
                    // count nav menu items
                    $my_menu = wp_get_nav_menu_object($key);
                    $counter = $my_menu->count;
                    // create new array with a 0-$counter range
                    $array = range(0, $counter);
                    // remove $array[0] because it's the same as $array[1] and we do not need confusion and duplicates
                    unset($array[0]);
                    // add some position pointers
                    $array[1] = '1 - first';
                    $last = ($counter + 1);
                    $array[$last] = $last . ' - last';
                    // return array
                    return $array;
                }
            )
            ->set_conditional_logic([ [ 'field' => 'slr_menu_id_' . $key, 'value' => true ] ])
            ->set_classes('indent business')
            ->set_required(true);

        $fields[] = Field::make('select', 'slr_link_type_' . $key, __('Link type', 'sky-login-redirect'))
            ->add_options($link_types)
            ->set_conditional_logic(
                [ [ 'field' => 'slr_menu_id_' . $key, 'value' => true ] ]
            )
            ->set_classes('indent business')
            ->set_required(true);

        // Custom menu link class
        $fields[] = Field::make('text', 'slr_link_class_' . $key, __('Link class', 'sky-login-redirect'))
            ->set_classes('indent inline-flex business w264')
            ->set_conditional_logic(
                [ [ 'field' => 'slr_menu_id_' . $key, 'value' => true ] ]
            );

        // Custom login text
        $fields[] = Field::make('text', 'slr_link_logintext_' . $key, __('Custom login text', 'sky-login-redirect'))
            ->set_classes('indent inline-flex business w264')
            ->set_conditional_logic(
                [ [ 'field' => 'slr_menu_id_' . $key, 'value' => true ] ]
            );

        // Custom logout text
        $fields[] = Field::make('text', 'slr_link_logouttext_' . $key, __('Custom logout text', 'sky-login-redirect'))
            ->set_classes('indent inline-flex business w264')
            ->set_conditional_logic(
                [ [ 'field' => 'slr_menu_id_' . $key, 'value' => true ] ]
            );

        // text color
        $fields[] = Field::make('color', 'slr_link_color_' . $key, __('Text color', 'sky-login-redirect'))
            ->set_conditional_logic(
                [ [ 'field' => 'slr_menu_id_' . $key, 'value' => true ] ]
            )
            ->set_classes('indent business');

        // text color hover
        $fields[] = Field::make('color', 'slr_link_hover_' . $key, __('Text color (hover)', 'sky-login-redirect'))
            ->set_conditional_logic(
                [ [ 'field' => 'slr_menu_id_' . $key, 'value' => true ] ]
            )
            ->set_classes('indent business');

        // font size
        $fields[] = Field::make('text', 'slr_link_size_' . $key, __('Font size (px)', 'sky-login-redirect'))
            ->set_classes('indent inline-flex business')
            ->set_attribute('type', 'number')
            ->set_attribute('min', 8)
            ->set_attribute('max', 60)
            ->set_attribute('step', 0.001)
            ->set_conditional_logic(
                [ [ 'field' => 'slr_menu_id_' . $key, 'value' => true ] ]
            );
    } // endforeach


    $fields[] = Field::make('html', 'slr_login_shortcode')
        ->set_html(
            sprintf('<h2>%s</h2>', __('Login form', 'sky-login-redirect'))
        );

    $fields[] = Field::make('html', 'slr_login_shortcode_p')
        ->set_html(
            sprintf('<p class="widgets business">%s<p>', __('Add a login form in your post or page content with this shortcode:', 'sky-login-redirect'))
            . '<ul class="slr-shortcode business"><li>'
            . __('Posts, pages, and custom post types:', 'sky-login-redirect')
            . ' <code>[login-form]</code></li><li>'
            . __('Theme templates:', 'sky-login-redirect')
            . ' <code>&lt;?php echo do_shortcode( \'[login-form]\' ); ?&gt;</code></li></ul>'
        );

    // custom blocks CSS
    $fields[] = Field::make('html', 'slr_custom_css')
        ->set_html(
            sprintf('<h2>%s</h2>', __('Custom blocks CSS', 'sky-login-redirect'))
        );

    $fields[] = Field::make('textarea', 'slr_custom_css_blocks', __('Custom CSS', 'sky-login-redirect'))
        ->set_classes('indent-grid business codemirror-css');

    // upsell
    $fields[] = Field::make('html', 'widgets_upsell')
        ->set_html(
            sprintf(
                '<div class="upselly business"><div id="buy"><a class="button" href="%s">%s</a></div></div>',
                SLR_FS()->get_upgrade_url(),
                __('Upgrade your plan', 'sky-login-redirect')
            )
        );

    return $fields;
}
add_filter(
    'sky_login_redirect_options_fields_tab_blocks',
    __NAMESPACE__ . '\\options_blocks_tab_theme_fields',
    10
);

/**
 * Count menu items
 *
 * @param mixed $key key
 *
 * @return array
 */
function slr_count_nav_menu_items($key)
{
    // Get menu object
    $my_menu = wp_get_nav_menu_object($key);
    $counter = $my_menu->count;
    $array = array_fill(0, $counter, $counter);
    return $array;
}

/**
 * Affiche la valeur d'un champ sous la metabox d'onglets
 *
 * @return void
 */
function display_content_after_fields()
{
    printf(
        '<hr><div>
		<h4>%1$s</h4>
		%2$s
		</div>',
        __('Valeur du champ "Champ WYSIWYG"', 'sky-login-redirect'),
        \carbon_get_theme_option('champ_riche')
    );

    printf(
        '<br><hr><div>
		<h4>%1$s</h4>
		%2$s
		</div>',
        __('Valeur du champ "Champs menu déroulant"', 'sky-login-redirect'),
        \carbon_get_theme_option('champ_select')
    );
}
//add_action( 'carbon_fields_container_options_du_plugin_after_fields', __NAMESPACE__ . '\\display_content_after_fields' );

/**
 * Upsell features
 *
 * @return mixed
 */
function Slr_Upsell_features()
{
    /* platinum users : bail early */
    if (SLR_FS()->is_plan('platinum')) {
        return;
    }

    /* define all plans */
    $features = [];

    $starter = [
        __('WooCommerce redirects', 'sky-login-redirect'),
        __('Easy Digital Downloads redirects', 'sky-login-redirect'),
        __('Additional login options', 'sky-login-redirect'),
    ];

    $business = [
        __('WooCommerce login customizer', 'sky-login-redirect'),
        __('Login form shortcode', 'sky-login-redirect'),
        __('Login & logout shortcode', 'sky-login-redirect'),
        __('Automatic login and logout links in menus', 'sky-login-redirect')
    ];

    $platinum = [
        __('Modal login form', 'sky-login-redirect'),
        __('Modal login customizer', 'sky-login-redirect'),
        __('Custom CSS block', 'sky-login-redirect'),
        __('Restrict content to logged-in users or roles', 'sky-login-redirect')
    ];

    /* Free plan or non-paying user */
    if (SLR_FS()->is_not_paying() || SLR_FS()->is_free_plan()) {
        $features = array_merge($features, $starter);
        $features = array_merge($features, $business);
        $features = array_merge($features, $platinum);
    }

    /* Business upsell */
    if (SLR_FS()->is_plan('pro', true)) {
        $features = array_merge($features, $business);
        $features = array_merge($features, $platinum);
    }

    /* Platinum upsell */
    if (SLR_FS()->is_plan('business', true)) {
        $features = array_merge($features, $platinum);
    }

    /* common features for all plans */
    $all_plans = [
        'More redirection rules',
        'Priority premium support'
    ];
    $features = array_merge($features, $all_plans);
    ?>
    <div id="promo-box" class="wp-core-ui">
    <div id="maximize-icon" class="maximize-icon" style="display: none;">
        <svg width="24" height="24" viewBox="0 0 24 24">
            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"></path>
        </svg>
    </div>
    <div id="minimize-icon" class="minimize-icon">
        <svg width="24" height="24" viewBox="0 0 24 24">
            <path d="M19 13H5v-2h14v2z"></path>
        </svg>
    </div>
    <div id="buy"><a class="button" href="<?php echo SLR_FS()->get_upgrade_url(); ?>">
    <span>🚀</span> <?php _e('Go Pro', 'sky-login-redirect'); ?>
    </a></div>
    <p><strong><?php _e('And get access to:', 'sky-login-redirect'); ?></strong></p>
    <ul>
    <?php
    foreach ($features as $f) {
        printf('<li>✔ ' . $f . '</li>');
    }
    ?>
    </ul>
    </div>
    <?php
}
add_action(
    'carbon_fields_container_sky_login_redirect_after_sidebar',
    __NAMESPACE__ . '\\Slr_Upsell_features'
);
/*
add_action( 'carbon_fields_register_fields',  __NAMESPACE__ . '\\slr_gutenberg_block');
function slr_gutenberg_block(){
    Block::make( __( 'My Shiny Gutenberg Block' ) )
        ->add_fields( array(
    Field::make( 'text', 'heading', __( 'Block Heading' ) ),
    Field::make( 'image', 'image', __( 'Block Image' ) ),
    Field::make( 'rich_text', 'content', __( 'Block Content' ) ),
    ) )
        ->set_render_callback( function ( $fields, $attributes, $inner_blocks ) {
        ?>
        <div class="block">
            <div class="block__heading">
            <h1><?php echo esc_html( $fields['heading'] ); ?></h1>
            </div><!-- /.block__heading -->

            <div class="block__image">
                <?php echo wp_get_attachment_image( $fields['image'], 'full' ); ?>
            </div><!-- /.block__image -->

            <div class="block__content">
                <?php echo apply_filters( 'the_content', $fields['content'] ); ?>
            </div><!-- /.block__content -->
        </div><!-- /.block -->
<?php
    } );
}
*/
