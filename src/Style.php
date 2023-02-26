<?php

use Sagar\AssetManager\Abstracts\Asset;

/**
 * Style file.
 *
 * @since x.x.x
 *
 * @package Sagar\AssetManager
 */

namespace Sagar\AssetManager;

use Sagar\AssetManager\Abstracts\Asset;
use Sagar\AssetManager\Enums\Media;

class Style extends Asset {
	/**
	 * Style data.
	 *
	 * @since x.x.x
	 *
	 * @var array
	 */
	protected $extra_data = array(
		'media' => Media::ALL,
	);

	/**
	 * Constructor.
	 *
	 * @since x.x.x
	 *
	 * @param string $handle        Name of the style.
	 * @param string $src           Full URL of the style or path of the style relative to the WP root directory.
	 * @param array $dependencies   An array of registered style handles this style depends on.
	 * @param string $version       String specifying style version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version.
	 * @param string $media         The media for which this stylesheet has been defined.
	 */
	public function __construct( $handle, $src = '', $dependencies = array(), $version = '', $media = Media::ALL ) {
		$this->extra_data = array(
			'media' => $media,
		);

		parent::__construct( $handle, $src, $dependencies, $version );
	}

	/*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	*/

	/**
	 * Return style media.
	 *
	 * @since x.x.x
	 *
	 * @return string
	 */
	public function get_media() {
		return $this->__get( 'media ' );
	}

	/*
	|--------------------------------------------------------------------------
	| Setters
	|--------------------------------------------------------------------------
	*/

	/**
	 * Set media  of the style.
	 *
	 * @since x.x.x
	 *
	 * @param string $media Style media
	 *
	 * @return \Sagar\AssetManager\Style
	 */
	public function get_emdia( $media ) {
		$this->__set( 'media', $media );
	}
}
