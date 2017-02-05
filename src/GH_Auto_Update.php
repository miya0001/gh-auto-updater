<?php

namespace Miya\WP;

class GH_Auto_Update
{
	private $gh_user;
	private $gh_repo;
	private $slug;

	public function __construct( $plugin_slug, $gh_user, $gh_repo )
	{
		$this->gh_user     = $gh_user;
		$this->gh_repo     = $gh_repo;
		$this->slug        = $plugin_slug;

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'pre_set_site_transient_update_plugins' ) );
		add_filter( 'plugins_api', array( $this, 'plugins_api' ), 10, 3 );
		add_filter( 'upgrader_source_selection', array( $this, 'upgrader_source_selection' ), 1 );
	}

	public function upgrader_source_selection( $source )
	{
		if(  strpos( $source, $this->gh_repo ) === false ) {
			return $source;
		}

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
		if ( is_wp_error( $remote_version ) ) {
			return $transient;
		}

		$current_version = $this->get_plugin_info();

		return $this->get_newer_version( $transient, $current_version, $remote_version );
	}

	public function plugins_api( $obj, $action, $arg )
	{
		if ( ( 'query_plugins' === $action || 'plugin_information' === $action ) &&
				isset( $arg->slug ) && $arg->slug === $this->slug ) {
			$remote_version = $this->get_api_data();
			if ( is_wp_error( $remote_version ) ) {
				return $obj;
			}
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

	private function get_newer_version( $transient, $current_version, $remote_version )
	{
		if ( version_compare( $current_version['Version'], $remote_version->tag_name, '<' ) ) {
			$obj = new \stdClass();
			$obj->slug = dirname( $this->slug );
			$obj->plugin = $this->slug;
			$obj->new_version = $remote_version->tag_name;
			$obj->url = $remote_version->html_url;
			$obj->package = $this->get_download_url( $remote_version );
			$transient->response[ $this->slug ] = $obj;
		}

		return $transient;
	}

	private function get_download_url( $remote_version )
	{
		if ( ! empty( $remote_version->assets[0] )
				&& ! empty( $remote_version->assets[0]->browser_download_url ) ) {
			$download_url = $remote_version->assets[0]->browser_download_url;
		} else {
			$download_url = sprintf(
				'https://github.com/%s/%s/archive/%s.zip',
				$this->gh_user,
				$this->gh_repo,
				$remote_version->tag_name
			);
		}

		return esc_url( $download_url );
	}

	private function get_plugin_info()
	{
		$plugin = get_plugin_data( WP_PLUGIN_DIR . '/' . $this->plugin_slug );
		return $plugin;
	}

	private function get_api_data()
	{
		$res = wp_remote_get( $this->get_gh_api() );
		if ( 200 !== wp_remote_retrieve_response_code( $res ) ) {
			return new WP_Error( wp_remote_retrieve_body( $res ) );
		}
		$body = wp_remote_retrieve_body( $res );
		return json_decode( $body );
	}

	private function get_gh_api( $endpoint = 'releases' )
	{
		return esc_url_raw( sprintf(
			'https://api.github.com/repos/%1$s/%2$s/%3$s/latest',
			$this->gh_user,
			$this->gh_repo,
			$endpoint
		), 'https' );
	}
}
