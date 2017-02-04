<?php

namespace Miya\WP;

class GH_Auto_Update
{
	private $gh_user;
	private $gh_repo;
	private $plugin_file;
	private $slug;

	public function __construct( $gh_user, $gh_repo, $plugin_file )
	{
		$this->gh_user     = $gh_user;
		$this->gh_repo     = $gh_repo;
		$this->plugin_file = $plugin_file;
		$this->slug        = plugin_basename( $plugin_file );

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'pre_set_site_transient_update_plugins' ) );
		add_filter( 'plugins_api', array( $this, 'plugins_api' ), 10, 3 );
		add_filter( 'upgrader_source_selection', array( $this, 'upgrader_source_selection' ), 1 );
	}

	public function upgrader_source_selection( $source )
	{
		if(  strpos( $source, $this->gh_repo ) === false )
			return $source;

		$path_parts = pathinfo( $source );
		$newsource = trailingslashit( $path_parts['dirname'] ) . trailingslashit( $this->gh_repo );
		rename( $source, $newsource );
		return $newsource;
	}

	public function pre_set_site_transient_update_plugins( $transient )
	{
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$remote_version = $this->get_api_data();
		$current_version = $this->get_plugin_info();

		if ( version_compare( $current_version['Version'], $remote_version->tag_name, '<' ) ) {
			$obj = new \stdClass();
			$obj->slug = $this->slug;
			$obj->new_version = $remote_version->tag_name;
			$obj->url = $remote_version->html_url;
			$obj->plugin = $this->slug;
			$obj->package = $this->get_download_url( $remote_version );
			$transient->response[ $this->slug ] = $obj;
		}
		return $transient;
	}

	public function plugins_api( $obj, $action, $arg )
	{
		if ( ( 'query_plugins' === $action || 'plugin_information' === $action ) &&
				isset( $arg->slug ) && $arg->slug === $this->slug ) {
			$remote_version = $this->get_api_data();
			$current_version = $this->get_plugin_info();

			$obj = new \stdClass();
			$obj->slug = $this->slug;
			$obj->name = $current_version['Name'];
			$obj->new_version = $remote_version->tag_name;
			$obj->last_updated = $remote_version->published_at;
			$obj->sections = array(
				'changelog' => $remote_version->body
			);
			$obj->download_link = $this->get_download_url( $remote_version );
			return $obj;
		}

		return $obj;
	}

	private function get_download_url( $remote_version )
	{
		return $remote_version->assets[0]->browser_download_url;
	}

	private function get_plugin_info()
	{
		$plugin = get_plugin_data( $this->plugin_file );
		return $plugin;
	}

	private function get_api_data()
	{
		$res = wp_remote_get( $this->get_gh_api() );
		$body = wp_remote_retrieve_body( $res );
		return json_decode( $body );
	}

	private function get_gh_api()
	{
		return sprintf(
			'https://api.github.com/repos/%s/%s/releases/latest',
			$this->gh_user,
			$this->gh_repo
		);
	}
}
