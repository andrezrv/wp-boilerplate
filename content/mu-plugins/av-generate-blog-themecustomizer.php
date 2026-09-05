<?php

/**
 * Remove GeneratePress Live Preview Customizer script.
 *
 * Trying to enqueue GeneratePress' Live Preview Customizer script will cause
 * an error. This plugin removes the script from the queue.
 *
 * @link              https://andrezrv.com
 * @since             1.0.0
 * @package           andrezrv\muplugins
 *
 * @wordpress-plugin
 * Plugin Name:       Remove GeneratePress Live Preview Customizer script
 * Description:       Trying to enqueue GeneratePress' Live Preview Customizer script will cause an error. This plugin removes the script from the queue.
 * Version:           1.0.0
 * Author:            Andrés Villarreal
 * Author URI:        https://andrezrv.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       andrezrv-custom-features
 * Domain Path:       /
 */

use function andrezrv\custom_features\make_custom_feature;

\add_action( 'muplugins_loaded', function () {
	/**
	 * Create a custom feature.
	 */
	$feature = make_custom_feature( 'disable_generatepress_customizer_live_preview' );

	/**
	 * Remove the attempt to queue the GeneratePress Live Preview Customizer
	 * script by replacing it with an empty pluggable function.
	 */
	$feature->set_callback( 'generate_blog_customizer_live_preview', function () {
		if ( ! \function_exists( 'generate_blog_customizer_live_preview' ) ) {
			function generate_blog_customizer_live_preview() {}
		}
	} );

	/**
	 * Add the feature to the plugin.
	 */
	add_action( 'muplugins_loaded', $feature->callback( 'generate_blog_customizer_live_preview' ), 20 );
} );


