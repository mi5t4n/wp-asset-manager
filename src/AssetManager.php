<?php
/**
 * Asset Manager
 *
 * @since x.x.x
 *
 * @package Sagar\AssetManager
 */

namespace Sagar\AssetManager;

use PO;
use Sagar\AssetManager\Enums\Location;
use Sagar\AssetManager\Abstracts\Asset;
use Sagar\AssetManager\Enums\AssetType;

class AssetManager {

	/**
	 * Script assets.
	 *
	 * @since x.x.x
	 *
	 * @var array
	 */
	protected static $scripts = array();

	/**
	 * Style assets.
	 *
	 * @since x.x.x
	 *
	 * @var array
	 */
	protected static $styles = array();

	/**
	 * Add script or style.
	 *
	 * @since x.x.x
	 *
	 * @param \Sagar\AssetManager\Abstracts\Asset $asset
	 * @param \Sagar\AssetManager\Enums\Location $location
	 * @param callable $callback
	 * @param bool $register
	 */
	public static function add( Asset $asset ) {
		if ( $asset instanceof Script ) {
			self::$scripts[ $asset->get_handle() ][] = $asset;
		} elseif ( $asset instanceof Style ) {
			self::$styles[ $asset->get_handle() ][] = $asset;
		}

		if ( $asset->should_register() ) {
			self::register( $asset );
		}
	}

	/**
	 * Remove assets.
	 *
	 * @since x.x.x
	 *
	 * @param \Sagar\AssetManager\Abstracts\Asset|string $asset Asset object or handle.
	 */
	public static function remove( $asset ) {
		if ( is_string( $asset ) ) {
			self::remove_script( $asset );
			self::remove_style( $asset );
		} elseif ( $asset instanceof Script ) {
			self::remove_script( $asset->get_handle() );
		} elseif ( $asset instanceof Style ) {
			self::remove_style( $asset->get_handle() );
		}
	}

	/**
	 * Get assets.
	 *
	 * @since x.x.x
	 *
	 * @param string $handle
	 * @param string $type Asset type.
	 *
	 * @return \Sagar\AssetManager\Abstracts\Asset
	 */
	public static function get( $handle, $type = AssetType::SCRIPT ) {
		if ( AssetType::SCRIPT === $type ) {
			$asset = array_filter(
				self::$scripts,
				function( $script ) use ( $handle ) {
					return $handle === $script->get_handle();
				}
			);
		} elseif ( AssetType::STYLE === $type ) {
			$asset = array_filter(
				self::$styles,
				function( $style ) use ( $handle ) {
					return $handle === $style->get_handle();
				}
			);
		} else {
			$asset = null;
		}

		return $asset;
	}

	/**
	 * Remove script.
	 *
	 * @since x.x.x
	 *
	 * @param string $handle
	 */
	protected static function remove_script( $handle ) {
		if ( isset( self::$scripts[ $handle ] ) ) {
			unset( self::$scripts[ $handle ] );
		}
	}

	/**
	 * Remove style.
	 *
	 * @since x.x.x
	 *
	 * @param string $handle
	 */
	protected static function remove_style( $handle ) {
		if ( isset( self::$styles[ $handle ] ) ) {
			unset( self::$styles[ $handle ] );
		}
	}

	/**
	 * Register assets.
	 *
	 * @since x.x.x
	 *
	 * @param \Sagar\AssetManager\Abstracts\Asset $asset
	 *
	 * @return boolean Whether the script has been registered. True on success, false on failure.
	 */
	public static function register( Asset $asset ) {
		// Bail early if the function doesn't exists.
		if ( ! ( function_exists( 'wp_register_script' ) && function_exists( 'wp_register_style' ) ) ) {
			return false;
		}

		if ( ! is_string( $asset->get_callback() ) ) {
			return false;
		}

		$src = is_callable( $asset->get_src() ) ? call_user_func_array( $asset->get_src(), array( $asset ) ) : $asset->get_src();

		if ( $asset instanceof Script ) {
			$registered = wp_register_script( $asset->get_handle(), $src, $asset->get_dependencies(), $asset->get_version(), $asset->get_in_footer() );
		} elseif ( $asset instanceof Style ) {
			$registered = wp_register_style( $asset->get_handle(), $src, $asset->get_dependencies(), $asset->get_version(), $asset->get_media() );
		} else {
			$registered = false;
		}

		return $registered;
	}

	/**
	 * Enqueue assets.
	 *
	 * @since x.x.x
	 *
	 * @param \Sagar\AssetManager\Abstracts\Asset $asset
	 */
	public static function enqueue( Asset $asset ) {
		// Bail early if the function doesn't exists.
		if ( ! ( function_exists( 'wp_enqueue_script' ) && function_exists( 'wp_enqueue_style' ) ) ) {
			return false;
		}

		if ( is_callable( $asset->get_callback() ) && false === call_user_func_array( $asset->get_callback(), array( $asset ) ) ) {
			return false;
		}

		$src = is_callable( $asset->get_src() ) ? call_user_func_array( $asset->get_src(), array( $asset ) ) : $asset->get_src();

		if ( $asset instanceof Script ) {
			wp_enqueue_script( $asset->get_handle(), $src, $asset->get_dependencies(), $asset->get_version(), $asset->get_in_footer() );
		} elseif ( $asset instanceof Style ) {
			wp_enqueue_style( $asset->get_handle(), $src, $asset->get_dependencies(), $asset->get_version(), $asset->get_media() );
		}
	}

	/**
	 * Initialize asset manager.
	 *
	 * @since x.x.x
	 */
	public static function init() {
		if ( function_exists( 'add_action' ) ) {
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_frontend_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_backend_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_frontend_styles' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_backend_styles' ) );
		}
	}

	/**
	 * Enqueue frontend scripts.
	 *
	 * @since x.x.x
	 */
	public static function enqueue_frontend_scripts() {
		$scripts = array_filter(
			self::$scripts,
			function ( $script ) {
				return Location::FRONTEND === $script->get_location();
			}
		);

		foreach ( $scripts as $script ) {
			self::enqueue( $script );
		}
	}

	/**
	 * Enqueue backend scripts.
	 *
	 * @since x.x.x
	 */
	public static function enqueue_backend_scripts() {
		$scripts = array_filter(
			self::$scripts,
			function ( $script ) {
				return Location::BACKEND === $script->get_location();
			}
		);

		foreach ( $scripts as $script ) {
			self::enqueue( $script );
		}
	}

	/**
	 * Enqueue frontend styles.
	 *
	 * @since x.x.x
	 */
	public static function enqueue_frontend_styles() {
		$styles = array_filter(
			self::$styles,
			function ( $script ) {
				return Location::FRONTEND === $script->get_location();
			}
		);

		foreach ( $styles as $style ) {
			self::enqueue( $style );
		}
	}

	/**
	 * Enqueue backend styles.
	 *
	 * @since x.x.x
	 */
	public static function enqueue_backend_styles() {
		$styles = array_filter(
			self::$styles,
			function ( $style ) {
				return Location::BACKEND === $style->get_location();
			}
		);

		foreach ( $styles as $style ) {
			self::enqueue( $style );
		}
	}
}
