<?php
/**
 * Regression tests for the WordPress.org release manifest.
 *
 * @package Sky_Login_Redirect
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ReleasePackagingTest extends TestCase {
	/**
	 * @return string[]
	 */
	private function exclusions( string $file ): array {
		$lines = file( dirname( __DIR__, 2 ) . '/' . $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
		self::assertIsArray( $lines );
		return array_map( static fn( string $line ): string => ltrim( trim( $line ), '/' ), $lines );
	}

	public function test_distribution_archive_excludes_development_assets(): void {
		$exclusions = $this->exclusions( '.distignore' );

		foreach ( [ 'dev', 'tests', '.git', '.DS_Store', 'composer.json', 'phpunit.xml.dist' ] as $path ) {
			self::assertContains( $path, $exclusions );
		}
	}

	public function test_svn_release_excludes_development_assets(): void {
		$exclusions = $this->exclusions( 'svn-ignore' );

		foreach ( [ 'dev', 'tests', '.git', '.DS_Store', 'composer.json', 'phpunit.xml.dist' ] as $path ) {
			self::assertContains( $path, $exclusions );
		}
	}

	public function test_svn_helper_applies_the_tracked_ignore_file(): void {
		$script = file_get_contents( dirname( __DIR__, 2 ) . '/svn-clean-and-install.sh' );

		self::assertIsString( $script );
		self::assertStringContainsString( 'svn propset svn:ignore -F svn-ignore .', $script );
		self::assertStringContainsString( 'composer install --no-dev', $script );
	}

	public function test_release_builder_uses_production_dependencies_and_dist_archive(): void {
		$script = file_get_contents( dirname( __DIR__, 2 ) . '/build-release.sh' );

		self::assertIsString( $script );
		self::assertStringContainsString( '--no-dev', $script );
		self::assertStringContainsString( 'wp dist-archive', $script );
	}
}
