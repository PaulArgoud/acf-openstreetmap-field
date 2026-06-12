<?php

use ACFFieldOpenstreetmap\Helper\MapHelper;

class MapHelperTest extends WP_UnitTestCase {

	/**
	 * @covers ACFFieldOpenstreetmap\Helper\MapHelper::zoomToOffset
	 */
	public function test_zoom_to_offset() {
		// zoom 0 = the whole equator (2·π·R) is visible
		$z0 = MapHelper::zoomToOffset( 0 );
		$this->assertEqualsWithDelta( 2 * M_PI * MapHelper::EARTH_RADIUS, $z0, 1.0 );

		// each zoom level halves the visible offset
		$this->assertEqualsWithDelta( $z0 / 2, MapHelper::zoomToOffset( 1 ), 1.0 );
		$this->assertEqualsWithDelta( $z0 / 4, MapHelper::zoomToOffset( 2 ), 1.0 );
	}

	/**
	 * @covers ACFFieldOpenstreetmap\Helper\MapHelper::getBBox
	 * @covers ACFFieldOpenstreetmap\Helper\MapHelper::getCoordOffset
	 */
	public function test_bbox_brackets_center() {
		list( $min_lon, $min_lat, $max_lon, $max_lat ) = MapHelper::getBBox( 53.5, 10.0, 12 );

		// min is south/west of max
		$this->assertLessThan( $max_lon, $min_lon );
		$this->assertLessThan( $max_lat, $min_lat );

		// the requested center sits in the middle of the box
		$this->assertEqualsWithDelta( 10.0, ( $min_lon + $max_lon ) / 2, 1e-6 );
		$this->assertEqualsWithDelta( 53.5, ( $min_lat + $max_lat ) / 2, 1e-6 );
	}

	/**
	 * A lower zoom (more zoomed out) yields a wider bounding box.
	 *
	 * @covers ACFFieldOpenstreetmap\Helper\MapHelper::getBBox
	 */
	public function test_lower_zoom_is_wider() {
		list( , , $max_lon_z10 ) = MapHelper::getBBox( 0.0, 0.0, 10 );
		list( , , $max_lon_z14 ) = MapHelper::getBBox( 0.0, 0.0, 14 );
		$this->assertGreaterThan( $max_lon_z14, $max_lon_z10 );
	}
}
