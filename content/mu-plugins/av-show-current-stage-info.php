<?php
/**
 * Show Current Stage Info
 *
 * Display information about the current environment in the admin toolbar.
 *
 * @link              https://andrezrv.com
 * @since             1.0.0
 * @package           andrezrv\muplugins
 *
 * @wordpress-plugin
 * Plugin Name:       Show Current Stage Info
 * Description:       Display information about the current environment in the admin toolbar.
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
		$feature = make_custom_feature( 'show_current_stage_info' );

		/**
		 * Show info for the current stage in the admin toolbar.
		 *
		 * @wp-hook admin_bar_menu
		 */
		$feature->set_callback(
			'admin_bar_menu',
			function ( $wp_admin_bar ) {
				$env  = \ucfirst( \wp_get_environment_type() );
				$args = array(
					'id'     => 'current-stage',
					'title'  => \sprintf( '%s Current Stage: %s', '<span class="dashicons dashicons-admin-site dashicons-before" style="padding: 7px 0; box-sizing: border-box;"></span>', $env ),
					'meta'   => array( 'class' => 'current-stage' ),
					'parent' => 'top-secondary',
				);

				$wp_admin_bar->add_node( $args );
			}
		);

		/**
		 * Hook callback to the action.
		 */
		\add_action( 'admin_bar_menu', $feature->callback( 'admin_bar_menu' ) );
	}
);
