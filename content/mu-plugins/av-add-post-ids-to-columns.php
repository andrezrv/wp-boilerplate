<?php
/**
 * Add ID Column To Posts Lists.
 *
 * Display the post ID in the posts list table.
 *
 * @link              https://andrezrv.com
 * @since             1.0.0
 * @package           andrezrv\muplugins
 *
 * @wordpress-plugin
 * Plugin Name:       Add ID Column To Posts Lists
 * Description:       Display the post ID in the posts list table.
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
	$feature = make_custom_feature( 'posts_columns_id' );

	/**
	 * Add ID to posts lists.
	 */
	$feature->set_callback( 'posts_columns_id', function ( array $columns ): array {
		$new_columns = [];

		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			// Insert the custom column right after the checkbox.
			if ( $key === 'cb' ) {
				$new_columns['wps_post_id'] = 'ID';
			}
		}

		return $new_columns;
	} );
	$feature->set_callback( 'posts_custom_id_columns', function( string $column_name, mixed $id ): void {
		if ( 'wps_post_id' === $column_name ) {
			echo $id;
		}
	} );
	$feature->set_callback( 'admin_head', function (): void {
		echo '<style>.column-wps_post_id { width: 7ch; }</style>';
	} );

	/**
	 * Add the feature to the plugin.
	 */
	\add_filter( 'manage_posts_columns', $feature->callback( 'posts_columns_id' ), 5 );
	\add_filter( 'manage_pages_columns', $feature->callback( 'posts_columns_id' ), 5 );
	\add_action( 'manage_posts_custom_column', $feature->callback( 'posts_custom_id_columns' ), 5, 2 );
	\add_action( 'manage_pages_custom_column', $feature->callback( 'posts_custom_id_columns' ), 5, 2 );
	\add_action( 'admin_head', $feature->callback( 'admin_head' ) );
} );
