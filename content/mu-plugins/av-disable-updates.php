<?php
/**
 * Disable auto updates for plugins.
 *
 * @link              https://andrezrv.com
 * @since             1.0.0
 * @package           andrezrv\muplugins
 *
 * @wordpress-plugin
 * Plugin Name:       Disable Auto Updates for Plugins
 * Description:       Disables auto updates for all plugins.
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
		$feature = make_custom_feature( 'disable_auto_update_plugin' );

		/**
		 * Set up a callback to disable auto updates for plugins.
		 */
		$feature->set_callback( 'auto_update_plugin', '__return_false' );

		/**
		 * Add the feature to the plugin.
		 */
		add_action( 'auto_update_plugin', $feature->callback( 'auto_update_plugin' ) );
	}
);
