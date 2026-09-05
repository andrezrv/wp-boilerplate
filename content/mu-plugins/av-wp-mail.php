<?php
/**
 * Override WP Mail.
 *
 * Avoid sending mails while this feature is enabled.
 *
 * @link              https://andrezrv.com
 * @since             1.0.0
 * @package           andrezrv\muplugins
 *
 * @wordpress-plugin
 * Plugin Name:       Override WP Mail
 * Description:       Avoid sending mails while this feature is enabled.
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
	$feature = make_custom_feature( 'wp_mail_override' );

	/**
	 * Avoid sending mails when working locally.
	 */
	$feature->set_callback( 'muplugins_loaded', function () {
		if ( ! \function_exists( 'wp_mail' ) ) {
			function wp_mail( string $to, string $subject, string $message, string $headers = '' ): bool {
				return false;
			}
		}
	} );

	/**
	 * Add the feature to the plugin.
	 */
	\add_action( 'muplugins_loaded', $feature->get_callback( 'muplugins_loaded' ), 20 );
} );
