<?php
/**
 * Custom Features functions.
 *
 * @package andrezrv\custom_features
 */

namespace andrezrv\custom_features;

/**
 * Get the custom feature manager instance.
 *
 * @return Custom_Feature_Manager
 */
function get_custom_feature_manager(): Custom_Feature_Manager {
	static $instance = null;

	if ( is_null( $instance ) ) {
		$instance = new Custom_Feature_Manager();
	}

	return $instance;
}

/**
 * Create a new custom feature instance.
 *
 * @param string $feature_name The name of the feature.
 *
 * @return Custom_Feature
 */
function make_custom_feature( string $feature_name ): Custom_Feature {
	return new Custom_Feature( get_custom_feature_manager(), $feature_name );
}
