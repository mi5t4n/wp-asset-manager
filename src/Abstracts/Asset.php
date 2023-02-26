<?php
/**
 * Script file.
 *
 * @since x.x.x
 *
 * @package Sagar\AssetManager
 */

namespace Sagar\AssetManager\Abstracts;

abstract class Asset {
	/**
	 * Script data.
	 *
	 * @since x.x.x
	 *
	 * @var array
	 */
	protected $data = array(
		'handle'       => '',
		'src'          => '',
		'dependencies' => array(),
		'version'      => '',
	);

	/**
	 * Script extra data.
	 *
	 * @since x.x.x
	 *
	 * @var array
	 */
	protected $extra_data = array();


	/**
	 * Constructor.
	 *
	 * @since x.x.x
	 *
	 * @param string $handle        Name of the script.
	 * @param string $src           Full URL of the script or path of the script relative to the WP root directory.
	 * @param array $dependencies   An array of registered script handles this script depends on.
	 * @param string $version       String specifying script version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version.
	 */
	public function __construct( $handle, $src = '', $dependencies = array(), $version = '' ) {
		$this->data = array(
			'handle'       => $handle,
			'src'          => $src,
			'dependencies' => $dependencies,
			'version'      => $version,
		);

		$this->data = array_merge( $this->data, $this->extra_data );
	}

	/*
	|--------------------------------------------------------------------------
	| Magic methods
	|--------------------------------------------------------------------------
	*/

	/**
	 * Magic method set
	 *
	 * @since x.x.x
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set( $name, $value ) {
		if ( isset( $this->name ) ) {
			$this->data[ $name ] = $value;
		}
	}

	/**
	 * Magic method get
	 *
	 * @since x.x.x
	 *
	 * @param string $name
	 */
	public function __get( $name ) {
		if ( isset( $this->name ) ) {
			return $this->data[ $name ];
		}
	}

	/**
	 * Magic method isset
	 *
	 * @since x.x.x
	 *
	 * @param string $name
	 */
	public function __isset( $name ) {
		return isset( $this->data[ $name ] );
	}

	/**
	 * Magic method unset
	 *
	 * @since x.x.x
	 *
	 * @param string $name
	 */
	public function __unset( $name ) {
		if ( isset( $this->data[ $name ] ) ) {
			unset( $this->data[ $name ] );
		}
	}

	/**
	 * Magic method toString
	 *
	 * @since x.x.x
	 *
	 * @return string
	 */
	public function __toString() {
		return array_reduce(
			array_keys( $this->data ),
			function( $carry, $key ) {
				return $carry . ' ' . $key . '="' . htmlspecialchars( $this->data[ $key ] ) . '"';
			}
		);
	}

	/**
	 * Magic method debugInfo
	 *
	 * @since x.x.x
	 *
	 * @return string
	 */
	public function __debugInfo() {
		return array_reduce(
			array_keys( $this->data ),
			function( $carry, $key ) {
				return $carry . ' ' . $key . '="' . htmlspecialchars( $this->data[ $key ] ) . '"';
			}
		);
	}

	/*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	*/

	/**
	 * Return name/handle of the script.
	 *
	 * @since x.x.x
	 *
	 * @return string
	 */
	public function get_handle() {
		return $this->__get( 'handle ' );
	}

	/**
	 * Return src of the script.
	 *
	 * @since x.x.x
	 *
	 * @return string
	 */
	public function get_src() {
		return $this->__get( 'src ' );
	}

	/**
	 * Return dependencies of the script.
	 *
	 * @since x.x.x
	 *
	 * @return string
	 */
	public function get_dependencies() {
		return $this->__get( 'dependencies ' );
	}

	/**
	 * Return version of the script.
	 *
	 * @since x.x.x
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->__get( 'version ' );
	}

	/*
	|--------------------------------------------------------------------------
	| Setters
	|--------------------------------------------------------------------------
	*/

	/**
	 * Set name/handle of the script.
	 *
	 * @since x.x.x
	 *
	 * @param string $handle Name of the script.
	 *
	 * @return \Sagar\AssetManager\Script
	 */
	public function set_handle( $handle ) {
		$this->__set( 'handle', $handle );
	}

	/**
	 * Set src of the script.
	 *
	 * @since x.x.x
	 *
	 * @param string $src Name of the script.
	 *
	 * @return \Sagar\AssetManager\Script
	 */
	public function set_src( $src ) {
		$this->__set( 'src', $src );
	}

	/**
	 * Set dependencies of the script.
	 *
	 * @since x.x.x
	 *
	 * @param string $dependencies Name of the script.
	 *
	 * @return \Sagar\AssetManager\Script
	 */
	public function set_dependencies( $dependencies ) {
		$this->__set( 'dependencies', $dependencies );
	}

	/**
	 * Set version of the script.
	 *
	 * @since x.x.x
	 *
	 * @param string $version Name of the script.
	 *
	 * @return \Sagar\AssetManager\Script
	 */
	public function set_version( $version ) {
		$this->__set( 'version', $version );
	}
}
