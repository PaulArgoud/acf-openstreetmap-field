<?php
/**
 *	@package ACFFieldOpenstreetmap\WPCLI
 */

namespace ACFFieldOpenstreetmap\WPCLI;

use ACFFieldOpenstreetmap\Core;

class WPCLI extends Core\Singleton {

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {
		// Registering the command class once lets WP-CLI auto-discover the
		// subcommands (install / uninstall / configure / status) and their help
		// from the method doc-blocks. Namespaced under `acf-osm` to avoid clashes.
		\WP_CLI::add_command( 'acf-osm map-proxy', Commands\MapProxy::class );
	}
}
