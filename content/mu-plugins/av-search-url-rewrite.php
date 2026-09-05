<?php
/**
 * Rewrite search URLs.
 *
 * Modify the search results page to use a cleaner URL.
 *
 * @link              https://andrezrv.com
 * @since             1.0.0
 * @package           andrezrv\muplugins
 *
 * @wordpress-plugin
 * Plugin Name:       Rewrite Search URLs
 * Description:       Modify the search results page to use a cleaner URL.
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
	$feature = make_custom_feature( 'search_url_rewrite' );

	/**
	 * Set up a callback to redirect search queries to a clean URL.
	 */
	$feature->set_callback( 'template_redirect', function (): void {
		if ( ! \is_search() || empty( $_GET['s'] ) ) {
			return;
		}

		\wp_redirect( \home_url( '/search/' ) . \urlencode( \get_query_var( 's' ) ) );
		exit();
	} );

	/**
	 * Hook callback to the action.
	 */
	\add_action( 'template_redirect', $feature->callback( 'template_redirect' ) );
} );
