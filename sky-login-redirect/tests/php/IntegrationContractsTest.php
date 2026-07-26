<?php
/**
 * Regression tests for authentication integration contracts.
 *
 * @package Sky_Login_Redirect
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use SkyLoginRedirect\LoginPro\LoginCustomizer;
use SkyLoginRedirect\RedirectManager;

final class IntegrationContractsTest extends TestCase {
	protected function setUp(): void {
		$GLOBALS['slr_test_options']        = [];
		$GLOBALS['slr_test_process_result'] = null;
		$GLOBALS['slr_test_process_args']   = [];
	}

	public function test_woocommerce_registers_current_logout_contract(): void {
		self::assertArrayHasKey( 'woocommerce_logout_default_redirect_url', $GLOBALS['slr_test_filters'] );
		self::assertArrayNotHasKey( 'woocommerce_logout_redirect', $GLOBALS['slr_test_filters'] );
		self::assertArrayHasKey( 'wp_logout', $GLOBALS['slr_test_actions'] );
		self::assertSame( 2, $GLOBALS['slr_test_actions']['wp_logout'][0]['accepted_args'] );
	}

	public function test_woocommerce_logout_passes_departing_user_to_rule_engine(): void {
		$user = new WP_User( 42, [ 'customer' ] );
		$GLOBALS['slr_test_actions']['wp_logout'][0]['callback']( 42, $user );

		$GLOBALS['slr_test_process_result'] = 'https://example.test/member-exit/';
		$result = $GLOBALS['slr_test_filters']['woocommerce_logout_default_redirect_url'][0]['callback'](
			'https://example.test/my-account/'
		);

		self::assertSame( 'https://example.test/member-exit/', $result );
		self::assertSame( $user, $GLOBALS['slr_test_process_args'][2] );
	}

	public function test_edd_login_resolves_user_and_runs_main_rule_engine(): void {
		$GLOBALS['slr_test_process_result'] = 'https://example.test/downloads/';
		$result = $GLOBALS['slr_test_filters']['edd_login_redirect'][0]['callback'](
			'https://example.test/account/',
			17
		);

		self::assertSame( 'https://example.test/downloads/', $result );
		self::assertInstanceOf( WP_User::class, $GLOBALS['slr_test_process_args'][2] );
		self::assertSame( 17, $GLOBALS['slr_test_process_args'][2]->ID );
	}

	public function test_default_session_duration_is_preserved_when_feature_is_disabled(): void {
		$customizer = new LoginCustomizer();
		self::assertSame( 1209600, $customizer->calculateExpiration( 1209600, 1, true ) );
	}

	public function test_custom_session_duration_only_applies_when_enabled(): void {
		$GLOBALS['slr_test_options']['slr_change_user_session'] = 'yes';
		$GLOBALS['slr_test_options']['slr_user_session_time']   = '1_hour';

		$customizer = new LoginCustomizer();
		self::assertSame( 3600, $customizer->calculateExpiration( 1209600, 1, false ) );
		self::assertSame( 7200, $customizer->calculateExpiration( 1209600, 1, true ) );
	}

	public function test_redirect_manager_maps_current_woocommerce_logout_filter(): void {
		$GLOBALS['slr_test_current_filter'] = 'woocommerce_logout_default_redirect_url';
		$method = new ReflectionMethod( RedirectManager::class, 'getCurrentAction' );

		self::assertSame( 'logout', $method->invoke( new RedirectManager() ) );
	}

	public function test_logo_css_does_not_emit_a_bare_important_token(): void {
		$source = file_get_contents( dirname( __DIR__, 2 ) . '/includes/login-customizer.php' );

		self::assertIsString( $source );
		self::assertStringNotContainsString( '{$link_styles}!important}', $source );
	}
}
