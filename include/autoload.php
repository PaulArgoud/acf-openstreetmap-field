<?php

namespace ACFFieldOpenstreetmap;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


function __autoload( $class ) {

	// Bail fast for classes outside our namespace. This is a pure in-memory
	// string check, so the autoloader no longer hits the filesystem for every
	// foreign class on the SPL stack (ElasticPress, MailPoet, Cloudflare, …).
	// Previously an is_dir() stat ran for each such class, and because PHP's
	// stat cache only keeps the most recent path, the same missing directory
	// got stat-ed again and again within a single request.
	$prefix = __NAMESPACE__ . '\\'; // 'ACFFieldOpenstreetmap\'

	if ( ! str_starts_with( $class, $prefix ) ) {
		return;
	}

	$file = __DIR__ . DIRECTORY_SEPARATOR . str_replace( '\\', DIRECTORY_SEPARATOR, $class ) . '.php';

	// Only require when the file exists. Returning quietly (instead of throwing)
	// keeps class_exists()/interface checks working: a missing class resolves to
	// false rather than blowing up the request.
	if ( file_exists( $file ) ) {
		require_once $file;
	}
}


spl_autoload_register( 'ACFFieldOpenstreetmap\__autoload' );
