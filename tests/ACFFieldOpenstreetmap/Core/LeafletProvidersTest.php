<?php

use ACFFieldOpenstreetmap\Core;

class LeafletProvidersTest extends WP_UnitTestCase {

	/**
	 * Legacy credentials stored for a provider that no longer exists in the
	 * catalogue (e.g. the old `HERE` app_id/app_key) must not be injected as a
	 * malformed provider. Regression test for #133.
	 *
	 * @covers ACFFieldOpenstreetmap\Core\LeafletProviders::get_providers
	 */
	public function test_legacy_token_does_not_leak() {
		update_option( 'acf_osm_provider_tokens', [
			'HERE' => [ 'app_id' => 'x', 'app_key' => 'y' ], // removed provider
		] );

		$providers = Core\LeafletProviders::instance()->get_providers( [ 'credentials' ] );

		$this->assertArrayNotHasKey( 'HERE', $providers, 'Legacy HERE token must not be injected as a provider (#133)' );
		$this->assertArrayHasKey( 'OpenStreetMap', $providers, 'Real providers must still be present' );

		// every returned provider must be a well-formed array (has options)
		foreach ( $providers as $key => $provider ) {
			$this->assertIsArray( $provider, "Provider {$key} should be an array" );
			$this->assertArrayHasKey( 'options', $provider, "Provider {$key} should expose options" );
		}

		delete_option( 'acf_osm_provider_tokens' );
	}

	/**
	 * Tokens for an existing provider are merged in.
	 *
	 * @covers ACFFieldOpenstreetmap\Core\LeafletProviders::get_providers
	 */
	public function test_known_provider_token_is_merged() {
		update_option( 'acf_osm_provider_tokens', [
			'Thunderforest' => [ 'options' => [ 'apikey' => 'abc123' ] ],
		] );

		$providers = Core\LeafletProviders::instance()->get_providers( [ 'credentials' ] );

		$this->assertArrayHasKey( 'Thunderforest', $providers );
		$this->assertSame( 'abc123', $providers['Thunderforest']['options']['apikey'] );

		delete_option( 'acf_osm_provider_tokens' );
	}
}
