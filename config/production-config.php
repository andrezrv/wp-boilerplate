<?php
/**
 * Production environment configuration.
 *
 * @package andrezrv
 */

/** Disable error reporting. */
ini_set( 'display_errors', 0 ); // phpcs:ignore WordPress.PHP.IniSet.display_errors_Disallowed

/** Debugging mode */
define( 'WP_DEBUG', false );

/** Current environment */
define( 'WP_ENVIRONMENT_TYPE', 'production' );
