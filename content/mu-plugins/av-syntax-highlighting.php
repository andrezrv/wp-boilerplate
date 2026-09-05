<?php
/**
 * Syntax Highlighting
 *
 * Provide custom syntax highlighting for code blocks.
 *
 * @link              https://andrezrv.com
 * @since             1.0.0
 * @package           andrezrv\muplugins
 *
 * @wordpress-plugin
 * Plugin Name:       Syntax Highlighting
 * Description:       Provide custom syntax highlighting for code blocks.
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
	$feature = make_custom_feature( 'syntax_highlighting' );

	/**
	 * Set Atom One Dark theme for code blocks.
	 */
	$feature->set_callback( 'syntax_highlighting_code_block_style', fn() => 'atom-one-dark' );

	/**
	 * Hook callback to the filter.
	 */
	\add_filter( 'syntax_highlighting_code_block_style', $feature->callback( 'syntax_highlighting_code_block_style' ) );
} );
