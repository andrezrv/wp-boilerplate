<?php
/**
 * Global WordPress configuration file.
 *
 * This file contains the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link https://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * It also defines the application root path, loads the Composer autoloader,
 * environment variables, per-environment configuration (via
 * config/local-config.php, config/production-config.php, etc.), sets up
 * WordPress database settings, and fires up the application bootstrap files.
 *
 * @author  Andrés Villarreal <me@andrezrv.com>
 * @package andrezrv
 */

/** The path to the application root. */
define( 'APPLICATION_PATH', realpath( __DIR__ . '/..' ) );

/** Load utility functions */
require_once APPLICATION_PATH . '/utils/functions.php';

/** Load Composer's autoloader. */
andrezrv\utils\setup_autoload();

/** Load environment variables. */
andrezrv\utils\load_env();

/** Load configuration by environment. */
andrezrv\utils\load_env_config();

/** The name of the database for WordPress */
define( 'DB_NAME', $_ENV['DB_NAME'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

/** Database username */
define( 'DB_USER', $_ENV['DB_USER'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

/** Database password */
define( 'DB_PASSWORD', $_ENV['DB_PASSWORD'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

/** Database hostname */
define( 'DB_HOST', $_ENV['DB_HOST'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

/** Set the directory path. */
define( 'WP_CONTENT_DIR', realpath( __DIR__ . '/../content' ) );

/** Set the URL path dynamically based on the current domain. */
define( 'WP_CONTENT_URL', andrezrv\utils\get_real_site_url() . '/content' );

/** Set the uploads directory. */
define( 'UPLOADS', 'content/uploads' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/** Disable the WordPress cron. */
define( 'DISABLE_WP_CRON', true );

/** Disable file editing. */
define( 'DISALLOW_FILE_EDIT', true );

/** Enables page caching for Cache Enabler. */
define( 'WP_CACHE', boolval( $_ENV['WP_CACHE'] ?? false ) );

/**
 * Authentication unique keys and salts.
 *
 * @link https://api.wordpress.org/secret-key/1.1/salt/
 */
require_once APPLICATION_PATH . '/config/secrets.php';

/** WordPress database table prefix. */
$table_prefix = $_ENV['DB_PREFIX'] ?? 'wp_'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited,WordPress.Security.ValidatedSanitizedInput

/** Load all bootstrap files. */
andrezrv\utils\bootstrap();
