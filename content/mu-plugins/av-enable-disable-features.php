<?php
/**
 * Enable/Disable Features.
 *
 * Enable or disable features based on environment and other conditions.
 *
 * @link              https://andrezrv.com
 * @since             1.0.0
 * @package           andrezrv\muplugins
 *
 * @wordpress-plugin
 * Plugin Name:       Enable/Disable Features
 * Description:       Enable or disable features based on environment and other conditions.
 * Version:           1.0.0
 * Author:            Andrés Villarreal
 * Author URI:        https://andrezrv.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       andrezrv-custom-features
 * Domain Path:       /
 */

use function andrezrv\custom_features\get_custom_feature_manager;

add_action(
	'muplugins_loaded',
	function () {
		$is_local_env       = 'local' === \wp_get_environment_type();
		$allows_local_email = ! defined( 'WP_MAIL_LOCAL' ) || WP_MAIL_LOCAL;

		$disable_features = [
			'wp_mail_override'                => fn() => ! $is_local_env || $allows_local_email,
			'recover_default_theme_directory' => fn() => ! $is_local_env,
			'remove_query_args_from_assets'   => fn() => ! $is_local_env,
			'deactivate_plugins'              => fn() => ! $is_local_env,
		];

		foreach ( $disable_features as $feature => $callback ) {
			if ( $callback() ) {
				get_custom_feature_manager()->disable_feature( $feature );
			}
		}
	},
	11
);
