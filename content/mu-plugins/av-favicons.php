<?php
/**
 * Add Favicons.
 *
 * Add favicons to the site.
 *
 * @link              https://andrezrv.com
 * @since             1.0.0
 * @package           andrezrv\muplugins
 *
 * @wordpress-plugin
 * Plugin Name:       Add Favicons
 * Description:       Adds favicons to the site.
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
	$feature = make_custom_feature( 'add_favicons' );

	/**
	 * Set up a callback to add favicons.
	 *
	 * @wp-hook wp_head
	 * @wp-hook admin_head
	 */
	$feature->set_callback( 'add_favicons', function () {
		?>
		<link rel="icon" href="/favicons/favicon.ico" sizes="any">
		<link rel="icon" href="/favicons/favicon.svg" sizes="any">
		<link rel="icon" type="image/png" sizes="96x96" href="/favicons/favicon-96x96.png">
		<link rel="apple-touch-icon" href="/favicons/apple-touch-icon.png">
		<link rel="manifest" href="/favicons/site.webmanifest">
		<?php
	} );

	/**
	 * Hook callback to the action.
	 */
	\add_action( 'wp_head', $feature->callback( 'add_favicons' ) );
	\add_action( 'admin_head', $feature->callback( 'add_favicons' ) );
} );
