<?php
/**
 * Uninstall ACF OpenStreetMap Field.
 *
 * Removes the plugin's options and the generated map-proxy directory.
 *
 * @package ACFFieldOpenstreetmap
 */

// Exit if accessed directly or not run as an uninstall.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$acf_osm_options = [
	'acf_osm_features',
	'acf_osm_geocoder',
	'acf_osm_provider_tokens',
	'acf_osm_providers',
	'acf_osm_proxy',
];

/**
 * Delete the plugin options and the wp-content/maps/ proxy directory for the
 * current site.
 *
 * @return void
 */
function acf_osm_uninstall_site( $options ) {
	foreach ( $options as $option ) {
		delete_option( $option );
	}

	// Remove the generated proxy directory (wp-content/maps/).
	if ( ! function_exists( 'WP_Filesystem' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}
	if ( WP_Filesystem() ) {
		global $wp_filesystem;
		$proxy_dir = trailingslashit( WP_CONTENT_DIR ) . 'maps';
		if ( $wp_filesystem->is_dir( $proxy_dir ) ) {
			$wp_filesystem->delete( $proxy_dir, true );
		}
	}
}

if ( is_multisite() ) {
	$site_ids = get_sites( [ 'fields' => 'ids', 'number' => 0 ] );
	foreach ( $site_ids as $site_id ) {
		switch_to_blog( $site_id );
		acf_osm_uninstall_site( $acf_osm_options );
		restore_current_blog();
	}
	delete_site_option( 'acf-openstreetmap-field_version' );
} else {
	acf_osm_uninstall_site( $acf_osm_options );
	delete_site_option( 'acf-openstreetmap-field_version' );
}
