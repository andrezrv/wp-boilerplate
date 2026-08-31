<?php
/** Debugging mode */
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', WP_DEBUG );
define( 'WP_DEBUG_DISPLAY', WP_DEBUG );
define( 'WP_DISABLE_FATAL_ERROR_HANDLER', WP_DEBUG );
define( 'SCRIPT_DEBUG', WP_DEBUG );
define( 'SAVEQUERIES', WP_DEBUG );

/** Current environment */
define( 'WP_ENVIRONMENT_TYPE', 'local' );

/** Avoid sending emails when working locally. */
define( 'WP_MAIL_LOCAL', false );
