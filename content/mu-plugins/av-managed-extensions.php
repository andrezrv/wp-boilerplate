<?php
/**
 * Managed Extensions.
 *
 * Marks plugins and themes installed via Composer as non-deletable in the admin.
 *
 * @link              https://andrezrv.com
 * @since             1.0.0
 * @package           andrezrv\muplugins
 *
 * @wordpress-plugin
 * Plugin Name:       Managed Extensions
 * Description:       Marks plugins and themes managed by Composer as non-deletable in the WordPress admin.
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
		$feature = make_custom_feature( 'managed_extensions' );

		/**
		 * A plugin is managed if its folder inside WP_PLUGIN_DIR is a symlink — those
		 * are created exclusively by bin/post-install pointing into content/managed/.
		 */
		$is_managed_plugin = function ( string $plugin_file ): bool {
			$folder = dirname( $plugin_file );
			return '.' !== $folder && is_link( WP_PLUGIN_DIR . '/' . $folder );
		};

		/**
		 * A theme is managed if its directory inside the theme root is a symlink.
		 */
		$is_managed_theme = function ( string $stylesheet ): bool {
			return is_link( get_theme_root() . '/' . $stylesheet );
		};

		/**
		 * Add a "Managed" filter tab to the plugins screen navigation.
		 */
		$feature->set_callback(
			'views_plugins',
			function ( array $views ) use ( $is_managed_plugin ): array {
				$count   = count( array_filter( array_keys( get_plugins() ), $is_managed_plugin ) );
				$current = isset( $_GET['plugin_status'] ) && 'managed' === $_GET['plugin_status']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				$views['managed'] = sprintf(
					'<a href="%s"%s>%s <span class="count">(%d)</span></a>',
					esc_url( admin_url( 'plugins.php?plugin_status=managed' ) ),
					$current ? ' class="current" aria-current="page"' : '',
					esc_html__( 'Managed', 'andrezrv-custom-features' ),
					$count
				);

				return $views;
			}
		);

		/**
		 * When the "Managed" tab is active, restrict the plugin list to managed plugins only.
		 */
		$feature->set_callback(
			'all_plugins',
			function ( array $plugins ) use ( $is_managed_plugin ): array {
				if ( ! isset( $_GET['plugin_status'] ) || 'managed' !== $_GET['plugin_status'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					return $plugins;
				}
				return array_filter( $plugins, fn( $file ) => $is_managed_plugin( $file ), ARRAY_FILTER_USE_KEY );
			}
		);

		/**
		 * Remove the "Delete" action link for managed plugins.
		 */
		$feature->set_callback(
			'plugin_action_links',
			function ( array $actions, string $plugin_file ) use ( $is_managed_plugin ): array {
				if ( $is_managed_plugin( $plugin_file ) ) {
					unset( $actions['delete'] );
				}
				return $actions;
			}
		);

		/**
		 * Add a "Managed via Composer" label in the plugin row meta.
		 */
		$feature->set_callback(
			'plugin_row_meta',
			function ( array $meta, string $plugin_file ) use ( $is_managed_plugin ): array {
				if ( $is_managed_plugin( $plugin_file ) ) {
					$meta[] = '<span class="av-managed-label"><span class="dashicons dashicons-lock" aria-hidden="true"></span> '
					. esc_html__( 'Managed via Composer — cannot be deleted here', 'andrezrv-custom-features' )
					. '</span>';
				}
				return $meta;
			}
		);

		/**
		 * Block deletion attempts that reach the server (e.g. direct URL access).
		 */
		$feature->set_callback(
			'admin_init',
			function () use ( $is_managed_plugin ): void {
				// Cover both the GET-based plugins-screen flow and the POST-based
				// wp_ajax_delete-plugin AJAX endpoint.
				// phpcs:disable WordPress.Security.NonceVerification -- defensive redirect; WP core enforces nonces before deletion proceeds.
				$action = sanitize_key( $_GET['action'] ?? $_POST['action'] ?? '' );

				if ( ! in_array( $action, array( 'delete-selected', 'delete', 'delete-plugin' ), true ) ) {
					return;
				}

				// phpcs:ignore Squiz.PHP.CommentedOutCode.Found -- explanatory comment, not commented-out code.
				// Single-plugin delete uses ?plugin= (GET) or $_POST['slug'] (AJAX); bulk uses ?checked[]= (GET).
				$candidates = array();
				if ( isset( $_GET['plugin'] ) ) {
					$candidates[] = sanitize_text_field( wp_unslash( $_GET['plugin'] ) );
				}
				if ( isset( $_POST['slug'] ) ) {
					$candidates[] = sanitize_text_field( wp_unslash( $_POST['slug'] ) );
				}
				if ( isset( $_GET['checked'] ) && is_array( $_GET['checked'] ) ) {
					foreach ( wp_unslash( $_GET['checked'] ) as $p ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
						$candidates[] = sanitize_text_field( wp_unslash( $p ) );
					}
				}
				// phpcs:enable WordPress.Security.NonceVerification

				foreach ( $candidates as $plugin_file ) {
					if ( $is_managed_plugin( $plugin_file ) ) {
						if ( wp_doing_ajax() ) {
							wp_send_json_error(
								array( 'message' => __( 'That plugin is managed via Composer and cannot be deleted from the WordPress admin. Remove it from composer.json instead.', 'andrezrv-custom-features' ) )
							);
						}
						wp_safe_redirect( admin_url( 'plugins.php?av_managed_delete_error=1' ) );
						exit;
					}
				}
			}
		);

		/**
		 * Show an admin notice when a managed-plugin deletion was blocked.
		 */
		$feature->set_callback(
			'admin_notices',
			function (): void {
				$screen = get_current_screen();
				if ( ! $screen || 'plugins' !== $screen->id ) {
					return;
				}
				if ( empty( $_GET['av_managed_delete_error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					return;
				}
				echo '<div class="notice notice-error"><p>'
				. esc_html__( 'That plugin is managed via Composer and cannot be deleted from the WordPress admin. Remove it from composer.json instead.', 'andrezrv-custom-features' )
				. '</p></div>';
			}
		);

		/**
		 * Remove the Delete action from managed themes in the JS theme data.
		 */
		$feature->set_callback(
			'wp_prepare_themes_for_js',
			function ( array $themes ) use ( $is_managed_theme ): array {
				foreach ( $themes as $stylesheet => &$theme ) {
					if ( $is_managed_theme( $stylesheet ) ) {
						unset( $theme['actions']['delete'] );
						$theme['av_managed'] = true;
					}
				}
				unset( $theme );
				return $themes;
			}
		);

		/**
		 * Last-resort: block any server-side theme deletion for managed themes.
		 */
		$feature->set_callback(
			'delete_theme',
			function ( string $stylesheet ) use ( $is_managed_theme ): void {
				if ( $is_managed_theme( $stylesheet ) ) {
					$message = __( 'This theme is managed via Composer and cannot be deleted from the WordPress admin. Remove it from composer.json instead.', 'andrezrv-custom-features' );
					if ( wp_doing_ajax() ) {
						wp_send_json_error( array( 'message' => $message ) );
					}
					wp_die(
						esc_html( $message ),
						esc_html__( 'Managed Theme', 'andrezrv-custom-features' ),
						array( 'back_link' => true )
					);
				}
			}
		);

		/**
		 * Inject CSS + JS for the admin UI indicators.
		 */
		$feature->set_callback(
			'admin_enqueue_scripts',
			function ( string $hook ): void {
				$css = '
			.av-managed-label {
				color: #646970;
				font-style: italic;
			}
			.av-managed-label .dashicons {
				font-size: 13px;
				width: 13px;
				height: 13px;
				vertical-align: text-bottom;
			}
            ';

				if ( 'themes.php' === $hook ) {
					$css .= '
				.theme-browser .theme.av-managed .av-managed-badge {
					display: inline-block;
					position: absolute;
					top: 8px;
					right: 8px;
					background: rgba(0,0,0,.65);
					color: #fff;
					font-size: 11px;
					line-height: 1;
					padding: 7px 7px 7px 21px;
					border-radius: 3px;
					pointer-events: none;
				}
				.theme-browser .theme.av-managed .av-managed-badge img {
					left: 7px;
					top: 6px;
				}
				.theme-browser .theme.av-managed .theme-screenshot::after {
					content: "";
					inset: 0;
					pointer-events: none;
				}
                ';

					// JS to add the .av-managed class and badge to theme cards.
					$js = "
			(function() {
				wp.themes.view.Themes = wp.themes.view.Themes || {};
				var origRender = wp.themes.view.Theme && wp.themes.view.Theme.prototype.render;
				if ( ! origRender ) { return; }

				wp.themes.view.Theme.prototype.render = function() {
					origRender.apply( this, arguments );
					if ( this.model && this.model.get( 'av_managed' ) ) {
						this.el.classList.add( 'av-managed' );
						var screenshot = this.el.querySelector( '.theme-screenshot' );
						if ( screenshot ) {
							var badge = document.createElement( 'span' );
							badge.className = 'av-managed-badge';
							badge.textContent = '🔒 Managed';
							screenshot.style.position = 'relative';
							screenshot.appendChild( badge );
						}
					}
				};
			})();
                ";

					wp_add_inline_script( 'theme', $js );
				}

				wp_add_inline_style( 'list-tables', $css );
			}
		);

		/**
		 * Add the feature callbacks to their respective hooks.
		 */
		\add_filter( 'views_plugins', $feature->callback( 'views_plugins' ) );
		\add_filter( 'all_plugins', $feature->callback( 'all_plugins' ) );
		\add_filter( 'plugin_action_links', $feature->callback( 'plugin_action_links' ), 10, 2 );
		\add_filter( 'plugin_row_meta', $feature->callback( 'plugin_row_meta' ), 9999, 2 );
		\add_action( 'admin_init', $feature->callback( 'admin_init' ) );
		\add_action( 'admin_notices', $feature->callback( 'admin_notices' ) );
		\add_filter( 'wp_prepare_themes_for_js', $feature->callback( 'wp_prepare_themes_for_js' ) );
		\add_action( 'delete_theme', $feature->callback( 'delete_theme' ), 1 );
		\add_action( 'admin_enqueue_scripts', $feature->callback( 'admin_enqueue_scripts' ) );
	}
);
