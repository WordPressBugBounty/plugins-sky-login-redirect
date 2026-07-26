<?php
/**
 * Minimal WordPress/WooCommerce test doubles for isolated unit tests.
 *
 * @package Sky_Login_Redirect
 */

declare(strict_types=1);

namespace {
	define( 'ABSPATH', __DIR__ . '/' );
	define( 'HOUR_IN_SECONDS', 3600 );
	define( 'DAY_IN_SECONDS', 86400 );
	define( 'WEEK_IN_SECONDS', 604800 );
	define( 'MONTH_IN_SECONDS', 2592000 );
	define( 'YEAR_IN_SECONDS', 31536000 );

	final class WP_User {
		/**
		 * @param int      $ID    User ID.
		 * @param string[] $roles User roles.
		 */
		public function __construct(
			public int $ID = 0,
			public array $roles = []
		) {}
	}

	$GLOBALS['slr_test_filters']        = [];
	$GLOBALS['slr_test_actions']        = [];
	$GLOBALS['slr_test_options']        = [];
	$GLOBALS['slr_test_current_filter'] = '';
	$GLOBALS['slr_test_process_result'] = null;
	$GLOBALS['slr_test_process_args']   = [];

	function add_filter( string $hook, callable $callback, int $priority = 10, int $accepted_args = 1 ): bool {
		$GLOBALS['slr_test_filters'][ $hook ][] = compact( 'callback', 'priority', 'accepted_args' );
		return true;
	}

	function add_action( string $hook, callable $callback, int $priority = 10, int $accepted_args = 1 ): bool {
		$GLOBALS['slr_test_actions'][ $hook ][] = compact( 'callback', 'priority', 'accepted_args' );
		return true;
	}

	function current_filter(): string {
		return (string) $GLOBALS['slr_test_current_filter'];
	}

	function home_url( string $path = '' ): string {
		return 'https://example.test' . $path;
	}

	function wp_validate_redirect( mixed $url, string $fallback = '' ): string {
		$url = is_string( $url ) ? $url : '';
		return str_starts_with( $url, 'https://example.test/' ) ? $url : $fallback;
	}

	function get_userdata( int $user_id ): WP_User|false {
		return $user_id > 0 ? new WP_User( $user_id, [ 'subscriber' ] ) : false;
	}

	function get_permalink( int $post_id ): string|false {
		return $post_id > 0 ? "https://example.test/page/{$post_id}/" : false;
	}

	function wc_get_page_id( string $page ): int {
		return [
			'myaccount' => 10,
			'shop'      => 20,
			'cart'      => 30,
			'checkout'  => 40,
		][ $page ] ?? 0;
	}

	function is_woocommerce(): bool {
		return true;
	}

	function url_to_postid( string $url ): int {
		return 0;
	}

	function wp_cache_get(): false {
		return false;
	}

	function wp_cache_set(): bool {
		return true;
	}

	function get_option( string $key, mixed $default = false ): mixed {
		return $default;
	}

	function plugins_url( string $path = '', string $plugin = '' ): string {
		return 'https://example.test/plugins/' . ltrim( $path, '/' );
	}

	function wp_enqueue_style(): void {}
	function wp_add_inline_style(): void {}
	function wp_strip_all_tags( string $text ): string {
		return strip_tags( $text );
	}

	require_once dirname( __DIR__ ) . '/includes/class-css-builder.php';
}

namespace SkyLoginRedirect {
	function carbonade( string $key, mixed $default = false ): mixed {
		return $GLOBALS['slr_test_options'][ $key ] ?? $default;
	}

	function process_redirection( mixed $redirect_to, mixed $requested_redirect_to, mixed $user ): string {
		$GLOBALS['slr_test_process_args'] = [ $redirect_to, $requested_redirect_to, $user ];
		return is_string( $GLOBALS['slr_test_process_result'] )
			? $GLOBALS['slr_test_process_result']
			: (string) $redirect_to;
	}
}

namespace {
	require_once dirname( __DIR__ ) . '/premium/woocommerce.php';
	require_once dirname( __DIR__ ) . '/premium/edd.php';
	require_once dirname( __DIR__ ) . '/premium/pro-code.php';
	require_once dirname( __DIR__ ) . '/includes/class-redirect-manager.php';
}
