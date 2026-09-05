<?php
use function andrezrv\custom_features\make_custom_feature;

/**
 * Remove Query Args From Assets.
 *
 * Remove query arguments from asset URLs.
 *
 * @link              https://andrezrv.com
 * @since             1.0.0
 * @package           andrezrv\muplugins
 *
 * @wordpress-plugin
 * Plugin Name:       Remove Query Args From Assets
 * Description:       Remove query arguments from asset URLs.
 * Version:           1.0.0
 * Author:            Andrés Villarreal
 * Author URI:        https://andrezrv.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       andrezrv-custom-features
 * Domain Path:       /
 */
\add_action( 'muplugins_loaded', function () {
	/**
	 * Create a custom feature.
	 */
	$feature = make_custom_feature( 'remove_query_args_from_assets' );

	/**
	 * Remove query args from asset URLs.
	 */
	$feature->set_callback( 'asset_loader_src', function ( $src ) {
		if ( \str_contains( $src, 'ver=' ) ) {
			$src = \remove_query_arg( 'ver', $src );
		}

		return $src;
	} );

	/**
	 * Add the feature to the plugin.
	 */
	\add_filter( 'style_loader_src', $feature->callback( 'asset_loader_src' ), 9999 );
	\add_filter( 'script_loader_src', $feature->callback( 'asset_loader_src' ), 9999 );
} );
