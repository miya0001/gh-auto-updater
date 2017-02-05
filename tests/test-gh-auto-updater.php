<?php
/**
 * Class SampleTest
 *
 * @package Gh_Auto_Updater_Example
 */

use Miya\WP\GH_Auto_Update;

/**
 * Sample test case.
 */
class GH_Auto_Update_Test extends WP_UnitTestCase
{
	protected $updater;

	/**
	 * get_gh_api()
	 */
	function test_get_gh_api()
	{
		$gh_user = 'miya0001';
		$gh_repo = 'gh-auto-updater-example';
		$updater = new GH_Auto_Update( __FILE__, $gh_user, $gh_repo );

		// For a private function.
		$reflection = new \ReflectionClass( $updater );
		$method = $reflection->getMethod( 'get_gh_api' );
		$method->setAccessible( true );
		$res = $method->invoke( $updater );
		$this->assertSame(
			"https://api.github.com/repos/miya0001/gh-auto-updater-example/releases/latest",
			$res
		);
	}

	/**
	 * get_api_data()
	 */
	function test_get_api_data()
	{
		$gh_user = 'miya0001';
		$gh_repo = 'gh-auto-updater-example';
		$updater = new GH_Auto_Update( __FILE__, $gh_user, $gh_repo );

		// For a private function.
		$reflection = new \ReflectionClass( $updater );
		$method = $reflection->getMethod( 'get_api_data' );
		$method->setAccessible( true );
		$res = $method->invoke( $updater );
		$expect = "https://api.github.com/repos/miya0001/gh-auto-updater-example/releases";
		$this->assertTrue( 0 === strpos( $res->url, $expect ) );
		$this->assertSame( 1, count( $res->assets ) );
	}
}