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

/**
 * Login page customizer manager.
 */
final class LoginPageCustomizer {
	/**
	 * Remember Me checkbox script (minified).
	 */
	private const REMEMBER_ME_SCRIPT = "<script>document.addEventListener('DOMContentLoaded',function(){var r=document.getElementById('rememberme');if(r)r.checked=true});</script>";

	// CSS Selectors.
	private const SELECTOR_BODY           = 'body.login';
	private const SELECTOR_LOGIN          = 'body.login #login';
	private const SELECTOR_FORM           = 'body.login #loginform';
	private const SELECTOR_SUBMIT         = 'body.login #wp-submit';
	private const SELECTOR_SUBMIT_HOVER   = 'body.login #wp-submit:hover';
	private const SELECTOR_LABELS         = 'body.login #loginform label';
	private const SELECTOR_NAV            = 'body.login #nav a';
	private const SELECTOR_BACKTOBLOG     = 'body.login #backtoblog a';
	private const SELECTOR_PRIVACY        = 'body.login .privacy-policy-page-link a';
	private const SELECTOR_SUBMIT_WRAPPER = 'body.login #login form p.submit';

	// Layout values.
	private const LAYOUT_MIN_HEIGHT = '100vh';
	private const LAYOUT_PADDING    = '4vh 16px';
	private const LAYOUT_MAX_WIDTH  = '380px';
	private const LAYOUT_MARGIN_TOP = '8vh';

	// Form styling.
	private const FORM_SHADOW        = '0 8px 24px rgba(15,23,42,0.12)';
	private const FORM_BORDER_RADIUS = '8px';
	private const FORM_BORDER        = '1px solid rgba(148,163,184,0.35)';
	private const FORM_PADDING       = '24px 24px 28px';
	private const FORM_BACKDROP      = 'blur(6px)';

	// Mobile responsive.
	private const MOBILE_BREAKPOINT = '480px';
	private const MOBILE_MARGIN_TOP = '4vh';
	private const MOBILE_PADDING    = '20px 18px 24px';
	private const MOBILE_SHADOW     = '0 4px 16px rgba(15,23,42,0.16)';

	// Logo styling.
	private const LOGO_MARGIN      = '0 auto 24px';
	private const LOGO_TEXT_ALIGN  = 'center';
	private const LOGO_MAX_WIDTH   = '320px';
	private const LOGO_TEXT_INDENT = '-9999px';

	/**
	 * Cached CSS output to avoid regenerating on multiple calls.
	 *
	 * @var string|null
	 */
	private ?string $cachedCSS = null;

	/**
	 * Cached logo data to avoid duplicate image processing.
	 *
	 * @var array
	 */
	private array $logoDataCache = [];

	public function __construct(
		private CSSBuilder $css = new CSSBuilder()
	) {}

	/**
	 * Filter custom login URL.
	 *
	 * @param mixed $url          Default login URL (may be polluted by other plugins).
	 * @param mixed $redirect     Redirect destination (may be polluted by other plugins).
	 * @param mixed $force_reauth Whether to force reauthentication.
	 * @return string Modified login URL.
	 */
	public function customLoginPage( mixed $url, mixed $redirect = '', mixed $force_reauth = false ): string {
		$url          = is_scalar( $url ) ? (string) $url : '';
		$redirect     = is_scalar( $redirect ) ? (string) $redirect : '';
		$force_reauth = (bool) $force_reauth;

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
	 *
	 * @param string $url Default logo URL.
	 * @return string Home URL or original URL.
	 */
	public function loginLogoUrl( string $url ): string {
		return 'yes' === carbonade( 'slr_logo_link' ) ? home_url( '/' ) : $url;
	}

	/**
	 * Change logo header text to site name.
	 *
	 * @param string $text Default header text.
	 * @return string Site name or original text.
	 */
	public function loginLogoTitle( string $text ): string {
		return 'yes' === carbonade( 'slr_logo_text' ) ? get_bloginfo( 'name' ) : $text;
	}

	/**
	 * Output login preview iframe on settings page.
	 *
	 * @param string $hook Current admin page hook suffix.
	 * @return void
	 */
	public function loginPreview( string $hook ): void {
		if ( 'toplevel_page_sky-login-redirect' !== $hook ) {
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
	 *
	 * Caches results to avoid duplicate image processing calls.
	 *
	 * @param string|int $logo Attachment ID or URL string.
	 * @return array{url: string, height: int} Logo data with URL and height in pixels.
	 */
	private function getLogoData( string|int $logo ): array {
		$cache_key = is_numeric( $logo ) ? "id_{$logo}" : md5( (string) $logo );

		if ( isset( $this->logoDataCache[ $cache_key ] ) ) {
			return $this->logoDataCache[ $cache_key ];
		}

		$url    = (string) $logo;
		$height = 80;

		if ( is_numeric( $logo ) ) {
			$src = wp_get_attachment_image_src( (int) $logo, 'full' );
			if ( $src ) {
				$this->logoDataCache[ $cache_key ] = [
					'url'    => $src[0],
					'height' => (int) ( $src[2] ?? 80 ),
				];
				return $this->logoDataCache[ $cache_key ];
			}
		} else {
			$uploads   = wp_get_upload_dir();
			$logo_host = wp_parse_url( $url, PHP_URL_HOST );
			$upl_host  = wp_parse_url( $uploads['baseurl'], PHP_URL_HOST );

			if ( $logo_host && $upl_host && strcasecmp( $logo_host, $upl_host ) === 0 ) {
				// Cache attachment_url_to_postid() — expensive reverse-lookup (VIP / Cache Product Objects compat).
				$att_cache_key = 'slr_att_' . md5( $url );
				$id            = wp_cache_get( $att_cache_key, 'slr' );
				if ( false === $id ) {
					$id = attachment_url_to_postid( $url );
					wp_cache_set( $att_cache_key, $id, 'slr', DAY_IN_SECONDS );
				}
				if ( $id ) {
					$src = wp_get_attachment_image_src( $id, 'full' );
					if ( $src ) {
						$this->logoDataCache[ $cache_key ] = [
							'url'    => $src[0],
							'height' => (int) ( $src[2] ?? 80 ),
						];
						return $this->logoDataCache[ $cache_key ];
					}
				}
			}
		}

		$this->logoDataCache[ $cache_key ] = [
			'url'    => $url,
			'height' => $height,
		];
		return $this->logoDataCache[ $cache_key ];
	}

	/**
	 * Output login page customizer CSS.
	 *
	 * Composes CSS from all builder methods and outputs as inline style tag.
	 * Uses array composition pattern for clean separation of concerns.
	 * Caches output to avoid regenerating CSS on multiple calls within same request.
	 *
	 * @return void
	 */
	public function customizerCSS(): void {
		if ( $this->cachedCSS === null ) {
			$styles = [
				$this->buildHideElementsStyles(),
				$this->buildLogoStyles(),
				$this->buildBodyStyles(),
				$this->buildFormStyles(),
				$this->buildLoginContainerStyles(),
				$this->buildMobileStyles(),
				$this->buildColorStyles(),
				$this->buildButtonStyles(),
				$this->buildButtonHoverStyles(),
			];

			$this->cachedCSS = implode( '', array_filter( $styles ) );
		}

		if ( $this->cachedCSS ) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Intentional inline CSS from admin options, strip_all_tags as defense-in-depth.
			echo '<style>' . wp_strip_all_tags( $this->cachedCSS ) . '</style>';
		}
	}

	/**
	 * Build CSS to hide optional login page elements.
	 *
	 * Generates display:none rules for:
	 * - Back to blog link
	 * - Privacy policy link
	 *
	 * @return string CSS rules for hiding elements, empty if no elements hidden.
	 */
	private function buildHideElementsStyles(): string {
		$styles = [];

		if ( 'yes' === carbonade( 'slr_hide_backtoblog' ) ) {
			$this->css->reset()->add( 'display', 'none' );
			$styles[] = 'p#backtoblog a{' . $this->css->build() . '}';
		}
		if ( 'yes' === carbonade( 'slr_hide_privacy_policy' ) ) {
			$this->css->reset()->add( 'display', 'none' );
			$styles[] = '.login .privacy-policy-page-link{' . $this->css->build() . '}';
		}

		return implode( '', $styles );
	}

	/**
	 * Build custom logo styles for login page.
	 *
	 * Generates CSS for custom logo including:
	 * - Logo container centering and spacing
	 * - Logo image with background-image property
	 * - Responsive height based on image dimensions
	 * - Text hiding for accessibility
	 *
	 * @return string CSS rules for logo styling, empty if no logo set.
	 */
	private function buildLogoStyles(): string {
		$logo = carbonade( 'slr_custom_logo' );
		if ( ! $logo ) {
			return '';
		}

		$data   = $this->getLogoData( $logo );
		$url    = esc_url( $data['url'] );
		$height = $data['height'];

		// Logo container.
		$this->css->reset()
			->add( 'margin', self::LOGO_MARGIN )
			->add( 'text-align', self::LOGO_TEXT_ALIGN );
		$h1_styles = $this->css->build();

		// Logo link with image.
		$this->css->reset()
			->add( 'background-image', "url('{$url}')" )
			->add( 'background-size', 'contain' )
			->add( 'background-position', 'center' )
			->add( 'background-repeat', 'no-repeat' )
			->add( 'color', '#444' )
			->add( 'font-size', '20px' )
			->add( 'font-weight', '400' )
			->add( 'line-height', '1.3' )
			->add( 'margin', '0 auto' )
			->add( 'padding', '0' )
			->add( 'text-decoration', 'none' )
			->add( 'text-indent', self::LOGO_TEXT_INDENT )
			->add( 'outline', '0' )
			->add( 'overflow', 'hidden' )
			->add( 'display', 'block' )
			->add( 'width', '100%' )
			->add( 'max-width', self::LOGO_MAX_WIDTH )
			->add( 'height', "{$height}px" );
		$link_styles = $this->css->build();

		return ".login h1{{$h1_styles}}.login h1 a{{$link_styles}!important}";
	}

	/**
	 * Build body.login container styles.
	 *
	 * Generates layout styles for the login page body including:
	 * - Full viewport height
	 * - Responsive padding
	 * - Optional background color/image
	 *
	 * @return string CSS rule for body.login selector, empty if no properties set.
	 */
	private function buildBodyStyles(): string {
		$this->css->reset()
			->add( 'min-height', self::LAYOUT_MIN_HEIGHT )
			->add( 'padding', self::LAYOUT_PADDING )
			->add( 'box-sizing', 'border-box' );

		$page_bg = carbonade( 'slr_page_background_color' );
		if ( $page_bg ) {
			$this->css->add( 'background', $page_bg );
		}

		$page_bg_img = carbonade( 'slr_page_background_image' );
		if ( $page_bg_img ) {
			$this->css->add( 'background-image', "url('" . esc_url( $page_bg_img ) . "')" )
				->add( 'background-repeat', 'no-repeat' )
				->add( 'background-position', 'center' )
				->add( 'background-size', 'auto' );
		}

		$props = $this->css->build();
		return $props ? self::SELECTOR_BODY . "{{$props}}" : '';
	}

	/**
	 * Build login form styles.
	 *
	 * Generates modern form styling including:
	 * - Elevated shadow for depth
	 * - Rounded corners
	 * - Semi-transparent border
	 * - Backdrop blur effect
	 * - Optional background color/image
	 *
	 * @return string CSS rule for #loginform selector, empty if no properties set.
	 */
	private function buildFormStyles(): string {
		$this->css->reset()
			->add( 'box-shadow', self::FORM_SHADOW )
			->add( 'border-radius', self::FORM_BORDER_RADIUS )
			->add( 'border', self::FORM_BORDER )
			->add( 'padding', self::FORM_PADDING )
			->add( 'backdrop-filter', self::FORM_BACKDROP )
			->add( 'box-sizing', 'border-box' );

		$form_bg = carbonade( 'slr_form_background_color' );
		if ( $form_bg ) {
			$this->css->add( 'background', $form_bg );
		}

		$form_bg_img = carbonade( 'slr_form_background_image' );
		if ( $form_bg_img ) {
			$this->css->add( 'background-image', "url('" . esc_url( $form_bg_img ) . "')" )
				->add( 'background-repeat', 'no-repeat' )
				->add( 'background-position', 'center' );
		}

		$props = $this->css->build();
		return $props ? self::SELECTOR_FORM . "{{$props}}" : '';
	}

	/**
	 * Build #login container styles.
	 *
	 * Generates centered container layout with:
	 * - Full width with max-width constraint
	 * - Vertical spacing from top
	 * - Horizontal centering
	 *
	 * @return string CSS rule for #login container.
	 */
	private function buildLoginContainerStyles(): string {
		$this->css->reset()
			->add( 'width', '100%' )
			->add( 'max-width', self::LAYOUT_MAX_WIDTH )
			->add( 'margin', self::LAYOUT_MARGIN_TOP . ' auto 0' )
			->add( 'padding', '0' )
			->add( 'box-sizing', 'border-box' );

		return self::SELECTOR_LOGIN . '{' . $this->css->build() . '}';
	}

	/**
	 * Build mobile responsive styles.
	 *
	 * Generates media query for mobile devices with:
	 * - Reduced top margin for smaller screens
	 * - Adjusted padding for touch targets
	 * - Lighter shadow for mobile context
	 *
	 * @return string CSS media query for mobile breakpoint.
	 */
	private function buildMobileStyles(): string {
		// Login container mobile styles.
		$this->css->reset()->add( 'margin-top', self::MOBILE_MARGIN_TOP );
		$login_mobile = self::SELECTOR_LOGIN . '{' . $this->css->build() . '}';

		// Form mobile styles.
		$this->css->reset()
			->add( 'padding', self::MOBILE_PADDING )
			->add( 'box-shadow', self::MOBILE_SHADOW );
		$form_mobile = self::SELECTOR_FORM . '{' . $this->css->build() . '}';

		return '@media(max-width:' . self::MOBILE_BREAKPOINT . '){' . $login_mobile . $form_mobile . '}';
	}

	/**
	 * Build custom color styles for form elements.
	 *
	 * Generates color overrides for:
	 * - Form labels
	 * - Navigation links
	 * - Back to blog link
	 * - Privacy policy link
	 *
	 * @return string CSS rules for custom colors, empty if no colors set.
	 */
	private function buildColorStyles(): string {
		$styles = [];

		$color = carbonade( 'slr_form_labels_color' );
		if ( $color ) {
			$this->css->reset()->add( 'color', $color );
			$styles[] = self::SELECTOR_LABELS . '{' . $this->css->build() . '}';
		}

		$color = carbonade( 'slr_form_nav_color' );
		if ( $color ) {
			$this->css->reset()->add( 'color', $color );
			$styles[] = self::SELECTOR_NAV . '{' . $this->css->build() . '}';
		}

		$color = carbonade( 'slr_form_backtoblog_color' );
		if ( $color ) {
			$this->css->reset()->add( 'color', $color );
			$styles[] = self::SELECTOR_BACKTOBLOG . '{' . $this->css->build() . '}';
		}

		$color = carbonade( 'slr_form_privacy_color' );
		if ( $color ) {
			$this->css->reset()->add( 'color', $color );
			$styles[] = self::SELECTOR_PRIVACY . '{' . $this->css->build() . '}';
		}

		return implode( '', $styles );
	}

	/**
	 * Build submit button styles.
	 *
	 * Generates comprehensive button styling including:
	 * - Background and text colors
	 * - Border styling (color, width, radius)
	 * - Size options (default, custom, full-width)
	 * - Alignment options (left, center, right)
	 *
	 * @return string CSS rules for submit button, empty if no properties set.
	 */
	private function buildButtonStyles(): string {
		$border_width = carbonade( 'slr_form_submit_border_width' );

		$this->css->reset()
			->add( 'background', carbonade( 'slr_form_submit_background_color' ) )
			->add( 'color', carbonade( 'slr_form_submit_text_color' ) )
			->add( 'border-color', carbonade( 'slr_form_submit_border_color' ) )
			->add( 'border-width', $border_width, 'px', true )
			->add( 'border-radius', carbonade( 'slr_form_submit_radius' ), 'px', true );

		if ( $border_width ) {
			$this->css->add( 'border-style', 'solid' );
		}

		// Button size.
		$size = carbonade( 'slr_form_submit_size' );
		if ( 'custom' === $size ) {
			$this->css->add( 'width', carbonade( 'slr_form_submit_size_width' ), 'px', true )
				->add( 'height', carbonade( 'slr_form_submit_size_height' ), 'px', true );
		} elseif ( 'full-width' === $size ) {
			$this->css->add( 'width', '100%' )->add( 'height', '100%' );
		}

		$props      = $this->css->build();
		$button_css = $props ? self::SELECTOR_SUBMIT . "{{$props}}" : '';

		// Button alignment.
		$align = carbonade( 'slr_form_submit_align' );
		if ( $align && 'default' !== $align ) {
			$justify     = 'center' === $align ? 'center' : ( 'end' === $align ? 'flex-end' : 'flex-start' );
			$button_css .= self::SELECTOR_SUBMIT_WRAPPER . "{display:flex;justify-content:{$justify};gap:8px}";
		}

		return $button_css;
	}

	/**
	 * Build submit button hover styles.
	 *
	 * Generates hover state styling for:
	 * - Background color transition
	 * - Text color transition
	 *
	 * @return string CSS rule for button hover state, empty if no colors set.
	 */
	private function buildButtonHoverStyles(): string {
		$this->css->reset()
			->add( 'background', carbonade( 'slr_form_submit_background_color_hover' ) )
			->add( 'color', carbonade( 'slr_form_submit_text_color_hover' ) );

		$props = $this->css->build();
		return $props ? self::SELECTOR_SUBMIT_HOVER . "{{$props}}" : '';
	}

	/**
	 * Hide "Remember Me" checkbox (WP/WC).
	 *
	 * @return void
	 */
	public function hideRememberMe(): void {
		if ( 'yes' === carbonade( 'slr_hide_remember_me' ) ) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static CSS string, no dynamic values.
			echo '<style>p.forgetmenot,label.woocommerce-form__label-for-checkbox.woocommerce-form-login__rememberme{display:none}</style>';
		}
	}

	/**
	 * Check "Remember Me" checkbox by default (WP/WC).
	 *
	 * @return void
	 */
	public function checkRememberMe(): void {
		if ( 'yes' === carbonade( 'slr_check_remember_me' ) ) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo self::REMEMBER_ME_SCRIPT;
		}
	}

	/**
	 * Modify EDD login form markup.
	 *
	 * @param string $html EDD login form HTML.
	 * @return string
	 */
	public function customizeEddLoginForm( string $html ): string {
		if ( 'yes' === carbonade( 'slr_hide_remember_me' ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static CSS string, no dynamic values.
			$html .= '<style>p.edd-login-remember{display:none}</style>';
		}

		if ( 'yes' === carbonade( 'slr_check_remember_me' ) ) {
			$html .= self::REMEMBER_ME_SCRIPT;
		}

		return $html;
	}
	
	/**
	 * Control whether the login language dropdown should be displayed.
	 *
	 * @param bool $display Whether to display the language dropdown.
	 * @return bool
	 */
	public function maybeDisplayLanguageDropdown( bool $display ): bool {
		if ( 'yes' === carbonade( 'slr_hide_language_switcher' ) ) {
			return false;
		}

		return $display;
	}
}

// Initialize login page customizer.
$login_customizer = new LoginPageCustomizer();

add_filter( 'login_url', $login_customizer->customLoginPage( ... ), 10, 3 );
add_filter( 'login_headerurl', $login_customizer->loginLogoUrl( ... ) );
add_filter( 'login_headertext', $login_customizer->loginLogoTitle( ... ) );

add_action( 'admin_enqueue_scripts', $login_customizer->loginPreview( ... ), 20 );

add_action( 'login_head', $login_customizer->customizerCSS( ... ), 50 );
add_action( 'login_head', $login_customizer->hideRememberMe( ... ), 50 );

add_action( 'woocommerce_login_form_start', $login_customizer->hideRememberMe( ... ), 10 );
add_action( 'login_footer', $login_customizer->checkRememberMe( ... ), 10 );
add_action( 'woocommerce_login_form_end', $login_customizer->checkRememberMe( ... ), 10 );
add_filter( 'edd_login_form', $login_customizer->customizeEddLoginForm( ... ) );

add_filter(	'login_display_language_dropdown',$login_customizer->maybeDisplayLanguageDropdown( ... ) );