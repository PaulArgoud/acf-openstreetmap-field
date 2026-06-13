<?php

/**
 * Tests for the value layer of the OpenStreetMap field
 * (sanitize / format / update). This is where silent regressions hurt.
 */
class OpenStreetMapTest extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		if ( ! function_exists( 'acf_get_field_type' ) || ! acf_get_field_type( 'open_street_map' ) ) {
			$this->markTestSkipped( 'ACF / Secure Custom Fields not loaded.' );
		}
	}

	private function type() {
		return acf_get_field_type( 'open_street_map' );
	}

	private function field( $args = [] ) {
		return wp_parse_args( $args, [ 'return_format' => 'raw' ] );
	}

	/**
	 * @covers ACFFieldOpenstreetmap\Field\OpenStreetMap::format_value
	 */
	public function test_empty_value_returns_empty() {
		$this->assertEmpty( $this->type()->format_value( null, 0, $this->field() ) );
		$this->assertEmpty( $this->type()->format_value( '', 0, $this->field() ) );
	}

	/**
	 * @covers ACFFieldOpenstreetmap\Field\OpenStreetMap::format_value
	 */
	public function test_raw_format_typecasts_and_adds_bc_keys() {
		$value = [ 'lat' => '45.5', 'lng' => '5.25', 'zoom' => '13', 'layers' => [ 'OpenStreetMap.Mapnik' ], 'markers' => [] ];
		$out   = $this->type()->format_value( $value, 0, $this->field() );

		$this->assertIsArray( $out );
		$this->assertSame( 45.5, $out['lat'] );           // cast to float
		$this->assertSame( 5.25, $out['lng'] );
		$this->assertSame( $out['lat'], $out['center_lat'] ); // <= 1.0.1 backward-compat keys
		$this->assertSame( $out['lng'], $out['center_lng'] );
		$this->assertArrayHasKey( 'markers', $out );
		$this->assertArrayHasKey( 'layers', $out );
	}

	/**
	 * @covers ACFFieldOpenstreetmap\Field\OpenStreetMap::format_value
	 */
	public function test_bare_openstreetmap_layer_is_normalized() {
		$value = [ 'lat' => 1, 'lng' => 2, 'zoom' => 5, 'layers' => [ 'OpenStreetMap' ], 'markers' => [] ];
		$out   = $this->type()->format_value( $value, 0, $this->field( [ 'allow_map_layers' => 1 ] ) );

		$this->assertContains( 'OpenStreetMap.Mapnik', $out['layers'] );
		$this->assertNotContains( 'OpenStreetMap', $out['layers'] );
	}

	/**
	 * @covers ACFFieldOpenstreetmap\Field\OpenStreetMap::format_value
	 */
	public function test_marker_coordinates_cast_to_float() {
		$value = [
			'lat' => 1, 'lng' => 2, 'zoom' => 5, 'layers' => [ 'OpenStreetMap.Mapnik' ],
			'markers' => [ [ 'lat' => '48.85', 'lng' => '2.35', 'label' => 'Paris', 'default_label' => '' ] ],
		];
		$out = $this->type()->format_value( $value, 0, $this->field() );

		$this->assertSame( 48.85, $out['markers'][0]['lat'] );
		$this->assertSame( 2.35, $out['markers'][0]['lng'] );
		$this->assertSame( 'Paris', $out['markers'][0]['label'] );
	}

	/**
	 * @covers ACFFieldOpenstreetmap\Field\OpenStreetMap::update_value
	 */
	public function test_update_value_decodes_json_and_stamps_version() {
		$json = wp_json_encode( [ 'lat' => 1.5, 'lng' => 2.5, 'zoom' => 8, 'layers' => [ 'OpenStreetMap.Mapnik' ], 'markers' => [] ] );
		$out  = $this->type()->update_value( $json, 0, $this->field() );

		$this->assertIsArray( $out );
		$this->assertSame( 1.5, $out['lat'] );
		$this->assertNotEmpty( $out['version'], 'update_value should stamp the plugin version' );
	}
}
