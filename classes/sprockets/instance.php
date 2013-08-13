<?php

/**
 * Fuel Sprockets
 *
 * A smarter asset manager for FuelPHP closely following Sprockets/Asset Pipeline.
 * Provide directives inside your Js, Css, Less, Sass/Compass, CoffeeScript files
 * and Fuel-Sprockets will combine, compile and minify all, in order of importance,
 * and generate an include tag to the compiled sprockets file. Uses smart caching in
 * order to avoid unnecessary recompilation
 *
 * @package    Fuel Sprockets
 * @version    1.3
 * @author     Veselin Vasilev @vesselinv
 * @license    MIT License
 * @copyright  2013 Veselin Vasilev
 * @link       http://vesselinv.com/fuel-sprockets
 */

namespace Sprockets;

/**
 * The Sprockets Instance
 *
 * @package     Fuel Sprockets
 */

class Sprockets_Instance
{
	protected $cache_dir,		// The Sprockets Cache Directory
		$asset_root_dir,			// The Asset Directory
		$asset_compile_dir,		// Asset Compile Directory
		$js_dir,							// Javascripts subdirectory
		$css_dir,							// Stylesheets subdirectory
		$force_minify;				// Force Minify if Fuel::$env !== "production"

	protected $Parser;

	/**
	 * Parse the config and initialize the object instance
	 *
	 * @return  void
	 */
	public function __construct($config)
	{
		$this->cache_dir 					= $config['cache_dir'];
		$this->asset_root_dir 		= $config['asset_root_dir'];
		$this->asset_compile_dir 	= $config['asset_compile_dir'];
		$this->js_dir							= $config['js_dir'];
		$this->css_dir						= $config['css_dir'];
		$this->force_minify				= $config['force_minify'];
		$this->base_url 					= $config['base_url'];

		$this->Parser 						= new Sprockets_Parser($config);
	}

	/**
	 * Render a javascript tag with the Sprockets packages
	 * @access  public
	 * @param   string filename
	 * @return  string sprockets_include_js_tag
	 */
	public function js($file) {
		return $this->Parser->parse($file, $this->js_dir);
	}

	/**
	 * Render a css tag with the Sprockets packages
	 * @access  public
	 * @param   string filename
	 * @return  string sprockets_include_css_tag
	 */
	public function css($file) {
		return $this->Parser->parse($file, $this->css_dir);
	}
}

class SprocketsException extends \FuelException {}
