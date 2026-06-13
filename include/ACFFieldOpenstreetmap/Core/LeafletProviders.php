<?php

namespace ACFFieldOpenstreetmap\Core;

use ACFFieldOpenstreetmap\Helper;

class LeafletProviders extends Singleton {

	/** @var array */
	private $leaflet_providers = null;

	/** @var array Memoized results of get_providers(), keyed by filter signature. */
	private $providers_cache = [];

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {
		// Invalidate the memoized provider list whenever the relevant settings change.
		foreach ( [ 'acf_osm_provider_tokens', 'acf_osm_providers', 'acf_osm_proxy' ] as $option ) {
			add_action( "add_option_{$option}", [ $this, 'flush_cache' ] );
			add_action( "update_option_{$option}", [ $this, 'flush_cache' ] );
			add_action( "delete_option_{$option}", [ $this, 'flush_cache' ] );
		}
	}

	/**
	 *	Reset the memoized provider list.
	 */
	public function flush_cache() {
		$this->providers_cache = [];
	}

	/**
	 *	Whether a provider option value is an unfilled access-token placeholder
	 *	such as `<insert your api key here>`.
	 *
	 *	@param mixed $value
	 *	@return boolean
	 */
	public static function is_token_placeholder( $value ) {
		return is_string( $value ) && 1 === preg_match( '/^<([^>]*)>$/imsU', $value );
	}


	/**
	 *	Returns raw leaflet providers
	 *	@param array $filters credentials|proxied|enabled
	 *	@param boolean $unfiltered Whether to apply filters
	 *	@return array
	 */
	public function get_providers( $filters = [], $unfiltered = false ) {

		$cache_key = ( $unfiltered ? '1' : '0' ) . '|' . implode( ',', (array) $filters );
		if ( isset( $this->providers_cache[ $cache_key ] ) ) {
			return $this->providers_cache[ $cache_key ];
		}

		if ( is_null( $this->leaflet_providers ) ) {
			$core = Core::instance();
			$this->leaflet_providers = json_decode( $core->read_file( 'etc/leaflet-providers.json' ), true );
		}

		$providers = $this->leaflet_providers;

		foreach ( (array) $filters as $filter ) {
			if ( 'credentials' === $filter ) {

				// get configured token
				$tokens = get_option( 'acf_osm_provider_tokens', [] );

				// Only merge tokens for providers that still exist in the catalog.
				// Legacy configs may hold entries for removed providers (e.g. a bare
				// `HERE` app_id/app_key) which would otherwise be injected as malformed
				// providers (missing `options`/`url`) and break map rendering. See #133.
				$tokens = array_intersect_key( $tokens, $providers );

				// merge tokens
				$providers = array_replace_recursive( $providers, $tokens );

				// remove providers without access tokens ($tokens passed so
				// has_access_token() does not re-read the option for every provider)
				$providers = array_filter( $providers, function( $provider, $provider_key ) use ( $tokens ) {
					return ! $this->needs_access_token( $provider_key, $provider )
						|| $this->has_access_token( $provider_key, $provider, $tokens );
				}, ARRAY_FILTER_USE_BOTH );

				if ( ! $unfiltered ) {
					$providers = apply_filters( 'acf_osm_leaflet_providers_'.$filter, $providers );
				}
			}

			if ( 'enabled' === $filter ) {

				// remove disabled providers
				$disabled_providers = get_option( 'acf_osm_providers', [] );

				$providers = array_replace_recursive( $providers, $disabled_providers );

				$providers = array_filter( $providers, function( $el ) {
					return is_array( $el );
				} );

				foreach ( $providers as &$provider ) {
					if ( isset( $provider['variants'] ) ) {
						// remove disabled variants
						$provider['variants'] = array_filter( $provider['variants'], function( $el ) {
							return ! in_array( $el, [ '0', false ], true );
						} );
						// remove empty variants
						if ( ! count( $provider['variants'] ) ) {
							unset( $provider['variants'] );
						}
					}
				}
			}
		}

		if ( ! $unfiltered ) {
			$providers = apply_filters( 'acf_osm_leaflet_providers', $providers );
		}

		$this->providers_cache[ $cache_key ] = $providers;

		return $providers;
	}

	/**
	 *	Get token configuration options
	 *
	 *	@return array
	 */
	public function get_token_options() {

		$token_options = [];

		foreach ( $this->get_providers() as $provider => $data ) {
			foreach( $data['options'] ?? [] as $option => $value ) {
				if ( self::is_token_placeholder( $value ) ) { // '<insert your [some token] here>'

					if ( ! isset($token_options[ $provider ]['options'] ) ) {
						$token_options[ $provider ] = [ 'options' => [] ];
					}
					$token_options[ $provider ]['options'][ $option ] = '';
				}
			}
		}

		return $token_options;
	}

	/**
	 *	Get a flat leaflet provider list
	 *
	 *	@return array [
	 *		'provider_key' 			=> 'provider',
	 *		'provider_key.variant'	=> 'provider.variant',
	 *		...
	 * ]
	 */
	public function get_layers() {

		$providers = [];

		foreach ( $this->get_providers([ 'credentials', 'enabled' ]) as $provider_key => $provider_data ) {
			//
			if ( isset( $provider_data[ 'variants' ] ) ) {
				foreach ( $provider_data[ 'variants' ] as $variant => $variant_data ) {
					$providers[ $provider_key . '.' . $variant ] = $provider_key . '.' . $variant;
				}
			} else {
				$providers[ $provider_key ] = $provider_key;
			}
		}

		return $providers;
	}

	public function get_layer_config() {
		return Helper\ArrayHelper::filter_recursive( get_option( 'acf_osm_provider_tokens', [] ) );
	}

	/**
	 *	Convert string variant definitions to object
	 */
	public function unify_provider_variants( $provider ) {
		if ( isset( $provider['variants'] ) ) {
			$provider['variants'] = array_map( function( $variant ) {
				if ( is_string( $variant ) ) {
					$variant = [
						'options' => [
							'variant' => $variant,
						]
					];
				}
				if ( ! isset( $variant['options'] ) ) {
					$variant['options'] = [];
				}
				return $variant;
			}, $provider['variants'] );
		}
		return $provider;
	}


	/**
	 *	Whether an access key needs to be entered to make this provider work.
	 *
	 *	@param string $provider_key
	 *	@param Array $provider_data
	 *	@return boolean Whether this map provider requires an access key and the access key is not configured yet
	 */
	public function needs_access_token( $provider_key, $provider_data ) {
		foreach ( $provider_data['options'] ?? [] as $option => $value ) {
			if ( self::is_token_placeholder( $value ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 *	Whether an access key needs to be entered to make this provider work.
	 *
	 *	@param string $provider_key
	 *	@param Array $provider_data
	 *	@return boolean Whether this map provider requires an access key and the access key is not configured yet
	 */
	public function has_access_token( $provider_key, $provider_data, $token_option = null ) {
		if ( null === $token_option ) {
			$token_option = get_option( 'acf_osm_provider_tokens' );
		}
		foreach ( $provider_data['options'] ?? [] as $option => $value ) {
			if ( self::is_token_placeholder( $value ) ) {
				return isset( $token_option[ $provider_key ][ 'options' ][ $option ] )
					&& ! empty( $token_option[ $provider_key ][ 'options' ][ $option ] );
			}
		}
		return false;
	}

}
