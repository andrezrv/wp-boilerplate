<?php
/** Load deployable config file. */
require __DIR__ . '/../config/global-config.php';

/** Absolute path to the WordPress directory. */
if ( ! \defined( 'ABSPATH' ) ) {
	\define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
