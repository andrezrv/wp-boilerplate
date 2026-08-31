<?php
/**
 * Plugin Name: AV Latest Release Info
 * Description: Displays the latest release timestamp in the admin footer.
 */

/**
 * Latest Release Info.
 *
 * Displays the latest release timestamp in the admin footer.
 *
 * @link              https://andrezrv.com
 * @since             1.0.0
 * @package           andrezrv\muplugins
 *
 * @wordpress-plugin
 * Plugin Name:       AV Latest Release Info
 * Description:       Displays the latest release timestamp in the admin footer.
 * Version:           1.0.0
 * Author:            Andrés Villarreal
 * Author URI:        https://andrezrv.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       av-latest-release-info
 * Domain Path:       /
 */

use function andrezrv\custom_features\make_custom_feature;

\add_action( 'muplugins_loaded', function () {
	/**
	 * Create a custom feature.
	 */
	$feature = make_custom_feature( 'av_display_release_timestamp' );

	$feature->set_callback( 'admin_footer_text', function ( string $text ) {
		$timestamp = get_option( 'av_latest_release_timestamp' );

		if ( $timestamp ) {
			// wp_date(), not date() — respects the site's configured timezone
			// rather than the server's raw system time.
			$formatted    = wp_date( 'Y-m-d H:i:s', (int) $timestamp );
			$release_text = 'Latest release: ' . esc_html( $formatted ) . ' (' . $timestamp . ')';
			$text         = ' &nbsp; <span id="footer-release"><em>' . $release_text . '</em></span>';
		}

		return $text;
	} );

	/**
	 * Add the feature to the plugin.
	 */
	add_filter( 'admin_footer_text', $feature->callback( 'admin_footer_text' ) );
} );
