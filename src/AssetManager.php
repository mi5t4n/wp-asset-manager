<?php
/**
 * Asset Manager
 *
 * @since x.x.x
 *
 * @package Sagar\AssetManager
 */

namespace Sagar\AssetManager;

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
	public static function add( Asset $asset, Location $location, callable $callback = null, bool $register = false ) {
		$data = array(
			'asset'    => $asset,
			'location' => $location,
			'callback' => $callback,
			'register' => $register,
		);

		if ( $asset instanceof Script ) {
			self::$scripts[ $asset->get_handle() ][] = $data;
		} elseif ( $asset instanceof Style ) {
			self::$styles[ $asset->get_handle() ][] = $data;
		}

		if ( $register ) {
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
	 */
	public static function get( $handle, $type = AssetType::SCRIPT, $field = 'asset' ) {
		$asset = null;

		if ( AssetType::SCRIPT === $type ) {
			$asset = array_filter(
				self::$scripts,
				function( $script ) use ( $handle ) {
					return $handle === $script['asset']->get_handle();
				}
			);
		} elseif ( AssetType::STYLE === $type ) {
			$asset = array_filter(
				self::$styles,
				function( $style ) use ( $handle ) {
					return $handle === $style['asset']->get_handle();
				}
			);
		}

		if ( isset( $asset[ $field ] ) ) {
			return $asset[ $field ];
		} else {
			$asset;
		}
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
	 * @param Asset $asset
	 */
	public static function register( Asset $asset ) {
		// Bail early if the function doesn't exists.
		if ( ! ( function_exists( 'wp_register_script' ) && function_exists( 'wp_register_style' ) ) ) {
			return false;
		}

		if ( $asset instanceof Script ) {
			wp_register_script( $asset->get_handle(), $asset->get_src(), $asset->get_dependencies(), $asset->get_version(), $asset->get_in_footer() );
		} elseif ( $asset instanceof Style ) {
			wp_register_style( $asset->get_handle(), $asset->get_src(), $asset->get_dependencies(), $asset->get_version(), $asset->get_media() );
		}
	}

	/**
	 * Enqueue assets.
	 *
	 * @since x.x.x
	 *
	 * @param Asset $asset
	 */
	public static function enqueue( Asset $asset ) {
		// Bail early if the function doesn't exists.
		if ( ! ( function_exists( 'wp_enqueue_script' ) && function_exists( 'wp_enqueue_style' ) ) ) {
			return false;
		}

		if ( is_null( $asset['callback'] ) || ( is_callable( $asset['callback'] ) && call_user_func( $asset['callback'] ) ) ) {
			if ( $asset instanceof Script ) {
				wp_enqueue_script( $asset->get_handle(), $asset->get_src(), $asset->get_dependencies(), $asset->get_version(), $asset->get_in_footer() );
			} elseif ( $asset instanceof Style ) {
				wp_enqueue_style( $asset->get_handle(), $asset->get_src(), $asset->get_dependencies(), $asset->get_version(), $asset->get_media() );
			}
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
				return Location::FRONTEND === $script['location'];
			}
		);

		foreach ( $scripts as $script ) {
			self::enqueue( $script['asset'] );
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
				return Location::BACKEND === $script['location'];
			}
		);

		foreach ( $scripts as $script ) {
			self::enqueue( $script['asset'] );
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
				return Location::FRONTEND === $script['location'];
			}
		);

		foreach ( $styles as $style ) {
			self::enqueue( $style['asset'] );
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
				return Location::BACKEND === $style['location'];
			}
		);

		foreach ( $styles as $style ) {
			self::enqueue( $style['asset'] );
		}
	}
}
