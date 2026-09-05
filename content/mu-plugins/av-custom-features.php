<?php
/**
 * Plugin Name: Custom Features API
 * Description: A PHP API for managing custom features in WordPress.
 * Version: 1.0.0
 * Author: Andrés Villarreal
 * Author URI: https://andrezrv.com/
 * License: GPL2+
 *
 * @package andrezrv\muplugins
 */

( function () {
	$files = [];

	foreach ( \glob( __DIR__ . '/av-custom-features/src/*.php' ) as $filename ) {
		$files[] = $filename;
		unset( $filename ); // Avoid reference pollution.
	}

	foreach ( \glob( __DIR__ . '/av-custom-features/app/*.php' ) as $filename ) {
		$files[] = $filename;
		unset( $filename ); // Avoid reference pollution.
	}

	foreach ( $files as $file ) {
		include $file;
		unset( $file );
	}
} )();
