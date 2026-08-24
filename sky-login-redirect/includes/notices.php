<?php
/**
 * Admin notices and promotional banners.
 *
 * Modern PHP 8.4+ implementation with proper state management.
 *
 * @category Notices
 * @package  Sky_Login_Redirect
 * @author   Utopique <support@utopique.net>
 * @license  GPL https://utopique.net
 * @link     https://utopique.net
 */

declare(strict_types=1);

namespace SkyLoginRedirect\Notices;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use function SkyLoginRedirect\sky_login_redirect_fs;

/**
 * Notice dismissal state enum.
 */
enum NoticeState: string {
	case DISMISSED = 'dismissed';
	case ACTIVE    = 'active';
}

/**
 * Admin notice manager class.
 */
final class NoticeManager {
	private const OPTION_KEY       = 'slr_promo_notice_dismissed';
	private const INSTALL_TIME_KEY = 'slr_install_time';
	private const NONCE_ACTION     = 'slr_notice_dismiss';
	private const AJAX_ACTION      = 'slr_dismiss_notice';
	private const DELAY_DAYS       = 2;

	private const PROMO_SCREENS = [
		'toplevel_page_sky-login-redirect',
		'login-redirect_page_sky-login-redirect-account',
		'login-redirect_page_sky-login-redirect-pricing',
		'plugins',
		'dashboard',
	];

	/**
	 * Create the notice manager.
	 *
	 * @param string $sale_end_date Sale end date in Ymd format.
	 * @param string $discount      Display discount.
	 * @param string $coupon_code   Upgrade coupon code.
	 */
	public function __construct(
		private string $sale_end_date = '20260131',
		private string $discount = '25%',
		private string $coupon_code = 'EOY2025'
	) {}

	/**
	 * Initialize notice hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->recordInstallTime();
		add_action( 'admin_init', $this->checkAndDisplayNotice( ... ) );
		add_action( 'admin_enqueue_scripts', $this->enqueueScripts( ... ) );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, $this->handleDismissal( ... ) );
	}

	/**
	 * Record installation time if not already set.
	 *
	 * @return void
	 */
	private function recordInstallTime(): void {
		if ( ! get_option( self::INSTALL_TIME_KEY ) ) {
			update_option( self::INSTALL_TIME_KEY, time(), true );
		}
	}

	/**
	 * Check if the delay period has passed since installation.
	 *
	 * @return bool True if delay period has elapsed.
	 */
	private function isDelayPeriodPassed(): bool {
		$install_time = get_option( self::INSTALL_TIME_KEY );

		if ( ! $install_time ) {
			return false;
		}

		$delay_seconds = self::DELAY_DAYS * DAY_IN_SECONDS;
		return ( time() - (int) $install_time ) >= $delay_seconds;
	}

	/**
	 * Check if notice is dismissed.
	 *
	 * @return bool True if the notice has been dismissed.
	 */
	private function isDismissed(): bool {
		$value = get_option( self::OPTION_KEY, false );
		return NoticeState::DISMISSED->value === $value || '1' === $value || 1 === $value || true === $value;
	}

	/**
	 * Check if sale is active.
	 *
	 * @return bool True if sale end date has not passed.
	 */
	private function isSaleActive(): bool {
		return gmdate( 'Ymd' ) <= $this->sale_end_date;
	}

	/**
	 * Check if current screen is valid for notices.
	 *
	 * @return bool True if current admin screen should show notices.
	 */
	private function isValidScreen(): bool {
		$screen = get_current_screen();
		return $screen && in_array( $screen->id, self::PROMO_SCREENS, true );
	}

	/**
	 * Check conditions and display notice if appropriate.
	 *
	 * @return void
	 */
	private function checkAndDisplayNotice(): void {
		if ( $this->isDismissed() || ! $this->isSaleActive() || ! $this->isDelayPeriodPassed() ) {
			return;
		}

		add_action( 'admin_notices', $this->renderNotice( ... ) );
	}

	/**
	 * Enqueue dismiss script.
	 *
	 * Only loads when notice will actually be displayed.
	 *
	 * @return void
	 */
	private function enqueueScripts(): void {
		// Check all conditions - only load if notice will be shown
		if ( $this->isDismissed() || ! $this->isSaleActive() || ! $this->isDelayPeriodPassed() || ! $this->isValidScreen() ) {
			return;
		}

		wp_enqueue_script(
			'slr-notice-dismiss',
			plugins_url( '/assets/js/admin-notices.js', dirname( __DIR__ ) . '/sky-login-redirect.php' ),
			[],
			SLR_VERSION,
			true
		);

		wp_localize_script(
			'slr-notice-dismiss',
			'slrNotice',
			[
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( self::NONCE_ACTION ),
				'action'   => self::AJAX_ACTION,
			]
		);
	}

	/**
	 * Handle AJAX dismissal request.
	 *
	 * @return void
	 */
	private function handleDismissal(): void {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Forbidden' ], 403 );
			return;
		}

		$updated = update_option( self::OPTION_KEY, NoticeState::DISMISSED->value, true );

		if ( $updated ) {
			wp_send_json_success( [ 'message' => 'Notice dismissed successfully' ] );
		} else {
			wp_send_json_error( [ 'message' => 'Failed to dismiss notice' ], 500 );
		}
	}

	/**
	 * Render the promotional notice.
	 *
	 * @return void
	 */
	private function renderNotice(): void {
		if ( ! $this->isValidScreen() ) {
			return;
		}

		$upgrade_url = sky_login_redirect_fs()->get_upgrade_url();
		$upgrade_url = add_query_arg( 'coupon', $this->coupon_code, $upgrade_url );

		printf(
			'<div class="slr-promo notice notice-info is-dismissible"><p>%s <a href="%s" target="_blank" rel="noopener">%s</a>.</p></div>',
			sprintf(
				/* translators: %s: discount percentage (e.g., "30%") */
				esc_html__( 'New Year sale: save %s on', 'sky-login-redirect' ),
				esc_html( $this->discount )
			),
			esc_url( $upgrade_url ),
			esc_html__( 'Sky Login Redirect Pro', 'sky-login-redirect' )
		);
	}
}

// Initialize notice manager.
$notice_manager = new NoticeManager();
$notice_manager->init();
