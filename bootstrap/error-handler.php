<?php

use JetBrains\PhpStorm\NoReturn;

( function () {
	/**
	 * This class helps to handle error messages with more flexibility than the
	 * default WordPress behavior.
	 */
	$errorHandler = new class {
		/**
		 * Supported error types.
		 *
		 * @var array|string[]
		 */
		static private array $supported_error_types = [
			E_NOTICE            => 'E_NOTICE',
			E_WARNING           => 'E_WARNING',
			E_DEPRECATED        => 'E_DEPRECATED',
			E_USER_ERROR        => 'E_USER_ERROR',
			E_USER_NOTICE       => 'E_USER_NOTICE',
			E_USER_WARNING      => 'E_USER_WARNING',
			E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
			E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR'
		];

		/**
		 * Paths to be ignored in case errors are triggered within them.
		 *
		 * @var array
		 */
		private array $ignored_paths = [];

		/**
		 * Basic constructor.
		 */
		public function __construct() {
			// Introduce WP_DEBUG_DEPRECATED to help enable and disable
			// deprecation messages.
			if ( ! defined( 'WP_DEBUG_DEPRECATED' ) ) {
				define( 'WP_DEBUG_DEPRECATED', true );
			}
		}

		/**
		 * Initialize ignored paths.
		 *
		 * @return void
		 */
		private function initialize_ignored_paths(): void {
			$this->ignored_paths = [
				// Just a sample. Please don't use this path:
				// WP_CONTENT_DIR . '/mu-plugins/my-plugin/',
			];
		}

		/**
		 * Replace the default behavior of the wp_debug_mode() function.
		 *
		 * @see wp_debug_mode()
		 *
		 * @return self
		 */
		public function set_custom_wp_debug_mode(): self {
			$GLOBALS['wp_filter'] = [
				'enable_wp_debug_mode_checks' => [
					10 => [
						[
							'accepted_args' => 0,
							'function'      => function () {
								// Set up custom debugging behavior.
								$this->wp_debug_mode();

								// Disable default behavior.
								return false;
							},
						],
					],
				],
			];

			return $this;
		}

		/**
		 * Set up custom debugging behavior.
		 *
		 * @return self
		 */
		public function wp_debug_mode(): self {
			$this->initialize_ignored_paths();

			if ( WP_DEBUG ) {
				if ( WP_DEBUG_DEPRECATED ) {
					error_reporting( E_ALL );
				} else {
					error_reporting( E_ALL & ~E_DEPRECATED );
				}

				if ( WP_DEBUG_DISPLAY ) {
					ini_set( 'display_errors', 1 );
				} elseif ( null !== WP_DEBUG_DISPLAY ) {
					ini_set( 'display_errors', 0 );
				}

				if ( in_array( strtolower( (string) WP_DEBUG_LOG ), [ 'true', '1' ], true ) ) {
					$log_path = WP_CONTENT_DIR . '/debug.log';
				} elseif ( is_string( WP_DEBUG_LOG ) ) {
					$log_path = WP_DEBUG_LOG;
				} else {
					$log_path = false;
				}

				if ( $log_path ) {
					ini_set( 'log_errors', 1 );
					ini_set( 'error_log', $log_path );
				}
			} else {
				error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE | E_RECOVERABLE_ERROR );
			}

			/*
			 * The 'REST_REQUEST' check here is optimistic as the constant is most
			 * likely not set at this point even if it is in fact a REST request.
			 */
			if ( defined( 'XMLRPC_REQUEST' ) || defined( 'REST_REQUEST' ) || defined( 'MS_FILES_REQUEST' )
			     || ( defined( 'WP_INSTALLING' ) && WP_INSTALLING )
			     || wp_doing_ajax() || wp_is_json_request()
			) {
				ini_set( 'display_errors', 0 );
			}

			return $this;
		}

		/**
		 * Set up a custom error handler.
		 *
		 * @return self
		 */
		public function set_custom_error_handler(): self {
			set_error_handler( [ $this, 'error_handler' ] );

			return $this;
		}

		/**
		 * Set up a custom exception handler.
		 *
		 * @return self
		 */
		public function set_custom_exception_handler(): self {
			set_exception_handler( [ $this, 'exception_handler' ] );

			return $this;
		}

		/**
		 * Handle errors.
		 *
		 * @param int    $error_num
		 * @param string $error_message
		 * @param string $error_file
		 * @param int    $error_line
		 *
		 * @return bool
		 */
		public function error_handler( int $error_num, string $error_message, string $error_file, int $error_line ): bool {
			if ( ! WP_DEBUG ) {
				return false;
			}

			if ( ! ( error_reporting() && $error_num ) ) {
				return false;
			}

			if ( ! self::is_supported_error_type( $error_num ) ) {
				return false;
			}

			$is_fatal      = false;
			$error_type    = self::get_error_type_name( $error_num );
			$error_message = htmlspecialchars( $error_message );

			if ( $this->should_ignore_error( $error_num, $error_message, $error_file, $error_line ) ) {
				return true;
			}

			if ( WP_DEBUG_LOG ) {
				// Write the error to the default WordPress debug log file.
				error_log( sprintf( "%s: %s in %s on line %s\n", $error_type, $error_message, $error_file, $error_line ) );
			}

			if ( ! WP_DEBUG_DISPLAY ) {
				// Don't execute PHP internal error handler.
				return true;
			}

			echo '<pre>';

			switch ( $error_num ) {
				case E_ERROR:
				case E_USER_ERROR:
					$is_fatal = true;
					$message  = sprintf( "Fatal error: %s in %s on line %s\n", $error_message, $error_file, $error_line );
					echo $message;
					break;

				case E_WARNING:
				case E_USER_WARNING:
					$message = sprintf( "Warning: %s in %s on line %s\n", $error_message, $error_file, $error_line );
					echo $message;
					break;

				case E_NOTICE:
				case E_USER_NOTICE:
					$message = sprintf( "Notice: %s in %s on line %s\n", $error_message, $error_file, $error_line );
					echo $message;
					break;

				case E_DEPRECATED:
				case E_USER_DEPRECATED:
					$message = sprintf( "Deprecated: %s in %s on line %s\n", $error_message, $error_file, $error_line );
					echo $message;
					break;

				default:
					$message = sprintf( "%s: %s in %s on line %s\n", $error_type, $error_message, $error_file, $error_line );
					echo $message;
					break;
			}

			// Generate stack trace string
			$trace     = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
			$trace_str = "Stack trace:\n";

			// Loop through trace items, skipping the current error handler item
			$i = 0;
			foreach ( $trace as $step ) {
				if ( $i === 0 ) { $i++; continue; } // Skip self

				$file     = $step['file'] ?? '[internal function]';
				$line     = isset( $step['line'] ) ? ' (' . $step['line'] . ')' : '';
				$class    = isset( $step['class'] ) ? $step['class'] . $step['type'] : '';
				$function = $step['function'] ?? '';

				$trace_str .= sprintf( "  #%d %s%s: %s%s\n", $i, $file, $line, $class, $function );
				$i++;
			}

			// Write backtrace to the default WordPress debug log file.
			error_log( $message . $trace_str );

			echo $trace_str;
			echo '</pre>';

			// Exit if fatal error.
			if ( $is_fatal ) {
				exit;
			}

			// Don't execute PHP internal error handler.
			return true;
		}

		/**
		 * Handle exceptions.
		 *
		 * @param Throwable $throwable
		 *
		 * @void
		 */
		#[NoReturn]
		function exception_handler( Throwable $throwable ): void {
			if ( ! WP_DEBUG || ! WP_DEBUG_DISPLAY ) {
				return;
			}

			printf( "<b>Uncaught Exception</b>: %s in <b>%s</b> on line <b>%s</b><br />", $throwable->getMessage(), $throwable->getFile(), $throwable->getLine() );
			printf( nl2br( $throwable->getTraceAsString() ) );

			exit;
		}

		// Stop the self-return chain.
		public function close(): void {}

		/**
		 * Get the name of the error type from the error number.
		 *
		 * @param int $errorNum
		 *
		 * @return string
		 */
		private static function get_error_type_name( int $errorNum ): string {
			return self::$supported_error_types[ $errorNum ] ?? 'unknown';
		}

		/**
		 * Check if the error type is supported.
		 *
		 * @param int $errorNum
		 *
		 * @return bool
		 */
		private static function is_supported_error_type( int $errorNum ): bool {
			return ! empty( self::$supported_error_types[ $errorNum ] );
		}

		/**
		 * Check if an error should be ignored.
		 *
		 * @param int    $error_num
		 * @param string $error_message
		 * @param string $error_file
		 * @param int    $error_line
		 *
		 * @return bool
		 * @noinspection PhpUnusedParameterInspection
		 */
		private function should_ignore_error( int $error_num, string $error_message, string $error_file, int $error_line ): bool {
			return array_any( $this->ignored_paths, fn( $path ) => stripos( $error_file, $path ) !== false );
		}

	};

	$errorHandler
		->set_custom_wp_debug_mode()
		->set_custom_error_handler()
		->set_custom_exception_handler()
		->close();
} )();
