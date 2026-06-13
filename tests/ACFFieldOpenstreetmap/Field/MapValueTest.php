<?php

use ACFFieldOpenstreetmap\Field\MapValue;

class MapValueTest extends WP_UnitTestCase {

	/** The field-type default value template (the key whitelist). */
	private function default_values() {
		return [
			'lat'     => 53.55064,
			'lng'     => 10.00065,
			'zoom'    => 12,
			'layers'  => [ 'OpenStreetMap.Mapnik' ],
			'markers' => [],
			'address' => '',
			'version' => '',
		];
	}

	/** A minimal, already-sanitized field array. */
	private function field( $overrides = [] ) {
		return array_merge( [
			'center_lat'       => 50.0,
			'center_lng'       => 5.0,
			'zoom'             => 10,
			'max_markers'      => '',
			'allow_map_layers' => 1,
			'layers'           => [ 'OpenStreetMap.Mapnik' ],
		], $overrides );
	}

	/**
	 * @covers ACFFieldOpenstreetmap\Field\MapValue::sanitize_layers
	 */
	public function test_sanitize_layers_maps_alias_and_dedupes() {
		$layers = MapValue::sanitize_layers( [ 'OpenStreetMap', '', 'CartoDB.Positron', 'CartoDB.Positron' ] );

		$this->assertSame( [ 'OpenStreetMap.Mapnik', 'CartoDB.Positron' ], $layers );
	}

	/**
	 * @covers ACFFieldOpenstreetmap\Field\MapValue::sanitize_geodata
	 */
	public function test_sanitize_geodata_converts_legacy_center_and_clamps_zoom() {
		$value = MapValue::sanitize_geodata( [
			'center_lat' => '40',
			'center_lng' => '3',
			'zoom'       => '99',
		] );

		$this->assertSame( 40.0, $value['lat'] );
		$this->assertSame( 3.0, $value['lng'] );
		$this->assertSame( 22, $value['zoom'] ); // clamped to max 22
		$this->assertArrayNotHasKey( 'center_lat', $value );
		$this->assertArrayNotHasKey( 'center_lng', $value );
	}

	/**
	 * @covers ACFFieldOpenstreetmap\Field\MapValue::sanitize
	 */
	public function test_sanitize_whitelists_keys_and_typecasts() {
		$value = MapValue::sanitize(
			[ 'lat' => '1', 'lng' => '2', 'evil' => 'drop me' ],
			$this->field(),
			$this->default_values(),
			'1.2.3',
			'display'
		);

		$this->assertArrayNotHasKey( 'evil', $value );        // intersect_key whitelist
		$this->assertArrayNotHasKey( 'center_lat', $value );  // legacy key stripped
		$this->assertSame( 1.0, $value['lat'] );
		$this->assertSame( [], $value['markers'] );
	}

	/**
	 * @covers ACFFieldOpenstreetmap\Field\MapValue::sanitize
	 */
	public function test_sanitize_accepts_json_string() {
		$value = MapValue::sanitize(
			wp_json_encode( [ 'lat' => 7, 'lng' => 8 ] ),
			$this->field(),
			$this->default_values(),
			'1.2.3',
			'display'
		);

		$this->assertSame( 7.0, $value['lat'] );
		$this->assertSame( 8.0, $value['lng'] );
	}

	/**
	 * @covers ACFFieldOpenstreetmap\Field\MapValue::sanitize
	 */
	public function test_sanitize_update_stamps_version_and_address() {
		$value = MapValue::sanitize(
			[ 'markers' => [ [ 'label' => 'Town Hall', 'lat' => 1, 'lng' => 2 ] ] ],
			$this->field(),
			$this->default_values(),
			'9.9.9',
			'update'
		);

		$this->assertSame( '9.9.9', $value['version'] );
		$this->assertSame( 'Town Hall', $value['address'] );
	}

	/**
	 * @covers ACFFieldOpenstreetmap\Field\MapValue::sanitize
	 */
	public function test_sanitize_display_creates_marker_from_gm_address() {
		$value = MapValue::sanitize(
			[ 'address' => 'Somewhere', 'lat' => 1, 'lng' => 2 ],
			$this->field(),
			$this->default_values(),
			'1.2.3',
			'display'
		);

		$this->assertCount( 1, $value['markers'] );
		$this->assertSame( 'Somewhere', $value['markers'][0]['label'] );
		$this->assertSame( 1.0, $value['markers'][0]['lat'] );
	}

	/**
	 * @covers ACFFieldOpenstreetmap\Field\MapValue::sanitize
	 */
	public function test_sanitize_layers_fallback_when_selection_disabled() {
		$value = MapValue::sanitize(
			[ 'layers' => [ 'CartoDB.Positron' ] ],
			$this->field( [ 'allow_map_layers' => 0 ] ),
			$this->default_values(),
			'1.2.3',
			'display'
		);

		// selection disabled => the field's own layers win
		$this->assertSame( [ 'OpenStreetMap.Mapnik' ], $value['layers'] );
	}
}
