<?php

use ACFFieldOpenstreetmap\Core;

class OSMProvidersTest extends WP_UnitTestCase {

	/**
	 * @covers ACFFieldOpenstreetmap\Core\OSMProviders::get_iframe_url
	 */
	public function test_iframe_url_basic() {
		$url = Core\OSMProviders::instance()->get_iframe_url( [ 'lat' => 45, 'lng' => 5, 'zoom' => 12 ] );
		$this->assertStringContainsString( 'openstreetmap.org/export/embed.html', $url );
		$this->assertStringContainsString( 'bbox=', $url );
	}

	/**
	 * @covers ACFFieldOpenstreetmap\Core\OSMProviders::get_iframe_url
	 */
	public function test_iframe_url_with_marker() {
		$url = Core\OSMProviders::instance()->get_iframe_url( [
			'lat' => 45, 'lng' => 5, 'zoom' => 12,
			'markers' => [ [ 'lat' => 45, 'lng' => 5 ] ],
		] );
		$this->assertStringContainsString( 'marker=', $url );
	}

	/**
	 * @covers ACFFieldOpenstreetmap\Core\OSMProviders::get_link_url
	 */
	public function test_link_url_contains_map_fragment() {
		$url = Core\OSMProviders::instance()->get_link_url( [ 'lat' => 45.5, 'lng' => 5.25, 'zoom' => 12 ] );
		$this->assertStringContainsString( 'openstreetmap.org', $url );
		$this->assertStringContainsString( '#map=12/45.5/5.25', $url );
	}
}
