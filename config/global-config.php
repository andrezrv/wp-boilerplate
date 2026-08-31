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
 */

/** The path to the application root. */
define( 'APPLICATION_PATH', realpath( __DIR__ . '/..' ) );

/** Load utility functions */
require_once APPLICATION_PATH . '/utils/functions.php';

/** Load Composer's autoloader. */
your_project\utils\setup_autoload();

/** Load environment variables. */
your_project\utils\load_env();

/** Load configuration by environment. */
your_project\utils\load_env_config();

/** The name of the database for WordPress */
define( 'DB_NAME', $_ENV['DB_NAME'] );

/** Database username */
define( 'DB_USER', $_ENV['DB_USER'] );

/** Database password */
define( 'DB_PASSWORD', $_ENV['DB_PASSWORD'] );

/** Database hostname */
define( 'DB_HOST', $_ENV['DB_HOST'] );

/** Set the directory path. */
define( 'WP_CONTENT_DIR', realpath( __DIR__ . '/../content' ) );

/** Set the URL path dynamically based on the current domain. */
define( 'WP_CONTENT_URL', your_project\utils\get_real_site_url() . '/content' );

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
define( 'AUTH_KEY', $_ENV['AUTH_KEY'] );
define( 'SECURE_AUTH_KEY', $_ENV['SECURE_AUTH_KEY'] );
define( 'LOGGED_IN_KEY', $_ENV['LOGGED_IN_KEY'] );
define( 'NONCE_KEY', $_ENV['NONCE_KEY'] );
define( 'AUTH_SALT', $_ENV['AUTH_SALT'] );
define( 'SECURE_AUTH_SALT', $_ENV['SECURE_AUTH_SALT'] );
define( 'LOGGED_IN_SALT', $_ENV['LOGGED_IN_SALT'] );
define( 'NONCE_SALT', $_ENV['NONCE_SALT'] );
define( 'WP_CACHE_KEY_SALT', $_ENV['WP_CACHE_KEY_SALT'] );

/** WordPress database table prefix. */
$table_prefix = 'wp_';

/** Load all bootstrap files. */
your_project\utils\bootstrap();
