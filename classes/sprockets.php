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
 * @version    1.0
 * @author     Veselin Vasilev @vesselinv
 * @license    MIT License
 * @copyright  2013 Veselin Vasilev
 * @link       http://vesselinv.com/fuel-sprockets
 */

namespace Sprockets;

class Sprockets
{
	/**
	 * default instance
	 *
	 * @var  array
	 */
	protected static $_instance = null;

	/**
	 * All the Sprockets instances
	 *
	 * @var  array
	 */
	protected static $_instances = array();

	/**
	 * Default configuration values
	 *
	 * @var  array
	 */
	protected static $default_config = array();

	/**
	 * This is called automatically by the Autoloader.  It loads in the config
	 *
	 * @return  void
	 */
	public static function _init()
	{
		\Config::load('sprockets', true, false, true);

		static::$default_config = array(
			'asset_root_dir' 				=> APPPATH . 'assets/',
			'asset_compile_dir' 		=> DOCROOT . 'assets/',
			'cache_dir'							=> APPPATH . 'cache/sprockets/',
			'base_url'							=> \Uri::base(false),
			'js_dir' 								=> 'js/',
			'css_dir'								=> 'css/',
			'img_dir'								=> 'img/',
			'force_minify'					=> true
		);
	}

	/**
	 * Return a specific instance, or the default instance (is created if necessary)
	 *
	 * @param   string  instance name
	 * @return  Asset_Instance
	 */
	public static function instance($instance = null)
	{
		if ($instance !== null)
		{
			if ( ! array_key_exists($instance, static::$_instances))
			{
				return false;
			}

			return static::$_instances[$instance];
		}

		if (static::$_instance === null)
		{
			static::$_instance = static::forge();
		}

		return static::$_instance;
	}

	/**
	 * Gets a new instance of the Sprockets class.
	 *
	 * @param   string  instance name
	 * @param   array  $config  default config overrides
	 * @return  Sprockets_Instance
	 */
	public static function forge($name = 'default', array $config = array())
	{
		if ($exists = static::instance($name))
		{
			\Error::notice('Sprocket with this name already exists, cannot be overwritten.');
			return $exists;
		}

		static::$_instances[$name] = new Sprockets_Instance(array_merge(static::$default_config, \Config::get('sprockets'), $config));

		if ($name == 'default')
		{
			static::$_instance = static::$_instances[$name];
		}

		return static::$_instances[$name];
	}

	/**
	 * Render a javascript tag with the Sprockets packages
	 * @access  public
	 * @param   string filename
	 * @return  string sprockets_include_js_tag
	 */
	public static function js($file) {

		return static::instance()->js($file);
		
	}

	/**
	 * Render a css tag with the Sprockets packages
	 * @access  public
	 * @param   string filename
	 * @return  string sprockets_include_css_tag
	 */
	public static function css($file) {

		return static::instance()->css($file);

	}
}