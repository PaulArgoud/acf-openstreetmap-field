<?php
/**
 *	@package ACFFieldOpenstreetmap\WPCLI
 */

namespace ACFFieldOpenstreetmap\WPCLI\Commands;

use ACFFieldOpenstreetmap\Core;
use WP_CLI\Utils;

/**
 * Manage the ACF OpenStreetMap tile proxy.
 */
class MapProxy extends \WP_CLI_Command {

	/**
	 * Install the proxy directory in wp-content/maps/.
	 *
	 * ## OPTIONS
	 *
	 * [--force]
	 * : Overwrite existing proxy files.
	 *
	 * ## EXAMPLES
	 *
	 *     wp acf-osm map-proxy install
	 *     wp acf-osm map-proxy install --force
	 */
	public function install( $args, $assoc_args ) {
		$force  = (bool) Utils\get_flag_value( $assoc_args, 'force', false );
		$proxy  = Core\MapProxy::instance();
		$result = $proxy->setup_proxy_dir( $force );

		if ( is_wp_error( $result ) ) {
			\WP_CLI::error( $result->get_error_message() );
		}

		\WP_CLI::success( sprintf( 'Created proxy directory in %s', $proxy->get_proxy_dir() ) );
	}

	/**
	 * Remove the proxy directory from wp-content/maps/.
	 *
	 * ## OPTIONS
	 *
	 * [--yes]
	 * : Skip the confirmation prompt.
	 *
	 * ## EXAMPLES
	 *
	 *     wp acf-osm map-proxy uninstall
	 *     wp acf-osm map-proxy uninstall --yes
	 */
	public function uninstall( $args, $assoc_args ) {
		$proxy = Core\MapProxy::instance();

		\WP_CLI::confirm(
			sprintf( 'Remove the proxy directory %s?', $proxy->get_proxy_dir() ),
			$assoc_args
		);

		$result = $proxy->reset_proxy_dir();

		if ( is_wp_error( $result ) ) {
			\WP_CLI::error( $result->get_error_message() );
		}

		\WP_CLI::success( 'Removed proxy directory.' );
	}

	/**
	 * Generate the local proxy configuration.
	 *
	 * ## EXAMPLES
	 *
	 *     wp acf-osm map-proxy configure
	 */
	public function configure( $args, $assoc_args ) {
		$proxy      = Core\MapProxy::instance();
		$upload_dir = wp_upload_dir( null, false );

		if ( ! empty( $upload_dir['error'] ) ) {
			\WP_CLI::error( $upload_dir['error'] );
		}

		$result = $proxy->save_proxy_config( $upload_dir['basedir'] );

		if ( is_wp_error( $result ) ) {
			\WP_CLI::error( $result->get_error_message() );
		}

		\WP_CLI::success( sprintf( 'Created proxy config in %s', $upload_dir['basedir'] ) );
	}

	/**
	 * Show the current proxy status.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - json
	 *   - csv
	 *   - yaml
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp acf-osm map-proxy status
	 *     wp acf-osm map-proxy status --format=json
	 */
	public function status( $args, $assoc_args ) {
		$proxy   = Core\MapProxy::instance();
		$proxied = $proxy->get_proxies();
		$format  = Utils\get_flag_value( $assoc_args, 'format', 'table' );

		$rows = [
			[ 'key' => 'installed',         'value' => $proxy->is_installed() ? 'yes' : 'no' ],
			[ 'key' => 'proxy_dir',         'value' => $proxy->get_proxy_dir() ],
			[ 'key' => 'proxied_providers', 'value' => (string) count( $proxied ) ],
		];

		if ( $proxied ) {
			$rows[] = [ 'key' => 'providers', 'value' => implode( ', ', $proxied ) ];
		}

		Utils\format_items( $format, $rows, [ 'key', 'value' ] );
	}
}
