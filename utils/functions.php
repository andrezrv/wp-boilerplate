<?php
/**
 * Utility functions.
 *
 * This file contains utility functions meant as helpers to initialize the
 * application and clean up configuration files.
 *
 * @author  Andrés Villarreal <me@andrezrv.com>
 * @package andrezrv\utils
 */

namespace andrezrv\utils;

use Dotenv\Dotenv;

/**
 * Load Composer's autoloader.
 *
 * @return void
 */
function setup_autoload(): void {
	require_once APPLICATION_PATH . '/vendor/autoload.php';
}

/**
 * Find a file in the current directory and its parent directories.
 *
 * @param string $start_dir  The directory to start searching from.
 * @param string $filename   The name of the file to search for.
 * @param int    $max_levels The maximum number of levels to search up.
 *
 * @return bool|string
 */
function find_file( string $start_dir, string $filename, int $max_levels = 1 ): bool|string {
	$current_dir = $start_dir;

	for ( $i = 0; $i <= $max_levels; $i++ ) {
		$filepath = $current_dir . DIRECTORY_SEPARATOR . $filename;

		if ( file_exists( $filepath ) ) {
			return realpath( $filepath );
		}

		// Move one level up.
		$parent_dir = dirname( $current_dir );

		// Stop if we have reached the root directory.
		if ( $parent_dir === $current_dir ) {
			break;
		}

		$current_dir = $parent_dir;
	}

	return false; // File wasn't found within x levels.
}

/**
 * Load environment variables.
 *
 * @return void
 */
function load_env(): void {
	foreach ( [ 'local', 'development', 'qa', 'staging', 'production' ] as $stage ) {
		$env_stage_file = find_file( APPLICATION_PATH, '.env.' . $stage, 3 );

		if ( $env_stage_file ) {
			$dotenv_stage = Dotenv::createImmutable( dirname( $env_stage_file ), '.env.' . $stage );
			$dotenv_stage->load();

			break;
		}
	}

	$env_global_file = find_file( APPLICATION_PATH, '.env', 3 );

	// Load environment variables from global .env file.
	if ( $env_global_file ) {
		$dotenv_global = Dotenv::createImmutable( dirname( $env_global_file ), '.env' );
		$dotenv_global->load();
	}
}

/**
 * Load configuration by environment.
 *
 * @return void
 * @noinspection PhpIncludeInspection
 */
function load_env_config(): void {
	if ( file_exists( APPLICATION_PATH . '/config/local-config.php' ) ) {
		require_once APPLICATION_PATH . '/config/local-config.php';
	} elseif ( file_exists( APPLICATION_PATH . '/config/development-config.php' ) ) {
		require_once APPLICATION_PATH . '/config/development-config.php';
	} elseif ( file_exists( APPLICATION_PATH . '/config/qa-config.php' ) ) {
		require_once APPLICATION_PATH . '/config/qa-config.php';
	} elseif ( file_exists( APPLICATION_PATH . '/config/staging-config.php' ) ) {
		require_once APPLICATION_PATH . '/config/staging-config.php';
	} else {
		require_once APPLICATION_PATH . '/config/production-config.php';
	}
}

/**
 * Obtain the real site URL.
 *
 * @return string
 */
function get_real_site_url(): string {
	$host = $_SERVER['HTTP_HOST'] ?? ''; // phpcs:ignore

	if ( ! $host ) {
		return '';
	}

	// Dynamically detect the current protocol.
	$https         = $_SERVER['HTTPS'] ?? ''; // phpcs:ignore
	$site_protocol = ( 'on' === $https ) ? 'https://' : 'http://';

	return $site_protocol . $host;
}

/**
 * Load the application bootstrap files.
 *
 * @return void
 */
function bootstrap(): void {
	foreach ( glob( APPLICATION_PATH . '/bootstrap/*.php' ) as $filename ) {
		include $filename;
	}
}
