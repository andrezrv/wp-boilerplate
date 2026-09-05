<?php
/**
 * Custom Features Manager class.
 *
 * This class is used to manage the custom features that are implemented
 * and tracked through MU plugins.
 *
 * @package andrezrv\custom_features
 */

namespace andrezrv\custom_features;

/**
 * The custom features object.
 * Add or remove elements to a list of custom features to be implemented
 * and tracked through MU plugins.
 */
class Custom_Feature_Manager {
	/**
	 * The list of custom features.
	 *
	 * @var array
	 */
	protected array $features = [];

	/**
	 * The hook to use to add features.
	 *
	 * @var string
	 */
	public static string $hook = 'andrezrv/custom_features_init';

	/**
	 * Initialize the custom feature manager.
	 */
	public function __construct() {
		/**
		 * Allow components to add features to the list.
		 *
		 * @param \stdClass $custom_features
		 *
		 * @hook andrezrv/custom_features_init
		 */
		\do_action( self::$hook, $this );
	}

	/**
	 * Run a custom feature.
	 * If the feature is not enabled, return an empty function. Otherwise,
	 * return the callback. This is useful for MU plugins that may need to be
	 * disabled programmatically.
	 *
	 * @param string   $feature  The feature name.
	 * @param callable $callback The callback function to run if enabled.
	 *
	 * @return callable
	 */
	public function run( string $feature, callable $callback ): callable {
		if ( ! $this->is_feature_enabled( $feature ) ) {
			return fn() => null;
		}

		return $callback;
	}

	/**
	 * Add a feature to the list.
	 *
	 * @param string $feature The feature name.
	 * @param bool   $status  Whether the feature is enabled.
	 *
	 * @return void
	 */
	public function add_feature( string $feature, bool $status = true ): void {
		if ( isset( $this->features[ $feature ] ) ) {
			return;
		}

		$this->features[ $feature ] = $status;
	}

	/**
	 * Check if a feature is enabled.
	 *
	 * @param string $feature The feature name.
	 *
	 * @return bool
	 */
	public function is_feature_enabled( string $feature ): bool {
		return $this->features[ $feature ] ?? false;
	}

	/**
	 * Enable a feature.
	 *
	 * @param string $feature The feature name.
	 *
	 * @return void
	 */
	public function enable_feature( string $feature ): void {
		$this->features[ $feature ] = true;
	}

	/**
	 * Disable a feature.
	 *
	 * @param string $feature The feature name.
	 *
	 * @return void
	 */
	public function disable_feature( string $feature ): void {
		$this->features[ $feature ] = false;
	}
}
