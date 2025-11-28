<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Class WC_QD_Autoloader
 *
 * @since 1.0
 */
class WC_QD_Autoloader {

	/**
	 * The extension class prefix
	 */
	const PREFIX = 'WC_QD_';

	/**
	 * @var string The classes path
	 */
	private $path;

	/**
	 * @var string the file prefix
	 */
	private $file_prefix;

	/**
	 * The Constructor
	 */
	public function __construct() {
		$this->path        = plugin_dir_path( __FILE__ );
		$this->file_prefix = strtolower( str_replace( '_', '-', self::PREFIX ) );
	}

	/**
	 * Autoloader load method. Load the class.
	 *
	 * @param $class_name
	 */
	public function load( $class_name ) {

		// Only autoload our WooCommerce Extension classes
		if ( 0 === strpos( $class_name, self::PREFIX ) ) {

			// String to lower
			$class_name = strtolower( $class_name );

			// Remove the prefix for file name construction
			$class_suffix = str_ireplace( self::PREFIX, '', $class_name );

			// Validate class name contains only allowed characters (alphanumeric and underscores)
			if ( ! preg_match( '/^[a-z0-9_]+$/', $class_suffix ) ) {
				return;
			}

			// Format file name
			$file_name = 'class-' . $this->file_prefix . str_replace( '_', '-', $class_suffix ) . '.php';

			// Setup the file path
			$file_path = $this->path;

			if ( strpos( $class_name, 'wc_qd_request' ) === 0 ) {
				$file_path .= 'requests/';
			}

			// Append file name to class path
			$file_path .= $file_name;

			// Validate the file path is within the expected directory (prevent path traversal)
			$real_path = realpath( $file_path );
			$real_base_path = realpath( $this->path );

			if ( false === $real_path || false === $real_base_path || 0 !== strpos( $real_path, $real_base_path ) ) {
				return;
			}

			// Check & load file
			if ( file_exists( $file_path ) ) {
				require_once( $file_path );
			}

		}

	}

}