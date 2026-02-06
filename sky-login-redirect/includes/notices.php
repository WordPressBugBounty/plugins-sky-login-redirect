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

use function SkyLoginRedirect\Sky_Login_Redirect_fs;

/**
 * Notice dismissal state enum.
 */
enum NoticeState: string {
    case DISMISSED = 'dismissed';
    case ACTIVE = 'active';
}

/**
 * Admin notice manager class.
 */
final class NoticeManager {
    private const OPTION_KEY = 'slr_promo_notice_dismissed';
    private const NONCE_ACTION = 'slr_notice_dismiss';
    private const AJAX_ACTION = 'slr_dismiss_notice';

    private const PROMO_SCREENS = [
        'toplevel_page_sky-login-redirect',
        'login-redirect_page_sky-login-redirect-account',
        'login-redirect_page_sky-login-redirect-pricing',
        'plugins',
        'dashboard',
    ];

    public function __construct(
        private string $saleEndDate = '20260131',
        private string $discount = '25%',
        private string $couponCode = 'EOY2025'
    ) {}

    /**
     * Initialize notice hooks.
     */
    public function init(): void {
        add_action( 'admin_init', $this->checkAndDisplayNotice(...) );
        add_action( 'admin_enqueue_scripts', $this->enqueueScripts(...) );
        add_action( 'wp_ajax_' . self::AJAX_ACTION, $this->handleDismissal(...) );
    }

    /**
     * Check if notice is dismissed.
     */
    private function isDismissed(): bool {
        $value = get_option( self::OPTION_KEY, false );
        return $value === NoticeState::DISMISSED->value || $value === '1' || $value === 1 || $value === true;
    }

    /**
     * Check if sale is active.
     */
    private function isSaleActive(): bool {
        return gmdate( 'Ymd' ) <= $this->saleEndDate;
    }

    /**
     * Check if current screen is valid for notices.
     */
    private function isValidScreen(): bool {
        $screen = get_current_screen();
        return $screen && in_array( $screen->id, self::PROMO_SCREENS, true );
    }

    /**
     * Check conditions and display notice if appropriate.
     */
    private function checkAndDisplayNotice(): void {
        if ( $this->isDismissed() || ! $this->isSaleActive() ) {
            return;
        }

        add_action( 'admin_notices', $this->renderNotice(...) );
    }

    /**
     * Enqueue dismiss script.
     */
    private function enqueueScripts(): void {
        if ( $this->isDismissed() || ! $this->isValidScreen() ) {
            return;
        }

        wp_enqueue_script(
            'slr-notice-dismiss',
            plugins_url( 'lib/js/admin-notices.js', dirname( __DIR__ ) . '/sky-login-redirect.php' ),
            [ 'jquery' ],
            SLR_VERSION,
            true
        );

        wp_localize_script( 'slr-notice-dismiss', 'slrNoticeParams', [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( self::NONCE_ACTION ),
            'action'  => self::AJAX_ACTION,
        ] );
    }

    /**
     * Handle AJAX dismissal request.
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
     */
    private function renderNotice(): void {
        if ( ! $this->isValidScreen() ) {
            return;
        }

        $upgrade_url = Sky_Login_Redirect_fs()->get_upgrade_url();
        $upgrade_url = add_query_arg( 'coupon', $this->couponCode, $upgrade_url );

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
