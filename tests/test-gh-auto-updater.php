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
		$plugin_slug = 'hello/hello.php';
		$updater = new GH_Auto_Update( $plugin_slug, $gh_user, $gh_repo );

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
		$plugin_slug = 'hello/hello.php';
		$updater = new GH_Auto_Update( $plugin_slug, $gh_user, $gh_repo );

		// For a private function.
		$reflection = new \ReflectionClass( $updater );
		$method = $reflection->getMethod( 'get_api_data' );
		$method->setAccessible( true );
		$res = $method->invoke( $updater );
		$expect = "https://api.github.com/repos/miya0001/gh-auto-updater-example/releases";
		$this->assertTrue( 0 === strpos( $res->url, $expect ) );
		$this->assertSame( 1, count( $res->assets ) );
	}

	/**
	 * get_dowload_url()
	 */
	function test_get_dowload_url()
	{
		$gh_user = 'miya0001';
		$gh_repo = 'gh-auto-updater-example';
		$plugin_slug = 'hello/hello.php';
		$updater = new GH_Auto_Update( $plugin_slug, $gh_user, $gh_repo );

		$mock = dirname( __FILE__ ) . '/remote-version.json';
		$remote_version = json_decode( file_get_contents( $mock ) );

		$reflection = new \ReflectionClass( $updater );
		$method = $reflection->getMethod( 'get_download_url' );
		$method->setAccessible( true );
		$res = $method->invoke( $updater, $remote_version );

		$this->assertSame(
			substr( $res, 0 - strlen( 'gh-auto-updater-example.zip' ) ),
			'gh-auto-updater-example.zip'
		);
	}

	/**
	 * get_newer_version()
	 */
	function test_get_newer_version()
	{
		$gh_user = 'miya0001';
		$gh_repo = 'gh-auto-updater-example';
		$plugin_slug = 'hello/hello.php';
		$updater = new GH_Auto_Update( $plugin_slug, $gh_user, $gh_repo );

		$transient = new \stdClass();
		$transient->response = array();

		$mock = dirname( __FILE__ ) . '/remote-version.json';
		$remote_version = json_decode( file_get_contents( $mock ) );

		$reflection = new \ReflectionClass( $updater );
		$method = $reflection->getMethod( 'get_newer_version' );
		$method->setAccessible( true );

		$current_version = array( 'Version' => '0.1.0' );
		$res = $method->invoke( $updater, $transient, $current_version, $remote_version );
		$this->assertTrue( !! count( $res->response ) );

		$current_version = array( 'Version' => '4.1.0' );
		$res = $method->invoke( $updater, $transient, $current_version, $remote_version );
		$this->assertTrue( !! count( $res->response ) );
	}

	/**
	 * get_newer_version()
	 */
	function test_get_newer_version_with_new_version()
	{
		$gh_user = 'miya0001';
		$gh_repo = 'gh-auto-updater-example';
		$plugin_slug = 'hello/hello.php';
		$updater = new GH_Auto_Update( $plugin_slug, $gh_user, $gh_repo );

		$transient = new \stdClass();
		$transient->response = array();

		$mock = dirname( __FILE__ ) . '/remote-version.json';
		$remote_version = json_decode( file_get_contents( $mock ) );

		$reflection = new \ReflectionClass( $updater );
		$method = $reflection->getMethod( 'get_newer_version' );
		$method->setAccessible( true );

		$current_version = array( 'Version' => '4.1.0' );
		$res = $method->invoke( $updater, $transient, $current_version, $remote_version );
		$this->assertTrue( ! count( $res->response ) );
	}

}
