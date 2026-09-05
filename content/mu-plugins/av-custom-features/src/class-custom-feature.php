<?php
/**
 * Custom Feature class file.
 *
 * This file defines the Custom_Feature class, which represents a custom
 * feature that can be added to the Custom_Feature_Manager.
 *
 * @package andrezrv\custom_features
 */

namespace andrezrv\custom_features;

/**
 * Custom Feature class.
 *
 * This class represents a custom feature that can be added to the
 * Custom_Feature_Manager.
 *
 * It allows setting and getting callbacks for specific hooks related to the
 * feature.
 */
class Custom_Feature {
	/**
	 * The Custom Feature Manager instance.
	 *
	 * @var Custom_Feature_Manager $custom_feature_manager
	 */
	protected Custom_Feature_Manager $custom_feature_manager;

	/**
	 * The name of the custom feature.
	 *
	 * @var string $feature_name
	 */
	protected string $feature_name;

	/**
	 * An array of callbacks associated with the custom feature.
	 *
	 * @var array $callbacks
	 */
	protected array $callbacks = array();

	/**
	 * Constructor for the Custom_Feature class.
	 *
	 * @param Custom_Feature_Manager $custom_feature_manager The custom feature manager instance.
	 * @param string                 $name                   The name of the custom feature.
	 */
	public function __construct( Custom_Feature_Manager $custom_feature_manager, string $name ) {
		$this->custom_feature_manager = $custom_feature_manager;
		$this->feature_name           = $name;

		$this->custom_feature_manager->add_feature( $name );
	}

	/**
	 * Add a callback to this custom feature.
	 *
	 * @param string   $handle   The hook name.
	 * @param callable $callback The callback function.
	 *
	 * @return void
	 */
	public function set_callback( string $handle, callable $callback ): void {
		$this->callbacks[ $handle ] = function ( ...$args ) use ( $handle, $callback ) {
			if ( ! $this->custom_feature_manager->is_feature_enabled( $this->feature_name ) ) {
				return $args[0] ?? null;
			}

			return $callback( ...$args );
		};
	}

	/**
	 * Get a specific callback associated with this custom feature.
	 *
	 * @param string $callback_name The callback name.
	 *
	 * @return callable
	 */
	public function get_callback( string $callback_name ): callable {
		return $this->callbacks[ $callback_name ];
	}

	/**
	 * Obtain a specific callback associated with this custom feature.
	 *
	 * @param string $callback_name The callback name.
	 *
	 * @return callable
	 */
	public function callback( string $callback_name ): callable {
		if ( ! $this->custom_feature_manager->is_feature_enabled( $this->feature_name ) ) {
			return fn() => null;
		}

		return $this->get_callback( $callback_name );
	}
}
