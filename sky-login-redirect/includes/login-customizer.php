<?php
/**
 * Login Customizer
 * PHP version 7
 *
 * @category Login_Customizer
 * @package  Sky_Login_Redirect
 * @author   Utopique <support@utopique.net>
 * @license  GPL https://utopique.net
 * @link     https://utopique.net
 */

namespace SkyLoginRedirect\LoginCustomizer;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Define carbonade()
 */
use function SkyLoginRedirect\carbonade as carbonade;

/**
 * Define custom Login URL
 *
 * This allows to filter login_url
 *
 * @param $url          is the current URL
 * @param $redirect     is the redirect URL
 * @param $force_reauth is the force_reauth GET var that can get appended
 *
 * @return $url
 */
function Slr_Custom_Login_page($url, $redirect, $force_reauth)
{
    if (is_admin()) {
        return $url;
    }
    $page = carbonade('slr_custom_login_url');
    if ($page) {
        // get slug
        $page = trailingslashit(basename(parse_url($page, PHP_URL_PATH)));
        $url = site_url($page);

        if (! empty($redirect)) {
            $url = add_query_arg('redirect_to', urlencode($redirect), $url);
        }
        if ($force_reauth) {
            $url = add_query_arg('reauth', '1', $url);
        }
    }
    return $url;
}
add_filter('login_url', __NAMESPACE__ . '\\Slr_Custom_Login_page', 10, 3);

/**
 * Custom Register URL
 */

/*
add_filter( 'register_url', __NAMESPACE__ . '\\slr_custom_register_page', 10, 1 );
function slr_custom_register_page( $url ) {
if( is_admin() ) {
    return $url;
            }
            $page = carbonade( 'slr_custom_register_url' );
            if ( $page ) :
            // get slug
            $page = trailingslashit( basename( parse_url( $page, PHP_URL_PATH ) ) );
            $url  = site_url( $page );
        endif;
        return $url;
    }
    */



/**
 * Login customizer: logo LINK
 * Change logo link from wordpress.org to our domain
 *
 * @param $url is the default wordpress.org link
 *
 * @return $url
 */
function Slr_Login_Logo_url($url)
{
    $slr_logo_link = carbonade('slr_logo_link');
    if ($slr_logo_link == 'yes') {
        return home_url('/');
    }
}
add_filter('login_headerurl', __NAMESPACE__ . '\\Slr_Login_Logo_url', 10, 1);

/**
 * Login customizer: logo header text
 * Change logo header text
 *
 * @return mixed the site's name
 */
function Slr_Login_Logo_title()
{
    $slr_logo_text = carbonade('slr_logo_text');
    if ($slr_logo_text == 'yes') {
        return get_bloginfo('name');
    }
}
add_filter('login_headertext', __NAMESPACE__ . '\\Slr_Login_Logo_title');

/**
 * Login preview
 *
 * @param $hook the current admin hook
 *
 * @return mixed
 */
function Slr_Login_preview($hook)
{
    if ('toplevel_page_sky-login-redirect' != $hook) {
        return;
    } ?>
<div id="slr-login-iframe" style="width:100%;border:0;display:none">
<h3 style="margin-left: 1.4rem;font-weight: 400;">
    <?php _e('Save your settings first to see changes &#8623;', 'sky-login-redirect'); ?>
</h3>
<iframe src="<?php echo home_url('/wp-login.php'); ?>" height="540px" width="100%" sandbox="allow-scripts allow-popups"></iframe>
</div>
        <?php
        //sandbox="allow-same-origin allow-scripts allow-popups"
}
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\\Slr_Login_preview', 20);

/**
 * Language switcher
 */
add_filter(
    'login_display_language_dropdown',
    function () {
        if (carbonade('slr_hide_language_switcher') == 'yes') {
            return false;
        }
        return true;
    }
);


/**
 * Customizer CSS
 *
 * Compile all the styles from the customizer into a single style tag.
 *
 * @return mixed the styles created in the customizer
 */
function Slr_Customizer_css()
{
    $style = '';

    // back to blog
    if (carbonade('slr_hide_backtoblog') == 'yes') {
        $style .= 'p#backtoblog a { display: none; }';
    }

    // privacy policy
    if (carbonade('slr_hide_privacy_policy') == 'yes') {
        $style .= '.login .privacy-policy-page-link { display: none; }';
    }

    /* custom login logo */
    $slr_logo = carbonade('slr_custom_logo');
    if (!empty($slr_logo)) {
        $imagedata = @getimagesize($slr_logo);
        // width : $imagedata[0];
        // height: $imagedata[1]
        $height = ((is_array($imagedata) && $imagedata[1]) ? 'height: '. $imagedata[1] . 'px;' : 'padding-bottom: 56.25%; height: 0;');

        $style .= ".login h1 {
                position: relative;
                {$height}
                overflow: hidden;
            }
            .login h1 a{
                background-image:none,url('{$slr_logo}') !important;
                background-size: auto;
                background-position: center top;
                background-repeat: no-repeat;
                color: #444;
                font-size: 20px;
                font-weight: 400;
                line-height: 1.3;
                margin: 0 auto 25px;
                padding: 0;
                text-decoration: none;
                text-indent: -9999px;
                outline: 0;
                overflow: hidden;
                display: block;
                position: absolute;
                top: 0;
                left: 0;
                width: 100% !important;
                height: 100%;
            }";
    }

    /* page background color */
    $slr_page_background_color = carbonade('slr_page_background_color');
    if (!empty($slr_page_background_color)) {
        $style .= "body.login { background: {$slr_page_background_color} }";
    }

    /* page background image */
    $slr_page_background_image = carbonade('slr_page_background_image');
    if (!empty($slr_page_background_image)) {
        $style .= "body.login {
                background-image: url('{$slr_page_background_image}');
                background-repeat: no-repeat;
                background-attachment: fixed;
                background-position: center;
                background-size:cover;
            }";
    }

    /* Login form background color */
    $slr_form_background_color = carbonade('slr_form_background_color');
    if (!empty($slr_form_background_color)) {
        $style .= "body.login #loginform { background: {$slr_form_background_color} }";
    }

    /* Login page background image */
    $slr_form_background_image = carbonade('slr_form_background_image');
    if (!empty($slr_form_background_image)) {
        $style .= "body.login #loginform {
                background-image: url('{$slr_form_background_image}');
                background-repeat: no-repeat;
                b__ackground-attachment: fixed;
                background-position: center;
            }";
    }

    /* label colors */
    $slr_form_labels_color = carbonade('slr_form_labels_color');
    if (!empty($slr_form_labels_color)) {
        $style .= "body.login div#login form#loginform label { 
            color: {$slr_form_labels_color} 
        }";
    }

    /* nav links color */
    $slr_form_nav_color = carbonade('slr_form_nav_color');
    if (!empty($slr_form_nav_color)) {
        $style .= "body.login #nav a { color: {$slr_form_nav_color} }";
    }

    /* back to blog link */
    $slr_form_backtoblog_color = carbonade('slr_form_backtoblog_color');
    if (!empty($slr_form_backtoblog_color)) {
        $style .= "body.login #backtoblog a { color: {$slr_form_backtoblog_color} }";
    }

    /* privacy-policy-page-link */
    $slr_form_privacy_color = carbonade('slr_form_privacy_color');
    if (!empty($slr_form_privacy_color)) {
        $style .= "body.login .privacy-policy-page-link a { 
            color: {$slr_form_privacy_color} 
        }";
    }

    /* submit button */

    // active
    $submit_bg_color = carbonade('slr_form_submit_background_color');
    $submit_text_color = carbonade('slr_form_submit_text_color');

    $submit_border_color = carbonade('slr_form_submit_border_color');
    $submit_border_radius = carbonade('slr_form_submit_radius');
    $submit_border_width = carbonade('slr_form_submit_border_width');
    $submit_border_radius = carbonade('slr_form_submit_radius');

    $submit_bg_color = ($submit_bg_color ? 'background:' . $submit_bg_color . ';' : '');
    $submit_text_color = ($submit_text_color ? 'color:' . $submit_text_color . ';' : '');

    $submit_border_color = ($submit_border_color ? 'border-color:' . $submit_border_color . ';' : '');
    $submit_border_width = ($submit_border_width ? 'border-width:' . $submit_border_width . 'px;' : '');
    $submit_border_style = ($submit_border_width ? 'border-style: solid' . ';' : '');
    $submit_border_radius = ($submit_border_radius ? 'border-radius:' . $submit_border_radius . 'px;' : '');

    // hover
    $submit_bg_color_hover = carbonade('slr_form_submit_background_color_hover');
    $submit_text_color_hover = carbonade('slr_form_submit_text_color_hover');
    $submit_bg_color_hover = ($submit_bg_color_hover ? 'background:' . $submit_bg_color_hover . ';' : '');
    $submit_text_color_hover = ($submit_text_color_hover ? 'color:' . $submit_text_color_hover . ';' : '');

    // align
    $submit_align = carbonade('slr_form_submit_align');
    if ($submit_align != 'default') {
        $style .= "body.login #login form p.submit { 
            display: grid; place-items: {$submit_align};
        }";
    }

    // size
    $submit_size = carbonade('slr_form_submit_size');

    if ($submit_size == 'custom') {
        $width = carbonade('slr_form_submit_size_width');
        $height = carbonade('slr_form_submit_size_height');

        $width = ($width ? 'width:' . $width . 'px;' : '');
        $height = ($height ? 'height:' . $height . 'px;' : '');
    } elseif ($submit_size == 'full-width') {
        $width = 'width:100%;';
        $height = 'height:100%;';
    } else {
        $width = '';
        $height = '';
    }

    if ($submit_bg_color || $submit_text_color || $submit_border_color
        || ($submit_border_width && $submit_border_style)
        ||  $submit_border_radius || $height || $width
    ) {
        $style .= "body.login #wp-submit {
            {$submit_bg_color} {$submit_text_color}
            {$submit_border_color} {$submit_border_width} 
            {$submit_border_style} {$submit_border_radius}
            {$height} {$width} 
        }";
    }

    if ($submit_bg_color_hover || $submit_text_color_hover) {
        $style .= "body.login #wp-submit:hover { 
            {$submit_bg_color_hover} {$submit_text_color_hover}
        }";
    }

    // echo!
    if (!empty($style)) {
        echo "<style>{$style}</style>";
    }
}
add_filter('login_head', __NAMESPACE__ . '\\Slr_Customizer_css', 50);

/**
 * WP and WC : Hide "Remember Me" checkbox
 *
 * @return mixed the styles to hide the "Remember Me" checkbox
 */
function Slr_Hide_Remember_me()
{
    $style = '';
    $slr_hide_remember_me = carbonade('slr_hide_remember_me');

    if ($slr_hide_remember_me == 'yes') {
        // WordPress
        $style .= 'p.forgetmenot {display:none}';
        // WooCommerce
        $style .= 'label.woocommerce-form__label.woocommerce-form__label-for-checkbox.woocommerce-form-login__rememberme {display:none}';
    }
    // echo!
    if (!empty($style)) {
        echo "<style>{$style}</style>";
    }
}
add_filter('login_head', __NAMESPACE__ . '\\Slr_Hide_Remember_me', 50);
add_action(
    'woocommerce_login_form_start',
    __NAMESPACE__ . '\\Slr_Hide_Remember_me',
    10
);

/**
 * EDD : Hide "Remember Me" checkbox
 *
 * @param $var the existing code
 *
 * @return mixed the styles to hide the "Remember Me" checkbox
 */
function Slr_Edd_Hide_Remember_me($var)
{
    $style = '';
    $slr_hide_remember_me = carbonade('slr_hide_remember_me');

    if ($slr_hide_remember_me == 'yes') {
        $style .= 'p.edd-login-remember { display: none; }'; // EDD
    }
    // echo!
    if (!empty($style)) {
        return $var . "<style>{$style}</style>";
    }

    // default return
    return $var;
}
add_filter('edd_login_form', __NAMESPACE__ . '\\Slr_Edd_Hide_Remember_me', 10, 1);


/**
 * WP and WC : check "Remember Me" checkbox
 *
 * @return mixed the script to check the "Remember Me" checkbox
 */
function Slr_Check_Remember_me()
{
    $slr_check_remember_me = carbonade('slr_check_remember_me');
    if ($slr_check_remember_me == 'yes') {
        echo "<script>
        const rememberme = document.getElementById('rememberme'); 
        if(rememberme){ rememberme.checked = true; }
        </script>";
    }
}
add_action('login_footer', __NAMESPACE__ . '\\slr_check_remember_me', 10);
add_action(
    'woocommerce_login_form_end',
    __NAMESPACE__ . '\\Slr_Check_Remember_me',
    10
);

/**
 * EDD : check the "Remember Me" checkbox
 *
 * @param $var the existing code
 *
 * @return mixed the styles to check the "Remember Me" checkbox
 */
function Slr_Edd_Check_Remember_me($var)
{
    $slr_check_remember_me = carbonade('slr_check_remember_me');
    if ($slr_check_remember_me == 'yes') {
        $var .= "<script>
        const rememberme = document.getElementById('rememberme'); 
        if(rememberme){ rememberme.checked = true; }
        </script>";
    }
    // default return
    return $var;
}
add_filter(
    'edd_login_form',
    __NAMESPACE__ . '\\Slr_Edd_Check_Remember_me',
    10,
    1
);
