<?php

class AutoloadTest extends WP_UnitTestCase {

	/**
	 * Classes from other plugins' namespaces must be ignored by our autoloader
	 * (string check, no filesystem stat, no exception). See the autoloader perf fix.
	 *
	 * @covers ACFFieldOpenstreetmap\__autoload
	 */
	public function test_foreign_namespace_is_ignored() {
		$this->assertFalse( class_exists( 'ElasticPress\\Elasticsearch', true ) );
		$this->assertFalse( class_exists( 'MailPoet\\DI\\ContainerWrapper', true ) );
		$this->assertFalse( class_exists( 'Cloudflare\\Whatever', true ) );
	}

	/**
	 * Our own classes still autoload from include/ACFFieldOpenstreetmap/.
	 *
	 * @covers ACFFieldOpenstreetmap\__autoload
	 */
	public function test_own_namespace_loads() {
		$this->assertTrue( class_exists( 'ACFFieldOpenstreetmap\\Helper\\MapHelper', true ) );
	}

	/**
	 * A missing class in our own namespace must resolve to false, not throw.
	 *
	 * @covers ACFFieldOpenstreetmap\__autoload
	 */
	public function test_missing_own_class_does_not_throw() {
		$this->assertFalse( class_exists( 'ACFFieldOpenstreetmap\\Definitely\\Missing', true ) );
	}
}
