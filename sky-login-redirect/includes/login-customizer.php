<?php
/**
 * Login page customizer.
 *
 * Modern PHP 8.1+ implementation with strict types and class-based architecture.
 *
 * @category Login_Customizer
 * @package  Sky_Login_Redirect
 * @author   Utopique <support@utopique.net>
 * @license  GPL https://utopique.net
 * @link     https://utopique.net
 */

declare(strict_types=1);

namespace SkyLoginRedirect\LoginCustomizer;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use SkyLoginRedirect\CSSBuilder;
use function SkyLoginRedirect\carbonade;

require_once dirname( __DIR__ ) . '/includes/class-css-builder.php';

/**
 * Login page customizer manager.
 */
final class LoginPageCustomizer {
    /**
     * Remember Me checkbox script (minified).
     */
    private const REMEMBER_ME_SCRIPT = "<script>document.addEventListener('DOMContentLoaded',function(){var r=document.getElementById('rememberme');if(r)r.checked=true});</script>";

    /**
     * Build CSS property string (legacy helper for inline usage).
     */
    private function css( string $prop, mixed $value, string $unit = '', bool $is_int = false ): string {
        if ( ! $value ) {
            return '';
        }
        $val = $is_int ? (int) $value : sanitize_text_field( (string) $value );
        return "{$prop}:{$val}{$unit};";
    }

    /**
     * Filter custom login URL.
     */
    public function customLoginPage( string $url, string $redirect, bool $force_reauth ): string {
        if ( is_admin() ) {
            return $url;
        }

        $page = carbonade( 'slr_custom_login_url' );
        if ( ! $page ) {
            return $url;
        }

        $slug = trailingslashit( basename( (string) wp_parse_url( (string) $page, PHP_URL_PATH ) ) );
        $url  = site_url( $slug );

        if ( $redirect ) {
            $url = add_query_arg( 'redirect_to', rawurlencode( wp_validate_redirect( $redirect, $url ) ), $url );
        }
        if ( $force_reauth ) {
            $url = add_query_arg( 'reauth', '1', $url );
        }

        return $url;
    }

    /**
     * Change logo link to home URL.
     */
    public function loginLogoUrl( string $url ): string {
        return carbonade( 'slr_logo_link' ) === 'yes' ? home_url( '/' ) : $url;
    }

    /**
     * Change logo header text to site name.
     */
    public function loginLogoTitle( string $text ): string {
        return carbonade( 'slr_logo_text' ) === 'yes' ? get_bloginfo( 'name' ) : $text;
    }

    /**
     * Output login preview iframe on settings page.
     */
    public function loginPreview( string $hook ): void {
        if ( $hook !== 'toplevel_page_sky-login-redirect' ) {
            return;
        }
        $preview_title = esc_attr__( 'Login page preview', 'sky-login-redirect' );
        ?>
<div id="slr-login-iframe" style="width:100%;border:0;display:none">
    <h3 style="margin-left:1.4rem;font-weight:400;"><?php esc_html_e( 'Save your settings first to see changes &#8623;', 'sky-login-redirect' ); ?></h3>
    <iframe src="<?php echo esc_url( home_url( '/wp-login.php' ) ); ?>" height="540px" width="100%" sandbox="allow-same-origin allow-scripts allow-forms" title="<?php echo $preview_title; ?>" aria-label="<?php echo $preview_title; ?>"></iframe>
</div>
        <?php
    }

    /**
     * Get logo URL and height from attachment or URL.
     */
    private function getLogoData( string|int $logo ): array {
    $url    = (string) $logo;
    $height = 80;

    if ( is_numeric( $logo ) ) {
        $src = wp_get_attachment_image_src( (int) $logo, 'full' );
        if ( $src ) {
            return [ 'url' => $src[0], 'height' => (int) ( $src[2] ?? 80 ) ];
        }
    } else {
        $uploads   = wp_get_upload_dir();
        $logo_host = wp_parse_url( $url, PHP_URL_HOST );
        $upl_host  = wp_parse_url( $uploads['baseurl'], PHP_URL_HOST );

        if ( $logo_host && $upl_host && strcasecmp( $logo_host, $upl_host ) === 0 ) {
            $id = attachment_url_to_postid( $url );
            if ( $id ) {
                $src = wp_get_attachment_image_src( $id, 'full' );
                if ( $src ) {
                    return [ 'url' => $src[0], 'height' => (int) ( $src[2] ?? 80 ) ];
                }
            }
        }
    }

    return [ 'url' => $url, 'height' => $height ];
}

    /**
     * Output login page customizer CSS.
     */
    public function customizerCSS(): void {
    $style = '';

    // Hide elements.
    if ( carbonade( 'slr_hide_backtoblog' ) === 'yes' ) {
        $style .= 'p#backtoblog a{display:none}';
    }
    if ( carbonade( 'slr_hide_privacy_policy' ) === 'yes' ) {
        $style .= '.login .privacy-policy-page-link{display:none}';
    }

    // Custom logo.
    $logo = carbonade( 'slr_custom_logo' );
    if ( $logo ) {
        $data   = $this->getLogoData( $logo );
        $url    = esc_url( $data['url'] );
        $height = $data['height'];

        $style .= ".login h1{margin:0 auto 24px;text-align:center}"
            . ".login h1 a{background-image:url('{$url}')!important;background-size:contain;"
            . "background-position:center;background-repeat:no-repeat;color:#444;font-size:20px;"
            . "font-weight:400;line-height:1.3;margin:0 auto;padding:0;text-decoration:none;"
            . "text-indent:-9999px;outline:0;overflow:hidden;display:block;width:100%;"
            . "max-width:320px;height:{$height}px}";
    }

    // Page background.
    $page_bg = carbonade( 'slr_page_background_color' );
    if ( $page_bg ) {
        $style .= 'body.login{background:' . sanitize_text_field( $page_bg ) . '}';
    }

    $page_bg_img = carbonade( 'slr_page_background_image' );
    if ( $page_bg_img ) {
        $style .= "body.login{background-image:url('" . esc_url( $page_bg_img ) . "');"
            . "background-repeat:no-repeat;background-position:center;background-size:cover}";
    }

    // Form background.
    $form_bg = carbonade( 'slr_form_background_color' );
    if ( $form_bg ) {
        $style .= 'body.login #loginform{background:' . sanitize_text_field( $form_bg ) . '}';
    }

    $form_bg_img = carbonade( 'slr_form_background_image' );
    if ( $form_bg_img ) {
        $style .= "body.login #loginform{background-image:url('" . esc_url( $form_bg_img ) . "');"
            . "background-repeat:no-repeat;background-position:center}";
    }

    // Modern layout.
    $style .= 'body.login{display:flex;align-items:center;justify-content:center;min-height:100vh;'
        . 'padding:4vh 16px;box-sizing:border-box}'
        . 'body.login #login{width:100%;max-width:380px;padding:0;box-sizing:border-box}'
        . 'body.login form#loginform{box-shadow:0 8px 24px rgba(15,23,42,0.12);border-radius:8px;'
        . 'border:1px solid rgba(148,163,184,0.35);padding:24px 24px 28px;backdrop-filter:blur(6px);box-sizing:border-box}'
        . '@media(max-width:480px){body.login{align-items:flex-start;padding-top:8vh}'
        . 'body.login form#loginform{padding:20px 18px 24px;box-shadow:0 4px 16px rgba(15,23,42,0.16)}}';

    // Colors.
    $style .= $this->css( 'color', carbonade( 'slr_form_labels_color' ) ) ? "body.login #loginform label{" . $this->css( 'color', carbonade( 'slr_form_labels_color' ) ) . "}" : '';
    $style .= $this->css( 'color', carbonade( 'slr_form_nav_color' ) ) ? "body.login #nav a{" . $this->css( 'color', carbonade( 'slr_form_nav_color' ) ) . "}" : '';
    $style .= $this->css( 'color', carbonade( 'slr_form_backtoblog_color' ) ) ? "body.login #backtoblog a{" . $this->css( 'color', carbonade( 'slr_form_backtoblog_color' ) ) . "}" : '';
    $style .= $this->css( 'color', carbonade( 'slr_form_privacy_color' ) ) ? "body.login .privacy-policy-page-link a{" . $this->css( 'color', carbonade( 'slr_form_privacy_color' ) ) . "}" : '';

    // Submit button.
    $btn = $this->css( 'background', carbonade( 'slr_form_submit_background_color' ) )
         . $this->css( 'color', carbonade( 'slr_form_submit_text_color' ) )
         . $this->css( 'border-color', carbonade( 'slr_form_submit_border_color' ) )
         . $this->css( 'border-width', carbonade( 'slr_form_submit_border_width' ), 'px', true )
         . ( carbonade( 'slr_form_submit_border_width' ) ? 'border-style:solid;' : '' )
         . $this->css( 'border-radius', carbonade( 'slr_form_submit_radius' ), 'px', true );

    // Button alignment.
    $align = carbonade( 'slr_form_submit_align' );
    if ( $align && $align !== 'default' ) {
        $justify = $align === 'center' ? 'center' : ( $align === 'end' ? 'flex-end' : 'flex-start' );
        $style  .= "body.login #login form p.submit{display:flex;justify-content:{$justify};gap:8px}";
    }

    // Button size.
    $size = carbonade( 'slr_form_submit_size' );
    if ( $size === 'custom' ) {
        $btn .= $this->css( 'width', carbonade( 'slr_form_submit_size_width' ), 'px', true )
              . $this->css( 'height', carbonade( 'slr_form_submit_size_height' ), 'px', true );
    } elseif ( $size === 'full-width' ) {
        $btn .= 'width:100%;height:100%;';
    }

    if ( $btn ) {
        $style .= "body.login #wp-submit{{$btn}}";
    }

    // Button hover.
    $hover = $this->css( 'background', carbonade( 'slr_form_submit_background_color_hover' ) )
           . $this->css( 'color', carbonade( 'slr_form_submit_text_color_hover' ) );
    if ( $hover ) {
        $style .= "body.login #wp-submit:hover{{$hover}}";
    }

    if ( $style ) {
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Intentional inline CSS from admin options.
        echo "<style>{$style}</style>";
    }
    }

    /**
     * Hide "Remember Me" checkbox (WP/WC).
     */
    public function hideRememberMe(): void {
        if ( carbonade( 'slr_hide_remember_me' ) === 'yes' ) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '<style>p.forgetmenot,label.woocommerce-form__label-for-checkbox.woocommerce-form-login__rememberme{display:none}</style>';
        }
    }

    /**
     * Hide "Remember Me" checkbox (EDD).
     */
    public function eddHideRememberMe( string $html ): string {
        if ( carbonade( 'slr_hide_remember_me' ) === 'yes' ) {
            return $html . '<style>p.edd-login-remember{display:none}</style>';
        }
        return $html;
    }

    /**
     * Check "Remember Me" checkbox by default (WP/WC).
     */
    public function checkRememberMe(): void {
        if ( carbonade( 'slr_check_remember_me' ) === 'yes' ) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo self::REMEMBER_ME_SCRIPT;
        }
    }

    /**
     * Check "Remember Me" checkbox by default (EDD).
     */
    public function eddCheckRememberMe( string $html ): string {
        if ( carbonade( 'slr_check_remember_me' ) === 'yes' ) {
            return $html . self::REMEMBER_ME_SCRIPT;
        }
        return $html;
    }
}

// Initialize login page customizer
$login_customizer = new LoginPageCustomizer();
add_filter( 'login_url', $login_customizer->customLoginPage(...), 10, 3 );
add_filter( 'login_headerurl', $login_customizer->loginLogoUrl(...) );
add_filter( 'login_headertext', $login_customizer->loginLogoTitle(...) );
add_action( 'admin_enqueue_scripts', $login_customizer->loginPreview(...), 20 );
add_filter( 'login_head', $login_customizer->customizerCSS(...), 50 );
add_filter( 'login_head', $login_customizer->hideRememberMe(...), 50 );
add_action( 'woocommerce_login_form_start', $login_customizer->hideRememberMe(...), 10 );
add_filter( 'edd_login_form', $login_customizer->eddHideRememberMe(...) );
add_action( 'login_footer', $login_customizer->checkRememberMe(...), 10 );
add_action( 'woocommerce_login_form_end', $login_customizer->checkRememberMe(...), 10 );
add_filter( 'edd_login_form', $login_customizer->eddCheckRememberMe(...) );

// Hide language switcher
if ( carbonade( 'slr_hide_language_switcher' ) === 'yes' ) {
    add_filter( 'login_display_language_dropdown', '__return_false' );
}
