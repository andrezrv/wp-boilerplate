<?php
/**
 * Recover Default Theme Directory.
 *
 * For installations with a custom content directory, it registers again the
 * default one, located in <code>wp-content/themes</code>, so you can use the
 * default themes pre-installed in every WordPress release.
 *
 * @link              https://andrezrv.com
 * @since             1.0.0
 * @package           andrezrv\muplugins
 *
 * @wordpress-plugin
 * Plugin Name:       Recover Default Theme Directory
 * Description:       For installations with a custom content directory, it registers again the default one, located in <code>wp-content/themes</code>, so you can use the default themes pre-installed in every WordPress release.
 * Version:           1.0.0
 * Author:            Andrés Villarreal
 * Author URI:        https://andrezrv.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       andrezrv-custom-features
 * Domain Path:       /
 */

use function andrezrv\custom_features\make_custom_feature;

\add_action(
	'muplugins_loaded',
	function () {
		/**
		 * Create a custom feature.
		 */
		$feature = make_custom_feature( 'recover_default_theme_directory' );

		/**
		 * Set up a callback to register the default theme directory.
		 *
		 * @wp-hook setup_theme
		 */
		$feature->set_callback(
			'setup_theme',
			function () {
				\register_theme_directory( ABSPATH . 'wp-content/themes/' );
			}
		);

		/**
		 * Hook callback to the action.
		 */
		\add_action( 'setup_theme', $feature->callback( 'setup_theme' ) );
	}
);
